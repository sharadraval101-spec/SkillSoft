<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ScheduleBlock;
use App\Models\Service;
use App\Models\ServiceVariant;
use App\Models\Slot;
use App\Models\User;
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
            $provider = $this->resolveProvider((int) $payload['provider_id']);
            $service = $this->resolveService((string) $payload['service_id'], $provider);
            $variant = $this->resolveVariant($payload['service_variant_id'] ?? null, $service);
            $slot = Slot::query()->lockForUpdate()->findOrFail($payload['slot_id']);

            $this->validateSlotForBooking($slot, $provider, $service, null);

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
            ->whereIn('status', Booking::activeStatuses())
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
            ->whereIn('status', Booking::activeStatuses())
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

    private function appendCancelReason(?string $existingNotes, ?string $reason): ?string
    {
        $reason = is_string($reason) ? trim($reason) : '';
        if ($reason === '') {
            return $existingNotes;
        }

        $prefix = '[Cancelled] '.$reason;
        return $existingNotes ? $existingNotes.PHP_EOL.$prefix : $prefix;
    }
}
