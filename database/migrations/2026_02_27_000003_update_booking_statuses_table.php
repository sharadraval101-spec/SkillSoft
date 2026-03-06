<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        DB::table('bookings')
            ->where('status', 'confirmed')
            ->update(['status' => 'accepted']);

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE bookings
                MODIFY status ENUM('pending', 'accepted', 'rejected', 'completed', 'cancelled')
                NOT NULL DEFAULT 'pending'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('bookings')) {
            return;
        }

        DB::table('bookings')
            ->where('status', 'accepted')
            ->update(['status' => 'confirmed']);

        DB::table('bookings')
            ->where('status', 'rejected')
            ->update(['status' => 'pending']);

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE bookings
                MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled')
                NOT NULL DEFAULT 'pending'
            ");
        }
    }
};
