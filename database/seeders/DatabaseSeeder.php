<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\BookLicense;
use App\Models\BookSubmission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutRequest;
use App\Models\RoyaltyLedger;
use App\Models\SubmissionReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // === TAHAP 1: PERSIAPAN DIREKTORI & GENERATOR DUMMY FILE ===
        $directories = [
            'private/private_books',
            'private/submissions',
            'private/kyc',
            'public/covers',
        ];

        foreach ($directories as $dir) {
            if (!Storage::disk('local')->exists($dir)) {
                Storage::disk('local')->makeDirectory($dir);
            }
        }

        $dummyPdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000052 00000 n\n0000000101 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF";

        Storage::disk('local')->put('private/private_books/sample_book_1.pdf', $dummyPdfContent);
        Storage::disk('local')->put('private/private_books/sample_book_2.pdf', $dummyPdfContent);
        Storage::disk('local')->put('private/submissions/manuscript_draft.pdf', $dummyPdfContent);
        
        // Buat dummy KTP (gambar base64 kecil)
        $dummyImageBase64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=";
        Storage::disk('local')->put('private/kyc/dummy_ktp.png', base64_decode($dummyImageBase64));

        // === TAHAP 2: SEEDING PENGGUNA & MULTI-ROLE (USERS & AUTHORS) ===
        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@p4i.org'],
            [
                'name' => 'Admin Utama P4I',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_active' => true,
            ]
        );

        // 2. Kurator Editorial
        $curator = User::firstOrCreate(
            ['email' => 'kurator@p4i.org'],
            [
                'name' => 'Kurator Naskah',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_active' => true,
            ]
        );

        // 3. Pembeli (Customer 1 & 2)
        $customer1 = User::firstOrCreate(
            ['email' => 'rian@gmail.com'],
            [
                'name' => 'Rian Mahasiswa',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'siti@gmail.com'],
            [
                'name' => 'Siti Pembaca',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        // 4. Penulis Terverifikasi (Author 1)
        $authorUser1 = User::firstOrCreate(
            ['email' => 'hendra@author.p4i.org'],
            [
                'name' => 'Dr. Hendra Wijaya',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $authorProfile1 = Author::firstOrCreate(
            ['user_id' => $authorUser1->id],
            [
                'pen_name' => 'Dr. Hendra Wijaya, M.Kom.',
                'bio' => 'Dosen dan peneliti di bidang Software Architecture dan Distributed Systems.',
                'id_card_number' => '3201012345678901',
                'id_card_path' => 'private/kyc/dummy_ktp.png',
                'bank_name' => 'Bank Central Asia (BCA)',
                'bank_account' => '8830123456',
                'bank_holder_name' => 'Hendra Wijaya',
                'kyc_status' => 'verified',
                'verified_at' => now()->subDays(30),
            ]
        );

        // 5. Penulis Pending KYC (Author 2)
        $authorUser2 = User::firstOrCreate(
            ['email' => 'ahmad@author.p4i.org'],
            [
                'name' => 'Ahmad Penulis Baru',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $authorProfile2 = Author::firstOrCreate(
            ['user_id' => $authorUser2->id],
            [
                'pen_name' => 'Ahmad F.',
                'bio' => 'Penulis lepas buku metodologi riset.',
                'id_card_number' => '3201098765432109',
                'id_card_path' => 'private/kyc/dummy_ktp.png',
                'bank_name' => 'Bank Mandiri',
                'bank_account' => '1330098765432',
                'bank_holder_name' => 'Ahmad Fauzi',
                'kyc_status' => 'pending',
            ]
        );

        // === TAHAP 3: SEEDING KATEGORI & BUKU KATALOG (BOOKS) ===
        // 1. Buat 5 Kategori
        $categories = ['Rekayasa Perangkat Lunak', 'Kecerdasan Buatan', 'Metodologi Penelitian', 'Manajemen Bisnis Digital', 'Pendidikan & Sosial'];
        $catIds = [];
        foreach ($categories as $cat) {
            $category = Category::firstOrCreate(['name' => $cat], ['slug' => Str::slug($cat)]);
            $catIds[$cat] = $category->id;
        }

        // 2. Buat Buku Terbitan Yayasan (Non-Author, owned by Admin for catalog purpose)
        $book1 = Book::firstOrCreate(
            ['slug' => 'panduan-penulisan-skripsi-tesis-sistem-informasi'],
            [
                'title' => 'Panduan Penulisan Skripsi & Tesis Sistem Informasi',
                'author' => 'Tim P4I',
                'price' => 45000,
                'file_path' => 'private_books/sample_book_1.pdf',
                'is_published' => true,
            ]
        );
        $book1->categories()->sync([$catIds['Metodologi Penelitian'], $catIds['Pendidikan & Sosial']]);

        $book2 = Book::firstOrCreate(
            ['slug' => 'dasar-metodologi-riset-kuantitatif'],
            [
                'title' => 'Dasar Metodologi Riset Kuantitatif',
                'author' => 'Tim P4I',
                'price' => 0,
                'file_path' => 'private_books/sample_book_1.pdf',
                'is_published' => true,
            ]
        );
        $book2->categories()->sync([$catIds['Metodologi Penelitian']]);

        // 3. Buat Buku Terbitan Mitra (Author: Dr. Hendra Wijaya)
        $book3 = Book::firstOrCreate(
            ['slug' => 'clean-architecture-di-laravel-panduan-praktisi'],
            [
                'title' => 'Clean Architecture di Laravel: Panduan Praktisi',
                'author' => $authorProfile1->pen_name,
                'author_id' => $authorProfile1->id,
                'price' => 120000,
                'file_path' => 'private_books/sample_book_2.pdf',
                'is_published' => true,
            ]
        );
        $book3->categories()->sync([$catIds['Rekayasa Perangkat Lunak']]);

        $book4 = Book::firstOrCreate(
            ['slug' => 'mikroservis-dan-cloud-storage-skalabilitas-tinggi'],
            [
                'title' => 'Mikroservis dan Cloud Storage Skalabilitas Tinggi',
                'author' => $authorProfile1->pen_name,
                'author_id' => $authorProfile1->id,
                'price' => 95000,
                'file_path' => 'private_books/sample_book_2.pdf',
                'is_published' => true,
            ]
        );
        $book4->categories()->sync([$catIds['Rekayasa Perangkat Lunak']]);

        // === TAHAP 4: TRANSAKSI RIIL, LISENSI DRM, & ROYALTY LEDGER ===
        // 1. Transaksi Sukses Buku Berbayar (User: Rian Mahasiswa membeli "Clean Architecture di Laravel")
        $order1 = Order::create([
            'id' => (string) Str::ulid(),
            'user_id' => $customer1->id,
            'gross_amount' => 120000,
            'status' => 'success',
            'snap_token' => 'dummy_snap_token_1',
            'payment_type' => 'bank_transfer',
        ]);

        $orderItem1 = OrderItem::create([
            'order_id' => $order1->id,
            'book_id' => $book3->id,
            'price' => 120000,
        ]);

        BookLicense::create([
            'user_id' => $customer1->id,
            'book_id' => $book3->id,
            'license_key' => (string) Str::uuid(),
            'status' => 'active',
        ]);

        RoyaltyLedger::create([
            'author_id' => $authorProfile1->id,
            'order_item_id' => $orderItem1->id,
            'book_id' => $book3->id,
            'gross_sale' => 120000,
            'author_percentage' => 70.00,
            'author_earning' => 84000,
            'platform_earning' => 36000,
            'status' => 'available',
        ]);

        // 2. Transaksi Sukses Tambahan (User: Siti Pembaca membeli "Mikroservis")
        $order2 = Order::create([
            'id' => (string) Str::ulid(),
            'user_id' => $customer2->id,
            'gross_amount' => 95000,
            'status' => 'success',
            'payment_type' => 'echannel',
        ]);

        $orderItem2 = OrderItem::create([
            'order_id' => $order2->id,
            'book_id' => $book4->id,
            'price' => 95000,
        ]);

        BookLicense::create([
            'user_id' => $customer2->id,
            'book_id' => $book4->id,
            'license_key' => (string) Str::uuid(),
            'status' => 'active',
        ]);

        RoyaltyLedger::create([
            'author_id' => $authorProfile1->id,
            'order_item_id' => $orderItem2->id,
            'book_id' => $book4->id,
            'gross_sale' => 95000,
            'author_percentage' => 70.00,
            'author_earning' => 66500,
            'platform_earning' => 28500,
            'status' => 'available',
        ]);

        // 3. Transaksi Buku Gratis Rp 0 (User: Rian mengambil "Dasar Metodologi Riset Kuantitatif")
        $order3 = Order::create([
            'id' => (string) Str::ulid(),
            'user_id' => $customer1->id,
            'gross_amount' => 0,
            'status' => 'success',
            'payment_type' => 'free',
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'book_id' => $book2->id,
            'price' => 0,
        ]);

        BookLicense::create([
            'user_id' => $customer1->id,
            'book_id' => $book2->id,
            'license_key' => (string) Str::uuid(),
            'status' => 'active',
        ]);

        // === TAHAP 5: SIKLUS KURASI NASKAH (BOOK SUBMISSIONS & REVIEWS) ===
        // 1. Submission 1: Status in_review
        $submission1 = BookSubmission::create([
            'author_id' => $authorProfile1->id,
            'title' => 'Desain Sistem Terdistribusi untuk Pemula',
            'synopsis' => 'Buku ini membahas dasar-dasar arsitektur sistem terdistribusi, load balancing, dan caching.',
            'proposed_price' => 110000,
            'manuscript_path' => 'submissions/manuscript_draft.pdf',
            'status' => 'in_review',
        ]);

        // 2. Submission 2: Status revision_requested
        $submission2 = BookSubmission::create([
            'author_id' => $authorProfile1->id,
            'title' => 'Optimasi Query Database SQL',
            'synopsis' => 'Panduan praktis melakukan optimasi query pada database SQL untuk menangani data berskala besar.',
            'proposed_price' => 85000,
            'manuscript_path' => 'submissions/manuscript_draft.pdf',
            'status' => 'revision_requested',
        ]);

        SubmissionReview::create([
            'submission_id' => $submission2->id,
            'reviewer_id' => $curator->id,
            'action' => 'request_revision',
            'feedback' => 'Mohon tambahkan bab komparasi index B-Tree dan Hash Index pada Bab 4 sebelum naskah diterbitkan.',
        ]);

        // 3. Submission 3: Status submitted
        $submission3 = BookSubmission::create([
            'author_id' => $authorProfile2->id,
            'title' => 'Panduan Menulis Opini Ilmiah',
            'synopsis' => 'Buku saku untuk mahasiswa dan akademisi dalam menyusun karya tulis opini yang berkualitas.',
            'proposed_price' => 50000,
            'manuscript_path' => 'submissions/manuscript_draft.pdf',
            'status' => 'submitted',
        ]);

        // === TAHAP 6: SEEDING RIWAYAT PENARIKAN (PAYOUT REQUEST SIMULASI) ===
        PayoutRequest::create([
            'id' => (string) Str::ulid(),
            'author_id' => $authorProfile1->id,
            'amount' => 100000,
            'bank_name_snapshot' => 'Bank Central Asia (BCA)',
            'bank_account_snapshot' => '8830123456',
            'bank_holder_name_snapshot' => 'Hendra Wijaya',
            'status' => 'completed',
            'reference_number' => 'TRF-BCA-20260901-09881',
            'processed_by' => $admin->id,
            'processed_at' => now()->subDays(5),
        ]);
    }
}
