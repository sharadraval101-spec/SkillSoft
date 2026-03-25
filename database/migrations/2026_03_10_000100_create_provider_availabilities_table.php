<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->index(); // 0 (Sun) to 6 (Sat)
            $table->boolean('is_active')->default(false)->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('break_start_time')->nullable();
            $table->time('break_end_time')->nullable();
            $table->unsignedSmallInteger('slot_duration')->default(30);
            $table->timestamps();

            $table->unique(['provider_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_availabilities');
    }
};

