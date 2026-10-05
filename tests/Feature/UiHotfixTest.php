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
}

