<?php

namespace Tests\Feature;

use App\Events\OrderPaidEvent;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookSubmission;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RoyaltyLedger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCurationAndRoyaltyTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $authorUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'email' => 'admin@p4i.test',
            'is_admin' => true,
        ]);
        
        $this->authorUser = User::factory()->create([
            'email' => 'author@p4i.test'
        ]);
    }

    public function test_admin_can_verify_author_kyc()
    {
        $author = Author::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'John Doe',
            'bio' => 'A great writer',
            'id_card_number' => '1234567890123456',
            'id_card_path' => 'private/kyc/test.jpg',
            'bank_name' => 'Bank Test',
            'bank_account' => '123456',
            'bank_holder_name' => 'John Doe',
            'kyc_status' => 'pending'
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.kyc.approve', $author->id));
        
        $response->assertRedirect(route('admin.kyc.index'));
        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'kyc_status' => 'verified'
        ]);
        $this->assertNotNull($author->fresh()->verified_at);
    }

    public function test_admin_can_request_revision_and_log_review()
    {
        $author = Author::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'John Doe',
            'kyc_status' => 'verified',
            'id_card_number' => '1234567890123456',
            'id_card_path' => 'path',
            'bank_name' => 'b',
            'bank_account' => '1',
            'bank_holder_name' => 'n'
        ]);

        $submission = BookSubmission::create([
            'author_id' => $author->id,
            'title' => 'Test Manuscript',
            'synopsis' => 'A great story.',
            'proposed_price' => 50000,
            'manuscript_path' => 'private/submissions/test.pdf',
            'status' => 'submitted'
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.submissions.request-revision', $submission->id), [
            'feedback' => 'Please fix the first chapter.'
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('book_submissions', [
            'id' => $submission->id,
            'status' => 'revision_requested'
        ]);

        $this->assertDatabaseHas('submission_reviews', [
            'submission_id' => $submission->id,
            'reviewer_id' => $this->admin->id,
            'action' => 'request_revision'
        ]);
    }

    public function test_admin_can_approve_publish_and_move_file()
    {
        Storage::fake('local');
        Storage::disk('local')->put('private/submissions/test.pdf', 'dummy content');

        $author = Author::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'John Doe',
            'kyc_status' => 'verified',
            'id_card_number' => '1234567890123456',
            'id_card_path' => 'path',
            'bank_name' => 'b',
            'bank_account' => '1',
            'bank_holder_name' => 'n'
        ]);
        
        $category = Category::create([
            'name' => 'Fiction',
            'slug' => 'fiction',
            'description' => 'Fiction books'
        ]);

        $submission = BookSubmission::create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Test Manuscript',
            'synopsis' => 'A great story.',
            'proposed_price' => 50000,
            'manuscript_path' => 'private/submissions/test.pdf',
            'status' => 'in_review'
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.submissions.approve-publish', $submission->id), [
            'final_price' => 75000
        ]);

        $response->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseHas('book_submissions', [
            'id' => $submission->id,
            'status' => 'published'
        ]);

        $book = Book::where('title', 'Test Manuscript')->first();
        $this->assertNotNull($book);
        $this->assertEquals(75000, $book->price);
        $this->assertEquals($author->id, $book->author_id);
        $this->assertTrue((bool)$book->is_published);
        
        // Assert file exists in private_books
        Storage::disk('local')->assertExists($book->file_path);
    }

    public function test_order_paid_event_creates_royalty_ledger()
    {
        $author = Author::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'John Doe',
            'kyc_status' => 'verified',
            'id_card_number' => '1234567890123456',
            'id_card_path' => 'path',
            'bank_name' => 'b',
            'bank_account' => '1',
            'bank_holder_name' => 'n'
        ]);

        $book = Book::create([
            'title' => 'Test Book',
            'author' => 'John Doe',
            'author_id' => $author->id,
            'price' => 100000,
            'file_path' => 'path.pdf',
            'is_published' => true
        ]);

        $order = Order::create([
            'id' => 'ORD-TEST-123',
            'user_id' => User::factory()->create()->id,
            'gross_amount' => 100000,
            'status' => 'success',
            'payment_type' => 'bank_transfer'
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'price' => 100000,
            'license_key' => 'ABC'
        ]);

        event(new OrderPaidEvent($order));

        $this->assertDatabaseHas('royalty_ledgers', [
            'author_id' => $author->id,
            'order_item_id' => $orderItem->id,
            'gross_sale' => 100000,
            'author_earning' => 70000, // 70%
            'platform_earning' => 30000,
        ]);
    }
}
