<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard(): void
    {
        $admin = $this->createUser('admin@example.com', User::ROLE_ADMIN);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Dashboard');
    }

    public function test_provider_is_redirected_from_admin_dashboard(): void
    {
        $provider = $this->createUser('provider@example.com', User::ROLE_PROVIDER);

        $response = $this->actingAs($provider)->get(route('admin.dashboard'));

        $response->assertRedirect('/provider/dashboard');
    }

    public function test_user_is_redirected_from_admin_dashboard(): void
    {
        $user = $this->createUser('user@example.com', User::ROLE_USER);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertRedirect('/user/dashboard');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    private function createUser(string $email, int $role): User
    {
        return User::query()->create([
            'name' => 'Test',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);
    }
}
