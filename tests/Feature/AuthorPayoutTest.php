<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutRequest;
use App\Models\RoyaltyLedger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorPayoutTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $authorUser;
    protected $authorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->authorUser = User::factory()->create();
        
        $this->authorProfile = Author::create([
            'user_id' => $this->authorUser->id,
            'pen_name' => 'Penulis Cerdas',
            'kyc_status' => 'verified',
            'bank_name' => 'Bank Mandiri',
            'bank_account' => '1234567890',
            'bank_holder_name' => 'Penulis Cerdas Asli',
            'id_card_number' => '3201234567890001',
            'id_card_path' => 'private/kyc/test.jpg'
        ]);

        $book = clone $this->authorProfile->books()->create([
            'title' => 'Buku Laris',
            'author' => 'Penulis Cerdas',
            'price' => 100000,
            'file_path' => 'private_books/test.pdf',
            'is_published' => true,
        ]);

        $order = Order::create([
            'id' => 'ORD-123',
            'user_id' => User::factory()->create()->id,
            'gross_amount' => 100000,
            'status' => 'success',
            'payment_type' => 'bank_transfer',
        ]);

        // Create some available ledgers
        for ($i = 0; $i < 3; $i++) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'book_id' => $book->id,
                'price' => 100000,
            ]);

            RoyaltyLedger::create([
                'author_id' => $this->authorProfile->id,
                'book_id' => $book->id,
                'order_item_id' => $orderItem->id,
                'gross_sale' => 100000,
                'author_percentage' => 70,
                'author_earning' => 70000,
                'platform_earning' => 30000,
                'status' => 'available',
            ]);
        }
    }

    public function test_author_cannot_withdraw_below_minimum()
    {
        $response = $this->actingAs($this->authorUser)->post(route('author.payouts.store'), [
            'amount' => 50000
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('payout_requests', 0);
    }

    public function test_author_cannot_withdraw_more_than_available()
    {
        // Available is 3 * 70,000 = 210,000
        $response = $this->actingAs($this->authorUser)->post(route('author.payouts.store'), [
            'amount' => 300000
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('payout_requests', 0);
    }

    public function test_payout_request_locks_ledgers_and_saves_snapshot()
    {
        // Withdraw exactly 140000 (2 ledgers)
        $response = $this->actingAs($this->authorUser)->post(route('author.payouts.store'), [
            'amount' => 140000
        ]);

        $response->assertSessionHasNoErrors();
        
        $payout = PayoutRequest::first();
        $this->assertNotNull($payout);
        $this->assertEquals(140000, $payout->amount);
        $this->assertEquals('Bank Mandiri', $payout->bank_name_snapshot);
        
        $pendingLedgers = RoyaltyLedger::where('status', 'pending')->get();
        $this->assertEquals(2, $pendingLedgers->count());
        foreach ($pendingLedgers as $ledger) {
            $this->assertEquals($payout->id, $ledger->payout_request_id);
        }

        $availableLedgers = RoyaltyLedger::where('status', 'available')->count();
        $this->assertEquals(1, $availableLedgers);
    }

    public function test_admin_can_reject_payout()
    {
        // Create request
        $this->actingAs($this->authorUser)->post(route('author.payouts.store'), [
            'amount' => 140000
        ]);

        $payout = PayoutRequest::first();

        // Admin rejects
        $response = $this->actingAs($this->adminUser)->post(route('admin.payouts.reject', $payout), [
            'admin_notes' => 'Nomor rekening salah'
        ]);

        $payout->refresh();
        $this->assertEquals('rejected', $payout->status);
        $this->assertEquals('Nomor rekening salah', $payout->admin_notes);
        $this->assertEquals($this->adminUser->id, $payout->processed_by);

        // Ledgers back to available
        $this->assertEquals(3, RoyaltyLedger::where('status', 'available')->count());
        $this->assertEquals(0, RoyaltyLedger::where('status', 'pending')->count());
    }

    public function test_admin_can_complete_payout()
    {
        // Create request
        $this->actingAs($this->authorUser)->post(route('author.payouts.store'), [
            'amount' => 140000
        ]);

        $payout = PayoutRequest::first();

        // Admin completes
        $response = $this->actingAs($this->adminUser)->post(route('admin.payouts.complete', $payout), [
            'reference_number' => 'REF-999-XYZ'
        ]);

        $payout->refresh();
        $this->assertEquals('completed', $payout->status);
        $this->assertEquals('REF-999-XYZ', $payout->reference_number);

        // Ledgers updated to withdrawn
        $this->assertEquals(2, RoyaltyLedger::where('status', 'withdrawn')->count());
        $this->assertEquals(1, RoyaltyLedger::where('status', 'available')->count());
    }

    public function test_unverified_author_cannot_access_payouts()
    {
        $unverifiedUser = User::factory()->create();
        Author::create([
            'user_id' => $unverifiedUser->id,
            'pen_name' => 'Baru',
            'kyc_status' => 'pending',
            'id_card_number' => '1234123412341234',
            'id_card_path' => 'private/kyc/test.jpg',
        ]);

        $response = $this->actingAs($unverifiedUser)->get(route('author.payouts.index'));
        $response->assertRedirect(route('author.kyc-status'));
    }
}
