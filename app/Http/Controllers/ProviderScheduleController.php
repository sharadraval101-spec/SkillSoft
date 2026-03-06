<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Schedule;
use App\Models\ScheduleBlock;
use App\Models\Service;
use App\Services\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProviderScheduleController extends Controller
{
    public function __construct(private readonly ScheduleAvailabilityService $availabilityService)
    {
    }

    public function index(Request $request): View
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();
        $providerProfile = $provider->providerProfile;

        $request->validate([
            'date' => 'nullable|date',
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'service_id' => 'nullable|uuid',
        ]);

        $services = Service::query()
            ->where('provider_profile_id', $providerProfile?->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'branch_id']);

        $serviceIds = $services->pluck('id')->all();
        if ($request->filled('service_id') && !in_array($request->string('service_id')->toString(), $serviceIds, true)) {
            throw ValidationException::withMessages([
                'service_id' => 'Selected service does not belong to this provider.',
            ]);
        }

        $branchIds = collect([$providerProfile?->branch_id])
            ->filter()
            ->merge($services->pluck('branch_id')->filter())
            ->unique()
            ->values();

        $branches = Branch::query()
            ->where('is_active', true)
            ->when($branchIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $branchIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedDate = Carbon::parse((string) $request->query('date', now()->toDateString()))->startOfDay();
        $selectedBranchId = $request->filled('branch_id') ? $request->string('branch_id')->toString() : null;
        $selectedService = $services->firstWhere('id', $request->query('service_id'));

        $availableSlots = $this->availabilityService->generateAvailableSlotsForDate(
            $provider,
            $selectedDate,
            $selectedBranchId,
            $selectedService
        );

        $schedules = Schedule::query()
            ->with('branch:id,name')
            ->where('provider_id', $provider->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $activeBlocks = ScheduleBlock::query()
            ->with('branch:id,name')
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        return view('provider.schedule.index', [
            'schedules' => $schedules,
            'activeBlocks' => $activeBlocks,
            'branches' => $branches,
            'services' => $services,
            'selectedDate' => $selectedDate,
            'selectedBranchId' => $selectedBranchId,
            'selectedServiceId' => $selectedService?->id,
            'availableSlots' => $availableSlots,
            'weekDays' => $this->weekDays(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();

        $data = $request->validate([
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'slot_duration_minutes' => 'required|integer|min:5|max:480',
            'buffer_minutes' => 'nullable|integer|min:0|max:120',
            'is_active' => 'nullable|boolean',
        ]);

        $this->validateTimeRange($data['start_time'], $data['end_time']);

        Schedule::query()->create([
            'provider_id' => $provider->id,
            'branch_id' => $data['branch_id'] ?? null,
            'day_of_week' => (int) $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_duration_minutes' => (int) $data['slot_duration_minutes'],
            'buffer_minutes' => (int) ($data['buffer_minutes'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Availability schedule saved.');
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();
        $this->ensureOwnsSchedule($provider->id, $schedule);

        $data = $request->validate([
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'slot_duration_minutes' => 'required|integer|min:5|max:480',
            'buffer_minutes' => 'nullable|integer|min:0|max:120',
            'is_active' => 'nullable|boolean',
        ]);

        $this->validateTimeRange($data['start_time'], $data['end_time']);

        $schedule->update([
            'branch_id' => $data['branch_id'] ?? null,
            'day_of_week' => (int) $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'slot_duration_minutes' => (int) $data['slot_duration_minutes'],
            'buffer_minutes' => (int) ($data['buffer_minutes'] ?? 0),
            'is_active' => $request->boolean('is_active', false),
        ]);

        return back()->with('success', 'Availability schedule updated.');
    }

    public function destroy(Request $request, Schedule $schedule): RedirectResponse
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();
        $this->ensureOwnsSchedule($provider->id, $schedule);

        $schedule->delete();

        return back()->with('success', 'Availability schedule removed.');
    }

    public function block(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();

        $data = $request->validate([
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'starts_at' => 'required|date|after_or_equal:now',
            'ends_at' => 'required|date|after:starts_at',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->availabilityService->blockDateRange(
            $provider,
            Carbon::parse($data['starts_at']),
            Carbon::parse($data['ends_at']),
            $data['branch_id'] ?? null,
            $data['reason'] ?? null
        );

        return back()->with('success', 'Time range blocked successfully.');
    }

    public function unblock(Request $request, ScheduleBlock $scheduleBlock): RedirectResponse
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();
        $this->ensureOwnsBlock($provider->id, $scheduleBlock);

        $scheduleBlock->update(['is_active' => false]);

        return back()->with('success', 'Blocked range removed.');
    }

    private function weekDays(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    private function validateTimeRange(string $startTime, string $endTime): void
    {
        if ($endTime <= $startTime) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be later than start time.',
            ]);
        }
    }

    private function ensureOwnsSchedule(int $providerId, Schedule $schedule): void
    {
        if ((int) $schedule->provider_id !== $providerId) {
            abort(403);
        }
    }

    private function ensureOwnsBlock(int $providerId, ScheduleBlock $scheduleBlock): void
    {
        if ((int) $scheduleBlock->provider_id !== $providerId) {
            abort(403);
        }
    }
}
