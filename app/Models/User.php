<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    const ROLE_USER = 1;
    const ROLE_ADMIN = 2;
    const ROLE_PROVIDER = 3;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Helper to check role in Blade or Controllers
    public function getRoleName(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'admin',
            self::ROLE_PROVIDER => 'provider',
            default => 'user',
        };
    }
}
