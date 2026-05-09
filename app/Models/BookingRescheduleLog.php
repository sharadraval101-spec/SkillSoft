<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRescheduleLog extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'booking_id',
        'provider_id',
        'customer_id',
        'actor_id',
        'old_slot_id',
        'new_slot_id',
        'initiated_by',
        'trigger',
        'old_scheduled_at',
        'new_scheduled_at',
        'reason',
        'meta',
    ];

    protected $casts = [
        'old_scheduled_at' => 'datetime',
        'new_scheduled_at' => 'datetime',
        'meta' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function oldSlot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'old_slot_id');
    }

    public function newSlot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'new_slot_id');
    }
}
