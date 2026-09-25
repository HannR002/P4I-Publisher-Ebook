<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG-06 / Bottleneck-03: Add performance indexes to prevent slow queries
     * as the tables grow. (user_id/book_id on book_licenses is already indexed
     * via the unique constraint added in BUG-10).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'orders_user_created_index');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index(['is_published', 'created_at'], 'books_published_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_created_index');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_published_created_index');
        });
    }
};
