<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LibraryAccessGrant;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_supported_library_item_types_can_be_created(): void
    {
        foreach (['book', 'journal', 'journal_article', 'article'] as $type) {
            LibraryItem::create(['type' => $type, 'title' => "Item {$type}", 'access_policy' => 'public_read_only', 'source_type' => 'local', 'status' => 'published', 'published_at' => now()]);
        }
        $this->assertDatabaseCount('library_items', 4);
    }

    public function test_public_read_download_and_read_only_policies_are_enforced_for_guests(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('library-items/public.pdf', '%PDF-public');
        $downloadable = $this->item('Public Download', 'public_read_download');
        $downloadable->files()->create(['file_type' => 'pdf', 'file_path' => 'library-items/public.pdf', 'visibility' => 'private', 'download_allowed' => true, 'is_primary' => true]);
        $readOnly = $this->item('Public Read', 'public_read_only');
        $readOnly->files()->create(['file_type' => 'pdf', 'file_path' => 'library-items/public.pdf', 'visibility' => 'private', 'download_allowed' => true, 'is_primary' => true]);

        $this->get(route('library.read', $downloadable))->assertOk();
        $this->get(route('library.download', $downloadable))->assertOk();
        $this->get(route('library.read', $readOnly))->assertOk();
        $this->get(route('library.download', $readOnly))->assertForbidden();
    }

    public function test_registered_external_and_manual_purchase_policies_are_enforced(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('library-items/private.pdf', '%PDF-private');
        $registered = $this->item('Registered', 'registered_read_only');
        $registered->files()->create(['file_type' => 'pdf', 'file_path' => 'library-items/private.pdf', 'visibility' => 'private', 'is_primary' => true]);
        $external = $this->item('OJS Article', 'external', ['source_type' => 'ojs', 'source_url' => 'https://ojs.example.org/article/1']);
        $paid = $this->item('Paid', 'manual_purchase');
        $paid->files()->create(['file_type' => 'pdf', 'file_path' => 'library-items/private.pdf', 'visibility' => 'private', 'download_allowed' => true, 'is_primary' => true]);

        $this->get(route('library.read', $registered))->assertRedirect(route('login'));
        $this->get(route('library.read', $external))->assertRedirect('https://ojs.example.org/article/1');
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('library.read', $paid))->assertForbidden();
        LibraryAccessGrant::create(['user_id' => $user->id, 'library_item_id' => $paid->id, 'source_type' => 'manual_order', 'source_id' => 1, 'granted_at' => now()]);
        $this->actingAs($user)->get(route('library.read', $paid))->assertOk();
    }

    public function test_global_search_and_filters_cover_metadata_creator_category_access_and_year(): void
    {
        $category = Category::create(['name' => 'Mitigasi', 'slug' => 'mitigasi']);
        $article = $this->item('Strategi Banjir', 'public_read_only', ['type' => 'journal_article', 'abstract' => 'Mitigasi banjir perkotaan', 'publication_year' => 2025]);
        $article->creators()->create(['name' => 'Siti Rahma', 'role' => 'author']);
        $article->categories()->attach($category);
        $this->item('Buku Berbayar', 'manual_purchase', ['type' => 'book', 'publication_year' => 2024]);

        foreach (['q=Strategi', 'q=Siti', 'q=perkotaan', 'type=journal_article', 'category=mitigasi', 'access=free', 'year=2025'] as $query) {
            $this->get('/library?'.$query)->assertOk()->assertSee('Strategi Banjir');
        }
        $this->get('/library?access=paid')->assertOk()->assertSee('Buku Berbayar')->assertDontSee('Strategi Banjir');
    }

    public function test_legacy_book_is_linked_to_a_library_item(): void
    {
        $book = \App\Models\Book::create(['title' => 'Warisan Lama', 'author' => 'P4I', 'price' => 0, 'file_path' => 'legacy.pdf', 'is_published' => true]);
        $this->assertNotNull($book->fresh()->library_item_id);
        $this->assertDatabaseHas('library_items', ['title' => 'Warisan Lama', 'source_type' => 'legacy_book', 'status' => 'published']);
        $this->get(route('books.show', $book->slug))->assertRedirect(route('library.show', $book->fresh()->libraryItem));
    }

    public function test_legacy_book_deletion_archives_library_item(): void
    {
        $book = \App\Models\Book::create(['title' => 'Warisan Lama 2', 'author' => 'P4I', 'price' => 0, 'file_path' => 'legacy2.pdf', 'is_published' => true]);
        $libraryItemId = $book->fresh()->library_item_id;

        $book->delete();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseHas('library_items', [
            'id' => $libraryItemId,
            'status' => 'archived'
        ]);
    }

    public function test_unsafe_external_url_schemes_are_rejected(): void
    {
        $unsafeItem = $this->item('Unsafe URL', 'external', ['source_type' => 'ojs', 'source_url' => 'javascript:alert(1)']);
        $this->get(route('library.read', $unsafeItem))->assertStatus(422);

        $unsafeFileItem = $this->item('Unsafe File URL', 'public_read_download');
        $unsafeFileItem->files()->create(['file_type' => 'pdf', 'external_url' => 'file:///etc/passwd', 'is_primary' => true]);
        $this->get(route('library.read', $unsafeFileItem))->assertStatus(422);

        $safeItem = $this->item('Safe URL', 'external', ['source_type' => 'ojs', 'source_url' => 'https://ojs.example.org']);
        $this->get(route('library.read', $safeItem))->assertRedirect('https://ojs.example.org');
    }

    private function item(string $title, string $policy, array $extra = []): LibraryItem
    {
        return LibraryItem::create(array_merge(['type' => 'book', 'title' => $title, 'access_policy' => $policy, 'source_type' => 'local', 'status' => 'published', 'published_at' => now(), 'price' => $policy === 'manual_purchase' ? 50000 : 0], $extra));
    }
}
