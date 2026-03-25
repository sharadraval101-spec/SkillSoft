<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'booking_number',
        'customer_id',
        'provider_id',
        'branch_id',
        'service_id',
        'service_variant_id',
        'slot_id',
        'scheduled_at',
        'status',
        'notes',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
        ];
    }

    public static function slotBlockingStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_CONFIRMED,
            self::STATUS_IN_PROGRESS,
        ];
    }

    public static function upcomingLimitStatuses(): array
    {
        return self::slotBlockingStatuses();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(ServiceVariant::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function providerPayout(): HasOne
    {
        return $this->hasOne(ProviderPayout::class);
    }
}
