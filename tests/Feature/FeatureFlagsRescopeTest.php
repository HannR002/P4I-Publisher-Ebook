<?php

namespace Tests\Feature;

use App\Events\OrderPaidEvent;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use DomainException;

class FeatureFlagsRescopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_checkout_and_webhook_are_intentionally_unavailable_when_disabled(): void
    {
        config(['features.midtrans' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/checkout', ['book_ids' => [1]])->assertNotFound();
        $this->post('/api/midtrans/webhook', [])->assertNotFound();
    }

    public function test_royalty_listener_does_nothing_when_disabled(): void
    {
        config(['features.royalty' => false]);
        $authorUser = User::factory()->create();
        $author = Author::create(['user_id' => $authorUser->id, 'pen_name' => 'Penulis', 'id_card_number' => '', 'kyc_status' => 'verified']);
        $book = Book::create(['title' => 'Buku', 'author' => 'Penulis', 'author_id' => $author->id, 'price' => 10000, 'file_path' => 'book.pdf', 'is_published' => true]);
        $order = Order::create(['id' => 'FLAG-ORDER', 'user_id' => User::factory()->create()->id, 'gross_amount' => 10000, 'status' => 'success']);
        OrderItem::create(['order_id' => $order->id, 'book_id' => $book->id, 'price' => 10000]);

        event(new OrderPaidEvent($order));

        $this->assertDatabaseCount('royalty_ledgers', 0);
    }

    public function test_payout_endpoints_are_unavailable_when_disabled(): void
    {
        config(['features.payout' => false]);
        $user = User::factory()->create();
        Author::create(['user_id' => $user->id, 'pen_name' => 'Penulis', 'id_card_number' => '', 'kyc_status' => 'verified']);

        $this->actingAs($user)->get('/author/payouts')->assertNotFound();
        $this->actingAs($user)->post('/author/payouts', ['amount' => 100000])->assertNotFound();
    }

    public function test_payout_service_cannot_request_complete_or_reject_when_disabled(): void
    {
        config(['features.payout' => false]);
        $user = User::factory()->create();
        $author = Author::create(['user_id' => $user->id, 'pen_name' => 'Penulis', 'id_card_number' => '', 'kyc_status' => 'verified']);
        $payout = new PayoutRequest(['id' => '01TESTPAYOUT', 'author_id' => $author->id, 'amount' => 100000, 'status' => 'requested']);
        $service = app(\App\Services\PayoutService::class);
        $blocked = 0;

        foreach ([
            fn () => $service->requestPayout($author, 100000),
            fn () => $service->completePayout($payout, 'REF', null, $user->id),
            fn () => $service->rejectPayout($payout, 'disabled', $user->id),
        ] as $operation) {
            try { $operation(); } catch (DomainException) { $blocked++; }
        }

        $this->assertSame(3, $blocked);
        $this->assertDatabaseCount('payout_requests', 0);
    }

    public function test_kyc_is_not_required_to_register_and_submit_a_book_when_disabled(): void
    {
        config(['features.author_kyc' => false]);
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/author/register', ['pen_name' => 'Tanpa KYC', 'bio' => 'Bio'])
            ->assertRedirect(route('author.dashboard'));
        $this->assertDatabaseHas('authors', ['user_id' => $user->id, 'pen_name' => 'Tanpa KYC', 'kyc_status' => 'unverified', 'id_card_number' => null]);
        $user->refresh()->unsetRelation('authorProfile');

        $category = Category::create(['name' => 'Teknologi', 'slug' => 'teknologi']);
        $this->actingAs($user)->post('/author/submissions', [
            'title' => 'Naskah Tanpa KYC', 'category_id' => $category->id,
            'synopsis' => str_repeat('Isi naskah ', 20), 'proposed_price' => 0,
            'manuscript_file' => UploadedFile::fake()->createWithContent('naskah.pdf', "%PDF-1.4\ncontent"),
            'action' => 'submit',
        ])->assertRedirect(route('author.submissions.index'));
        $this->assertDatabaseHas('book_submissions', ['title' => 'Naskah Tanpa KYC', 'status' => 'submitted']);
    }

    public function test_author_dashboard_route_is_resolvable(): void
    {
        config(['features.author_kyc' => false]);
        $user = User::factory()->create();
        Author::create(['user_id' => $user->id, 'pen_name' => 'Penulis', 'id_card_number' => '', 'kyc_status' => 'unverified']);
        $this->actingAs($user)->get(route('author.dashboard'))->assertOk();
    }
}
