<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderRequest extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'business_name',
        'owner_name',
        'email',
        'phone',
        'service_category_id',
        'business_details',
        'address',
        'documents',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'approved_user_id',
        'provider_profile_id',
    ];

    protected $casts = [
        'documents' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}
