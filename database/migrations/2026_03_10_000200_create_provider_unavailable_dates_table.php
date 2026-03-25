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
        Schema::create('provider_unavailable_dates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('block_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'block_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_unavailable_dates');
    }
};

