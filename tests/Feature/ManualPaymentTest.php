<?php

namespace Tests\Feature;

use App\Models\LibraryItem;
use App\Models\ManualOrder;
use App\Models\PaymentMethod;
use App\Models\PaymentSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_payment_methods_are_displayed(): void
    {
        $user = User::factory()->create();
        PaymentMethod::create(['type' => 'bank', 'name' => 'Bank Aktif', 'is_active' => true]);
        PaymentMethod::create(['type' => 'bank', 'name' => 'Bank Nonaktif', 'is_active' => false]);
        $order = $this->order($user);

        $this->actingAs($user)->get(route('manual-orders.show', $order))
            ->assertOk()->assertSee('Bank Aktif')->assertDontSee('Bank Nonaktif');
    }

    public function test_payment_proof_is_stored_privately(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $method = PaymentMethod::create(['type' => 'bank', 'name' => 'Bank Jambi', 'is_active' => true]);
        $order = $this->order($user);

        $this->actingAs($user)->post(route('manual-orders.proof', $order), [
            'payment_method_id' => $method->id, 'amount' => 50000,
            'proof' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertSessionHasNoErrors();

        $submission = PaymentSubmission::firstOrFail();
        Storage::disk('local')->assertExists($submission->proof_path);
        $this->assertStringStartsWith('payment-proofs/', $submission->proof_path);
        $this->assertDatabaseHas('manual_orders', ['id' => $order->id, 'status' => 'payment_submitted']);
    }

    public function test_admin_can_verify_and_grant_digital_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $item = $this->item();
        $method = PaymentMethod::create(['type' => 'bank', 'name' => 'Mandiri', 'is_active' => true]);
        $order = $this->order($user, $item, 'payment_submitted');
        $submission = PaymentSubmission::create(['manual_order_id' => $order->id, 'payment_method_id' => $method->id, 'amount' => 50000, 'proof_path' => 'payment-proofs/a.jpg', 'status' => 'submitted', 'submitted_at' => now()]);

        $this->actingAs($admin)->post(route('admin.payments.verify', $submission))->assertRedirect();
        $this->assertDatabaseHas('payment_submissions', ['id' => $submission->id, 'status' => 'verified', 'verified_by' => $admin->id]);
        $this->assertDatabaseHas('library_access_grants', ['user_id' => $user->id, 'library_item_id' => $item->id, 'source_type' => 'manual_order']);
    }

    public function test_admin_can_reject_without_granting_access(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $item = $this->item();
        $method = PaymentMethod::create(['type' => 'bank', 'name' => 'SeaBank', 'is_active' => true]);
        $order = $this->order($user, $item, 'payment_submitted');
        $submission = PaymentSubmission::create(['manual_order_id' => $order->id, 'payment_method_id' => $method->id, 'amount' => 50000, 'proof_path' => 'payment-proofs/b.jpg', 'status' => 'submitted', 'submitted_at' => now()]);

        $this->actingAs($admin)->post(route('admin.payments.reject', $submission), ['rejection_reason' => 'Nominal tidak cocok'])->assertRedirect();
        $this->assertDatabaseHas('payment_submissions', ['id' => $submission->id, 'status' => 'rejected', 'rejection_reason' => 'Nominal tidak cocok']);
        $this->assertDatabaseMissing('library_access_grants', ['user_id' => $user->id, 'library_item_id' => $item->id]);
    }

    private function item(): LibraryItem
    {
        return LibraryItem::create(['type' => 'book', 'title' => 'Buku Manual', 'source_type' => 'local', 'access_policy' => 'manual_purchase', 'price' => 50000, 'status' => 'published', 'published_at' => now()]);
    }

    private function order(User $user, ?LibraryItem $item = null, string $status = 'awaiting_payment'): ManualOrder
    {
        $item ??= $this->item();
        $order = ManualOrder::create(['user_id' => $user->id, 'order_type' => 'digital_publication', 'subtotal' => 50000, 'shipping_cost' => 0, 'total' => 50000, 'status' => $status]);
        $order->items()->create(['item_type' => LibraryItem::class, 'item_id' => $item->id, 'description' => $item->title, 'quantity' => 1, 'unit_price' => 50000, 'subtotal' => 50000]);
        return $order;
    }
}
