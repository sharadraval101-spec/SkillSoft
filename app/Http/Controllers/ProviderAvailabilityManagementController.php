<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProviderAvailabilityManagementController extends Controller
{
    public function index(): View
    {
        return view('provider.schedule.index', [
            'availabilityDataUrl' => route('provider.schedule.data'),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        /** @var User $provider */
        $provider = $request->user();

        $slots = Slot::query()
            ->with([
                'provider:id,name',
                'booking:id,slot_id,status',
            ])
            ->where('provider_id', $provider->id)
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => $slots->map(fn(Slot $slot): array => $this->toDataRow($slot)),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'is_blocked' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        [$startAt, $endAt] = $this->buildDateTimes($validated['date'], $validated['start_time'], $validated['end_time']);
        $this->ensureUniqueSlot($provider->id, $startAt, $endAt);

        $schedule = $this->resolveSchedule($provider, $startAt, $endAt);
        $isBlocked = (bool) $request->boolean('is_blocked');

        Slot::query()->create([
            'schedule_id' => $schedule->id,
            'provider_id' => $provider->id,
            'branch_id' => $provider->providerProfile?->branch_id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_available' => !$isBlocked,
            'reason' => $isBlocked ? ($validated['reason'] ?? null) : null,
        ]);

        return $this->successResponse($request, 'Availability slot created successfully.', 201);
    }

    public function update(Request $request, Slot $slot): JsonResponse|RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        $this->ensureSlotOwnership($provider->id, $slot);

        if ($this->hasActiveBooking($slot)) {
            return $this->errorResponse(
                $request,
                'This slot has an active booking and cannot be edited.',
                422
            );
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'is_blocked' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        [$startAt, $endAt] = $this->buildDateTimes($validated['date'], $validated['start_time'], $validated['end_time']);
        $this->ensureUniqueSlot($provider->id, $startAt, $endAt, $slot->id);

        $schedule = $this->resolveSchedule($provider, $startAt, $endAt);
        $isBlocked = (bool) $request->boolean('is_blocked');

        $slot->update([
            'schedule_id' => $schedule->id,
            'branch_id' => $provider->providerProfile?->branch_id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_available' => !$isBlocked,
            'reason' => $isBlocked ? ($validated['reason'] ?? null) : null,
        ]);

        return $this->successResponse($request, 'Availability slot updated successfully.');
    }

    public function toggleBlocked(Request $request, Slot $slot): JsonResponse|RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        $this->ensureSlotOwnership($provider->id, $slot);

        if ($this->hasActiveBooking($slot)) {
            return $this->errorResponse(
                $request,
                'This slot has an active booking and cannot be blocked or unblocked.',
                422
            );
        }

        $slot->is_available = !$slot->is_available;
        if ($slot->is_available) {
            $slot->reason = null;
        } elseif (!$slot->reason) {
            $slot->reason = 'Manually blocked';
        }
        $slot->save();

        $message = $slot->is_available
            ? 'Slot marked as available.'
            : 'Slot marked as blocked.';

        return $this->successResponse($request, $message);
    }

    public function destroy(Request $request, Slot $slot): JsonResponse|RedirectResponse
    {
        /** @var User $provider */
        $provider = $request->user();
        $this->ensureSlotOwnership($provider->id, $slot);

        if ($this->hasActiveBooking($slot)) {
            return $this->errorResponse(
                $request,
                'This slot has an active booking and cannot be deleted.',
                422
            );
        }

        $slot->delete();

        return $this->successResponse($request, 'Availability slot deleted successfully.');
    }

    private function toDataRow(Slot $slot): array
    {
        $isBlocked = !$slot->is_available;
        $isLocked = $this->hasActiveBooking($slot);

        return [
            'id' => $slot->id,
            'provider_id' => $slot->provider_id,
            'provider_name' => $slot->provider?->name ?? 'N/A',
            'date' => $slot->start_at?->format('d M Y') ?? '-',
            'date_input' => $slot->start_at?->format('Y-m-d') ?? '',
            'start_time' => $slot->start_at?->format('h:i A') ?? '-',
            'start_time_input' => $slot->start_at?->format('H:i') ?? '',
            'end_time' => $slot->end_at?->format('h:i A') ?? '-',
            'end_time_input' => $slot->end_at?->format('H:i') ?? '',
            'is_blocked' => $isBlocked,
            'blocked_label' => $isBlocked ? 'Blocked' : 'Available',
            'reason' => $slot->reason ? Str::limit($slot->reason, 80) : '-',
            'full_reason' => $slot->reason,
            'is_locked' => $isLocked,
            'created_at' => $slot->created_at?->format('d M Y, h:i A') ?? '-',
            'created_at_timestamp' => $slot->created_at?->timestamp ?? 0,
            'update_url' => route('provider.schedule.update', $slot),
            'toggle_block_url' => route('provider.schedule.toggle-block', $slot),
            'delete_url' => route('provider.schedule.destroy', $slot),
        ];
    }

    private function buildDateTimes(string $date, string $startTime, string $endTime): array
    {
        $startAt = Carbon::parse($date . ' ' . $startTime);
        $endAt = Carbon::parse($date . ' ' . $endTime);

        if ($endAt->lessThanOrEqualTo($startAt)) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be later than start time.',
            ]);
        }

        return [$startAt, $endAt];
    }

    private function ensureUniqueSlot(
        int $providerId,
        Carbon $startAt,
        Carbon $endAt,
        ?string $ignoreSlotId = null
    ): void {
        $exists = Slot::query()
            ->where('provider_id', $providerId)
            ->where('start_at', $startAt)
            ->where('end_at', $endAt)
            ->when($ignoreSlotId, fn($query) => $query->where('id', '!=', $ignoreSlotId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_time' => 'A slot already exists for this date and time range.',
            ]);
        }
    }

    private function resolveSchedule(User $provider, Carbon $startAt, Carbon $endAt): Schedule
    {
        $dayOfWeek = (int) $startAt->dayOfWeek;
        $startTime = $startAt->format('H:i:s');
        $endTime = $endAt->format('H:i:s');

        $existingSchedule = Schedule::query()
            ->where('provider_id', $provider->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->orderBy('start_time')
            ->first();

        if ($existingSchedule) {
            return $existingSchedule;
        }

        return Schedule::query()->create([
            'provider_id' => $provider->id,
            'branch_id' => $provider->providerProfile?->branch_id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startAt->format('H:i'),
            'end_time' => $endAt->format('H:i'),
            'slot_duration_minutes' => max(5, $startAt->diffInMinutes($endAt)),
            'buffer_minutes' => 0,
            'is_active' => true,
        ]);
    }

    private function hasActiveBooking(Slot $slot): bool
    {
        if ($slot->relationLoaded('booking')) {
            $booking = $slot->booking;
            if (!$booking) {
                return false;
            }

            return in_array($booking->status, Booking::activeStatuses(), true);
        }

        return $slot->booking()
            ->whereIn('status', Booking::activeStatuses())
            ->exists();
    }

    private function ensureSlotOwnership(int $providerId, Slot $slot): void
    {
        abort_unless((int) $slot->provider_id === $providerId, 403);
    }

    private function successResponse(
        Request $request,
        string $message,
        int $status = 200
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('success', $message);
    }

    private function errorResponse(
        Request $request,
        string $message,
        int $status = 422
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
