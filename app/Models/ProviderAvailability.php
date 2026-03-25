<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderAvailability extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'provider_id',
        'day_of_week',
        'is_active',
        'start_time',
        'end_time',
        'break_start_time',
        'break_end_time',
        'slot_duration',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active' => 'boolean',
        'slot_duration' => 'integer',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}

