<?php

namespace App\Http\Controllers;

use App\Http\Requests\Provider\StoreProviderUnavailableDateRequest;
use App\Http\Requests\Provider\UpdateProviderAvailabilityRequest;
use App\Models\Booking;
use App\Models\ProviderAvailability;
use App\Models\ProviderUnavailableDate;
use App\Models\Slot;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProviderAvailabilityController extends Controller
{
    public function __construct(
        private readonly ScheduleAvailabilityService $availabilityService,
        private readonly BookingService $bookingService
    ) {
    }

    public function index(Request $request): View
    {
        /** @var User $provider */
        $provider = $request->user();

        $availabilityByDay = ProviderAvailability::query()
            ->where('provider_id', $provider->id)
            ->get()
            ->keyBy('day_of_week');

        $dayOrder = [1, 2, 3, 4, 5, 6, 0];
        $dayLabels = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        $weeklyRows = collect($dayOrder)->map(function (int $day) use ($availabilityByDay, $dayLabels): array {
            /** @var ProviderAvailability|null $availability */
            $availability = $availabilityByDay->get($day);

            return [
                'day_of_week' => $day,
                'label' => $dayLabels[$day],
                'is_active' => (bool) ($availability?->is_active ?? false),
                'start_time' => $availability?->start_time ? substr((string) $availability->start_time, 0, 5) : null,
                'end_time' => $availability?->end_time ? substr((string) $availability->end_time, 0, 5) : null,
                'break_start_time' => $availability?->break_start_time ? substr((string) $availability->break_start_time, 0, 5) : null,
                'break_end_time' => $availability?->break_end_time ? substr((string) $availability->break_end_time, 0, 5) : null,
                'slot_duration' => (int) ($availability?->slot_duration ?? 30),
            ];
        })->values();

        $blockedDates = ProviderUnavailableDate::query()
            ->where('provider_id', $provider->id)
            ->latest('block_date')
            ->latest('start_time')
            ->paginate(10);

        return view('provider.availability.index', [
            'weeklyRows' => $weeklyRows,
            'blockedDates' => $blockedDates,
        ]);
    }

    public function weeklyData(Request $request): JsonResponse
    {
        /** @var User $provider */
        $provider = $request->user();

        $availabilityByDay = ProviderAvailability::query()
            ->where('provider_id', $provider->id)
            ->get()
            ->keyBy('day_of_week');

        $dayOrder = [1, 2, 3, 4, 5, 6, 0];
        $dayLabels = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        $data = collect($dayOrder)->map(function (int $day) use ($availabilityByDay, $dayLabels): array {
            /** @var ProviderAvailability|null $availability */
            $availability = $availabilityByDay->get($day);

            $isActive = (bool) ($availability?->is_active ?? false);
            $startTime = $this->formatTime($availability?->start_time);
            $endTime = $this->formatTime($availability?->end_time);
            $breakStartTime = $this->formatTime($availability?->break_start_time);
            $breakEndTime = $this->formatTime($availability?->break_end_time);

            return [
                'day_of_week' => $day,
                'day_label' => $dayLabels[$day],
                'is_active' => $isActive,
                'status_label' => $isActive ? 'Active' : 'Off',
                'working_window' => $isActive && $startTime && $endTime
                    ? $startTime.' - '.$endTime
                    : '-',
                'break_window' => !$isActive
                    ? '-'
                    : ($breakStartTime && $breakEndTime
                        ? $breakStartTime.' - '.$breakEndTime
                        : 'No break'),
                'slot_duration_label' => $isActive
                    ? (int) ($availability?->slot_duration ?? 30).' minutes'
                    : '-',
                'updated_at' => $availability?->updated_at?->format('d M Y, h:i A') ?? '-',
                'updated_at_timestamp' => $availability?->updated_at?->timestamp ?? 0,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
    public function saveWeekly(UpdateProviderAvailabilityRequest $request): RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        $days = $request->validated('days');

        DB::transaction(function () use ($provider, $days): void {
            for ($day = 0; $day <= 6; $day++) {
                $dayData = $days[$day] ?? [];
                $isActive = filter_var($dayData['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

                ProviderAvailability::query()->updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'is_active' => $isActive,
                        'start_time' => $isActive ? ($dayData['start_time'] ?? null) : null,
                        'end_time' => $isActive ? ($dayData['end_time'] ?? null) : null,
                        'break_start_time' => $isActive ? ($dayData['break_start_time'] ?? null) : null,
                        'break_end_time' => $isActive ? ($dayData['break_end_time'] ?? null) : null,
                        'slot_duration' => $isActive
                            ? (int) ($dayData['slot_duration'] ?? 30)
                            : 30,
                    ]
                );
            }
        });

        $this->markFutureUnbookedSlotsUnavailable($provider);

        return back()->with('success', 'Weekly availability saved successfully.');
    }
    public function storeBlockedDate(StoreProviderUnavailableDateRequest $request): RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        $data = $request->validated();
        $rescheduledCount = 0;
        $shouldReschedule = $request->filled('reschedule_to_date') || $request->boolean('reschedule_bookings');

        DB::transaction(function () use ($provider, $data, $shouldReschedule, &$rescheduledCount): void {
            $blockedDate = ProviderUnavailableDate::query()->create([
                'provider_id' => $provider->id,
                'block_date' => $data['block_date'],
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'reason' => $data['reason'] ?? null,
            ]);

            if ($shouldReschedule) {
                $rescheduledCount = $this->bookingService->rescheduleProviderBookingsForUnavailableDate(
                    $provider,
                    $blockedDate,
                    (string) $data['reschedule_to_date'],
                    $data['reason'] ?? null
                );
            }

            $this->applyUnavailableBlockToSlots($provider, $blockedDate);
        });

        $message = 'Blocked date/time added successfully.';

        if ($shouldReschedule) {
            $message = $rescheduledCount > 0
                ? 'Blocked date/time added and '.$rescheduledCount.' appointment'.($rescheduledCount === 1 ? ' was' : 's were').' rescheduled successfully.'
                : 'Blocked date/time added. No active appointments needed rescheduling.';
        }

        return back()->with('success', $message);
    }

    public function destroyBlockedDate(Request $request, ProviderUnavailableDate $providerUnavailableDate): RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        $this->ensureOwnsBlock($provider, $providerUnavailableDate);

        $date = Carbon::parse($providerUnavailableDate->block_date)->toDateString();
        $providerUnavailableDate->delete();

        $this->refreshSlotsForDate($provider, $date);

        return back()->with('success', 'Blocked date/time removed successfully.');
    }

    private function ensureOwnsBlock(User $provider, ProviderUnavailableDate $blockedDate): void
    {
        abort_unless((int) $blockedDate->provider_id === (int) $provider->id, 403);
    }

    private function markFutureUnbookedSlotsUnavailable(User $provider): void
    {
        Slot::query()
            ->where('provider_id', $provider->id)
            ->where('start_at', '>=', now())
            ->whereDoesntHave('booking', function ($query) {
                $query->whereIn('status', Booking::slotBlockingStatuses());
            })
            ->update(['is_available' => false]);
    }

    private function applyUnavailableBlockToSlots(User $provider, ProviderUnavailableDate $blockedDate): void
    {
        $date = Carbon::parse($blockedDate->block_date)->toDateString();
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        $slotsQuery = Slot::query()
            ->where('provider_id', $provider->id)
            ->where('start_at', '>=', now())
            ->where('start_at', '<', $dayEnd)
            ->where('end_at', '>', $dayStart)
            ->whereDoesntHave('booking', function ($query) {
                $query->whereIn('status', Booking::slotBlockingStatuses());
            });

        if (!$blockedDate->start_time || !$blockedDate->end_time) {
            $slotsQuery->update(['is_available' => false]);
            return;
        }

        $blockedStart = Carbon::parse($date.' '.$blockedDate->start_time);
        $blockedEnd = Carbon::parse($date.' '.$blockedDate->end_time);

        $slotsQuery
            ->where('start_at', '<', $blockedEnd)
            ->where('end_at', '>', $blockedStart)
            ->update(['is_available' => false]);
    }

    private function refreshSlotsForDate(User $provider, string $date): void
    {
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        Slot::query()
            ->where('provider_id', $provider->id)
            ->where('start_at', '<', $dayEnd)
            ->where('end_at', '>', $dayStart)
            ->where('start_at', '>=', now())
            ->whereDoesntHave('booking', function ($query) {
                $query->whereIn('status', Booking::slotBlockingStatuses());
            })
            ->update(['is_available' => false]);

        $this->availabilityService->generateAvailableSlotsForDate(
            $provider,
            Carbon::parse($date)->startOfDay()
        );
    }

    private function formatTime(?string $time): ?string
    {
        if (!$time) {
            return null;
        }

        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Throwable) {
            return null;
        }
    }
}
