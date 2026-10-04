<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LibraryItem;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\ManualOrder;
use App\Models\ManualOrderItem;
use App\Models\PaymentSubmission;
use App\Models\BookLicense;
use Illuminate\Support\Str;

class DevelopmentVisualFixtureSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('DevelopmentVisualFixtureSeeder cannot run in production.');
            return;
        }

        $this->command->info('Creating Development Visual Fixtures...');

        // Base user for orders
        $user = User::firstOrCreate(
            ['email' => 'fixture_test@p4i.test'],
            ['name' => 'Visual Test User', 'password' => bcrypt('password123')]
        );

        // 1. Create a Fake PDF File
        Storage::disk('local')->put('fixtures/test_reader.pdf', '%PDF-1.4 Fake test pdf content for visual check');
        $pdfPath = 'fixtures/test_reader.pdf';

        // 2. Journal Hierarchy
        $journal = LibraryItem::updateOrCreate(
            ['slug' => 'jurnal-contoh-p4i-fixture'],
            [
            'title' => 'Jurnal Contoh P4I',
            'type' => 'journal',
            'description' => 'Jurnal ilmiah multidisiplin yang diterbitkan sebagai contoh tampilan untuk pengembangan antarmuka pengguna Phase 3B.',
            'publisher' => 'P4I Press',
            'issn' => '1234-5678',
            'publication_year' => 2026,
            'status' => 'published',
            'access_policy' => 'public',
        ]);

        $issue = LibraryItem::updateOrCreate(
            ['slug' => 'jurnal-contoh-p4i-vol-1-no-1'],
            [
            'title' => 'Vol. 1 No. 1 (2026)',
            'type' => 'journal_issue',
            'parent_id' => $journal->id,
            'publication_year' => 2026,
            'publication_date' => now(),
            'status' => 'published',
            'access_policy' => 'public',
        ]);

        $article = LibraryItem::updateOrCreate(
            ['slug' => 'artikel-contoh-pengujian-tampilan'],
            [
            'title' => 'Artikel Contoh untuk Pengujian Tampilan Phase 3B',
            'type' => 'journal_article',
            'parent_id' => $issue->id,
            'abstract' => "Ini adalah abstrak sintetik yang dibuat khusus untuk pengujian antarmuka. Abstrak ini berfungsi untuk menguji apakah tata letak, margin, typografi, dan panjang teks dirender dengan benar pada komponen artikel jurnal, baik dalam mode terang maupun gelap.\n\nPenelitian ini menemukan bahwa pengujian visual manual sangat penting untuk memastikan tidak ada teks yang menumpuk atau 'overflow' pada layar berukuran kecil.",
            'doi' => '10.12345/p4i.v1i1.fixture',
            'publication_year' => 2026,
            'status' => 'published',
            'access_policy' => 'public',
            'featured_excerpt' => 'Pengujian visual manual sangat penting untuk UI yang responsif.',
            'excerpt_source' => 'Simpulan Penelitian',
            'excerpt_page' => '14',
        ]);

        $article->creators()->updateOrCreate(['name' => 'Budi Penguji'], ['role' => 'author']);

        // 3. Books
        $bookPublic = LibraryItem::updateOrCreate(
            ['slug' => 'buku-publik-test'],
            [
            'title' => 'Buku Publik Test (Baca & Download)',
            'type' => 'book',
            'synopsis' => 'Buku pengujian ini bersifat gratis untuk dibaca dan diunduh oleh publik.',
            'isbn' => '978-0-00-000000-1',
            'publisher' => 'P4I Press',
            'publication_year' => 2026,
            'status' => 'published',
            'access_policy' => 'public',
        ]);
        $bookPublic->files()->updateOrCreate(['mime_type' => 'application/pdf'], ['file_path' => $pdfPath, 'download_allowed' => true, 'is_primary' => true]);

        $bookProtected = LibraryItem::updateOrCreate(
            ['slug' => 'buku-premium-test'],
            [
            'title' => 'Buku Premium Test (Berbayar Digital)',
            'type' => 'book',
            'synopsis' => 'Buku pengujian berbayar.',
            'isbn' => '978-0-00-000000-2',
            'publisher' => 'P4I Press',
            'publication_year' => 2026,
            'status' => 'published',
            'access_policy' => 'manual_purchase',
        ]);
        $bookProtected->files()->updateOrCreate(['mime_type' => 'application/pdf'], ['file_path' => $pdfPath, 'download_allowed' => false, 'is_primary' => true]);
        $bookProtected->editions()->updateOrCreate(['format' => 'digital'], ['price' => 50000, 'is_active' => true]);

        $bookPhysical = LibraryItem::updateOrCreate(
            ['slug' => 'buku-fisik-test'],
            [
            'title' => 'Buku Fisik Test (Cetak Softcover)',
            'type' => 'book',
            'synopsis' => 'Buku fisik ini hanya bisa dipesan versi cetaknya.',
            'isbn' => '978-0-00-000000-3',
            'publisher' => 'P4I Press',
            'publication_year' => 2026,
            'status' => 'published',
            'access_policy' => 'physical_only',
        ]);
        $bookPhysical->editions()->updateOrCreate(['format' => 'printed_softcover'], ['price' => 75000, 'is_active' => true, 'stock' => 10]);

        // 4. Payment Method
        $paymentMethod = PaymentMethod::updateOrCreate(
            ['name' => 'Bank Test P4I'],
            [
                'type' => 'bank_transfer',
                'account_name' => 'PT Pengujian Antarmuka',
                'account_number' => '0000000000',
                'instructions' => 'Silakan transfer ke rekening fiktif ini untuk pengujian UX.',
                'is_active' => true,
            ]
        );

        // 5. Manual Orders
        $statuses = ['awaiting_payment', 'payment_submitted', 'verified', 'processing', 'rejected'];
        
        foreach ($statuses as $index => $status) {
            $type = $status === 'processing' ? 'physical' : 'digital';
            
            $order = ManualOrder::updateOrCreate(
                ['user_id' => $user->id, 'status' => $status],
                [
                'order_number' => 'TEST-ORD-' . Str::upper(Str::random(5)) . '-' . $index,
                'order_type' => $type,
                'subtotal' => 50000,
                'shipping_cost' => $type === 'physical' ? 15000 : 0,
                'total' => $type === 'physical' ? 65000 : 50000,
                'notes' => 'Generated by fixture seeder'
            ]);

            $order->items()->updateOrCreate(
                ['item_type' => LibraryItem::class, 'item_id' => $type === 'physical' ? $bookPhysical->id : $bookProtected->id],
                [
                'description' => $type === 'physical' ? $bookPhysical->title . ' (Cetak)' : $bookProtected->title . ' (Digital)',
                'quantity' => 1,
                'unit_price' => 50000,
                'subtotal' => 50000
            ]);

            if (in_array($status, ['payment_submitted', 'verified', 'processing', 'rejected'])) {
                // Mock a proof upload
                $proofPath = 'fixtures/proof_mock.png';
                Storage::disk('local')->put($proofPath, 'mock image');
                
                $order->paymentSubmissions()->updateOrCreate(
                    ['payment_method_id' => $paymentMethod->id],
                    [
                    'amount' => $order->total,
                    'proof_path' => $proofPath,
                    'status' => $status === 'rejected' ? 'rejected' : ($status === 'payment_submitted' ? 'pending' : 'verified'),
                    'rejection_reason' => $status === 'rejected' ? 'Gambar tidak jelas atau nominal kurang.' : null,
                    'submitted_at' => now(),
                ]);
            }
        }

        // 6. License for Reader Test
        $legacyBook = DB::table('books')->where('title', 'Legacy Book for DRM Test')->first();
        if (!$legacyBook) {
            $legacyBookId = DB::table('books')->insertGetId([
                'title' => 'Legacy Book for DRM Test',
                'slug' => 'legacy-book-for-drm-test',
                'author' => 'Test Author',
                'price' => 0,
                'file_path' => 'fixtures/test_reader.pdf',
                'is_published' => true,
            ]);
        } else {
            $legacyBookId = $legacyBook->id;
        }
        
        BookLicense::updateOrCreate(
            ['user_id' => $user->id, 'book_id' => $legacyBookId],
            [
            'license_key' => 'TEST-LICENSE-' . Str::random(10),
            'status' => 'active'
        ]);

        // 7. Author Fixtures (Phase 4)
        $authorUser = User::firstOrCreate(
            ['email' => 'author_test@p4i.test'],
            ['name' => 'Visual Author User', 'password' => bcrypt('password123')]
        );

        $authorProfile = \App\Models\Author::updateOrCreate(
            ['user_id' => $authorUser->id],
            [
                'pen_name' => 'Pena Visual',
                'bio' => 'Penulis sintetik untuk pengujian tampilan Author Center Phase 4.',
                'kyc_status' => 'verified',
            ]
        );

        $submissionStatuses = ['draft', 'submitted', 'in_review', 'revision_requested', 'approved', 'rejected', 'published'];
        
        $cat = \App\Models\Category::firstOrCreate(['slug' => 'fiksi-ilmiah'], ['name' => 'Fiksi Ilmiah']);

        foreach ($submissionStatuses as $idx => $status) {
            $sub = \App\Models\BookSubmission::updateOrCreate(
                ['author_id' => $authorProfile->id, 'status' => $status],
                [
                    'title' => 'Naskah Contoh ' . ucfirst(str_replace('_', ' ', $status)),
                    'category_id' => $cat->id,
                    'synopsis' => "Ini adalah sinopsis buatan untuk naskah contoh dengan status {$status}. Sinopsis ini dirancang agar cukup panjang memenuhi syarat minimal karakter untuk pengujian visual, menampilkan bagaimana teks panjang membungkus dalam kotak. Kami sangat berharap naskah ini bisa diterbitkan dan dibaca luas oleh masyarakat Indonesia di seluruh nusantara tanpa terkecuali.",
                    'proposed_price' => 75000 + ($idx * 5000),
                    'manuscript_path' => 'fixtures/test_reader.pdf',
                ]
            );

            if ($status === 'revision_requested') {
                \App\Models\SubmissionReview::updateOrCreate(
                    ['submission_id' => $sub->id],
                    [
                        'reviewer_id' => $user->id,
                        'feedback' => "Mohon perbaiki struktur bab 3. Selain itu, gambar cover yang diusulkan terlalu gelap.\n\nHarap unggah ulang naskah PDF yang sudah direvisi.",
                        'action' => 'request_revision'
                    ]
                );
            }
            
            if ($status === 'published') {
                $linkedBook = \App\Models\Book::updateOrCreate(
                    ['slug' => 'buku-terbit-penulis-visual'],
                    [
                        'title' => 'Buku Terbit Penulis Visual',
                        'author_id' => $authorProfile->id,
                        'author' => $authorProfile->pen_name,
                        'description' => $sub->synopsis,
                        'price' => $sub->proposed_price,
                        'is_published' => true,
                        'publish_date' => now(),
                        'file_path' => 'fixtures/test_reader.pdf',
                    ]
                );
                
                $sub->update(['book_id' => $linkedBook->id]);
            }
        }

        $this->command->info('Development Visual Fixtures generated successfully!');
    }
}

