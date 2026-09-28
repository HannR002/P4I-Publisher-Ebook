# 📋 Ekstraksi Arsitektur & Logika Sistem P4I Publisher
*Dokumen ini dirancang sebagai data mentah (raw data extraction) untuk digunakan oleh AI pembuat diagram (ERD, Sequence, Activity) atau penyusun laporan skripsi/dokumentasi teknis.*

---

## 1. Skema Database & Relasi (Data Layer)
Sistem menggunakan pangkalan data **MySQL** relasional dengan penegakan batasan (constraints) ketat untuk mencegah redundansi dan anomali.

### Identifikasi Struktur & Relasi Utama (Models):
- **User (Pengguna):**
  - Tipe Primary Key: `Auto-Increment (BigInt)`.
  - Relasi: `hasMany(Order)`, `hasMany(BookLicense)`, `hasMany(Review)`.
- **Book (Buku/Produk):**
  - Tipe Primary Key: `Auto-Increment (BigInt)`.
  - Relasi: `hasMany(BookLicense)`, `hasMany(Review)`, `belongsToMany(Category)`.
- **Order (Transaksi):**
  - Tipe Primary Key: `String (UUID/Custom Order ID format)`. Auto-increment = `false`.
  - Relasi: `belongsTo(User)`, `hasMany(OrderItem)`.
  - **Security Casts:** Kolom `snap_token` di-*cast* sebagai `encrypted` untuk mencegah eksploitasi jika *database* bocor.
- **BookLicense (Hak Milik E-Book):**
  - Mengelola siapa memiliki apa. Status: `active`, `revoked`.
  - **Constraint:** `unique(['user_id', 'book_id'])` (Mencegah satu user memiliki dua lisensi untuk buku yang sama).
- **Review (Ulasan):**
  - **Constraint:** `unique(['user_id', 'book_id'])` (Satu user hanya bisa memberi satu ulasan per buku).
- **Book_Category (Pivot):**
  - **Constraint:** `unique(['book_id', 'category_id'])`.

---

## 2. Logika Bisnis & Pengendali Inti (Application Layer)
Application layer mematuhi prinsip *Single Responsibility Principle* (SRP) di mana masing-masing pengontrol (*Controller*) hanya menangani domain khususnya.

### A. CheckoutController (Logika Transaksi)
- **Bypass Harga Rp0:** Jika `gross_amount == 0`, algoritma melompati pemanggilan Midtrans API, status pesanan otomatis diset `success`, dan lisensi (`BookLicense`) langsung diterbitkan di dalam *Database Transaction*.
- **Integrasi Midtrans:** Jika harga > 0, sistem memanggil Midtrans SNAP API untuk mengambil `snap_token` dan mengubah status pesanan menjadi `pending`.

### B. MidtransWebhookController (Validasi & Race Condition)
- **Validasi Signature (Keamanan):** Menerima Payload POST dari Midtrans, lalu memvalidasi keaslian *request* dengan melakukan *hashing* SHA512 terhadap `order_id + status_code + gross_amount + server_key`. Jika tidak cocok, *request* ditolak (403).
- **Idempotency/Race Condition Check:** Menggunakan `DB::transaction()` dengan *lock* atau langsung menolak pengulangan pemrosesan jika pesanan sudah berstatus `success` atau `settlement`. Saat *settlement*, lisensi `BookLicense` dibuat otomatis.

### C. DrmController (Keamanan Streaming PDF)
- **Generate Signed URL:** Endpoint `/reader/{bookId}` memvalidasi kepemilikan lisensi, lalu memproduksi URL *streaming* `/drm/stream/{license_key}` yang ditandatangani menggunakan `URL::temporarySignedRoute` milik Laravel (kadaluwarsa dalam 15 menit).
- **Stream PDF:** Mengembalikan berkas lokal menggunakan `response()->file()` yang diinjeksikan langsung ke kanvas PDF.js di peramban pengguna, menyembunyikan lokasi asli berkas `private_books/`.

---

## 3. Arsitektur Rute & Hak Akses (Routing & Middleware)
Sistem memisahkan lalu lintas (traffic) jaringan menggunakan Middleware untuk mencegah kebocoran hak akses (RBAC - Role-Based Access Control).

### Peta Navigasi & Proteksi (routes/web.php):
1. **Public Endpoint (Tidak dilindungi):**
   - `GET /` & `GET /books`: Dapat diakses siapa saja untuk melihat katalog.
   - `GET /books/{slug}`: Halaman rincian buku.
   - `POST /api/midtrans/webhook`: Jalur API publik untuk Midtrans (dikecualikan dari CSRF token).
2. **Authenticated Endpoint (`middleware: auth`):**
   - `POST /checkout`: Memerlukan otentikasi. Guest akan dilempar ke login dan dikembalikan (*intended URL redirect*).
   - `GET /my-library`, `GET /my-orders`: Portal privat milik pengguna.
   - `GET /reader/{bookId}` & `GET /drm/stream/*`: Membutuhkan otentikasi sekaligus memverifikasi kepemilikan lisensi.
3. **Administrative Endpoint (`middleware: auth, admin`):**
   - Semua rute dengan awalan (prefix) `/admin/*` (`/admin/books`, `/admin/books/upload`) secara eksplisit menolak pengguna yang flag `is_admin == false` dengan mengembalikan status `403 Forbidden`.

---

## 4. Spesifikasi Lingkungan & Dependensi (Infrastructure)
Spesifikasi mesin penggerak sistem diekstraksi langsung dari `composer.json`.

### Komponen Inti:
- **Bahasa Induk:** `PHP ^8.2`
- **Framework Inti:** `Laravel Framework ^12.0` (Versi terbaru dengan optimasi performa tinggi).
- **Payment Gateway SDK:** `midtrans/midtrans-php ^2.6` (Untuk transaksi SNAP Token).

### Dependency Development & Testing (Performance):
- **Testing Engine:** `phpunit/phpunit ^11.5.50` (Digunakan untuk unit testing dan integrasi test).
- **Database Engine Target:** `MySQL` / `SQLite` (sebagai basis data relasional via PDO).
- **Web Server:** Node.js (Vite) untuk *frontend compilation* dan PHP Artisan Serve / Nginx / Apache XAMPP untuk *backend execution*.
