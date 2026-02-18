<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AdminDashboardMetricsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_zero_safe_metrics_when_no_data_exists(): void
    {
        $service = app(AdminDashboardMetricsService::class);

        $data = $service->getData();

        $this->assertSame(0, $data['metrics']['total_users']);
        $this->assertSame(0, $data['metrics']['registrations_7d']);
        $this->assertSame(0, $data['metrics']['logins_24h']);
        $this->assertCount(14, $data['charts']['registrations_daily_14d']['labels']);
        $this->assertCount(14, $data['charts']['registrations_daily_14d']['series']);
        $this->assertSame(array_fill(0, 14, 0), $data['charts']['registrations_daily_14d']['series']);
        $this->assertCount(14, $data['charts']['logins_daily_14d']['series']);
        $this->assertSame(array_fill(0, 14, 0), $data['charts']['logins_daily_14d']['series']);
        $this->assertCount(0, $data['recentActivities']);
    }

    public function test_aggregates_roles_and_activity_windows_correctly(): void
    {
        $now = Carbon::now();

        $admin = $this->createUser('admin@example.com', User::ROLE_ADMIN, $now->copy()->subDay());
        $provider = $this->createUser('provider@example.com', User::ROLE_PROVIDER, $now->copy()->subDays(2));
        $this->createUser('student-recent@example.com', User::ROLE_USER, $now->copy());
        $studentOld = $this->createUser('student-old@example.com', User::ROLE_USER, $now->copy()->subDays(7));
        $this->createUser('student-very-old@example.com', User::ROLE_USER, $now->copy()->subDays(20));

        ActivityLog::query()->create([
            'user_id' => $admin->id,
            'event_type' => 'auth.login',
            'event_label' => 'User logged in',
            'created_at' => $now->copy()->subHours(2),
        ]);

        ActivityLog::query()->create([
            'user_id' => $provider->id,
            'event_type' => 'auth.login',
            'event_label' => 'User logged in',
            'created_at' => $now->copy()->subHours(23),
        ]);

        ActivityLog::query()->create([
            'user_id' => $studentOld->id,
            'event_type' => 'auth.login',
            'event_label' => 'User logged in',
            'created_at' => $now->copy()->subHours(30),
        ]);

        $service = app(AdminDashboardMetricsService::class);
        $data = $service->getData();

        $this->assertSame(5, $data['metrics']['total_users']);
        $this->assertSame(1, $data['metrics']['total_admins']);
        $this->assertSame(1, $data['metrics']['total_providers']);
        $this->assertSame(3, $data['metrics']['total_students']);
        $this->assertSame(3, $data['metrics']['registrations_7d']);
        $this->assertSame(2, $data['metrics']['logins_24h']);
        $this->assertSame(4, array_sum($data['charts']['registrations_daily_14d']['series']));
        $this->assertSame(3, array_sum($data['charts']['logins_daily_14d']['series']));
    }

    private function createUser(string $email, int $role, Carbon $createdAt): User
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
        ]);

        $user->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $user->fresh();
    }
}
