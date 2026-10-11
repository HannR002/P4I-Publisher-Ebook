<?php

namespace Tests\Support;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\RoyaltyLedger;
use App\Models\User;
use Illuminate\Support\Str;

trait FinancialFixtures
{
    protected function financialAuthor(): Author
    {
        return Author::create([
            'user_id' => User::factory()->create()->id, 'pen_name' => 'Synthetic Author',
            'kyc_status' => 'verified', 'id_card_number' => null,
            'bank_name' => 'Test Bank', 'bank_account' => '0000000000', 'bank_holder_name' => 'Synthetic Account',
        ]);
    }

    protected function financialLedger(Author $author, string $earning = '210000.00'): RoyaltyLedger
    {
        $book = Book::create(['author_id' => $author->id, 'title' => 'Synthetic Book '.Str::uuid(), 'author' => $author->pen_name, 'price' => $earning, 'file_path' => 'synthetic.pdf', 'is_published' => true]);
        $order = Order::create(['id' => (string) Str::uuid(), 'user_id' => User::factory()->create()->id, 'gross_amount' => $earning, 'status' => 'success']);
        $item = $order->items()->create(['book_id' => $book->id, 'price' => $earning]);

        return RoyaltyLedger::create(['author_id' => $author->id, 'book_id' => $book->id, 'order_item_id' => $item->id, 'gross_sale' => $earning, 'author_percentage' => 100, 'author_earning' => $earning, 'platform_earning' => 0, 'status' => 'available']);
    }
}
