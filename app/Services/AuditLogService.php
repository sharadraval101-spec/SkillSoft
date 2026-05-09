<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(
        string $event,
        Model $auditable,
        ?User $actor = null,
        array $oldValues = [],
        array $newValues = []
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'event' => $event,
            'auditable_type' => $auditable::class,
            'auditable_id' => (string) $auditable->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'created_at' => now(),
        ]);
    }
}
