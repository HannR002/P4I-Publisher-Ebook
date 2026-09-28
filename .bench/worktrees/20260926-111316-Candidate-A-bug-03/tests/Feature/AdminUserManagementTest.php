<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLicense;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $this->customer = User::factory()->create(['is_admin' => false, 'is_active' => true]);
    }

    public function test_admin_can_view_user_list()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    public function test_admin_can_suspend_user_and_force_logout()
    {
        // Admin suspends customer
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $this->customer->id));
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'is_active' => false
        ]);

        // Suspended customer tries to access protected route
        $customerResponse = $this->actingAs($this->customer->fresh())
            ->get(route('my-library'));
            
        // Middleware should log them out and redirect to login
        $customerResponse->assertRedirect(route('login'));
        $customerResponse->assertSessionHasErrors('email');
        
        $this->assertGuest();
    }

    public function test_admin_cannot_suspend_self()
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-status', $this->admin->id));
            
        $response->assertSessionHasErrors('error');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'is_active' => true
        ]);
    }

    public function test_admin_can_revoke_license_safely()
    {
        $book = Book::create([
            'title' => 'Test Book',
            'author' => 'Test Author',
            'description' => 'Desc',
            'file_path' => 'dummy.pdf',
            'price' => 10000,
            'is_published' => true,
        ]);

        $license = BookLicense::create([
            'user_id' => $this->customer->id,
            'book_id' => $book->id,
            'license_key' => 'test-key-123',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.licenses.revoke', [$this->customer->id, $license->id]), [
                'reason' => 'Violation of terms'
            ]);
            
        $response->assertRedirect();
        
        // Ensure non-destructive
        $this->assertDatabaseHas('book_licenses', [
            'id' => $license->id,
            'status' => 'revoked',
            'revocation_reason' => 'Violation of terms'
        ]);

        // Attempting to stream DRM should fail
        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'drm.stream',
            now()->addMinutes(15),
            ['license_key' => 'test-key-123', 'ip' => '127.0.0.1']
        );

        $drmResponse = $this->actingAs($this->customer)->get($signedUrl);
        $drmResponse->assertStatus(404);
    }

    public function test_admin_can_restore_revoked_license()
    {
        $book = Book::create([
            'title' => 'Test Book',
            'author' => 'Test Author',
            'description' => 'Desc',
            'file_path' => 'dummy.pdf',
            'price' => 10000,
            'is_published' => true,
        ]);

        $license = BookLicense::create([
            'user_id' => $this->customer->id,
            'book_id' => $book->id,
            'license_key' => 'test-key-123',
            'status' => 'revoked',
            'revocation_reason' => 'Violation',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.users.licenses.restore', [$this->customer->id, $license->id]));
            
        $response->assertRedirect();
        
        $this->assertDatabaseHas('book_licenses', [
            'id' => $license->id,
            'status' => 'active',
            'revocation_reason' => null
        ]);
    }

    public function test_customer_cannot_access_user_management_routes()
    {
        $response1 = $this->actingAs($this->customer)->get(route('admin.users.index'));
        $response1->assertStatus(403);

        $response2 = $this->actingAs($this->customer)->patch(route('admin.users.toggle-status', $this->customer->id));
        $response2->assertStatus(403);
    }
}
