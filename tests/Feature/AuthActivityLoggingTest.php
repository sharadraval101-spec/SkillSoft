<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_creates_activity_log(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'event_type' => 'auth.login',
            'event_label' => 'User logged in',
        ]);
    }

    public function test_successful_register_creates_activity_log(): void
    {
        $response = $this->post('/register', [
            'name' => 'Student One',
            'email' => 'student@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_USER,
        ]);

        $response->assertRedirect('/user/dashboard');

        $user = User::query()->where('email', 'student@example.com')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event_type' => 'auth.register',
            'event_label' => 'New account registered',
        ]);
    }
}
