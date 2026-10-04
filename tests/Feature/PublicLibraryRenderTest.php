<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\DevelopmentVisualFixtureSeeder;
use App\Models\User;
use App\Models\ManualOrder;
use App\Models\LibraryItem;

class PublicLibraryRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable the local environment check for this specific test so the seeder can run in testing
        $this->app['env'] = 'local';
        
        $this->seed(DevelopmentVisualFixtureSeeder::class);
        
        $this->app['env'] = 'testing';
    }

    public function test_library_catalog_renders()
    {
        $this->get('/library')->assertOk()->assertSee('Jurnal Contoh P4I');
    }

    public function test_book_detail_renders()
    {
        $this->get('/library/buku-publik-test')->assertOk()->assertSee('Buku Publik Test');
    }

    public function test_journal_detail_renders()
    {
        $this->get('/library/jurnal-contoh-p4i-fixture')->assertOk()->assertSee('Jurnal Contoh P4I');
    }

    public function test_issue_detail_renders()
    {
        $this->get('/library/jurnal-contoh-p4i-vol-1-no-1')->assertOk()->assertSee('Vol. 1 No. 1 (2026)');
    }

    public function test_article_detail_renders()
    {
        $this->get('/library/artikel-contoh-pengujian-tampilan')->assertOk()->assertSee('Artikel Contoh untuk Pengujian Tampilan Phase 3B');
    }

    public function test_digital_reader_renders_for_authorized_user()
    {
        $user = User::where('email', 'fixture_test@p4i.test')->firstOrFail();
        $legacyBook = \DB::table('books')->where('slug', 'legacy-book-for-drm-test')->first();
        
        $this->actingAs($user)
             ->get('/reader/' . $legacyBook->id)
             ->assertOk()
             ->assertSee('Pembaca Digital');
    }

    public function test_manual_order_ux_renders_for_authorized_user()
    {
        $user = User::where('email', 'fixture_test@p4i.test')->firstOrFail();
        $order = ManualOrder::firstOrFail();
        
        $this->actingAs($user)
             ->get('/manual-orders/' . $order->id)
             ->assertOk();
    }
}
