<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleAvailabilityService $availabilityService)
    {
    }

    public function block(Request $request): JsonResponse
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();

        $data = $request->validate([
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'starts_at' => 'required|date|after_or_equal:now',
            'ends_at' => 'required|date|after:starts_at',
            'reason' => 'nullable|string|max:255',
        ]);

        $block = $this->availabilityService->blockDateRange(
            $provider,
            Carbon::parse($data['starts_at']),
            Carbon::parse($data['ends_at']),
            $data['branch_id'] ?? null,
            $data['reason'] ?? null
        );

        return response()->json([
            'message' => 'Schedule blocked successfully.',
            'data' => [
                'id' => $block->id,
                'provider_id' => $block->provider_id,
                'branch_id' => $block->branch_id,
                'starts_at' => $block->starts_at,
                'ends_at' => $block->ends_at,
                'reason' => $block->reason,
                'is_active' => (bool) $block->is_active,
            ],
        ], 201);
    }

    public function slots(Request $request): JsonResponse
    {
        /** @var \App\Models\User $provider */
        $provider = $request->user();

        $data = $request->validate([
            'date' => 'required|date',
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'service_id' => 'nullable|uuid',
        ]);

        $service = null;
        if (!empty($data['service_id'])) {
            $providerProfileId = $provider->providerProfile?->id;
            if (!$providerProfileId) {
                throw ValidationException::withMessages([
                    'service_id' => 'Provider profile is missing.',
                ]);
            }

            $service = Service::query()
                ->where('provider_profile_id', $providerProfileId)
                ->whereKey($data['service_id'])
                ->first();

            if (!$service) {
                throw ValidationException::withMessages([
                    'service_id' => 'Selected service does not belong to this provider.',
                ]);
            }
        }

        $slots = $this->availabilityService->generateAvailableSlotsForDate(
            $provider,
            Carbon::parse($data['date']),
            $data['branch_id'] ?? null,
            $service
        );

        return response()->json([
            'data' => [
                'date' => Carbon::parse($data['date'])->toDateString(),
                'branch_id' => $data['branch_id'] ?? null,
                'service_id' => $service?->id,
                'slots' => $slots,
            ],
        ]);
    }
}
