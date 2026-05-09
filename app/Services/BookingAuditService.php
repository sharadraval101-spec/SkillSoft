<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRescheduleLog;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;

class BookingAuditService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function recordReschedule(
        Booking $booking,
        ?Slot $oldSlot,
        ?Slot $newSlot,
        ?Carbon $oldScheduledAt,
        ?Carbon $newScheduledAt,
        ?string $reason = null,
        ?User $actor = null,
        string $initiatedBy = 'system',
        string $trigger = 'system',
        array $meta = []
    ): void {
        BookingRescheduleLog::query()->create([
            'booking_id' => $booking->id,
            'provider_id' => $booking->provider_id,
            'customer_id' => $booking->customer_id,
            'actor_id' => $actor?->id,
            'old_slot_id' => $oldSlot?->id,
            'new_slot_id' => $newSlot?->id,
            'initiated_by' => $initiatedBy,
            'trigger' => $trigger,
            'old_scheduled_at' => $oldScheduledAt,
            'new_scheduled_at' => $newScheduledAt,
            'reason' => $reason,
            'meta' => $meta ?: null,
        ]);

        $this->auditLogService->log(
            'booking.rescheduled',
            $booking,
            $actor,
            [
                'slot_id' => $oldSlot?->id,
                'scheduled_at' => $oldScheduledAt?->toIso8601String(),
            ],
            [
                'slot_id' => $newSlot?->id,
                'scheduled_at' => $newScheduledAt?->toIso8601String(),
                'reason' => $reason,
                'initiated_by' => $initiatedBy,
                'trigger' => $trigger,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function recordCancellation(
        Booking $booking,
        array $oldValues,
        array $newValues,
        ?User $actor = null
    ): void {
        $this->auditLogService->log(
            'booking.cancelled',
            $booking,
            $actor,
            $oldValues,
            $newValues
        );
    }
}
