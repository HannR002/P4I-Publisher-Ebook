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
        Schema::create('submission_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('book_submissions')->cascadeOnDelete();
            $table->string('manuscript_path');
            $table->text('revision_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_revisions');
    }
};
