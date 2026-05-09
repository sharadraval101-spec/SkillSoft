<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderProfile extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    public const AVAILABILITY_AVAILABLE = 'available';
    public const AVAILABILITY_UNAVAILABLE = 'unavailable';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'branch_id',
        'business_name',
        'bio',
        'experience_years',
        'commission_rate',
        'status',
        'availability_status',
        'unavailable_from',
        'unavailable_until',
        'unavailability_reason',
        'verified_at',
    ];

    protected $casts = [
        'unavailable_from' => 'datetime',
        'unavailable_until' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function isOperationallyUnavailable(): bool
    {
        return $this->availability_status === self::AVAILABILITY_UNAVAILABLE;
    }
}
