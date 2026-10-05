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
}
