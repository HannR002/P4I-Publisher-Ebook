<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('name');
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('qr_image_path')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('manual_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_type', 30)->index();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('status', 30)->default('awaiting_payment')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_order_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 80);
            $table->unsignedBigInteger('item_id');
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
            $table->index(['item_type', 'item_id']);
        });

        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('proof_path');
            $table->string('status', 30)->default('submitted')->index();
            $table->timestamp('submitted_at');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
        Schema::dropIfExists('manual_order_items');
        Schema::dropIfExists('manual_orders');
        Schema::dropIfExists('payment_methods');
    }
};
