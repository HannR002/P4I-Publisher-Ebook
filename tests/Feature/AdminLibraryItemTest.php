<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLibraryItemTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_list_library_items()
    {
        LibraryItem::create(['type' => 'book', 'source_type' => 'local', 'title' => 'Test', 'access_policy' => 'public_read_only', 'price' => 0, 'status' => 'published']);
        $response = $this->actingAs($this->admin)->get(route('admin.library.index'));
        $response->assertStatus(200);
        $response->assertSee('Test');
    }

    public function test_admin_can_create_library_item()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin)->post(route('admin.library.store'), [
            'type' => 'journal',
            'title' => 'New Journal',
            'source_type' => 'local',
            'access_policy' => 'public_read_download',
            'price' => 0,
            'status' => 'draft',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.library.index'));
        $this->assertDatabaseHas('library_items', ['title' => 'New Journal']);
    }

    public function test_admin_can_edit_generic_library_item()
    {
        $item = LibraryItem::create(['type' => 'journal', 'source_type' => 'local', 'title' => 'Test', 'access_policy' => 'public_read_only', 'price' => 0, 'status' => 'published']);
        $response = $this->actingAs($this->admin)->get(route('admin.library.edit', $item));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->put(route('admin.library.update', $item), [
            'type' => 'journal',
            'title' => 'Updated Title',
            'source_type' => 'local',
            'access_policy' => 'public_read_download',
            'price' => 0,
            'status' => 'published',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.library.index'));
        $this->assertDatabaseHas('library_items', ['title' => 'Updated Title']);
    }

    public function test_admin_cannot_edit_legacy_book_directly_and_is_redirected()
    {
        $book = clone Book::create(['title' => 'Legacy Book', 'author' => 'Test Author', 'price' => 100, 'type' => 'book', 'status' => 'published', 'cover_image' => 'a.jpg', 'file_path' => 'a.pdf']);
        $item = LibraryItem::where('source_type', 'legacy_book')->first();

        $response = $this->actingAs($this->admin)->get(route('admin.library.edit', $item));
        $response->assertRedirect(route('admin.books.index'));

        $response = $this->actingAs($this->admin)->put(route('admin.library.update', $item), [
            'title' => 'Hacked',
        ]);
        $response->assertStatus(422);
    }

    public function test_normal_user_denied()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get(route('admin.library.index'));
        $response->assertStatus(403);
    }
}
