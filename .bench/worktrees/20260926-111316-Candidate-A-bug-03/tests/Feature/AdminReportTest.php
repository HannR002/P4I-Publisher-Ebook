<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->customer = User::factory()->create();
    }

    public function test_admin_can_view_reports_and_metrics_are_accurate()
    {
        $book = Book::create([
            'title' => 'Test', 'author' => 'A', 'description' => 'D', 'price' => 50000, 'file_path' => 'f', 'is_published' => true
        ]);

        $orderSuccess = Order::create(['id' => 'O1', 'user_id' => $this->customer->id, 'gross_amount' => 50000, 'status' => 'success', 'payment_type' => 'bank']);
        OrderItem::create(['order_id' => $orderSuccess->id, 'book_id' => $book->id, 'price' => 50000]);

        $orderPending = Order::create(['id' => 'O2', 'user_id' => $this->customer->id, 'gross_amount' => 100000, 'status' => 'pending', 'payment_type' => 'bank']);
        OrderItem::create(['order_id' => $orderPending->id, 'book_id' => $book->id, 'price' => 100000]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);

        // Assert pending order is NOT included in metrics
        $response->assertViewHas('totalRevenue', 50000);
        $response->assertViewHas('totalTransactions', 1);
        $response->assertViewHas('averageOrderValue', 50000);
        $response->assertViewHas('totalBooksSold', 1);
    }

    public function test_admin_can_export_orders_csv()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.orders'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('"Order ID",Tanggal,Waktu,"Nama Pembeli"', $csvContent);
    }

    public function test_admin_can_export_books_csv()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.books'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_customer_cannot_access_reports()
    {
        $response = $this->actingAs($this->customer)->get(route('admin.reports.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->customer)->get(route('admin.reports.export.orders'));
        $response->assertStatus(403);
    }
}
