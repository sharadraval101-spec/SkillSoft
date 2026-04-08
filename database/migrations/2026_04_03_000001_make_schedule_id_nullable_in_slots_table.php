<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Slots generated from ProviderAvailability records have no associated
     * Schedule row, so schedule_id must be nullable.
     */
    public function up(): void
    {
        Schema::table('slots', function (Blueprint $table) {
            // Drop the existing NOT NULL foreign key constraint first
            $table->dropForeign(['schedule_id']);
            // Re-add as nullable with the same cascade behaviour
            $table->foreignUuid('schedule_id')
                  ->nullable()
                  ->change()
                  ->constrained()
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->foreignUuid('schedule_id')
                  ->nullable(false)
                  ->change()
                  ->constrained()
                  ->cascadeOnDelete();
        });
    }
};
