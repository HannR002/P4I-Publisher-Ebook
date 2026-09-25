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
        Schema::create('book_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('authors')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('title', 255);
            $table->text('synopsis');
            $table->decimal('proposed_price', 12, 2)->default(0);
            $table->string('manuscript_path', 255);
            $table->string('cover_preview_path', 255)->nullable();
            $table->enum('status', ['draft', 'submitted', 'in_review', 'revision_requested', 'approved', 'rejected', 'published'])->default('draft');
            $table->foreignId('book_id')->nullable()->constrained('books')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_submissions');
    }
};
