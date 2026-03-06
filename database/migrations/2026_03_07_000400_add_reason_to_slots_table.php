<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slots', function (Blueprint $table): void {
            if (!Schema::hasColumn('slots', 'reason')) {
                $table->string('reason', 255)->nullable()->after('is_available');
            }
        });
    }

    public function down(): void
    {
        Schema::table('slots', function (Blueprint $table): void {
            if (Schema::hasColumn('slots', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
};
