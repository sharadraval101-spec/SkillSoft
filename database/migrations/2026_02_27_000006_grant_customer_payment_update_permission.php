<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            return;
        }

        $permission = Permission::query()->firstOrCreate([
            'name' => 'payments.update',
            'guard_name' => 'web',
        ]);

        $customerRole = Role::query()->where('name', 'customer')->where('guard_name', 'web')->first();
        if ($customerRole && !$customerRole->hasPermissionTo($permission)) {
            $customerRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            return;
        }

        $permission = Permission::query()
            ->where('name', 'payments.update')
            ->where('guard_name', 'web')
            ->first();
        if (!$permission) {
            return;
        }

        $customerRole = Role::query()->where('name', 'customer')->where('guard_name', 'web')->first();
        if (!$customerRole) {
            return;
        }

        if ($customerRole->hasPermissionTo($permission)) {
            $customerRole->revokePermissionTo($permission);
        }
    }
};
