<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roleNames = ['admin', 'provider', 'customer'];

        foreach ($roleNames as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $roleIdMap = DB::table('roles')
            ->whereIn('name', $roleNames)
            ->pluck('id', 'name');

        $legacyToSpatie = [
            User::ROLE_ADMIN => 'admin',
            User::ROLE_PROVIDER => 'provider',
            User::ROLE_CUSTOMER => 'customer',
        ];

        DB::table('users')
            ->select('id', 'role')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($legacyToSpatie, $roleIdMap): void {
                foreach ($users as $user) {
                    $roleName = $legacyToSpatie[(int) $user->role] ?? 'customer';
                    $roleId = $roleIdMap[$roleName] ?? null;

                    if (!$roleId) {
                        continue;
                    }

                    DB::table('model_has_roles')->updateOrInsert([
                        'role_id' => $roleId,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->delete();
    }
};

