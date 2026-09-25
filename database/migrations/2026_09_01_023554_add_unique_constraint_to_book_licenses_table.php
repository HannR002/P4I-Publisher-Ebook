<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG-10 Fix: Add composite unique constraint on (user_id, book_id)
     * to prevent duplicate licenses even under race condition / concurrent webhooks.
     *
     * Before this migration, BookLicense::firstOrCreate() used three columns
     * (user_id, book_id, order_id) as the lookup key — meaning two different
     * orders for the same book would produce two licenses. This DB-level
     * constraint makes it physically impossible.
     */
    public function up(): void
    {
        Schema::table('book_licenses', function (Blueprint $table) {
            // Remove duplicate rows first (SQLite-compatible: keep lowest id per user+book)
            \DB::statement('
                DELETE FROM book_licenses
                WHERE id NOT IN (
                    SELECT MIN(id)
                    FROM book_licenses
                    GROUP BY user_id, book_id
                )
            ');

            $table->unique(['user_id', 'book_id'], 'book_licenses_user_book_unique');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('book_licenses', function (Blueprint $table) {
            $table->dropUnique('book_licenses_user_book_unique');
        });
    }
};
