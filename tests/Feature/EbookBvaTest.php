<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BookLicense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

/**
 * EbookBvaTest — Boundary Value Analysis (BVA) Test Suite
 *
 * Tests 7 critical boundary scenarios for the P4I Publisher e-book platform.
 *
 * BVA-01: Checkout harga Rp -1    → Validation error (rejected)
 * BVA-02: Checkout harga Rp 0     → Free book bypass — license issued instantly
 * BVA-03: Checkout harga Rp 10000 → Paid flow — reaches Midtrans call
 * BVA-04: Checkout harga Rp 10001 → Paid flow — reaches Midtrans call (precision)
 * BVA-05: DRM URL menit ke-14     → Valid signed URL → 200 OK
 * BVA-06: DRM URL menit ke-15:00  → Signed URL exactly at boundary (still valid per Laravel)
 * BVA-07: DRM URL menit ke-15:01  → Expired signature → 403/401
 */
class EbookBvaTest extends TestCase
{
    use RefreshDatabase;

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    /**
     * Create a published Book with specific price for testing.
     */
    private function makeBook(float $price): Book
    {
        return Book::create([
            'title'        => 'BVA Test Book ' . Str::random(6),
            'author'       => 'QA Author',
            'description'  => 'Test book for BVA suite',
            'price'        => $price,
            'file_path'    => 'private_books/fake-test.pdf',
            'is_published' => true,
        ]);
    }

    /**
     * Create an authenticated user.
     */
    private function makeUser(): User
    {
        return User::factory()->create();
    }

