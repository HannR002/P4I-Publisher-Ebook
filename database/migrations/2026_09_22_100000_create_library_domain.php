<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('synopsis')->nullable();
            $table->longText('abstract')->nullable();
            $table->text('featured_excerpt')->nullable();
            $table->string('excerpt_source')->nullable();
            $table->string('excerpt_page', 50)->nullable();
            $table->string('publisher')->nullable()->index();
            $table->date('publication_date')->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable()->index();
            $table->string('isbn', 32)->nullable()->index();
            $table->string('issn', 32)->nullable()->index();
            $table->string('doi')->nullable()->index();
            $table->string('language', 20)->nullable()->index();
            $table->string('cover_path')->nullable();
            $table->text('keywords')->nullable();
            $table->string('source_type', 30)->default('local')->index();
            $table->text('source_url')->nullable();
            $table->string('access_policy', 50)->index();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('status', 30)->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('library_item_id')->nullable()->unique()->after('id')
                ->constrained('library_items')->nullOnDelete();
        });

        Schema::create('category_library_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['category_id', 'library_item_id']);
        });

        Schema::create('library_item_creators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_item_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role', 40)->default('author')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('external_identifier')->nullable();
            $table->timestamps();
            $table->index(['library_item_id', 'sort_order']);
        });

        Schema::create('library_item_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_item_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('file_type', 40)->default('publication');
            $table->string('mime_type')->nullable();
            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();
            $table->string('visibility', 30)->default('private');
            $table->boolean('download_allowed')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
            $table->index(['library_item_id', 'is_primary']);
        });

        Schema::create('book_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_item_id')->constrained()->cascadeOnDelete();
            $table->string('format', 30)->index();
            $table->string('edition_name')->nullable();
            $table->string('isbn', 32)->nullable();
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('stock')->nullable();
            $table->unsignedInteger('weight')->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('thickness', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('library_access_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_item_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'library_item_id']);
        });

        $this->backfillBooks();
    }

    private function backfillBooks(): void
    {
        DB::table('books')->whereNull('library_item_id')->orderBy('id')->chunkById(100, function ($books): void {
            foreach ($books as $book) {
                $itemId = DB::table('library_items')->insertGetId([
                    'type' => 'book',
                    'title' => $book->title,
                    'slug' => $book->slug,
                    'description' => $book->description,
                    'synopsis' => $book->description,
                    'publication_date' => $book->publish_date,
                    'publication_year' => $book->publish_date ? (int) substr($book->publish_date, 0, 4) : null,
                    'isbn' => $book->isbn,
                    'cover_path' => $book->cover_image_path,
                    'source_type' => 'legacy_book',
                    'access_policy' => ((float) $book->price > 0) ? 'manual_purchase' : 'public_read_download',
                    'price' => $book->price,
                    'status' => $book->is_published ? 'published' : 'draft',
                    'published_at' => $book->is_published ? ($book->created_at ?? now()) : null,
                    'created_at' => $book->created_at ?? now(),
                    'updated_at' => now(),
                ]);

                DB::table('books')->where('id', $book->id)->update(['library_item_id' => $itemId]);
                DB::table('library_item_creators')->insert([
                    'library_item_id' => $itemId,
                    'name' => $book->author,
                    'role' => 'author',
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('library_item_files')->insert([
                    'library_item_id' => $itemId,
                    'label' => 'Berkas utama',
                    'file_type' => 'pdf',
                    'mime_type' => 'application/pdf',
                    'file_path' => $book->file_path,
                    'visibility' => 'private',
                    'download_allowed' => (float) $book->price === 0.0,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $categoryIds = DB::table('book_category')->where('book_id', $book->id)->pluck('category_id');
                foreach ($categoryIds as $categoryId) {
                    DB::table('category_library_item')->insertOrIgnore([
                        'category_id' => $categoryId,
                        'library_item_id' => $itemId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_access_grants');
        Schema::dropIfExists('book_editions');
        Schema::dropIfExists('library_item_files');
        Schema::dropIfExists('library_item_creators');
        Schema::dropIfExists('category_library_item');
        Schema::table('books', fn (Blueprint $table) => $table->dropConstrainedForeignId('library_item_id'));
        Schema::dropIfExists('library_items');
    }
};
