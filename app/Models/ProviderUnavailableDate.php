<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderUnavailableDate extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'provider_id',
        'block_date',
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'block_date' => 'date',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function isFullDay(): bool
    {
        return !$this->start_time && !$this->end_time;
    }
}

