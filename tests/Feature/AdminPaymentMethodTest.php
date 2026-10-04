<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        Storage::fake('public');
    }

    public function test_admin_can_list_payment_methods()
    {
        PaymentMethod::create([
            'type' => 'bank',
            'name' => 'Bank BCA',
            'account_name' => 'PT P4I',
            'account_number' => '12345678',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payment-methods.index'));
        $response->assertStatus(200);
        $response->assertSee('Bank BCA');
    }

    public function test_admin_can_create_payment_method()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payment-methods.store'), [
            'type' => 'e_wallet',
            'name' => 'Gopay',
            'account_name' => 'PT P4I Gopay',
            'account_number' => '081234567890',
            'is_active' => true,
            'qr_image' => UploadedFile::fake()->image('qr.png'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('payment_methods', ['name' => 'Gopay']);
        $method = PaymentMethod::where('name', 'Gopay')->first();
        $this->assertNotNull($method->qr_image_path);
        Storage::disk('public')->assertExists($method->qr_image_path);
    }

    public function test_admin_can_update_payment_method()
    {
        $method = PaymentMethod::create([
            'type' => 'bank',
            'name' => 'Bank BCA',
            'account_name' => 'PT P4I',
            'account_number' => '12345678',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.payment-methods.update', $method), [
            'type' => 'bank',
            'name' => 'Bank BCA Updated',
            'account_name' => 'PT P4I',
            'account_number' => '12345678',
            'is_active' => false,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('payment_methods', [
            'id' => $method->id,
            'name' => 'Bank BCA Updated',
            'is_active' => false,
        ]);
    }

    public function test_normal_user_denied()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get(route('admin.payment-methods.index'));
        $response->assertStatus(403);
    }
}
