<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ProviderUnavailabilityService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkRescheduleProviderAppointmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $providerId,
        public readonly string $windowStart,
        public readonly string $windowEnd,
        public readonly ?string $reason = null,
        public readonly ?int $actorId = null
    ) {
    }

    public function handle(ProviderUnavailabilityService $providerUnavailabilityService): void
    {
        $provider = User::query()->find($this->providerId);
        if (!$provider) {
            return;
        }

        $actor = $this->actorId ? User::query()->find($this->actorId) : null;

        $providerUnavailabilityService->bulkRescheduleForWindow(
            $provider,
            Carbon::parse($this->windowStart),
            Carbon::parse($this->windowEnd),
            $this->reason,
            $actor
        );
    }
}
