# Blueprint Arsitektur & Dokumentasi Sistem P4I E-Book

Dokumen ini merupakan panduan komprehensif mengenai arsitektur, teknologi, dan alur kerja (workflow) dari sistem **P4I E-Book**, sebuah platform *digital publishing* dan toko buku premium.

---

## 1. Tumpukan Teknologi (Tech Stack)

Sistem ini dibangun menggunakan arsitektur monolitik modern dengan performa tinggi:
- **Backend Framework:** Laravel 11 (PHP 8.2+)
- **Frontend Styling:** Tailwind CSS 3.4 (dengan dukungan *Dark Mode*)
- **Frontend Reactivity:** Alpine.js (untuk interaktivitas UI ringan tanpa perlu Vue/React)
- **Database:** SQLite / MySQL (via Laravel Eloquent ORM)
- **Payment Gateway:** Midtrans (Snap API)
- **PDF Viewer & DRM:** PDF.js (Mozilla) terintegrasi dengan proteksi kanvas kustom.

---

## 2. Struktur Folder & Direktori Backend (Code Organization)

Berikut adalah struktur kode inti untuk memahami navigasi *codebase*:
```text
D:\P4I_Publisher_Ebook\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/BookController.php (CRUD Buku oleh Admin)
│   │   │   ├── BookController.php (Katalog Buku Publik)
│   │   │   ├── CheckoutController.php (Logika Transaksi & Midtrans Snap)
│   │   │   ├── MidtransWebhookController.php (Menerima notifikasi dari Midtrans)
│   │   │   ├── LibraryController.php (Menampilkan buku milik user & riwayat)
│   │   │   └── SecureReaderController.php (Engine Pembaca PDF dengan DRM)
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php (Proteksi akses route khusus Admin)
│   │       └── VerifyCsrfToken.php (Pengecualian CSRF untuk rute Webhook)
│   ├── Models/
│   │   ├── User.php, Book.php, Category.php
│   │   └── Order.php, OrderItem.php, BookLicense.php, Review.php
│   └── Services/
│       └── OrderIdGenerator.php (Generator ULID unik untuk ID Pesanan)
├── database/
│   └── migrations/ (Berisi semua file definisi skema database)
├── routes/
│   └── web.php (Titik masuk seluruh rute aplikasi, dipisah berdasarkan Roles)
├── resources/
│   ├── views/ (Seluruh antarmuka UI/Blade Templates)
│   │   ├── admin/ (Antarmuka Admin Panel)
│   │   ├── checkout/ (UI Keranjang & Tagihan)
│   │   ├── components/ (Reusabel komponen seperti public-layout.blade.php)
│   │   └── books/ (Halaman Katalog Publik)
│   └── js/
│       └── secure-reader.js (Logika Front-End DRM & Anti-Pembajakan PDF.js)
└── storage/
    └── app/private/private_books/ (Tempat penyimpanan file PDF Asli, TIDAK dapat diakses publik)
```

---

## 3. Skema Database & Relasi (Data Layer)

Struktur data didesain untuk mencegah redundansi dan memastikan integritas transaksi:

1. **`users`**
   - **Tipe PK:** `id` (Auto-Increment / BIGINT).
   - **Kolom Utama:** `name`, `email`, `password`, `is_admin` (BOOLEAN, default: false).
2. **`books`**
   - **Tipe PK:** `id` (Auto-Increment / BIGINT).
   - **Kolom Utama:** `title`, `author`, `isbn`, `description`, `price` (DECIMAL), `file_path`, `cover_image_path`.
3. **`orders` (Tabel Transaksi Utama)**
   - **Tipe PK:** `id` (STRING - Menggunakan **ULID** via `Str::ulid()`). 
     - *Alasan penggunaan ULID:* Menghindari tabrakan ID (Collision) saat dikirim ke Midtrans `order_id` yang mensyaratkan keunikan absolut, serta mencegah pengguna menebak jumlah transaksi platform.
   - **Foreign Key:** `user_id` (Merujuk ke `users(id)`).
   - **Kolom Utama:** `gross_amount`, `status` (ENUM: pending, success, failed, expired), `snap_token`.
4. **`order_items`**
   - **Tipe PK:** `id` (Auto-Increment).
   - **Foreign Keys:** `order_id` (STRING, Merujuk ke `orders(id)` dengan relasi CASCADE), `book_id` (Merujuk ke `books(id)`).
   - **Kolom Utama:** `price`.
5. **`book_licenses` (Kunci Keamanan Akses)**
   - **Tipe PK:** `id` (Auto-Increment).
   - **Foreign Keys:** `user_id`, `book_id`.
   - **Constraints:** Kombinasi UNIQUE (`user_id`, `book_id`) agar satu *user* tidak membeli buku yang sama dua kali.
   - **Kolom Utama:** `status` (ENUM: active), `last_read_page` (INTEGER).
