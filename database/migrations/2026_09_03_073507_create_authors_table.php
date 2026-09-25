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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('pen_name', 150);
            $table->text('bio')->nullable();
            $table->text('id_card_number');
            $table->string('id_card_path', 255)->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_holder_name', 150)->nullable();
            $table->enum('kyc_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
