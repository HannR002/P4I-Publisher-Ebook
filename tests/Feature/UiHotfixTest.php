<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UiHotfixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the scroll-to-top component renders correctly on the homepage.
     */
    public function test_homepage_contains_scroll_to_top_component()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Kembali ke atas');
        $response->assertSee('aria-label="Kembali ke atas"', false);
    }

    /**
     * Verify the empty state does not emit raw "document-text".
     */
    public function test_library_detail_empty_state_does_not_emit_raw_document_text()
    {
        $view = $this->blade('<x-empty-state icon="document-text" title="Tidak ada deskripsi" />');

        $view->assertDontSee('>document-text<', false);
        $view->assertDontSee('> document-text <', false);
        // Should contain the SVG for document-text now.
        $view->assertSee('<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"', false);
    }

    public function test_navbar_consolidation()
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Should not see top-level Buku / Jurnal separate from Perpustakaan
        $response->assertDontSee('class="px-3 py-2 hover:text-primary transition-colors focus-ring rounded-lg ">Buku</a>', false);
        $response->assertDontSee('class="px-3 py-2 hover:text-primary transition-colors focus-ring rounded-lg ">Jurnal</a>', false);

        // Should see them inside Perpustakaan dropdown
        $response->assertSee('Perpustakaan');
        $response->assertSee('Semua Koleksi');
        // We know it rendered correctly if it reaches here and the above assertions pass
        $response->assertSee('Semua Koleksi');
    }

    public function test_account_menu_surface()
    {
        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)->get('/library');
        $response->assertStatus(200);

        // Verify the account dropdown no longer has a hardcoded bg-surface/95 wrapper
        $response->assertDontSee('<div class="bg-surface/95 backdrop-blur-md border border-border rounded-xl py-2 mt-1 w-52">', false);
        // Verify it still has the links
        $response->assertSee('Perpustakaan Saya');
        $response->assertSee('Log Out');
    }

    public static function guestAuthPages(): array
    {
        return [
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot-password' => ['/forgot-password'],
            'reset-password' => ['/reset-password/test-token'],
        ];
    }

    /**
     * @dataProvider guestAuthPages
     */
    public function test_guest_auth_pages_are_light_only(string $uri)
    {
        $response = $this->get($uri);
        $response->assertStatus(200);

        $response->assertSee('P4I', false);
        $response->assertSee('Digital Library', false);
        $response->assertDontSee('P4I Publisher Anda', false);

        // Light-only lock is present.
        $response->assertSee('<meta name="color-scheme" content="light only">', false);
        $response->assertSee('style="color-scheme: light;"', false);
        $response->assertSee("classList.remove('dark')", false);

        // The shared dark-theme initializer must not run on auth pages.
        $response->assertDontSee('prefers-color-scheme: dark', false);
        $response->assertDontSee("classList.add('dark')", false);
        $response->assertDontSee('localStorage.theme', false);

        // Accessible form remains.
        $response->assertSee('<form method="POST"', false);
    }

    public function test_login_and_register_forms_remain_accessible()
    {
        $this->get('/login')
            ->assertSee('Selamat Datang Kembali')
            ->assertSee('for="email"', false)
            ->assertSee('for="password"', false)
            ->assertSee('Lupa sandi?')
            ->assertSee('Ingat Saya')
            ->assertSee('MASUK SEKARANG')
            ->assertSee('Daftar Gratis');

        $this->get('/register')
            ->assertSee('Buat Akun Baru')
            ->assertSee('for="name"', false)
            ->assertSee('for="password_confirmation"', false)
            ->assertSee('DAFTAR SEKARANG');
    }

    public function test_public_area_remains_dual_theme()
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('prefers-color-scheme: dark', false);
    }

    public function test_localized_book_type_label()
    {
        $this->assertSame('Buku', \App\Models\LibraryItem::getLocalizedType('book'));
        $this->assertSame('Jurnal', \App\Models\LibraryItem::getLocalizedType('journal'));
        $this->assertSame('Artikel', \App\Models\LibraryItem::getLocalizedType('article'));
    }

    public function test_public_navigation_contains_main_site_link()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('https://p4ijournal.org', false);
        $response->assertSee('Situs Utama P4I');
    }

    public function test_auth_pages_contain_return_to_p4i_link()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('https://p4ijournal.org', false);
        $response->assertSee('Kembali ke P4I');
    }

    public function test_forgot_password_page_is_light_and_accessible()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi?');
        $response->assertSee('KIRIM TAUTAN RESET');
        $response->assertSee('Kembali ke Login');
        $response->assertSee('https://p4ijournal.org', false);
        $response->assertSee('color-scheme" content="light only"', false);
    }

    public function test_password_reset_routes_are_available()
    {
        // GET forgot-password
        $this->get('/forgot-password')->assertStatus(200);
        // GET reset-password/{token}
        $this->get('/reset-password/test-token')->assertStatus(200);
    }
}
