<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\ScheduleBlock;
use App\Models\Booking;
use App\Models\ProviderAvailability;
use App\Models\ProviderUnavailableDate;
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
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        $workingWindows = $this->resolveWorkingWindows($provider, $dayOfWeek, $branchId, $service);
        if ($workingWindows->isEmpty()) {
            return collect();
        }

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

        $unavailableDates = ProviderUnavailableDate::query()
            ->where('provider_id', $provider->id)
            ->whereDate('block_date', $date->toDateString())
            ->get(['id', 'block_date', 'start_time', 'end_time']);

        if ($unavailableDates->contains(fn (ProviderUnavailableDate $blocked) => $blocked->isFullDay())) {
            Slot::query()
                ->where('provider_id', $provider->id)
                ->where('start_at', '<', $dayEnd)
                ->where('end_at', '>', $dayStart)
                ->where('start_at', '>=', now())
                ->whereDoesntHave('booking', function ($query) {
                    $query->whereIn('status', Booking::slotBlockingStatuses());
                })
                ->update(['is_available' => false]);

            return collect();
        }

        $bookedSlots = Slot::query()
            ->where('provider_id', $provider->id)
            ->where('start_at', '<', $dayEnd)
            ->where('end_at', '>', $dayStart)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereHas('booking', function ($query) {
                $query->whereIn('status', Booking::slotBlockingStatuses());
            })
            ->get(['id', 'branch_id', 'start_at', 'end_at']);

        $available = collect();

        foreach ($workingWindows as $window) {
            if (empty($window['start_time']) || empty($window['end_time'])) {
                continue;
            }

            $scheduleStart = Carbon::parse($date->format('Y-m-d').' '.$window['start_time']);
            $scheduleEnd = Carbon::parse($date->format('Y-m-d').' '.$window['end_time']);

            if ($scheduleEnd->lessThanOrEqualTo($scheduleStart)) {
                continue;
            }

            $duration = max(1, (int) $window['slot_duration_minutes']);
            $stepMinutes = max(1, $duration + (int) $window['buffer_minutes']);
            $slotCursor = $scheduleStart->copy();
            $effectiveBranchId = $window['branch_id'] ?: $branchId;

            $breakStart = null;
            $breakEnd = null;
            if (!empty($window['break_start_time']) && !empty($window['break_end_time'])) {
                $breakStart = Carbon::parse($date->format('Y-m-d').' '.$window['break_start_time']);
                $breakEnd = Carbon::parse($date->format('Y-m-d').' '.$window['break_end_time']);
            }

            while ($slotCursor->copy()->addMinutes($duration)->lessThanOrEqualTo($scheduleEnd)) {
                $slotStart = $slotCursor->copy();
                $slotEnd = $slotCursor->copy()->addMinutes($duration);
                $slotCursor->addMinutes($stepMinutes);

                if ($slotStart->lessThan($now)) {
                    continue;
                }

                if ($breakStart && $breakEnd && $this->isRangeOverlapping($breakStart, $breakEnd, $slotStart, $slotEnd)) {
                    continue;
                }

                if ($this->hasUnavailableDateOverlap($unavailableDates, $slotStart, $slotEnd)) {
                    continue;
                }

                $isBlocked = $this->hasBlockOverlap($blocks, $effectiveBranchId, $slotStart, $slotEnd);
                $isBooked = $this->hasBookedOverlap($bookedSlots, $effectiveBranchId, $slotStart, $slotEnd);
                $isAvailable = !$isBlocked && !$isBooked;

                $slot = Slot::query()->firstOrNew([
                    'schedule_id' => $window['schedule_id'],
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
                    'schedule_id' => $window['schedule_id'],
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
                $query->whereIn('status', Booking::slotBlockingStatuses());
            })
            ->update(['is_available' => false]);

        return $block;
    }

    private function resolveWorkingWindows(
        User $provider,
        int $dayOfWeek,
        ?string $branchId,
        ?Service $service
    ): Collection {
        $providerAvailabilities = ProviderAvailability::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($providerAvailabilities->isNotEmpty()) {
            return $providerAvailabilities->map(function (ProviderAvailability $availability) {
                return [
                    'schedule_id' => null,
                    'branch_id' => null,
                    'start_time' => $availability->start_time,
                    'end_time' => $availability->end_time,
                    'break_start_time' => $availability->break_start_time,
                    'break_end_time' => $availability->break_end_time,
                    'slot_duration_minutes' => (int) $availability->slot_duration,
                    'buffer_minutes' => 0,
                ];
            });
        }

        $serviceDuration = $service?->duration_minutes ? (int) $service->duration_minutes : null;

        return Schedule::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($innerQuery) use ($branchId) {
                    $innerQuery->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->orderBy('start_time')
            ->get()
            ->map(function (Schedule $schedule) use ($serviceDuration): array {
                return [
                    'schedule_id' => $schedule->id,
                    'branch_id' => $schedule->branch_id,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'break_start_time' => null,
                    'break_end_time' => null,
                    'slot_duration_minutes' => max(1, $serviceDuration ?: (int) $schedule->slot_duration_minutes),
                    'buffer_minutes' => (int) $schedule->buffer_minutes,
                ];
            });
    }

    private function hasUnavailableDateOverlap(
        Collection $unavailableDates,
        Carbon $slotStart,
        Carbon $slotEnd
    ): bool {
        return $unavailableDates->contains(function (ProviderUnavailableDate $blockedDate) use ($slotStart, $slotEnd) {
            if ($blockedDate->isFullDay()) {
                return true;
            }

            $date = $slotStart->format('Y-m-d');
            $blockedStart = Carbon::parse($date.' '.$blockedDate->start_time);
            $blockedEnd = Carbon::parse($date.' '.$blockedDate->end_time);

            return $this->isRangeOverlapping($blockedStart, $blockedEnd, $slotStart, $slotEnd);
        });
    }

    private function isRangeOverlapping(
        Carbon $firstStart,
        Carbon $firstEnd,
        Carbon $secondStart,
        Carbon $secondEnd
    ): bool {
        return $firstStart->lt($secondEnd) && $firstEnd->gt($secondStart);
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
