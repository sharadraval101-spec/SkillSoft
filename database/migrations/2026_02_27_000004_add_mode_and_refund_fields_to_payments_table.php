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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('method', 20)->default('online')->after('gateway')->index();
            $table->string('payment_mode', 20)->default('prepaid')->after('method')->index();
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('amount');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->string('refund_reason')->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'method',
                'payment_mode',
                'refunded_amount',
                'refunded_at',
                'refund_reason',
            ]);
        });
    }
};
