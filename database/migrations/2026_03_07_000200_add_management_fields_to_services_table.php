<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            if (!Schema::hasColumn('services', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->index()->after('base_price');
            }

            if (!Schema::hasColumn('services', 'type')) {
                $table->enum('type', ['1-on-1', 'group'])->default('1-on-1')->after('duration_minutes');
            }

            if (!Schema::hasColumn('services', 'max_customers')) {
                $table->unsignedInteger('max_customers')->nullable()->after('type');
            }
        });

        if (Schema::hasColumn('services', 'status') && Schema::hasColumn('services', 'is_active')) {
            DB::table('services')
                ->where('is_active', false)
                ->update(['status' => 'inactive']);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            if (Schema::hasColumn('services', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('services', 'max_customers')) {
                $table->dropColumn('max_customers');
            }

            if (Schema::hasColumn('services', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