6. **`categories`** & **`book_category`**
   - Sistem relasi M:N (*Many to Many*).
7. **`reviews`**
   - **Foreign Keys:** `user_id`, `book_id` (Constraint: UNIQUE).
   - **Kolom Utama:** `rating` (TINYINT 1-5), `comment`.

---

## 4. Hak Akses & Matriks Fitur (Berdasarkan Role)

### A. GUEST (Pengunjung Tanpa Login)
- ✅ Melihat *Landing Page*, Fitur Unggulan, dan semua Halaman Statis (Tentang Kami, Pusat Bantuan, dsb).
- ✅ Melihat Katalog Buku, menggunakan *Search Bar*, dan filter Kategori.
- ✅ Melihat Halaman Detail Buku, membaca sinopsis, dan melihat Ulasan & Rating.
- ❌ Tidak bisa menekan tombol "Beli Sekarang" (akan dialihkan ke halaman Login secara paksa).
- ❌ Tidak bisa memberikan Ulasan/Rating.

### B. USER (Pelanggan Terdaftar)
- Semua fitur GUEST, ditambah:
- ✅ **Checkout Flow:** Dapat menekan "Beli Sekarang", otomatis dibuatkan `Order` ID (ULID) berstatus Pending, dan menampilkan Popup Midtrans.
- ✅ **My Library:** Mengakses panel Perpustakaan Saya untuk melihat buku yang lisensinya aktif.
- ✅ **Transaction History:** Melihat daftar riwayat pembelian dan meneruskan pembayaran (*Resume Payment*) jika status masih Pending.
- ✅ **Secure Reader:** Membaca E-Book dengan antarmuka yang mengunci klik kanan, mem- *bookmark* halaman secara otomatis saat di- *scroll*, dan mode gelap otomatis.
- ✅ **Reviews:** Memberikan atau mengedit 1 ulasan untuk setiap buku yang *telah dimiliki/dibeli*.

### C. ADMIN (Pengelola Platform)
- Semua fitur GUEST dan USER, ditambah:
- ✅ **Admin Dashboard:** Melacak jumlah total buku, total pengguna.
- ✅ **Manajemen Buku (CRUD):** 
  - Mengunggah sampul (*Cover*).
  - Mengunggah file PDF Asli yang secara otomatis divalidasi dengan *Magic Bytes* untuk memastikan *file* tersebut adalah PDF murni. File akan disimpan secara rahasia ke `storage/app/private/private_books`.
  - Mengikat relasi kategori secara dinamis.
- ❌ Admin tidak bisa melihat file PDF dari Dasbor Admin secara langsung jika ia sendiri tidak membeli bukunya secara sistematis (karena arsitektur keamanan lisensi yang ketat).

---

## 5. Logika Bisnis & Pengendali Inti (Application Layer)

1. **Midtrans Webhook (`MidtransWebhookController@handle`)**
   - **Misi Kritis:** Controller ini menerima HTTP POST dari server Midtrans. Ia melakukan verifikasi `signature_key` menggunakan SHA512 hash `(order_id + status_code + gross_amount + server_key)`.
   - **Transaksi Database:** Jika status adalah `settlement` atau `capture`, controller akan mengubah status `Order` menjadi `success`.
   - **Auto-Provisioning:** Begitu `Order` berstatus sukses, sistem me-*loop* semua `order_items` dan menanamkan (*inject*) *record* baru ke tabel `book_licenses` dengan status `active`. Ini yang membuat buku seketika muncul di Perpustakaan Pembeli.
2. **Keamanan Streaming (`SecureReaderController@streamPdf`)**
   - **Misi Kritis:** Mencegah pencurian *file* statis. Rute `/drm/stream/{book_id}` dilindungi *middleware* `auth`.
   - **Lisensi Cek:** Melakukan *query* ke `book_licenses` untuk memastikan `user_id` memiliki lisensi aktif.
   - **Header Modifikasi:** Mengembalikan *Response File* dengan header `Content-Disposition: inline` (bukan *attachment*) agar dipaksa dibaca via kanvas *browser*, serta menyuntikkan *Header No-Cache* agar jejak unduhan tidak masuk ke memori *browser*.
3. **Penyimpanan Proses Baca (Auto-Save Progress)**
   - Rute `POST /api/drm/progress` menerima input `last_read_page` via AJAX (dikirim diam-diam oleh *Intersection Observer* di `secure-reader.js`).
   - Sistem memperbarui tabel `book_licenses` sehingga di kunjungan berikutnya, kanvas secara otomatis menggulir (*scroll*) ke halaman terakhir.
