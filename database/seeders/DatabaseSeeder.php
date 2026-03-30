<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(DemoBookingFlowSeeder::class);

        $user = User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        $user->syncRoleFromLegacyValue();
    }
}
