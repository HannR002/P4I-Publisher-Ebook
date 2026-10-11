<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLicense;
use App\Models\BookSubmission;
use App\Models\LibraryItem;
use App\Models\User;
use App\Services\SessionRevocationService;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\Support\FinancialFixtures;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use FinancialFixtures, RefreshDatabase;

    public function test_shared_signed_pdf_url_cannot_be_used_by_other_user_on_same_ip(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::create(['title' => 'Private PDF', 'author' => 'Synthetic Author', 'price' => 0, 'file_path' => 'private_books/a.pdf', 'is_published' => true]);
        Storage::disk('local')->put($book->file_path, '%PDF-1.4 synthetic');
        $license = BookLicense::create(['user_id' => $owner->id, 'book_id' => $book->id, 'license_key' => (string) Str::uuid(), 'status' => 'active']);
        $url = URL::temporarySignedRoute('drm.stream', now()->addMinutes(15), ['license_key' => $license->license_key, 'ip' => '127.0.0.1']);
        $this->actingAs($other)->get($url)->assertNotFound();
        $this->actingAs($owner)->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_revoked_license_cannot_save_progress(): void
    {
        $owner = User::factory()->create();
        $book = Book::create(['title' => 'Revoked PDF', 'author' => 'Synthetic', 'price' => 0, 'file_path' => 'a.pdf']);
        $license = BookLicense::create(['user_id' => $owner->id, 'book_id' => $book->id, 'license_key' => (string) Str::uuid(), 'status' => 'revoked']);
        $this->actingAs($owner)->post(route('drm.progress'), ['license_key' => $license->license_key, 'page' => 2])->assertForbidden();
        $this->assertNotEquals(2, $license->fresh()->last_read_page);
    }

    public function test_unpublished_library_file_cannot_be_read_downloaded_or_purchased(): void
    {
        Storage::fake('local');
        $item = LibraryItem::create(['type' => 'book', 'title' => 'Draft', 'source_type' => 'local', 'access_policy' => 'public_read_download', 'price' => 0, 'status' => 'draft']);
        Storage::disk('local')->put('draft.pdf', '%PDF-1.4 synthetic');
        $item->files()->create(['file_type' => 'publication', 'file_path' => 'draft.pdf', 'mime_type' => 'application/pdf', 'download_allowed' => true]);
        $this->get(route('library.read', $item))->assertNotFound();
        $this->get(route('library.download', $item))->assertNotFound();
        $item->update(['access_policy' => 'manual_purchase']);
        $this->actingAs(User::factory()->create())->post(route('manual-orders.store', $item))->assertNotFound();
        $this->assertDatabaseCount('manual_orders', 0);
    }

    public function test_kyc_document_uses_correct_private_disk_and_requires_admin(): void
    {
        Storage::fake('local');
        $author = $this->financialAuthor();
        $author->update(['id_card_path' => 'private/kyc/a.jpg']);
        Storage::disk('local')->put($author->id_card_path, 'synthetic image');
        $this->actingAs($author->user)->get(route('admin.kyc.stream-id-card', $author))->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('admin.kyc.stream-id-card', $author))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_manuscript_stream_uses_correct_private_disk_and_requires_admin(): void
    {
        Storage::fake('local');
        $author = $this->financialAuthor();
        $submission = BookSubmission::create(['author_id' => $author->id, 'title' => 'Synthetic Submission', 'synopsis' => 'Synthetic content', 'proposed_price' => 0, 'manuscript_path' => 'private/submissions/a.pdf', 'status' => 'submitted']);
        Storage::disk('local')->put($submission->manuscript_path, '%PDF-1.4 synthetic');
        $this->actingAs($author->user)->get(route('admin.submissions.stream', $submission))->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('admin.submissions.stream', $submission))->assertOk();
    }

    public function test_bank_fields_are_encrypted_and_legacy_read_backfill_is_idempotent(): void
    {
        $author = $this->financialAuthor();
        $raw = DB::table('authors')->where('id', $author->id)->value('bank_account');
        $this->assertStringStartsWith('enc:v1:', $raw);
        $this->assertSame('0000000000', $author->fresh()->bank_account);
        $this->assertArrayNotHasKey('bank_account', $author->toArray());
        DB::table('authors')->where('id', $author->id)->update(['bank_account' => '1111111111']);
        $this->assertSame('1111111111', $author->fresh()->bank_account);
        $this->artisan('security:encrypt-financial-data')->assertSuccessful();
        $encrypted = DB::table('authors')->where('id', $author->id)->value('bank_account');
        $this->assertStringStartsWith('enc:v1:', $encrypted);
        $this->artisan('security:encrypt-financial-data')->expectsOutput('Records encrypted: 0')->assertSuccessful();
        $this->assertSame($encrypted, DB::table('authors')->where('id', $author->id)->value('bank_account'));
    }

    public function test_account_deletion_preserves_author_financial_history(): void
    {
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author);
        $this->actingAs($author->user)->delete(route('profile.destroy'), ['password' => 'password'])->assertSessionHasErrorsIn('userDeletion', ['account']);
        $this->assertDatabaseHas('users', ['id' => $author->user_id]);
        $this->assertDatabaseHas('royalty_ledgers', ['id' => $ledger->id]);
    }

    public function test_registration_cannot_mass_assign_admin_role(): void
    {
        $this->post('/register', ['name' => 'Synthetic User', 'email' => 'synthetic@example.test', 'password' => 'test-password', 'password_confirmation' => 'test-password', 'is_admin' => true])->assertSessionHasNoErrors();
        $this->assertFalse(User::where('email', 'synthetic@example.test')->firstOrFail()->is_admin);
    }

    public function test_stale_session_hash_is_invalidated_after_password_change(): void
    {
        $user = User::factory()->create();
        $previousHash = $user->password;
        $user->update(['password' => Hash::make('new-synthetic-password')]);
        $this->actingAs($user->fresh())->withSession(['password_hash_web' => $previousHash])->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_database_session_revocation_keeps_only_current_session(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create();
        foreach (['old-session', 'current-session'] as $id) {
            DB::table('sessions')->insert(['id' => $id, 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        }
        app(SessionRevocationService::class)->revoke($user->id, 'current-session');
        $this->assertDatabaseMissing('sessions', ['id' => 'old-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'current-session']);
        app(SessionRevocationService::class)->revoke($user->id);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    public function test_production_admin_seeding_is_prohibited(): void
    {
        $this->app['env'] = 'production';
        $this->expectException(\RuntimeException::class);
        app(AdminSeeder::class)->run();
    }
}
