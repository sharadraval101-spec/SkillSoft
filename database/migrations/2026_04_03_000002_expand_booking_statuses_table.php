<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE bookings
                MODIFY status ENUM('pending', 'accepted', 'confirmed', 'in_progress', 'rejected', 'completed', 'cancelled')
                NOT NULL DEFAULT 'pending'
            ");

            return;
        }

        if ($driver === 'sqlite') {
            $this->recreateSqliteBookingsTable([
                'pending',
                'accepted',
                'confirmed',
                'in_progress',
                'rejected',
                'completed',
                'cancelled',
            ]);
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

        DB::table('bookings')
            ->where('status', 'in_progress')
            ->update(['status' => 'confirmed']);

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE bookings
                MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled')
                NOT NULL DEFAULT 'pending'
            ");

            return;
        }

        if ($driver === 'sqlite') {
            $this->recreateSqliteBookingsTable([
                'pending',
                'confirmed',
                'completed',
                'cancelled',
            ]);
        }
    }

    private function recreateSqliteBookingsTable(array $statuses): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            Schema::rename('bookings', 'bookings_temp');
            $this->dropSqliteBookingIndexes();

            Schema::create('bookings', function (Blueprint $table) use ($statuses) {
                $table->uuid('id')->primary();
                $table->string('booking_number')->unique();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('branch_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignUuid('service_id')->constrained()->restrictOnDelete();
                $table->foreignUuid('service_variant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignUuid('slot_id')->nullable()->unique()->constrained()->nullOnDelete();
                $table->dateTime('scheduled_at')->index();
                $table->enum('status', $statuses)->default('pending')->index();
                $table->text('notes')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();

                $table->index(['customer_id', 'status']);
                $table->index(['provider_id', 'status']);
            });

            DB::table('bookings')->insertUsing(
                [
                    'id',
                    'booking_number',
                    'customer_id',
                    'provider_id',
                    'branch_id',
                    'service_id',
                    'service_variant_id',
                    'slot_id',
                    'scheduled_at',
                    'status',
                    'notes',
                    'cancelled_at',
                    'created_at',
                    'updated_at',
                ],
                DB::table('bookings_temp')->select([
                    'id',
                    'booking_number',
                    'customer_id',
                    'provider_id',
                    'branch_id',
                    'service_id',
                    'service_variant_id',
                    'slot_id',
                    'scheduled_at',
                    'status',
                    'notes',
                    'cancelled_at',
                    'created_at',
                    'updated_at',
                ])
            );

            Schema::drop('bookings_temp');
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    private function dropSqliteBookingIndexes(): void
    {
        foreach ([
            'bookings_booking_number_unique',
            'bookings_slot_id_unique',
            'bookings_scheduled_at_index',
            'bookings_status_index',
            'bookings_customer_id_status_index',
            'bookings_provider_id_status_index',
        ] as $indexName) {
            DB::statement('DROP INDEX IF EXISTS "'.$indexName.'"');
        }
    }
};
