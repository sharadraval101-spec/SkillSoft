<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ProviderAvailability;
use App\Models\ProviderUnavailableDate;
use App\Models\Schedule;
use App\Models\ScheduleBlock;
use App\Models\Service;
use App\Models\ServiceVariant;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function createBooking(User $customer, array $payload): Booking
    {
        return DB::transaction(function () use ($customer, $payload): Booking {
            $customer = User::query()->lockForUpdate()->findOrFail($customer->id);
            $this->ensureCustomerCanBook($customer);

            $provider = $this->resolveProvider((int) $payload['provider_id']);
            $service = $this->resolveService((string) $payload['service_id'], $provider);
            $variant = $this->resolveVariant($payload['service_variant_id'] ?? null, $service);
            $slot = Slot::query()->lockForUpdate()->findOrFail($payload['slot_id']);

            $this->validateSlotForBooking($slot, $provider, $service, null);
            $this->ensureActiveUpcomingBookingLimit($customer);
            $this->ensureNoDuplicateBooking($customer, $provider, $service, $slot);
            $this->ensureNoOverlappingBooking($customer, $slot);

            $booking = Booking::query()->create([
                'booking_number' => $this->generateBookingNumber(),
                'customer_id' => $customer->id,
                'provider_id' => $provider->id,
                'branch_id' => $slot->branch_id ?: $service->branch_id,
                'service_id' => $service->id,
                'service_variant_id' => $variant?->id,
                'slot_id' => $slot->id,
                'scheduled_at' => $slot->start_at,
                'status' => Booking::STATUS_PENDING,
                'notes' => $payload['notes'] ?? null,
            ]);

            $slot->update(['is_available' => false]);

            $freshBooking = $booking->fresh([
                'provider:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
            ]);

            $this->notificationService->notifyUser(
                $customer,
                'booking.created',
                'Booking Created',
                'Your booking '.$freshBooking->booking_number.' has been created.',
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            $this->notificationService->notifyUser(
                $provider,
                'booking.new_request',
                'New Booking Request',
                $customer->name.' booked '.$service->name.'.',
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                    'customer_id' => $customer->id,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            return $freshBooking;
        });
    }

    public function rescheduleBooking(User $customer, Booking $booking, string $newSlotId): Booking
    {
        return DB::transaction(function () use ($customer, $booking, $newSlotId): Booking {
            $booking = Booking::query()
                ->with(['service.providerProfile', 'provider'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $this->ensureBookingOwner($booking, $customer);
            $this->ensureCanReschedule($booking);

            $oldSlot = Slot::query()->lockForUpdate()->findOrFail($booking->slot_id);
            $newSlot = Slot::query()->lockForUpdate()->findOrFail($newSlotId);

            if ($oldSlot->id === $newSlot->id) {
                return $booking;
            }

            $provider = $this->resolveProvider((int) $booking->provider_id);
            $service = $this->resolveService((string) $booking->service_id, $provider);

            $this->validateSlotForBooking($newSlot, $provider, $service, $booking->id);
            $this->ensureNoDuplicateBooking($customer, $provider, $service, $newSlot, $booking->id);
            $this->ensureNoOverlappingBooking($customer, $newSlot, $booking->id);

            $booking->update([
                'slot_id' => $newSlot->id,
                'scheduled_at' => $newSlot->start_at,
                'status' => Booking::STATUS_PENDING,
                'cancelled_at' => null,
            ]);

            $newSlot->update(['is_available' => false]);
            $this->refreshSlotAvailability($oldSlot);

            $freshBooking = $booking->fresh([
                'provider:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
            ]);

            $this->notificationService->notifyUser(
                $customer,
                'booking.rescheduled',
                'Booking Rescheduled',
                'Your booking '.$freshBooking->booking_number.' has been rescheduled.',
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                    'new_slot_id' => $freshBooking->slot_id,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            $this->notificationService->notifyUser(
                $provider,
                'booking.rescheduled.by_customer',
                'Booking Rescheduled by Customer',
                $customer->name.' rescheduled booking '.$freshBooking->booking_number.'.',
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                    'new_slot_id' => $freshBooking->slot_id,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            return $freshBooking;
        });
    }

    public function rescheduleProviderBookingsForUnavailableDate(
        User $provider,
        ProviderUnavailableDate $blockedDate,
        string $targetDate,
        ?string $reason = null
    ): int {
        $sourceDate = Carbon::parse($blockedDate->block_date)->startOfDay();
        $targetDay = Carbon::parse($targetDate)->startOfDay();

        if (!$targetDay->gt($sourceDate)) {
            throw ValidationException::withMessages([
                'reschedule_to_date' => 'Reschedule date must be later than the blocked date.',
            ]);
        }

        $bookings = Booking::query()
            ->with([
                'customer:id,name,email',
                'service:id,name,branch_id,duration_minutes',
                'slot:id,schedule_id,provider_id,branch_id,start_at,end_at,is_available,reason',
            ])
            ->where('provider_id', $provider->id)
            ->where('scheduled_at', '>=', now())
            ->whereDate('scheduled_at', $sourceDate->toDateString())
            ->whereIn('status', Booking::slotBlockingStatuses())
            ->lockForUpdate()
            ->get()
            ->filter(fn (Booking $booking): bool => $this->bookingFallsWithinUnavailableWindow($booking, $blockedDate))
            ->sortBy(fn (Booking $booking): int => (int) ($booking->scheduled_at?->timestamp ?? 0))
            ->values();

        if ($bookings->isEmpty()) {
            return 0;
        }

        $targetSlots = [];
        $claimedTargetSlots = [];

        foreach ($bookings as $booking) {
            if (!$booking->slot || !$booking->slot->start_at || !$booking->slot->end_at) {
                throw ValidationException::withMessages([
                    'reschedule_to_date' => 'One or more appointments cannot be moved because the original slot is missing.',
                ]);
            }

            $targetStart = Carbon::parse($targetDay->toDateString().' '.$booking->slot->start_at->format('H:i:s'));
            $targetEnd = Carbon::parse($targetDay->toDateString().' '.$booking->slot->end_at->format('H:i:s'));
            $targetSlot = $this->resolveProviderRescheduleTargetSlot($provider, $booking, $targetStart, $targetEnd);
            $slotKey = (string) $targetSlot->id;

            if (isset($claimedTargetSlots[$slotKey])) {
                throw ValidationException::withMessages([
                    'reschedule_to_date' => 'Multiple appointments map to the same target slot. Please choose another date.',
                ]);
            }

            $claimedTargetSlots[$slotKey] = $booking->id;
            $targetSlots[$booking->id] = $targetSlot;
        }

        $rescheduledCount = 0;

        foreach ($bookings as $booking) {
            $oldSlot = Slot::query()->lockForUpdate()->findOrFail($booking->slot_id);
            $targetSlot = Slot::query()->lockForUpdate()->findOrFail($targetSlots[$booking->id]->id);

            $hasExistingBookingRecord = Booking::query()
                ->where('slot_id', $targetSlot->id)
                ->whereKeyNot($booking->id)
                ->exists();

            if ($hasExistingBookingRecord) {
                throw ValidationException::withMessages([
                    'reschedule_to_date' => 'One or more target slots already contain another booking record.',
                ]);
            }

            $originalScheduledAt = $booking->scheduled_at?->copy();

            $booking->update([
                'slot_id' => $targetSlot->id,
                'branch_id' => $targetSlot->branch_id ?: $booking->branch_id,
                'scheduled_at' => $targetSlot->start_at,
                'notes' => $this->appendProviderRescheduleNote($booking->notes, $originalScheduledAt, $targetSlot->start_at, $reason),
                'cancelled_at' => null,
            ]);

            $targetSlot->update([
                'is_available' => false,
                'reason' => null,
            ]);

            $this->refreshSlotAvailability($oldSlot);

            $freshBooking = $booking->fresh([
                'customer:id,name,email',
                'service:id,name',
                'slot:id,start_at,end_at',
            ]);

            $this->notificationService->notifyUser(
                $freshBooking->customer_id,
                'booking.rescheduled.by_provider',
                'Booking Rescheduled by Provider',
                $this->buildProviderRescheduleMessage($freshBooking, $reason),
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                    'old_scheduled_at' => $originalScheduledAt?->toIso8601String(),
                    'new_scheduled_at' => $freshBooking->scheduled_at?->toIso8601String(),
                    'reason' => $reason,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            $rescheduledCount++;
        }

        return $rescheduledCount;
    }

    public function acceptBookingByProvider(User $provider, Booking $booking): Booking
    {
        return DB::transaction(function () use ($provider, $booking): Booking {
            $booking = Booking::query()
                ->with([
                    'customer:id,name,email',
                    'service:id,name',
                    'serviceVariant:id,name',
                    'slot:id,start_at,end_at',
                ])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((int) $booking->provider_id !== (int) $provider->id) {
                abort(403);
            }

            if (!$this->canProviderAccept($booking)) {
                throw ValidationException::withMessages([
                    'booking' => 'Only pending upcoming bookings can be accepted.',
                ]);
            }

            $booking->update([
                'status' => Booking::STATUS_ACCEPTED,
                'cancelled_at' => null,
            ]);

            $freshBooking = $booking->fresh([
                'customer:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
            ]);

            $this->notificationService->notifyUser(
                $freshBooking->customer_id,
                'booking.accepted.by_provider',
                'Booking Accepted',
                'Your booking '.$freshBooking->booking_number.' has been accepted.',
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            return $freshBooking;
        });
    }

    public function rescheduleBookingByProvider(
        User $provider,
        Booking $booking,
        string $targetDate,
        ?string $reason = null
    ): Booking {
        return DB::transaction(function () use ($provider, $booking, $targetDate, $reason): Booking {
            $booking = Booking::query()
                ->with([
                    'customer:id,name,email',
                    'service:id,name,branch_id,duration_minutes',
                    'slot:id,schedule_id,provider_id,branch_id,start_at,end_at,is_available,reason',
                ])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((int) $booking->provider_id !== (int) $provider->id) {
                abort(403);
            }

            if (!$this->canProviderReschedule($booking)) {
                throw ValidationException::withMessages([
                    'booking' => 'This appointment cannot be rescheduled by the provider.',
                ]);
            }

            $oldSlot = Slot::query()->lockForUpdate()->findOrFail($booking->slot_id);

            if (!$oldSlot->start_at || !$oldSlot->end_at) {
                throw ValidationException::withMessages([
                    'booking' => 'The current appointment slot is missing scheduling details.',
                ]);
            }

            $booking->setRelation('slot', $oldSlot);

            $targetDay = Carbon::parse($targetDate)->startOfDay();
            $targetStart = Carbon::parse($targetDay->toDateString().' '.$oldSlot->start_at->format('H:i:s'));
            $targetEnd = Carbon::parse($targetDay->toDateString().' '.$oldSlot->end_at->format('H:i:s'));
            $targetSlot = $this->resolveProviderRescheduleTargetSlot($provider, $booking, $targetStart, $targetEnd);

            if ((string) $targetSlot->id === (string) $oldSlot->id) {
                return $booking;
            }

            $targetSlot = Slot::query()->lockForUpdate()->findOrFail($targetSlot->id);
            $originalScheduledAt = $booking->scheduled_at?->copy();

            $booking->update([
                'slot_id' => $targetSlot->id,
                'branch_id' => $targetSlot->branch_id ?: $booking->branch_id,
                'scheduled_at' => $targetSlot->start_at,
                'notes' => $this->appendProviderRescheduleNote($booking->notes, $originalScheduledAt, $targetSlot->start_at, $reason),
                'cancelled_at' => null,
            ]);

            $targetSlot->update([
                'is_available' => false,
                'reason' => null,
            ]);

            $this->refreshSlotAvailability($oldSlot);

            $freshBooking = $booking->fresh([
                'customer:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
            ]);

            $this->notificationService->notifyUser(
                $freshBooking->customer_id,
                'booking.rescheduled.by_provider',
                'Booking Rescheduled by Provider',
                $this->buildProviderRescheduleMessage($freshBooking, $reason),
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                    'old_scheduled_at' => $originalScheduledAt?->toIso8601String(),
                    'new_scheduled_at' => $freshBooking->scheduled_at?->toIso8601String(),
                    'reason' => $reason,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            return $freshBooking;
        });
    }

    public function cancelBooking(User $customer, Booking $booking, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($customer, $booking, $reason): Booking {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $this->ensureBookingOwner($booking, $customer);
            $this->ensureCanCancel($booking);

            $slot = Slot::query()->lockForUpdate()->findOrFail($booking->slot_id);

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes' => $this->appendCancelReason($booking->notes, $reason),
            ]);

            $this->refreshSlotAvailability($slot);

            $freshBooking = $booking->fresh([
                'provider:id,name,email',
                'service:id,name',
                'serviceVariant:id,name',
                'slot:id,start_at,end_at',
            ]);

            $this->notificationService->notifyUser(
                $customer,
                'booking.cancelled',
                'Booking Cancelled',
                'Your booking '.$freshBooking->booking_number.' has been cancelled.',
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            $this->notificationService->notifyUser(
                $freshBooking->provider_id,
                'booking.cancelled.by_customer',
                'Booking Cancelled by Customer',
                $customer->name.' cancelled booking '.$freshBooking->booking_number.'.',
                [
                    'booking_id' => $freshBooking->id,
                    'booking_number' => $freshBooking->booking_number,
                ],
                sendEmailFallback: true,
                sendSms: true,
                sendWhatsapp: true
            );

            return $freshBooking;
        });
    }

    public function canReschedule(Booking $booking): bool
    {
        if (!in_array($booking->status, Booking::activeStatuses(), true)) {
            return false;
        }

        $cutoffHours = max(0, (int) config('booking.rules.reschedule_cutoff_hours', 12));
        return now()->lt($booking->scheduled_at->copy()->subHours($cutoffHours));
    }

    public function canProviderAccept(Booking $booking): bool
    {
        if (!$booking->scheduled_at) {
            return false;
        }

        return $booking->status === Booking::STATUS_PENDING && $booking->scheduled_at->gte(now());
    }

    public function canProviderReschedule(Booking $booking): bool
    {
        if (!$booking->scheduled_at) {
            return false;
        }

        if (!in_array($booking->status, Booking::slotBlockingStatuses(), true)) {
            return false;
        }

        return $booking->scheduled_at->gt(now());
    }

    public function canCancel(Booking $booking): bool
    {
        if (!in_array($booking->status, Booking::activeStatuses(), true)) {
            return false;
        }

        $cutoffHours = max(0, (int) config('booking.rules.cancel_cutoff_hours', 2));
        return now()->lt($booking->scheduled_at->copy()->subHours($cutoffHours));
    }

    private function validateSlotForBooking(Slot $slot, User $provider, Service $service, ?string $ignoreBookingId): void
    {
        if ((int) $slot->provider_id !== (int) $provider->id) {
            throw ValidationException::withMessages([
                'slot_id' => 'Selected slot does not belong to the selected provider.',
            ]);
        }

        if ($service->branch_id && $slot->branch_id && $service->branch_id !== $slot->branch_id) {
            throw ValidationException::withMessages([
                'slot_id' => 'Selected slot does not match the service branch.',
            ]);
        }

        if (!$slot->is_available) {
            throw ValidationException::withMessages([
                'slot_id' => 'Selected slot is not available.',
            ]);
        }

        if ($slot->start_at->lt(now())) {
            throw ValidationException::withMessages([
                'slot_id' => 'You cannot book a past slot.',
            ]);
        }

        $minHours = max(0, (int) config('booking.window.min_hours_before', 2));
        $maxDays = max(1, (int) config('booking.window.max_days_ahead', 45));

        if ($slot->start_at->lt(now()->addHours($minHours))) {
            throw ValidationException::withMessages([
                'slot_id' => 'Selected slot is outside minimum booking window.',
            ]);
        }

        if ($slot->start_at->gt(now()->addDays($maxDays))) {
            throw ValidationException::withMessages([
                'slot_id' => 'Selected slot is outside maximum booking window.',
            ]);
        }

        $hasActiveBooking = Booking::query()
            ->where('slot_id', $slot->id)
            ->whereIn('status', Booking::slotBlockingStatuses())
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->exists();

        if ($hasActiveBooking) {
            throw ValidationException::withMessages([
                'slot_id' => 'Selected slot is already booked.',
            ]);
        }

        $isBlocked = ScheduleBlock::query()
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->where('starts_at', '<', $slot->end_at)
            ->where('ends_at', '>', $slot->start_at)
            ->where(function ($query) use ($slot) {
                $query->whereNull('branch_id');
                if ($slot->branch_id) {
                    $query->orWhere('branch_id', $slot->branch_id);
                }
            })
            ->exists();

        if ($isBlocked) {
            throw ValidationException::withMessages([
                'slot_id' => 'Selected slot is blocked by provider availability rules.',
            ]);
        }
    }

    private function ensureCanReschedule(Booking $booking): void
    {
        if (!in_array($booking->status, Booking::activeStatuses(), true)) {
            throw ValidationException::withMessages([
                'booking' => 'Only pending or accepted bookings can be rescheduled.',
            ]);
        }

        if (!$this->canReschedule($booking)) {
            throw ValidationException::withMessages([
                'booking' => 'Reschedule window has closed for this booking.',
            ]);
        }
    }

    private function ensureCanCancel(Booking $booking): void
    {
        if (!in_array($booking->status, Booking::activeStatuses(), true)) {
            throw ValidationException::withMessages([
                'booking' => 'Only pending or accepted bookings can be cancelled.',
            ]);
        }

        if (!$this->canCancel($booking)) {
            throw ValidationException::withMessages([
                'booking' => 'Cancel window has closed for this booking.',
            ]);
        }
    }

    private function resolveProvider(int $providerId): User
    {
        $provider = User::query()
            ->whereKey($providerId)
            ->where('role', User::ROLE_PROVIDER)
            ->where('is_active', true)
            ->whereHas('providerProfile', fn ($query) => $query->where('status', 'active'))
            ->first();

        if (!$provider) {
            throw ValidationException::withMessages([
                'provider_id' => 'Selected provider is invalid or inactive.',
            ]);
        }

        return $provider;
    }

    private function resolveService(string $serviceId, User $provider): Service
    {
        $service = Service::query()
            ->with('providerProfile:id,user_id,status')
            ->whereKey($serviceId)
            ->where('is_active', true)
            ->first();

        if (
            !$service ||
            !$service->providerProfile ||
            (int) $service->providerProfile->user_id !== (int) $provider->id ||
            $service->providerProfile->status !== 'active'
        ) {
            throw ValidationException::withMessages([
                'service_id' => 'Selected service is invalid for the chosen provider.',
            ]);
        }

        return $service;
    }

    private function resolveVariant(?string $variantId, Service $service): ?ServiceVariant
    {
        if (!$variantId) {
            return null;
        }

        $variant = ServiceVariant::query()
            ->whereKey($variantId)
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->first();

        if (!$variant) {
            throw ValidationException::withMessages([
                'service_variant_id' => 'Selected variant does not belong to this service.',
            ]);
        }

        return $variant;
    }

    private function ensureBookingOwner(Booking $booking, User $customer): void
    {
        if ((int) $booking->customer_id !== (int) $customer->id) {
            abort(403);
        }
    }

    private function refreshSlotAvailability(Slot $slot): void
    {
        $isBooked = Booking::query()
            ->where('slot_id', $slot->id)
            ->whereIn('status', Booking::slotBlockingStatuses())
            ->exists();

        $isBlocked = ScheduleBlock::query()
            ->where('provider_id', $slot->provider_id)
            ->where('is_active', true)
            ->where('starts_at', '<', $slot->end_at)
            ->where('ends_at', '>', $slot->start_at)
            ->where(function ($query) use ($slot) {
                $query->whereNull('branch_id');
                if ($slot->branch_id) {
                    $query->orWhere('branch_id', $slot->branch_id);
                }
            })
            ->exists();

        $slot->update([
            'is_available' => !$isBooked && !$isBlocked && $slot->start_at->gte(now()),
        ]);
    }

    private function generateBookingNumber(): string
    {
        do {
            $number = 'BK-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Booking::query()->where('booking_number', $number)->exists());

        return $number;
    }

    private function bookingFallsWithinUnavailableWindow(Booking $booking, ProviderUnavailableDate $blockedDate): bool
    {
        if (!$booking->slot || !$booking->slot->start_at || !$booking->slot->end_at) {
            return false;
        }

        if ($blockedDate->isFullDay()) {
            return true;
        }

        $date = Carbon::parse($blockedDate->block_date)->toDateString();
        $blockedStart = Carbon::parse($date.' '.$blockedDate->start_time);
        $blockedEnd = Carbon::parse($date.' '.$blockedDate->end_time);

        return $this->isRangeOverlapping($blockedStart, $blockedEnd, $booking->slot->start_at, $booking->slot->end_at);
    }

    private function resolveProviderRescheduleTargetSlot(
        User $provider,
        Booking $booking,
        Carbon $targetStart,
        Carbon $targetEnd
    ): Slot {
        $currentSlot = $booking->slot;
        $service = $booking->service ?: Service::query()->findOrFail($booking->service_id);
        $customer = $booking->customer ?: User::query()->findOrFail($booking->customer_id);
        $branchId = $currentSlot?->branch_id ?: $booking->branch_id ?: $service->branch_id;
        $slotDuration = max(1, $targetStart->diffInMinutes($targetEnd));

        $schedule = $this->resolveProviderScheduleForReschedule($provider, $branchId, $targetStart, $targetEnd, $slotDuration);
        $this->ensureProviderTargetWindowIsAvailable($provider, $branchId, $targetStart, $targetEnd);

        $slotQuery = Slot::query()
            ->where('provider_id', $provider->id)
            ->where('start_at', $targetStart)
            ->where('end_at', $targetEnd);

        if ($branchId) {
            $slotQuery->where('branch_id', $branchId);
        } else {
            $slotQuery->whereNull('branch_id');
        }

        $slot = $slotQuery->lockForUpdate()->first();

        if (!$slot) {
            $slot = Slot::query()->create([
                'schedule_id' => $schedule->id,
                'provider_id' => $provider->id,
                'branch_id' => $branchId,
                'start_at' => $targetStart,
                'end_at' => $targetEnd,
                'is_available' => true,
                'reason' => null,
            ]);
        } else {
            if (!$slot->is_available) {
                throw ValidationException::withMessages([
                    'reschedule_to_date' => 'One or more target slots are unavailable for the selected date.',
                ]);
            }

            if ((string) $slot->schedule_id !== (string) $schedule->id || (string) ($slot->branch_id ?? '') !== (string) ($branchId ?? '')) {
                $slot->schedule_id = $schedule->id;
                $slot->branch_id = $branchId;
                $slot->save();
            }
        }

        $hasExistingBookingRecord = Booking::query()
            ->where('slot_id', $slot->id)
            ->whereKeyNot($booking->id)
            ->exists();

        if ($hasExistingBookingRecord) {
            throw ValidationException::withMessages([
                'reschedule_to_date' => 'One or more target slots already belong to another booking.',
            ]);
        }

        if ($slot->start_at->lt(now())) {
            throw ValidationException::withMessages([
                'reschedule_to_date' => 'One or more target slots are already in the past.',
            ]);
        }

        if ($service->branch_id && $slot->branch_id && $service->branch_id !== $slot->branch_id) {
            throw ValidationException::withMessages([
                'reschedule_to_date' => 'One or more target slots do not match the booking branch.',
            ]);
        }

        $this->ensureNoDuplicateBooking($customer, $provider, $service, $slot, $booking->id);
        $this->ensureNoOverlappingBooking($customer, $slot, $booking->id);

        return $slot;
    }

    private function resolveProviderScheduleForReschedule(
        User $provider,
        ?string $branchId,
        Carbon $targetStart,
        Carbon $targetEnd,
        int $slotDuration
    ): Schedule {
        $dayOfWeek = (int) $targetStart->dayOfWeek;
        $date = $targetStart->toDateString();

        $providerAvailabilities = ProviderAvailability::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($providerAvailabilities->isNotEmpty()) {
            foreach ($providerAvailabilities as $availability) {
                if (!$availability->start_time || !$availability->end_time) {
                    continue;
                }

                $windowStart = Carbon::parse($date.' '.$availability->start_time);
                $windowEnd = Carbon::parse($date.' '.$availability->end_time);

                if ($targetStart->lt($windowStart) || $targetEnd->gt($windowEnd)) {
                    continue;
                }

                if ((int) $availability->slot_duration !== $slotDuration) {
                    continue;
                }

                $offset = $windowStart->diffInMinutes($targetStart, false);
                if ($offset < 0 || $offset % max(1, (int) $availability->slot_duration) !== 0) {
                    continue;
                }

                if ($availability->break_start_time && $availability->break_end_time) {
                    $breakStart = Carbon::parse($date.' '.$availability->break_start_time);
                    $breakEnd = Carbon::parse($date.' '.$availability->break_end_time);

                    if ($this->isRangeOverlapping($breakStart, $breakEnd, $targetStart, $targetEnd)) {
                        continue;
                    }
                }

                return $this->findOrCreateScheduleForReschedule(
                    $provider,
                    $branchId,
                    $dayOfWeek,
                    (string) $availability->start_time,
                    (string) $availability->end_time,
                    (int) $availability->slot_duration,
                    0
                );
            }

            throw ValidationException::withMessages([
                'reschedule_to_date' => 'The new date does not have a matching working slot for one or more appointments.',
            ]);
        }

        $schedules = Schedule::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->when(
                $branchId,
                function ($query) use ($branchId) {
                    $query->where(function ($innerQuery) use ($branchId) {
                        $innerQuery->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                },
                fn ($query) => $query->whereNull('branch_id')
            )
            ->orderBy('start_time')
            ->get();

        foreach ($schedules as $schedule) {
            $windowStart = Carbon::parse($date.' '.$schedule->start_time);
            $windowEnd = Carbon::parse($date.' '.$schedule->end_time);

            if ($targetStart->lt($windowStart) || $targetEnd->gt($windowEnd)) {
                continue;
            }

            $step = max(1, $slotDuration + (int) $schedule->buffer_minutes);
            $offset = $windowStart->diffInMinutes($targetStart, false);

            if ($offset < 0 || $offset % $step !== 0) {
                continue;
            }

            return $schedule;
        }

        throw ValidationException::withMessages([
            'reschedule_to_date' => 'The new date does not have a matching working slot for one or more appointments.',
        ]);
    }

    private function findOrCreateScheduleForReschedule(
        User $provider,
        ?string $branchId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        int $slotDuration,
        int $bufferMinutes
    ): Schedule {
        $normalizedStart = substr($startTime, 0, 5);
        $normalizedEnd = substr($endTime, 0, 5);

        $schedule = Schedule::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $normalizedStart)
            ->where('end_time', '>=', $normalizedEnd)
            ->when(
                $branchId,
                function ($query) use ($branchId) {
                    $query->where(function ($innerQuery) use ($branchId) {
                        $innerQuery->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                },
                fn ($query) => $query->whereNull('branch_id')
            )
            ->orderBy('start_time')
            ->first();

        if ($schedule) {
            return $schedule;
        }

        return Schedule::query()->create([
            'provider_id' => $provider->id,
            'branch_id' => $branchId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $normalizedStart,
            'end_time' => $normalizedEnd,
            'slot_duration_minutes' => max(5, $slotDuration),
            'buffer_minutes' => max(0, $bufferMinutes),
            'is_active' => true,
        ]);
    }

    private function ensureProviderTargetWindowIsAvailable(
        User $provider,
        ?string $branchId,
        Carbon $targetStart,
        Carbon $targetEnd
    ): void {
        $blockedDates = ProviderUnavailableDate::query()
            ->where('provider_id', $provider->id)
            ->whereDate('block_date', $targetStart->toDateString())
            ->get();

        $hasUnavailableDateConflict = $blockedDates->contains(function (ProviderUnavailableDate $blockedDate) use ($targetStart, $targetEnd) {
            if ($blockedDate->isFullDay()) {
                return true;
            }

            $date = $targetStart->toDateString();
            $blockedStart = Carbon::parse($date.' '.$blockedDate->start_time);
            $blockedEnd = Carbon::parse($date.' '.$blockedDate->end_time);

            return $this->isRangeOverlapping($blockedStart, $blockedEnd, $targetStart, $targetEnd);
        });

        if ($hasUnavailableDateConflict) {
            throw ValidationException::withMessages([
                'reschedule_to_date' => 'The selected reschedule date is blocked in provider availability.',
            ]);
        }

        $hasScheduleBlockConflict = ScheduleBlock::query()
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->where('starts_at', '<', $targetEnd)
            ->where('ends_at', '>', $targetStart)
            ->where(function ($query) use ($branchId) {
                $query->whereNull('branch_id');
                if ($branchId) {
                    $query->orWhere('branch_id', $branchId);
                }
            })
            ->exists();

        if ($hasScheduleBlockConflict) {
            throw ValidationException::withMessages([
                'reschedule_to_date' => 'The selected reschedule date is blocked for the provider schedule.',
            ]);
        }
    }

    private function isRangeOverlapping(
        Carbon $firstStart,
        Carbon $firstEnd,
        Carbon $secondStart,
        Carbon $secondEnd
    ): bool {
        return $firstStart->lt($secondEnd) && $firstEnd->gt($secondStart);
    }

    private function appendCancelReason(?string $existingNotes, ?string $reason): ?string
    {
        $reason = is_string($reason) ? trim($reason) : '';
        if ($reason === '') {
            return $existingNotes;
        }

        $prefix = '[Cancelled] '.$reason;
        return $existingNotes ? $existingNotes.PHP_EOL.$prefix : $prefix;
    }

    private function appendProviderRescheduleNote(
        ?string $existingNotes,
        ?Carbon $originalScheduledAt,
        ?Carbon $newScheduledAt,
        ?string $reason
    ): ?string {
        if (!$originalScheduledAt || !$newScheduledAt) {
            return $existingNotes;
        }

        $note = '[Provider Rescheduled] From '.$originalScheduledAt->format('d M Y, h:i A')
            .' to '.$newScheduledAt->format('d M Y, h:i A');

        $reason = is_string($reason) ? trim($reason) : '';
        if ($reason !== '') {
            $note .= ' | Reason: '.$reason;
        }

        return $existingNotes ? $existingNotes.PHP_EOL.$note : $note;
    }

    private function buildProviderRescheduleMessage(Booking $booking, ?string $reason): string
    {
        $message = 'Your booking '.$booking->booking_number.' has been moved to '
            .($booking->scheduled_at?->format('d M Y, h:i A') ?? 'a new time').'.';

        $reason = is_string($reason) ? trim($reason) : '';
        if ($reason !== '') {
            $message .= ' Reason: '.$reason.'.';
        }

        return $message;
    }

    private function ensureCustomerCanBook(User $customer): void
    {
        if (array_key_exists('is_active', $customer->getAttributes()) && !$customer->is_active) {
            throw ValidationException::withMessages([
                'user' => 'Your account is not eligible for booking at this time.',
            ]);
        }

        $hasVerificationColumn = array_key_exists('email_verified_at', $customer->getAttributes());
        if ($hasVerificationColumn && empty($customer->email_verified_at)) {
            throw ValidationException::withMessages([
                'user' => 'Please verify your account before creating a booking.',
            ]);
        }

        if (array_key_exists('is_verified', $customer->getAttributes()) && !$customer->getAttribute('is_verified')) {
            throw ValidationException::withMessages([
                'user' => 'Please verify your account before creating a booking.',
            ]);
        }
    }

    private function ensureActiveUpcomingBookingLimit(User $customer): void
    {
        $limit = max(1, (int) config('booking.rules.max_active_upcoming', 3));

        $activeUpcomingCount = Booking::query()
            ->where('customer_id', $customer->id)
            ->where('scheduled_at', '>', now())
            ->whereIn('status', Booking::upcomingLimitStatuses())
            ->count();

        if ($activeUpcomingCount >= $limit) {
            throw ValidationException::withMessages([
                'booking' => 'You already have the maximum allowed active upcoming bookings.',
            ]);
        }
    }

    private function ensureNoDuplicateBooking(
        User $customer,
        User $provider,
        Service $service,
        Slot $slot,
        ?string $ignoreBookingId = null
    ): void {
        $hasDuplicateBooking = Booking::query()
            ->where('customer_id', $customer->id)
            ->where('provider_id', $provider->id)
            ->where('service_id', $service->id)
            ->where('slot_id', $slot->id)
            ->whereDate('scheduled_at', $slot->start_at->toDateString())
            ->whereIn('status', Booking::upcomingLimitStatuses())
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->exists();

        if ($hasDuplicateBooking) {
            throw ValidationException::withMessages([
                'slot_id' => 'You already have a booking for this slot.',
            ]);
        }
    }

    private function ensureNoOverlappingBooking(User $customer, Slot $slot, ?string $ignoreBookingId = null): void
    {
        $hasOverlap = Booking::query()
            ->join('slots as booked_slots', 'booked_slots.id', '=', 'bookings.slot_id')
            ->where('bookings.customer_id', $customer->id)
            ->whereDate('bookings.scheduled_at', $slot->start_at->toDateString())
            ->whereIn('bookings.status', Booking::upcomingLimitStatuses())
            ->where('booked_slots.start_at', '<', $slot->end_at)
            ->where('booked_slots.end_at', '>', $slot->start_at)
            ->when($ignoreBookingId, fn ($query) => $query->where('bookings.id', '!=', $ignoreBookingId))
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'slot_id' => 'This slot overlaps with another active booking.',
            ]);
        }
    }
}
