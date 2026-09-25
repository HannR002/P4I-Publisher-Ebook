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
        Schema::create('royalty_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('authors')->onDelete('restrict');
            $table->foreignId('order_item_id')->unique()->constrained('order_items')->onDelete('restrict');
            $table->foreignId('book_id')->constrained('books')->onDelete('restrict');
            $table->decimal('gross_sale', 12, 2);
            $table->decimal('author_percentage', 5, 2)->default(70.00);
            $table->decimal('author_earning', 12, 2);
            $table->decimal('platform_earning', 12, 2);
            $table->enum('status', ['pending', 'available', 'withdrawn'])->default('available');
            $table->timestamps();
            $table->index(['author_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('royalty_ledgers');
    }
};
