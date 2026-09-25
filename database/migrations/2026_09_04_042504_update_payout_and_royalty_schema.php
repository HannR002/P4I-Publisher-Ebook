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
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('bank_name_snapshot', 50)->after('amount')->nullable();
            $table->string('bank_account_snapshot', 50)->after('bank_name_snapshot')->nullable();
            $table->string('bank_holder_name_snapshot', 150)->after('bank_account_snapshot')->nullable();
            $table->string('reference_number', 100)->nullable()->after('transfer_proof_path');
        });

        Schema::table('royalty_ledgers', function (Blueprint $table) {
            $table->string('payout_request_id', 26)->nullable()->after('book_id');
            $table->foreign('payout_request_id')->references('id')->on('payout_requests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('royalty_ledgers', function (Blueprint $table) {
            $table->dropForeign(['payout_request_id']);
            $table->dropColumn('payout_request_id');
        });

        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name_snapshot',
                'bank_account_snapshot',
                'bank_holder_name_snapshot',
                'reference_number'
            ]);
        });
    }
};