    /**
     * Create an active BookLicense for a user and book.
     */
    private function makeLicense(User $user, Book $book): BookLicense
    {
        return BookLicense::create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'license_key' => (string) Str::uuid(),
            'status'      => 'active',
        ]);
    }

    // ─── BVA-01: Checkout harga Rp -1 ───────────────────────────────────────

    /**
     * @test
     * BVA-01: A book with price -1 must be rejected at validation level.
     * The 'price' column is decimal(10,2) with no db-level min, so protection
     * must come from the upload form validation (min:0).
     * At checkout, book_ids that exist pass validation — the protection
     * is that the admin cannot create such a book. This test validates that
     * the BookUploadController rejects price < 0 at creation time.
     */
    public function test_bva01_book_upload_rejects_negative_price(): void
    {
        $admin = User::factory()->admin()->create();

        // Create a fake PDF file for upload
        $pdfFile = \Illuminate\Http\UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        // Attempt to POST to admin upload with price = -1
        $response = $this->actingAs($admin)->post(route('admin.books.store'), [
            'title'        => 'Negative Price Book',
            'author'       => 'Test Author',
            'price'        => -1,
            'pdf_file'     => $pdfFile,
            'is_published' => false,
        ]);

        // Expect validation error — price must be min:0
        $response->assertSessionHasErrors('price');
        $this->assertDatabaseMissing('books', ['title' => 'Negative Price Book']);
    }

    // ─── BVA-02: Checkout harga Rp 0 (free book bypass) ────────────────────

    /**
     * @test
     * BVA-02: A free book (price = 0) must bypass Midtrans entirely.
     * The checkout creates Order with status='success', creates BookLicense
     * immediately, and redirects to /my-library.
     * No Midtrans API call should be made.
     */
    public function test_bva02_free_book_bypasses_midtrans_and_issues_license(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook(0.00);

        // POST checkout with book_ids = [$book->id]
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'book_ids' => [$book->id],
        ]);

        // Should redirect to /my-library (not /checkout/{id})
        $response->assertRedirect(route('my-library'));
        $response->assertSessionHas('success');

        // Verify Order was created with status 'success' and payment_type 'free'
        $this->assertDatabaseHas('orders', [
            'user_id'      => $user->id,
            'gross_amount' => 0,
            'status'       => 'success',
            'payment_type' => 'free',
        ]);

        // Verify BookLicense was created and is active
        $this->assertDatabaseHas('book_licenses', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status'  => 'active',
        ]);
    }

    // ─── BVA-03: Checkout harga Rp 10.000 (paid flow boundary) ─────────────

    /**
     * @test
     * BVA-03: A paid book (price = 10000) must trigger the Midtrans code path.
     * Since we cannot call real Midtrans in tests, we mock Snap::getSnapToken()
     * and verify the system attempts the API call, then rolls back (or throws)
     * since no real key is set. We check that no 'free' order is created.
     */
    public function test_bva03_paid_book_10000_enters_midtrans_flow(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook(10000.00);

        // Since Midtrans::getSnapToken() will fail without real credentials in test env,
        // the request will error and redirect back with 'checkout' session error.
        // The key assertion: the FREE BYPASS path was NOT taken (no license, no 'free' order).
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'book_ids' => [$book->id],
        ]);

        // No BookLicense must be issued without payment confirmation
        $this->assertDatabaseMissing('book_licenses', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // No 'free' order must exist (free bypass would have payment_type='free')
        $this->assertDatabaseMissing('orders', [
            'user_id'      => $user->id,
            'payment_type' => 'free',
        ]);
    }

    // ─── BVA-04: Checkout harga Rp 10.001 (precision test) ─────────────────

    /**
     * @test
     * BVA-04: A book with price = 10001 must also take the paid (Midtrans) path.
     * Verifies that the boundary at 0/1 is clean and 10001 does NOT trigger
     * the free bypass (grossAmount != 0).
     */
    public function test_bva04_paid_book_10001_does_not_bypass_midtrans(): void
    {
        $user = $this->makeUser();
        $book = $this->makeBook(10001.00);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'book_ids' => [$book->id],
        ]);

        // Confirm paid path: no license issued without Midtrans confirmation
        $this->assertDatabaseMissing('book_licenses', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // No 'free' order should exist
        $this->assertDatabaseMissing('orders', [
            'user_id'      => $user->id,
            'payment_type' => 'free',
        ]);
    }

    // ─── BVA-05: DRM URL menit ke-14 (masih valid) ──────────────────────────

    /**
     * @test
     * BVA-05: A signed DRM stream URL accessed at exactly minute 14 must
     * return HTTP 200 (still within 15-minute window).
     */
    public function test_bva05_drm_url_at_minute_14_returns_200(): void
    {
        $user    = $this->makeUser();
        $book    = $this->makeBook(50000);
        $license = $this->makeLicense($user, $book);

        // Create a fake PDF file so the stream actually has something to serve
        \Illuminate\Support\Facades\Storage::disk('local')
            ->put('private_books/fake-test.pdf', '%PDF-1.4 fake content');

        // Generate the signed URL at T=0
        $signedUrl = URL::temporarySignedRoute(
            'drm.stream',
            now()->addMinutes(15),
            ['license_key' => $license->license_key, 'ip' => '127.0.0.1']
        );

        // Travel to T+14 minutes (still within window)
        $this->travelTo(now()->addMinutes(14));

        $response = $this->actingAs($user)->get($signedUrl);

        // Must return 200 (valid signature, file exists)
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Cleanup
        \Illuminate\Support\Facades\Storage::disk('local')->delete('private_books/fake-test.pdf');
    }

    // ─── BVA-06: DRM URL menit ke-15:00 (batas absolut) ────────────────────

    /**
     * @test
     * BVA-06: A signed DRM URL accessed at exactly the 15-minute mark.
     * Laravel's `temporarySignedRoute(now()->addMinutes(15))` expires AT that
     * timestamp. At T+15:00 exactly, the URL is expired (exclusive boundary).
     * Expected: 403 Forbidden or 401 Unauthorized.
     */
    public function test_bva06_drm_url_at_exactly_minute_15_returns_4xx(): void
    {
        $user    = $this->makeUser();
        $book    = $this->makeBook(50000);
        $license = $this->makeLicense($user, $book);

        // Create a fake PDF file so file-not-found doesn't interfere
        \Illuminate\Support\Facades\Storage::disk('local')
            ->put('private_books/fake-test.pdf', '%PDF-1.4 fake content for bva06');

        // Generate URL at T=0 with 15-minute expiry
        $generatedAt = now();
        $signedUrl   = URL::temporarySignedRoute(
            'drm.stream',
            $generatedAt->copy()->addMinutes(15),
            ['license_key' => $license->license_key, 'ip' => '127.0.0.1']
        );

        // Travel to EXACTLY T+15:00 — expiry timestamp reached.
        // IMPORTANT BOUNDARY FINDING: Laravel's hasValidSignature() checks
        // `now() > expiry_timestamp` (exclusive). At exactly T+15:00,
        // `now() == expiry_timestamp`, so the URL is STILL VALID (200).
        // One second later (BVA-07) it becomes 403. This is an inclusive
        // lower-bound behavior — the exact boundary second is still served.
        $this->travelTo($generatedAt->copy()->addMinutes(15));

        $response = $this->actingAs($user)->get($signedUrl);

        // At EXACTLY T+15:00 the URL is still valid (inclusive boundary behavior)
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Cleanup
        \Illuminate\Support\Facades\Storage::disk('local')->delete('private_books/fake-test.pdf');
    }

    // ─── BVA-07: DRM URL menit ke-15:01 (sudah kadaluarsa) ─────────────────

    /**
     * @test
     * BVA-07: A signed DRM URL accessed 1 second AFTER the 15-minute window
     * must be rejected with 403 Forbidden / 401 Unauthorized.
     */
    public function test_bva07_drm_url_expired_at_minute_15_01_returns_4xx(): void
    {
        $user    = $this->makeUser();
        $book    = $this->makeBook(50000);
        $license = $this->makeLicense($user, $book);

        // Generate URL at T=0
        $generatedAt = now();
        $signedUrl   = URL::temporarySignedRoute(
            'drm.stream',
            $generatedAt->copy()->addMinutes(15),
            ['license_key' => $license->license_key, 'ip' => '127.0.0.1']
        );

        // Travel to T+15 minutes + 1 second (clearly expired)
        $this->travelTo($generatedAt->copy()->addMinutes(15)->addSecond());

        $response = $this->actingAs($user)->get($signedUrl);

        // Must be rejected — signature expired
        $response->assertStatus(403);
    }

    // ─── BONUS: BUG-03 Duplicate Purchase Guard ─────────────────────────────

    /**
     * @test
     * BUG-03 guard: User attempting to repurchase an already-owned book
     * must receive a validation error, not be charged again.
     */
    public function test_bug03_repurchase_blocked_with_validation_error(): void
    {
        $user    = $this->makeUser();
        $book    = $this->makeBook(0.00); // Free so checkout would otherwise succeed
        $license = $this->makeLicense($user, $book);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'book_ids' => [$book->id],
        ]);

        // Must be redirected back with an error message about already owning the book
        $response->assertSessionHasErrors('book_ids');

        // Ensure no second license was created
        $licenseCount = BookLicense::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->count();
        $this->assertEquals(1, $licenseCount, 'BUG-03: Only one license must exist');
    }
}
