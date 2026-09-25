# Implementation Plan: Fase 2 (Skalabilitas, Fitur, & Keamanan)

Dokumen ini merangkum rencana arsitektur dan eksekusi untuk mengembangkan 8 fitur "Backlog Fase 2" ke dalam sistem P4I Publisher. Karena cakupannya sangat besar, implementasi akan dibagi menjadi beberapa komponen.

> [!WARNING]
> **User Review Required**
> Mohon tinjau **Open Questions** di bawah ini sebelum menyetujui rencana ini, karena ada beberapa kebutuhan infrastruktur (S3 dan Redis) yang bergantung pada *environment* lokal Anda.

## ❓ Open Questions (Mohon Dijawab)
1. **Infrastruktur S3 (AWS):** Apakah Anda memiliki kredensial AWS S3 sungguhan (Access Key, Secret Key, Bucket)? Jika tidak, apakah Anda setuju jika kita mengonfigurasi driver S3 namun mengarahkannya ke **MinIO (Local S3 Dummy)** atau mensimulasikannya saja di `.env`?
2. **Infrastruktur Redis:** Apakah aplikasi Redis server (atau Docker Redis) sudah terinstal dan berjalan di sistem Windows Anda? Jika belum, apakah Anda ingin saya menggunakan `CACHE_DRIVER=file` (sistem file lokal) terlebih dahulu sebagai simulasi caching-nya?
3. **Prioritas Eksekusi:** Karena ada 8 fitur besar, eksekusi sekaligus bisa memakan waktu panjang. Apakah Anda setuju jika kita mengeksekusinya secara berurutan, dimulai dari **Database & Model (Migration, Kategori, Review)** terlebih dahulu, lalu dilanjutkan ke **Fitur UI & PDF Viewer**, dan terakhir **Infrastruktur (S3 & Redis)**?

---

## 🛠️ Proposed Changes

### Komponen 1: Pembaruan Skema Database (Migrations)
Kita membutuhkan beberapa tabel dan kolom baru untuk mendukung fitur Bookmark, Reviews, dan Categories.

#### [NEW] Database Migrations
- `*_create_categories_table.php` (tabel: `categories`)
- `*_create_book_category_table.php` (tabel pivot: `book_category`)
- `*_create_reviews_table.php` (tabel: `reviews` - rating 1-5, comment)
- `*_add_last_read_page_to_book_licenses_table.php` (kolom baru: `last_read_page` di lisensi)

#### [MODIFY] Model Classes
- [app/Models/Book.php](file:///d:/P4I_Publisher_Ebook/app/Models/Book.php): Tambah relasi `categories()` dan `reviews()`.
- [app/Models/User.php](file:///d:/P4I_Publisher_Ebook/app/Models/User.php): Tambah relasi `reviews()`.
- [app/Models/Order.php](file:///d:/P4I_Publisher_Ebook/app/Models/Order.php): Tambah `$casts = ['snap_token' => 'encrypted'];` untuk **Otomatisasi Enkripsi Kredensial Database**.

---

### Komponen 2: Keamanan & Logika Lanjutan

#### [MODIFY] app/Http/Controllers/Admin/BookUploadController.php
- Menambahkan validasi **Magic Bytes PDF** secara manual (membaca header `%PDF-`) untuk memastikan file benar-benar biner PDF, selain mengandalkan validasi MIME Laravel.
- Menambahkan logika untuk menyimpan relasi kategori (`sync()`) saat buku diunggah.

#### [MODIFY] app/Http/Controllers/DrmController.php & public/js/secure-reader.js
- **Fitur Bookmark:** Membuat *endpoint* API baru `POST /api/drm/progress` yang akan dipanggil oleh *AJAX* setiap kali pengguna membalik halaman di PDF Viewer.
- Saat PDF di-load, PDF.js akan diarahkan untuk *jump* ke `last_read_page`.

---

### Komponen 3: Peningkatan UI Pengguna

#### [MODIFY] resources/views/books/show.blade.php
- Menampilkan rata-rata Rating (Bintang) dan daftar Ulasan dari pengguna lain.
- Menambahkan formulir (Form) "Tulis Ulasan" khusus untuk pengguna yang sudah membeli buku tersebut (memiliki lisensi aktif).

#### [MODIFY] resources/views/books/index.blade.php
- Menambahkan filter *Badge/Chips* Kategori di bagian atas katalog agar pengguna dapat mencari buku berdasarkan genre (Pendidikan, Teknologi, Fiksi).

---

### Komponen 4: Dashboard Admin Lanjut

#### [MODIFY] resources/views/admin/books/index.blade.php (atau membuat dashboard baru)
- Membuat antarmuka **Dashboard Analitik** menggunakan library grafik sederhana (seperti Chart.js atau visualisasi HTML/CSS).
- Menampilkan metrik: Total Pendapatan, Top 3 Buku Terlaris, dan Rasio Checkout.

---

### Komponen 5: Skalabilitas Infrastruktur

#### [MODIFY] config/filesystems.php & .env
- Menyiapkan konfigurasi disk `s3` untuk penyimpanan E-Book.
- Menyiapkan *Signed URL* generator menggunakan *Temporary URL* AWS S3.

#### [MODIFY] app/Http/Controllers/BookController.php
- Membungkus *query* pemuatan Katalog Utama (`Book::with('categories')->where('is_published', true)->get()`) menggunakan `Cache::remember()` dengan waktu kedaluwarsa 1 jam (Terkait **Redis Caching**).

---

## 🧪 Verification Plan
1. **Automated Mocks:** Kita akan menggunakan Laravel Tinker untuk mengisi puluhan buku *dummy*, *reviews*, dan transaksi tiruan guna melihat apakah Dashboard Analitik Admin menampilkan angka yang akurat.
2. **Security Testing:** Mencoba mengunggah skrip PHP yang di-rename menjadi `.pdf` untuk melihat apakah sistem *Magic Bytes* berhasil menolaknya.
3. **Database Inspection:** Membuka SQLite DB untuk memastikan bahwa kolom `snap_token` di tabel `orders` sekarang berisi teks acak yang terenkripsi, bukan *plaintext*.
4. **Browser Testing (Bookmark):** Membuka DRM Reader, berpindah ke Halaman 5, menutup browser, lalu membukanya lagi untuk memverifikasi apakah otomatis berlanjut di Halaman 5.
