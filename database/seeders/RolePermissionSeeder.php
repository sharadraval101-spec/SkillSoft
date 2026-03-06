<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the application's roles and permissions matrix.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'bookings.create',
            'bookings.update',
            'services.create',
            'services.update',
            'payments.create',
            'payments.update',
            'reports.create',
            'reports.update',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $providerRole = Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $adminRole->syncPermissions($permissions);

        $providerRole->syncPermissions([
            'bookings.create',
            'bookings.update',
            'services.create',
            'services.update',
            'payments.create',
            'payments.update',
            'reports.create',
        ]);

        $customerRole->syncPermissions([
            'bookings.create',
            'bookings.update',
            'payments.create',
            'payments.update',
        ]);
    }
}
