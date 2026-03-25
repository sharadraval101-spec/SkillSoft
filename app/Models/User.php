<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    const ROLE_CUSTOMER = 1;
    const ROLE_USER = self::ROLE_CUSTOMER; // Backward-compatible alias
    const ROLE_ADMIN = 2;
    const ROLE_PROVIDER = 3;

    protected $fillable = ['uuid', 'name', 'email', 'password', 'role', 'is_active', 'profile_photo_path'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    protected string $guard_name = 'web';

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });

        static::created(function (self $user): void {
            $user->syncRoleFromLegacyValue();
        });

        static::updated(function (self $user): void {
            if ($user->wasChanged('role')) {
                $user->syncRoleFromLegacyValue();
            }
        });
    }

    // Helper to check role in Blade or Controllers
    public function getRoleName(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'admin',
            self::ROLE_PROVIDER => 'provider',
            default => 'customer',
        };
    }

    public function syncRoleFromLegacyValue(): void
    {
        $roleName = $this->getRoleName();

        if (
            !$this->exists ||
            !Schema::hasTable('roles') ||
            !Schema::hasTable('model_has_roles')
        ) {
            return;
        }

        if ($this->hasRole($roleName)) {
            return;
        }

        $this->syncRoles([$roleName]);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo_path) {
            return null;
        }

        if (!Storage::disk('public')->exists($this->profile_photo_path)) {
            return null;
        }

        return route('profile.photo.show', ['user' => $this->id]);
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }

    public function customerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function providerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    public function providerPayouts(): HasMany
    {
        return $this->hasMany(ProviderPayout::class, 'provider_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'provider_id');
    }

    public function scheduleBlocks(): HasMany
    {
        return $this->hasMany(ScheduleBlock::class, 'provider_id');
    }

    public function providerAvailabilities(): HasMany
    {
        return $this->hasMany(ProviderAvailability::class, 'provider_id');
    }

    public function providerUnavailableDates(): HasMany
    {
        return $this->hasMany(ProviderUnavailableDate::class, 'provider_id');
    }

    public function notificationsList(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
