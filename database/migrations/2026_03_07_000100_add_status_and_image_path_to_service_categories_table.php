<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table): void {
            if (!Schema::hasColumn('service_categories', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->index()->after('description');
            }

            if (!Schema::hasColumn('service_categories', 'image_path')) {
                $table->string('image_path')->nullable()->after('status');
            }
        });

        if (Schema::hasColumn('service_categories', 'status') && Schema::hasColumn('service_categories', 'is_active')) {
            DB::table('service_categories')
                ->where('is_active', false)
                ->update(['status' => 'inactive']);
        }
    }

    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table): void {
            if (Schema::hasColumn('service_categories', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('service_categories', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
