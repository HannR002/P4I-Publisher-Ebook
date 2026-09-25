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
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->foreignId('author_id')->constrained('authors')->onDelete('restrict');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['requested', 'processing', 'completed', 'rejected'])->default('requested');
            $table->string('transfer_proof_path', 255)->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
