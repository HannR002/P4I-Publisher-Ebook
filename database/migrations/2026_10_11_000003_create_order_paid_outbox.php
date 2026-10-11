<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_paid_outbox', function (Blueprint $table) {
            $table->string('order_id')->primary();
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
            $table->timestamp('dispatched_at')->nullable()->index();
            $table->timestamps();
        });
        Schema::table('orders', fn (Blueprint $table) => $table->timestamp('confirmation_sent_at')->nullable());
    }

    public function down(): void
    {
        if (DB::table('order_paid_outbox')->exists() || DB::table('orders')->whereNotNull('confirmation_sent_at')->exists()) {
            throw new RuntimeException('Payment events exist; an approved data-preserving rollback is required.');
        }
        Schema::dropIfExists('order_paid_outbox');
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('confirmation_sent_at'));
    }
};
