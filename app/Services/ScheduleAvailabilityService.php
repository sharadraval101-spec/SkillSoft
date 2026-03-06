<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\ScheduleBlock;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleAvailabilityService
{
    /**
     * Generate and return bookable slots for a provider on a given date.
     */
    public function generateAvailableSlotsForDate(
        User $provider,
        Carbon|string $date,
        ?string $branchId = null,
        ?Service $service = null
    ): Collection {
        $date = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();
        $now = now();
        $dayOfWeek = (int) $date->dayOfWeek;

        $schedules = Schedule::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($innerQuery) use ($branchId) {
                    $innerQuery->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return collect();
        }

        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        $blocks = ScheduleBlock::query()
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->where('starts_at', '<', $dayEnd)
            ->where('ends_at', '>', $dayStart)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($innerQuery) use ($branchId) {
                    $innerQuery->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->get(['id', 'branch_id', 'starts_at', 'ends_at']);

        $bookedSlots = Slot::query()
            ->where('provider_id', $provider->id)
            ->where('start_at', '<', $dayEnd)
            ->where('end_at', '>', $dayStart)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereHas('booking', function ($query) {
                $query->whereIn('status', Booking::activeStatuses());
            })
            ->get(['id', 'branch_id', 'start_at', 'end_at']);

        $available = collect();
        $serviceDuration = $service?->duration_minutes ? (int) $service->duration_minutes : null;

        foreach ($schedules as $schedule) {
            $scheduleStart = Carbon::parse($date->format('Y-m-d').' '.$schedule->start_time);
            $scheduleEnd = Carbon::parse($date->format('Y-m-d').' '.$schedule->end_time);

            if ($scheduleEnd->lessThanOrEqualTo($scheduleStart)) {
                continue;
            }

            $duration = max(1, $serviceDuration ?: (int) $schedule->slot_duration_minutes);
            $stepMinutes = max(1, $duration + (int) $schedule->buffer_minutes);
            $slotCursor = $scheduleStart->copy();
            $effectiveBranchId = $schedule->branch_id ?: $branchId;

            while ($slotCursor->copy()->addMinutes($duration)->lessThanOrEqualTo($scheduleEnd)) {
                $slotStart = $slotCursor->copy();
                $slotEnd = $slotCursor->copy()->addMinutes($duration);
                $slotCursor->addMinutes($stepMinutes);

                if ($slotStart->lessThan($now)) {
                    continue;
                }

                $isBlocked = $this->hasBlockOverlap($blocks, $effectiveBranchId, $slotStart, $slotEnd);
                $isBooked = $this->hasBookedOverlap($bookedSlots, $effectiveBranchId, $slotStart, $slotEnd);
                $isAvailable = !$isBlocked && !$isBooked;

                $slot = Slot::query()->firstOrNew([
                    'schedule_id' => $schedule->id,
                    'provider_id' => $provider->id,
                    'branch_id' => $effectiveBranchId,
                    'start_at' => $slotStart,
                    'end_at' => $slotEnd,
                ]);

                $slot->is_available = $isAvailable;
                $slot->save();

                if (!$isAvailable) {
                    continue;
                }

                $available->push([
                    'slot_id' => $slot->id,
                    'schedule_id' => $schedule->id,
                    'branch_id' => $effectiveBranchId,
                    'start_at' => $slotStart->toIso8601String(),
                    'end_at' => $slotEnd->toIso8601String(),
                    'label' => $slotStart->format('h:i A').' - '.$slotEnd->format('h:i A'),
                ]);
            }
        }

        return $available
            ->unique(fn (array $slot) => ($slot['branch_id'] ?? 'none').'|'.$slot['start_at'].'|'.$slot['end_at'])
            ->sortBy('start_at')
            ->values();
    }

    public function blockDateRange(
        User $provider,
        Carbon|string $startsAt,
        Carbon|string $endsAt,
        ?string $branchId = null,
        ?string $reason = null
    ): ScheduleBlock {
        $startsAt = $startsAt instanceof Carbon ? $startsAt : Carbon::parse($startsAt);
        $endsAt = $endsAt instanceof Carbon ? $endsAt : Carbon::parse($endsAt);

        $block = ScheduleBlock::query()->create([
            'provider_id' => $provider->id,
            'branch_id' => $branchId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'reason' => $reason,
            'is_active' => true,
        ]);

        Slot::query()
            ->where('provider_id', $provider->id)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('start_at', '>=', now())
            ->where('start_at', '<', $endsAt)
            ->where('end_at', '>', $startsAt)
            ->whereDoesntHave('booking', function ($query) {
                $query->whereIn('status', Booking::activeStatuses());
            })
            ->update(['is_available' => false]);

        return $block;
    }

    private function hasBlockOverlap(
        Collection $blocks,
        ?string $branchId,
        Carbon $slotStart,
        Carbon $slotEnd
    ): bool {
        return $blocks->contains(function (ScheduleBlock $block) use ($branchId, $slotStart, $slotEnd) {
            $branchMatches = $block->branch_id === null || $block->branch_id === $branchId;
            $overlaps = $block->starts_at->lt($slotEnd) && $block->ends_at->gt($slotStart);

            return $branchMatches && $overlaps;
        });
    }

    private function hasBookedOverlap(
        Collection $bookedSlots,
        ?string $branchId,
        Carbon $slotStart,
        Carbon $slotEnd
    ): bool {
        return $bookedSlots->contains(function (Slot $bookedSlot) use ($branchId, $slotStart, $slotEnd) {
            $branchMatches = $branchId === null || $bookedSlot->branch_id === $branchId;
            $overlaps = $bookedSlot->start_at->lt($slotEnd) && $bookedSlot->end_at->gt($slotStart);

            return $branchMatches && $overlaps;
        });
    }
}
