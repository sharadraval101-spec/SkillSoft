<?php

namespace App\Services;

use App\Jobs\BulkRescheduleProviderAppointmentsJob;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProviderUnavailabilityService
{
    public function __construct(
        private readonly ScheduleAvailabilityService $availabilityService,
        private readonly NotificationService $notificationService,
        private readonly BookingAuditService $bookingAuditService,
        private readonly AuditLogService $auditLogService
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateOperationalStatus(User $provider, array $payload, ?User $actor = null): ProviderProfile
    {
        return DB::transaction(function () use ($provider, $payload, $actor): ProviderProfile {
            $profile = ProviderProfile::query()
                ->where('user_id', $provider->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldValues = [
                'availability_status' => $profile->availability_status,
                'unavailable_from' => $profile->unavailable_from?->toIso8601String(),
                'unavailable_until' => $profile->unavailable_until?->toIso8601String(),
                'unavailability_reason' => $profile->unavailability_reason,
            ];

            $status = (string) ($payload['availability_status'] ?? ProviderProfile::AVAILABILITY_AVAILABLE);
            $reason = isset($payload['unavailability_reason'])
                ? trim((string) $payload['unavailability_reason'])
                : null;

            if ($status === ProviderProfile::AVAILABILITY_AVAILABLE) {
                $profile->update([
                    'availability_status' => ProviderProfile::AVAILABILITY_AVAILABLE,
                    'unavailable_from' => null,
                    'unavailable_until' => null,
                    'unavailability_reason' => null,
                ]);

                $this->auditLogService->log(
                    'provider.operational_status.updated',
                    $profile,
                    $actor,
                    $oldValues,
                    [
                        'availability_status' => $profile->availability_status,
                        'unavailable_from' => null,
                        'unavailable_until' => null,
                        'unavailability_reason' => null,
                    ]
                );

                return $profile->fresh();
            }

            [$unavailableFrom, $unavailableUntil] = $this->normalizeUnavailabilityWindow(
                $payload['unavailable_from'] ?? null,
                $payload['unavailable_until'] ?? null
            );

            $profile->update([
                'availability_status' => ProviderProfile::AVAILABILITY_UNAVAILABLE,
                'unavailable_from' => $unavailableFrom,
                'unavailable_until' => $unavailableUntil,
                'unavailability_reason' => $reason ?: null,
            ]);

            $this->auditLogService->log(
                'provider.operational_status.updated',
                $profile,
                $actor,
                $oldValues,
                [
                    'availability_status' => $profile->availability_status,
                    'unavailable_from' => $profile->unavailable_from?->toIso8601String(),
                    'unavailable_until' => $profile->unavailable_until?->toIso8601String(),
                    'unavailability_reason' => $profile->unavailability_reason,
                ]
            );

            BulkRescheduleProviderAppointmentsJob::dispatch(
                $provider->id,
                $unavailableFrom->toIso8601String(),
                $unavailableUntil->toIso8601String(),
                $reason,
                $actor?->id
            )->afterCommit();

            return $profile->fresh();
        });
    }

    public function bulkRescheduleForWindow(
        User $provider,
        Carbon $windowStart,
        Carbon $windowEnd,
        ?string $reason = null,
        ?User $actor = null
    ): int {
        $bookings = Booking::query()
            ->with([
                'customer:id,name,email',
                'service:id,name,branch_id,duration_minutes',
                'slot:id,schedule_id,provider_id,branch_id,start_at,end_at,is_available,reason',
            ])
            ->where('provider_id', $provider->id)
            ->where('scheduled_at', '>=', now())
            ->whereIn('status', Booking::slotBlockingStatuses())
            ->where('scheduled_at', '>=', $windowStart)
            ->where('scheduled_at', '<=', $windowEnd)
            ->orderBy('scheduled_at')
            ->get();

        $reservedSlotIds = [];
        $rescheduledCount = 0;

        foreach ($bookings as $booking) {
            $targetSlot = $this->findNextAvailableSlotForBooking(
                $provider,
                $booking,
                $windowEnd->copy()->addMinute(),
                $reservedSlotIds
            );

            if (!$targetSlot) {
                continue;
            }

            DB::transaction(function () use ($booking, $targetSlot, $reason, $actor, &$reservedSlotIds, &$rescheduledCount): void {
                $booking = Booking::query()
                    ->with([
                        'customer:id,name,email',
                        'service:id,name',
                        'slot:id,start_at,end_at,branch_id',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($booking->id);

                $oldSlot = Slot::query()->lockForUpdate()->findOrFail($booking->slot_id);
                $newSlot = Slot::query()->lockForUpdate()->findOrFail($targetSlot->id);

                if (!$newSlot->is_available) {
                    return;
                }

                $originalScheduledAt = $booking->scheduled_at?->copy();

                $booking->update([
                    'slot_id' => $newSlot->id,
                    'branch_id' => $newSlot->branch_id ?: $booking->branch_id,
                    'scheduled_at' => $newSlot->start_at,
                    'notes' => $this->appendUnavailabilityNote(
                        $booking->notes,
                        $originalScheduledAt,
                        $newSlot->start_at,
                        $reason
                    ),
                    'cancelled_at' => null,
                    'cancelled_by' => null,
                    'cancellation_reason' => null,
                ]);

                $newSlot->update([
                    'is_available' => false,
                    'reason' => null,
                ]);

                $this->refreshSlotAvailability($oldSlot);

                $freshBooking = $booking->fresh([
                    'customer:id,name,email',
                    'service:id,name',
                    'slot:id,start_at,end_at',
                ]);

                $this->bookingAuditService->recordReschedule(
                    $freshBooking,
                    $oldSlot,
                    $newSlot,
                    $originalScheduledAt,
                    $newSlot->start_at,
                    $reason,
                    $actor,
                    $actor && (int) $actor->role === User::ROLE_ADMIN ? 'admin' : 'provider',
                    'provider_unavailability_status'
                );

                $this->notificationService->notifyUser(
                    $freshBooking->customer_id,
                    'booking.rescheduled.provider_unavailability',
                    'Appointment Rescheduled Due to Provider Unavailability',
                    $this->buildRescheduleMessage($freshBooking, $originalScheduledAt, $reason),
                    [
                        'booking_id' => $freshBooking->id,
                        'booking_number' => $freshBooking->booking_number,
                        'old_schedule' => $originalScheduledAt?->format('d M Y, h:i A'),
                        'new_schedule' => $freshBooking->scheduled_at?->format('d M Y, h:i A'),
                        'reason' => $reason,
                    ],
                    sendEmailFallback: true,
                    sendSms: false,
                    sendWhatsapp: false
                );

                $reservedSlotIds[] = (string) $newSlot->id;
                $rescheduledCount++;
            });
        }

        return $rescheduledCount;
    }

    /**
     * @param  array<int, string>  $reservedSlotIds
     */
    private function findNextAvailableSlotForBooking(
        User $provider,
        Booking $booking,
        Carbon $earliestStart,
        array $reservedSlotIds
    ): ?Slot {
        /** @var Service|null $service */
        $service = $booking->service ?: Service::query()->find($booking->service_id);
        if (!$service) {
            return null;
        }

        $searchDays = max(7, (int) config('booking.auto_reschedule.search_days', 45));
        $branchId = $booking->branch_id ?: $service->branch_id;

        foreach (range(0, $searchDays) as $offset) {
            $date = $earliestStart->copy()->startOfDay()->addDays($offset);
            $slots = $this->availabilityService->generateAvailableSlotsForDate(
                $provider,
                $date,
                $branchId,
                $service
            );

            foreach ($slots as $slotData) {
                $slotStart = Carbon::parse((string) $slotData['start_at']);

                if ($slotStart->lt($earliestStart)) {
                    continue;
                }

                $slotId = (string) $slotData['slot_id'];
                if (in_array($slotId, $reservedSlotIds, true)) {
                    continue;
                }

                $slot = Slot::query()->find($slotId);
                if (!$slot || !$slot->is_available) {
                    continue;
                }

                if ($this->customerHasOverlap($booking, $slot)) {
                    continue;
                }

                return $slot;
            }
        }

        return null;
    }

    private function customerHasOverlap(Booking $booking, Slot $slot): bool
    {
        return Booking::query()
            ->join('slots as booked_slots', 'booked_slots.id', '=', 'bookings.slot_id')
            ->where('bookings.customer_id', $booking->customer_id)
            ->where('bookings.id', '!=', $booking->id)
            ->whereIn('bookings.status', Booking::slotBlockingStatuses())
            ->where('booked_slots.start_at', '<', $slot->end_at)
            ->where('booked_slots.end_at', '>', $slot->start_at)
            ->exists();
    }

    private function refreshSlotAvailability(Slot $slot): void
    {
        $isBooked = Booking::query()
            ->where('slot_id', $slot->id)
            ->whereIn('status', Booking::slotBlockingStatuses())
            ->exists();

        $slot->update([
            'is_available' => !$isBooked && $slot->start_at->gte(now()),
        ]);
    }

    /**
     * @return array{0:Carbon,1:Carbon}
     */
    private function normalizeUnavailabilityWindow(mixed $from, mixed $until): array
    {
        $normalizedFrom = $from
            ? Carbon::parse((string) $from)->startOfDay()
            : now()->startOfDay();
        $normalizedUntil = $until
            ? Carbon::parse((string) $until)->endOfDay()
            : $normalizedFrom->copy()->endOfDay();

        if ($normalizedUntil->lt($normalizedFrom)) {
            throw ValidationException::withMessages([
                'unavailable_until' => 'Unavailable end date must be after the start date.',
            ]);
        }

        return [$normalizedFrom, $normalizedUntil];
    }

    private function appendUnavailabilityNote(
        ?string $existingNotes,
        ?Carbon $oldScheduledAt,
        ?Carbon $newScheduledAt,
        ?string $reason
    ): ?string {
        if (!$oldScheduledAt || !$newScheduledAt) {
            return $existingNotes;
        }

        $note = '[Provider Unavailability Rescheduled] From '.$oldScheduledAt->format('d M Y, h:i A')
            .' to '.$newScheduledAt->format('d M Y, h:i A');

        if ($reason) {
            $note .= ' | Reason: '.trim($reason);
        }

        return $existingNotes ? $existingNotes.PHP_EOL.$note : $note;
    }

    private function buildRescheduleMessage(Booking $booking, ?Carbon $oldScheduledAt, ?string $reason): string
    {
        $message = 'Your appointment '.$booking->booking_number.' was rescheduled because the provider is unavailable.'
            .' Old schedule: '.($oldScheduledAt?->format('d M Y, h:i A') ?? 'N/A')
            .'. New schedule: '.($booking->scheduled_at?->format('d M Y, h:i A') ?? 'N/A').'.';

        if ($reason) {
            $message .= ' Reason: '.trim($reason).'.';
        }

        return $message;
    }
}
