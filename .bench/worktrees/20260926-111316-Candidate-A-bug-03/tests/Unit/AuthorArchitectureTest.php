<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookSubmission;
use App\Models\PayoutRequest;
use App\Models\RoyaltyLedger;
use App\Models\SubmissionReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthorArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_author_submission_relations()
    {
        $user = User::factory()->create();
        $author = Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John Doe',
            'id_card_number' => '1234567890123456',
        ]);

        $submission = BookSubmission::create([
            'author_id' => $author->id,
            'title' => 'Test Book',
            'synopsis' => 'Test Synopsis',
            'manuscript_path' => 'dummy.pdf',
        ]);

        $reviewer = User::factory()->create();
        $review = SubmissionReview::create([
            'submission_id' => $submission->id,
            'reviewer_id' => $reviewer->id,
            'feedback' => 'Good job',
            'action' => 'approve',
        ]);

        $this->assertEquals($author->id, $user->authorProfile->id);
        $this->assertEquals($submission->id, $author->submissions->first()->id);
        $this->assertEquals($review->id, $submission->reviews->first()->id);
        $this->assertEquals($reviewer->id, $review->reviewer->id);
    }

    public function test_id_card_encryption()
    {
        $user = User::factory()->create();
        $author = Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John Doe',
            'id_card_number' => '1234567890123456',
        ]);

        $rawDbValue = \Illuminate\Support\Facades\DB::table('authors')->where('id', $author->id)->value('id_card_number');
        
        $this->assertNotEquals('1234567890123456', $rawDbValue);
        $this->assertEquals('1234567890123456', $author->id_card_number);
    }

    public function test_payout_request_auto_generates_ulid()
    {
        $user = User::factory()->create();
        $author = Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John Doe',
            'id_card_number' => '1234567890123456',
        ]);

        $payout = PayoutRequest::create([
            'author_id' => $author->id,
            'amount' => 100000,
        ]);

        $this->assertNotNull($payout->id);
        $this->assertEquals(26, strlen($payout->id));
    }

    public function test_author_available_balance_calculation()
    {
        $user = User::factory()->create();
        $author = Author::create([
            'user_id' => $user->id,
            'pen_name' => 'John Doe',
            'id_card_number' => '1234567890123456',
        ]);

        $book = Book::create([
            'title' => 'Test',
            'author' => 'Test',
            'description' => 'Test',
            'file_path' => 'dummy',
            'price' => 100,
            'is_published' => true,
        ]);

        $order = \App\Models\Order::create([
            'id' => 'ORD-1',
            'user_id' => $user->id,
            'gross_amount' => 100,
            'status' => 'success',
            'payment_type' => 'bank_transfer',
        ]);

        $orderItem1 = \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'price' => 100,
        ]);

        $orderItem2 = \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'price' => 150,
        ]);

        RoyaltyLedger::create([
            'author_id' => $author->id,
            'order_item_id' => $orderItem1->id,
            'book_id' => $book->id,
            'gross_sale' => 100,
            'author_earning' => 70,
            'platform_earning' => 30,
            'status' => 'available',
        ]);

        RoyaltyLedger::create([
            'author_id' => $author->id,
            'order_item_id' => $orderItem2->id,
            'book_id' => $book->id,
            'gross_sale' => 150,
            'author_earning' => 105,
            'platform_earning' => 45,
            'status' => 'withdrawn',
        ]);

        $this->assertEquals(70.00, $author->available_balance);
    }
}
