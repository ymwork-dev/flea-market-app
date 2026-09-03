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
        Schema::table('orders', function (Blueprint $table) {
            // paid: 支払い完了 / unpaid: コンビニ払いでまだ支払われていない / failed: 支払い失敗
            $table->string('payment_status')->default('paid');
            // Webhookで届いた通知が、どの注文の話かを照合するためのStripeセッションID
            $table->string('stripe_session_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'stripe_session_id']);
        });
    }
};
