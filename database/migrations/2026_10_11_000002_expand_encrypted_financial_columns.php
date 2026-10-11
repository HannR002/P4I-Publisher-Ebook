<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'authors' => ['bank_name' => 50, 'bank_account' => 50, 'bank_holder_name' => 150],
        'payout_requests' => ['bank_name_snapshot' => 50, 'bank_account_snapshot' => 50, 'bank_holder_name_snapshot' => 150],
        'orders' => ['snap_token' => 255],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $name => $length) {
                    $blueprint->text($name)->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $name => $length) {
                if (DB::table($table)->whereRaw('LENGTH('.$name.') > ?', [$length])->exists()) {
                    throw new RuntimeException('Encrypted financial data exceeds legacy column length. Restore an approved snapshot or use a forward fix.');
                }
            }
        }
        foreach ($this->columns as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $name => $length) {
                    $blueprint->string($name, $length)->nullable()->change();
                }
            });
        }
    }
};
