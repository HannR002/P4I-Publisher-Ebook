<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_licenses', function (Blueprint $table) {
            $table->integer('last_read_page')->default(1)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('book_licenses', function (Blueprint $table) {
            $table->dropColumn('last_read_page');
        });
    }
};
