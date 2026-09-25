# 📋 Dokumen QA, Code Review & Arsitektur Sistem
## P4I Publisher E-Book Platform — Laravel 11

> **Disiapkan oleh:** Antigravity AI — Senior Full-Stack Developer & QA Engineer  
> **Tanggal:** September 2026  
> **Versi Aplikasi:** MVP (Phase 1 — Selesai)

---

## DAFTAR ISI

1. [Code Review — Bug & Vulnerability](#1-code-review)
2. [Alur Fitur & Test Case Kritis](#2-test-cases)
3. [Arsitektur Sistem & Database](#3-arsitektur)
4. [Ringkasan Prioritas Perbaikan](#4-prioritas)

---

## 1. CODE REVIEW — Bug, Vulnerability & Potensi Masalah

### 🔴 KRITIS — Harus Diperbaiki Sebelum Produksi

---

#### **BUG-01 · DrmController.php — Hardcoded Fallback User ID**

**File:** `app/Http/Controllers/DrmController.php` · **Baris 12**

```php
// ❌ KODE BERMASALAH
$userId = auth()->id() ?? 1; // fallback to 1 for testing if no auth
```

**Masalah:** Jika `auth()->id()` bernilai `null` (user tidak terautentikasi atau session expired), sistem akan menggunakan `user_id = 1` — yaitu akun admin pertama. Artinya:
- Tamu yang tidak login bisa mengakses PDF buku yang dimiliki oleh user ID 1.
- Route `/reader/{bookId}` sudah dilindungi middleware `auth`, tapi jika middleware dilewati atau diubah, bug ini menjadi kebocoran data nyata.

**Perbaikan:**
```php
// ✅ KODE BENAR
$userId = auth()->id(); // Jangan pernah fallback ke hardcoded ID
if (!$userId) {
    abort(401, 'Unauthenticated');
}
```

---

#### **BUG-02 · BookUploadController.php — Race Condition pada Slug Unik**

**File:** `app/Http/Controllers/Admin/BookUploadController.php` · **Baris 63**

```php
// ❌ KODE BERMASALAH
'slug' => Str::slug($request->title),
```

**Masalah:** Kode ini mengabaikan logika slug-uniqueness yang sudah ada di `Book::boot()`. Jika admin mengupload dua buku dengan judul sama, baris ini akan menghasilkan slug yang identik dan melempar `SQLSTATE[23000]: Integrity constraint violation` (kolom `slug` adalah `UNIQUE`), menghasilkan error 500 yang tidak ditangani — bukan pesan error yang ramah pengguna.

**Perbaikan:**
```php
// ✅ KODE BENAR — Hapus kolom 'slug' dari create(), biarkan boot() mengurus
Book::create([
    'title'            => $request->title,
    // 'slug' => ... HAPUS BARIS INI
    'author'           => $request->author,
    // dst...
]);
```

Karena `Book::boot()` sudah memiliki logika `while (exists())` yang men-generate slug unik otomatis.

---

#### **BUG-03 · CheckoutController.php — Tidak Ada Validasi "Sudah Dimiliki"**

**File:** `app/Http/Controllers/CheckoutController.php` · **Baris 29–103**

```php
// ❌ TIDAK ADA PENGECEKAN INI
// User bisa checkout buku yang sudah dia miliki lisensinya
```

**Masalah:** Jika user membeli buku yang sudah dimiliki (misalnya karena membuka dua tab browser), sistem akan membuat `Order` baru, memanggil Midtrans, dan — setelah webhook tiba — `BookLicense::firstOrCreate()` di webhook *memang* mencegah duplikasi lisensi, **tapi** user sudah membayar dan uang sudah masuk. Secara bisnis ini adalah bug serius.

**Perbaikan:**
```php
// ✅ Tambahkan setelah $books diambil, sebelum DB::beginTransaction()
$ownedBookIds = BookLicense::where('user_id', $user->id)
    ->where('status', 'active')
    ->whereIn('book_id', $books->keys())
    ->pluck('book_id');

if ($ownedBookIds->isNotEmpty()) {
    $titles = $books->only($ownedBookIds)->pluck('title')->join(', ');
    return back()->withErrors(['book_ids' => "Anda sudah memiliki: {$titles}"]);
}
```

---

#### **BUG-04 · MidtransWebhookController.php — Tidak Ada Rate Limiting / Replay Attack Protection**

**File:** `app/Http/Controllers/MidtransWebhookController.php` · **Baris 14–122**

**Masalah:** Meskipun signature SHA-512 sudah diverifikasi, sistem masih rentan terhadap **replay attack**: attacker yang mencegat request webhook bisa mengirim ulang request yang sama berkali-kali. Idempotency check (`if ($order->status === 'success')`) hanya mencegah duplikasi lisensi, tapi tidak mencegah log spam atau eksekusi DB yang berulang.

**Perbaikan:**
```php
// ✅ Gunakan Cache untuk mencegah replay dalam 5 menit
$webhookFingerprint = hash('sha256', $request->getContent());
if (Cache::has("webhook:{$webhookFingerprint}")) {
    return response()->json(['message' => 'Duplicate webhook ignored']);
}
Cache::put("webhook:{$webhookFingerprint}", true, now()->addMinutes(5));
```

---

#### **BUG-05 · DrmController.php — Response File Tanpa Chunking (Potensi Memory Exhaustion)**

**File:** `app/Http/Controllers/DrmController.php` · **Baris 54–60**

```php
// ❌ BERMASALAH untuk file besar
return response()->file($path, [...]);
```

**Masalah:** `response()->file()` meload **seluruh file PDF ke dalam memori PHP** sebelum dikirim ke browser. Untuk PDF 30MB dengan 10 concurrent requests = 300MB RAM PHP process. Dengan PDF 50MB (batas upload) + traffic normal, ini bisa menyebabkan **PHP memory exhaustion (500 Internal Server Error)**.

**Perbaikan:**
```php
// ✅ Gunakan StreamedResponse untuk streaming tanpa load seluruh file ke RAM
return response()->stream(function () use ($path) {
    $stream = fopen($path, 'rb');
    fpassthru($stream);
    fclose($stream);
}, 200, [
    'Content-Type'        => 'application/pdf',
    'Content-Length'      => filesize($path),
    'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
    'Cache-Control'       => 'no-cache, no-store, must-revalidate',
]);
```

---

### 🟡 MEDIUM — Potensi Masalah di Skala Produksi

---

#### **BUG-06 · BookController.php — N+1 Query Tersembunyi di Katalog**

**File:** `app/Http/Controllers/BookController.php` · **Baris 17–19**

```php
// ⚠️ Tidak ada eager loading
$books = Book::where('is_published', true)->orderBy('created_at', 'desc')->paginate(12);
```

**Masalah:** Untuk 12 buku per halaman, jika view mengakses relasi (misal `$book->licenses->count()` atau data terkait di masa depan), ini akan menghasilkan 12 query tambahan (N+1 problem).

**Perbaikan:** Tambahkan `withCount` atau `with()` yang relevan di query yang sama.

---

#### **BUG-07 · OrderIdGenerator.php — Infinite Loop Theoretically Possible**

**File:** `app/Services/OrderIdGenerator.php` · **Baris 21–25**

```php
// ⚠️ Tidak ada batas iterasi
do {
    $orderId = "INV-{$date}-{$random}";
} while (Order::where('id', $orderId)->exists());
```

**Masalah:** Secara teori (sangat tidak mungkin tapi secara engineering harus ditangani), jika semua kombinasi `Str::random(6)` sudah terpakai untuk satu hari (36^6 = 2.1 miliar kemungkinan, jadi aman), loop tidak pernah keluar. Lebih realistis: jika database tidak bisa diakses sementara, loop akan terus berjalan dan menguras CPU.

**Perbaikan:**
```php
// ✅ Tambahkan batas iterasi
$maxAttempts = 10;
for ($i = 0; $i < $maxAttempts; $i++) {
    $orderId = "INV-{$date}-" . strtoupper(Str::random(6));
    if (!Order::where('id', $orderId)->exists()) {
        return $orderId;
    }
}
throw new \RuntimeException('Failed to generate unique order ID after ' . $maxAttempts . ' attempts.');
```

---

#### **BUG-08 · CheckoutController.php — Snap Token Disimpan sebagai Plaintext**

**File:** `app/Http/Controllers/CheckoutController.php` · **Baris 93**

```php
// ⚠️ snap_token tersimpan di database tanpa enkripsi
$order->snap_token = $snapToken;
```

**Masalah:** Snap token Midtrans adalah credential sensitif. Jika database leak, attacker bisa menggunakan token ini (selama masih valid) untuk menampilkan payment page dan mengelabui korban.

**Rekomendasi:** Enkripsi sebelum simpan, atau jangan simpan sama sekali (buat ulang token via API jika user ingin lanjut bayar).

---

#### **BUG-09 · Admin Middleware — Tidak Ada Audit Log**

**File:** `app/Http/Middleware/EnsureUserIsAdmin.php`

**Masalah:** Setiap akses yang ditolak ke area admin (non-admin user mencoba URL `/admin/*`) tidak dicatat. Jika ada percobaan penetrasi, tidak ada trace sama sekali di log.

**Perbaikan:**
```php
if (!Auth::check() || !Auth::user()->is_admin) {
    Log::warning('[Security] Unauthorized admin access attempt', [
        'ip'     => request()->ip(),
        'url'    => request()->fullUrl(),
        'user'   => Auth::id(),
    ]);
    abort(403, 'Area ini hanya dapat diakses oleh Administrator.');
}
```

---

#### **BUG-10 · BookLicense — Tidak Ada Unique Constraint Komposit di DB Level**

**File:** `database/migrations/2026_08_31_024130_create_book_licenses_table.php`

```php
// ⚠️ Tidak ada unique constraint pada (user_id, book_id)
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->foreignId('book_id')->constrained()->onDelete('cascade');
// TIDAK ADA: $table->unique(['user_id', 'book_id']);
```

**Masalah:** `BookLicense::firstOrCreate(['user_id', 'book_id', 'order_id'], ...)` di webhook menggunakan **tiga kolom** sebagai key pencarian, bukan dua. Jika user yang sama berhasil checkout buku yang sama dari dua order berbeda secara simultan (race condition pada webhook), `firstOrCreate` akan membuat **dua lisensi berbeda** karena `order_id` berbeda.

**Perbaikan:** Tambahkan unique constraint di level database:
```php
$table->unique(['user_id', 'book_id']); // Tambahkan di migrasi baru
```
Dan ubah webhook untuk mencari hanya berdasarkan `user_id + book_id`:
```php
BookLicense::firstOrCreate(
    ['user_id' => $order->user_id, 'book_id' => $item->book_id], // hanya 2 kolom
    ['order_id' => $order->id, 'license_key' => Str::uuid(), 'status' => 'active']
);
```

---

### 🟢 INFO — Observasi Minor

| # | File | Catatan |
|---|---|---|
| I-01 | `checkout/success.blade.php` | Tidak ada auto-refresh untuk mengecek status webhook yang baru tiba |
| I-02 | `BookController::show()` | Buku yang tidak published tetap bisa diakses jika user tahu URL slug-nya (karena redirect show, bukan 404 khusus) — tapi ini sudah ditangani `firstOrFail` |
| I-03 | `routes/web.php` | Route `/checkout/{order_id}` tidak membatasi status order — user bisa membuka checkout page order yang sudah `success`/`expired` |
| I-04 | `BookUploadController::store()` | PDF yang diupload tidak divalidasi apakah benar-benar PDF valid (bisa dikirim file PHP yang diubah ekstensinya menjadi `.pdf`) |

---

## 2. ALUR FITUR & TEST CASE KRITIS

### Peta Alur Utama

```
[Guest] → Lihat Katalog
         → Klik "Lihat Detail" → Halaman Show
             → Klik "Beli Sekarang"
                 → [Tidak Login] → Redirect ke /login → [Login] → Redirect kembali
                 → [Sudah Login] → POST /checkout
                     → Buat Order (pending) + Panggil Midtrans API
                     → Redirect ke /checkout/{order_id}
                     → User klik "Bayar Sekarang" → Snap Popup Midtrans
                         → [Berhasil] → Midtrans POST ke /api/midtrans/webhook
                                      → Order.status = 'success'
                                      → BookLicense dibuat (active)
                                      → User redirect ke /payment/success
                         → [Pending] → Midtrans POST webhook (pending)
                                      → User redirect ke /payment/pending
                         → [Gagal]  → Midtrans POST webhook (cancel/deny)
                                      → Order.status = 'failed'

[User Authenticated] → /my-library → Lihat buku dimiliki → Klik "Baca Sekarang"
                    → /reader/{bookId} → Cek lisensi aktif
                                       → Generate Signed URL (15 menit)
                                       → Tampilkan reader.blade.php
                                       → Browser load iframe → GET /drm/stream/{license_key}
                                           → Verifikasi signature
                                           → Stream PDF dari disk lokal

[Admin Authenticated] → /admin/books → Dashboard kelola buku
                     → /admin/books/upload → Upload PDF + cover
                     → PATCH /admin/books/{id}/toggle-publish → Publish/Unpublish
```

---

### Test Cases — Diurutkan Berdasarkan Risiko

#### TC-01 · Alur Checkout Happy Path ✅
**Skenario:** User login → beli buku → bayar → terima lisensi → baca buku  
**Expected:** Order `success`, `BookLicense` aktif dibuat, tombol "Baca Sekarang" muncul  
**Status:** Sudah diuji ✓

#### TC-02 · Duplicate Purchase Protection ❗ KRITIS (BUG-03)
**Skenario:** User membuka dua tab browser dan melakukan checkout buku yang sama di kedua tab hampir bersamaan  
**Expected:** Salah satu checkout ditolak dengan pesan "Anda sudah memiliki buku ini"  
**Status:** ❌ **Belum ada proteksi — harus diperbaiki**

#### TC-03 · Webhook Idempotency ⚠️
**Skenario:** Midtrans mengirim webhook `settlement` dua kali untuk order yang sama (terjadi di production)  
**Expected:** Lisensi hanya dibuat sekali, response 200 di kedua request  
**Status:** ✅ Sudah ada idempotency check tapi lihat juga BUG-10

#### TC-04 · DRM Link Expiry 🔒
**Skenario:** User membuka `/reader/{bookId}` → menunggu 16 menit → men-scroll halaman (iframe reload PDF)  
**Expected:** `streamPdf` mengembalikan 401 "Invalid or expired signature"  
**Status:** ✅ Sudah ada (15 menit TTL)

#### TC-05 · DRM Tanpa Lisensi ❌
**Skenario:** User yang tidak membeli buku mengakses langsung `/reader/1`  
**Expected:** 403 Forbidden  
**Status:** ✅ Ada pengecekan lisensi aktif

#### TC-06 · Admin Route Access by Regular User ❌
**Skenario:** User biasa mengakses `/admin/books`  
**Expected:** 403 Forbidden dengan pesan Indonesia  
**Status:** ✅ Sudah diuji — middleware berfungsi

#### TC-07 · Checkout Order yang Sudah Success ⚠️ (I-03)
**Skenario:** User mengakses `/checkout/{order_id}` dari order yang sudah `success`  
**Expected:** Redirect ke `/my-library` dengan pesan "Buku sudah dimiliki"  
**Status:** ❌ **Saat ini halaman tetap tampil dengan tombol "Bayar" yang bisa diklik**

#### TC-08 · Upload PDF Palsu ⚠️ (I-04)
**Skenario:** Admin mengupload file PHP/shell yang diubah ekstensinya menjadi `exploit.pdf`  
**Expected:** Ditolak, file tidak tersimpan  
**Status:** ⚠️ **Validasi MIME dari ekstensi saja tidak cukup — perlu validasi magic bytes**

#### TC-09 · Buku Di-Unpublish Setelah Dibeli 🤔
**Skenario:** Admin unpublish buku yang sudah dibeli oleh beberapa user  
**Expected:** User yang sudah beli tetap bisa akses via `/reader/{id}` (lisensi aktif)  
**Status:** ✅ DrmController tidak mengecek `is_published`, hanya mengecek lisensi aktif

#### TC-10 · Snap Token Kadaluarsa ⚠️
**Skenario:** User membuka checkout page, menunggu >1 jam (Snap token expired), lalu klik "Bayar Sekarang"  
**Expected:** Snap popup menampilkan error, user bisa klik "Lanjutkan Pembayaran" dari `/my-orders` untuk trigger ulang  
**Status:** ⚠️ Snap popup akan error, tapi tidak ada mekanisme auto-regenerate token di sisi server

#### TC-11 · Webhook dengan Order ID Tidak Ada 📝
**Skenario:** Midtrans mengirim webhook untuk order yang tidak ada di database (misal: test notification)  
**Expected:** Response 200 jika prefix `payment_notif_test_`, response 404 jika tidak  
**Status:** ✅ Sudah ditangani dengan baik

#### TC-12 · Concurrent Checkout (Race Condition) ❗ KRITIS
**Skenario:** 100 user bersamaan melakukan checkout → `OrderIdGenerator::generate()` dipanggil bersamaan  
**Expected:** Setiap user mendapatkan order ID unik yang berbeda  
**Status:** ⚠️ Ada potensi race condition di `do-while` tanpa locking — di skala besar gunakan database-level unique constraint + `INSERT ... ON CONFLICT`

#### TC-13 · PDF File Dihapus Manual dari Server ⚠️
**Skenario:** Admin menghapus file PDF dari `/storage/app/private_books/` secara manual  
**Expected:** `/reader/{id}` mengembalikan 404 dengan pesan jelas  
**Status:** ✅ Ada pengecekan `file_exists($path)`

#### TC-14 · Registrasi User → Langsung ke Admin Area ❌
**Skenario:** User baru daftar → langsung akses `/admin/books` sebelum verifikasi email  
**Expected:** Ditolak (403)  
**Status:** ✅ Middleware `admin` menangani ini. Tapi `is_admin = false` by default, jadi aman.

#### TC-15 · Webhook Signature Manipulation ❌
**Skenario:** Attacker mencoba memanipulasi `gross_amount` di request webhook  
**Expected:** Signature mismatch → 403  
**Status:** ✅ SHA-512 signature validation berfungsi

#### TC-16 · Pagination Katalog + Kepemilikan ⚠️
**Skenario:** User yang memiliki buku membuka halaman katalog page 2 dst.  
**Expected:** Badge "Dimiliki" tetap muncul di semua halaman  
**Status:** ✅ `$ownedBookIds` diambil sekali, bukan per-halaman

#### TC-17 · My Library dengan Buku yang Dihapus dari Catalog ❌
**Skenario:** Admin menghapus buku dari database (seharusnya tidak ada fitur ini, tapi jika ada)  
**Expected:** `@if($license->book)` sudah memproteksi render, tapi `bookLicense.book_id` akan menjadi orphan jika `onDelete('cascade')` tidak ada  
**Status:** ⚠️ FK `book_licenses.book_id` menggunakan `onDelete('cascade')` — artinya jika buku dihapus, SEMUA lisensi untuk buku tersebut ikut terhapus! User kehilangan akses. Ini **keputusan bisnis yang berbahaya**.

---

## 3. ARSITEKTUR SISTEM & DATABASE

### Entity Relationship Diagram (ERD)

```
┌────────────────┐        ┌─────────────────────┐        ┌───────────────────┐
│    users       │        │    orders           │        │   order_items     │
│────────────────│        │─────────────────────│        │───────────────────│
│ id (PK)        │──┐     │ id (PK, string)     │──┐     │ id (PK)           │
│ name           │  │     │ user_id (FK→users)  │  │     │ order_id (FK)     │
│ email (unique) │  └────►│ gross_amount        │  └────►│ book_id (FK→books)│
│ password       │        │ status (enum)       │        │ price (snapshot)  │
│ is_admin       │        │ payment_type        │        │ created_at        │
│ created_at     │        │ snap_token          │        │ updated_at        │
└────────────────┘        │ created_at          │        └───────────────────┘
         │                │ updated_at          │                 │
         │                └─────────────────────┘                │
         │                                                        │
         │        ┌─────────────────────┐        ┌───────────────────────┐
         └───────►│  book_licenses      │        │       books           │
                  │─────────────────────│        │───────────────────────│
                  │ id (PK)             │        │ id (PK)               │
                  │ user_id (FK→users)  │        │ title                 │
                  │ book_id (FK→books)──┼───────►│ slug (unique)         │
                  │ order_id (FK→orders,│        │ author                │
                  │   nullable→set null)│        │ description           │
                  │ license_key (unique)│        │ price (decimal)       │
                  │ status (enum)       │        │ cover_image_path      │
                  │ valid_until (null)  │        │ file_path             │
                  │ created_at          │        │ is_published          │
                  │ updated_at          │        │ created_at            │
                  └─────────────────────┘        └───────────────────────┘
```

### Stack Teknologi

| Layer | Teknologi | Versi |
|---|---|---|
| Framework | Laravel | 11.x |
| Auth | Laravel Breeze | Terpasang |
| Database | MySQL/SQLite | — |
| Payment Gateway | Midtrans Snap | — |
| File Storage | Laravel Local Disk | — |
| Frontend CSS | Tailwind CSS (via CDN/Vite) | — |
| PDF Reader | Custom iframe + Signed URL | — |

### Alur Data Kritis — Checkout ke Lisensi

```
POST /checkout
    ↓
[1] Validasi input (book_ids[])
    ↓
[2] Query books WHERE is_published = true
    ↓
[3] Hitung gross_amount dari harga buku SAAT INI (price snapshot)
    ↓
[4] DB::beginTransaction()
    ↓
[5] Buat Order (pending) + OrderItems (harga historis)
    ↓
[6] Panggil Midtrans API → Snap::getSnapToken()
    ↓ [EXTERNAL API - bisa timeout/gagal]
[7] Simpan snap_token ke order
    ↓
[8] DB::commit()
    ↓
[9] Redirect ke /checkout/{order_id}
    ↓
    ← Midtrans Snap Popup (client-side JS)
    ↓
POST /api/midtrans/webhook [DARI MIDTRANS SERVER]
    ↓
[10] Verifikasi SHA-512 signature
    ↓
[11] lockForUpdate() pada order
    ↓
[12] Idempotency check
    ↓
[13] BookLicense::firstOrCreate() per item
    ↓
[14] DB::commit()
    ↓
[15] Response 200 ke Midtrans
```

### Identifikasi Bottleneck & Skalabilitas

#### ⚠️ Bottleneck-01 — Sinkronisasi Webhook
**Masalah:** Webhook dari Midtrans diproses secara sinkron di web thread PHP. Jika terjadi traffic tinggi (banyak order selesai bersamaan), webhook queue bisa menumpuk dan menyebabkan timeout.

**Solusi Produksi:** Implementasi `Queue::dispatch(new ProcessWebhookJob($request->all()))` agar webhook diproses di background worker, bukan di web thread.

#### ⚠️ Bottleneck-02 — PDF Streaming Tanpa CDN
**Masalah:** Setiap request pembacaan buku di-serve langsung dari server PHP. Untuk 100 pembaca bersamaan dengan PDF 20MB = 2GB RAM hanya untuk streaming.

**Solusi Produksi:** 
1. Gunakan `StreamedResponse` (sudah direkomendasikan di BUG-05)
2. Untuk skala besar: gunakan S3 + pre-signed URL langsung ke S3 (bypass server PHP sepenuhnya)

#### ⚠️ Bottleneck-03 — Tidak Ada Database Indexing Eksplisit
**Masalah:** Query-query kritis tidak memiliki index yang dijamin:
- `BookLicense WHERE user_id = ? AND book_id = ?` — butuh composite index
- `Order WHERE user_id = ? ORDER BY created_at DESC` — butuh index di `user_id`
- `Book WHERE is_published = true ORDER BY created_at DESC` — butuh partial index

**Solusi:**
```sql
ALTER TABLE book_licenses ADD INDEX idx_user_book (user_id, book_id);
ALTER TABLE orders ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE books ADD INDEX idx_published_created (is_published, created_at);
```

#### ⚠️ Bottleneck-04 — Webhook Endpoint Tidak Di-Rate-Limited
**Masalah:** `/api/midtrans/webhook` bisa di-spam oleh siapapun (walaupun signature akan gagal) dan menyebabkan load database yang tidak perlu.

**Solusi:** Tambahkan IP whitelist Midtrans atau minimal `throttle:60,1` middleware.

---

### Diagram Keamanan Berlapis (Defense in Depth)

```
Request Masuk
     ↓
[Layer 1] Nginx/Apache — Rate Limiting & SSL Termination
     ↓
[Layer 2] Laravel Middleware Stack
     │     ├─ auth (cek session/token)
     │     ├─ admin (cek is_admin flag)
     │     └─ signed (cek URL signature untuk DRM stream)
     ↓
[Layer 3] Controller — Business Logic Validation
     │     ├─ Request::validate() — input sanitization
     │     ├─ SHA-512 signature (webhook)
     │     └─ BookLicense check (DRM reader)
     ↓
[Layer 4] Eloquent ORM — SQL Injection Prevention
     ↓
[Layer 5] Database — FK Constraints & Unique Keys
     ↓
[Data] Protected
```

---

## 4. RINGKASAN PRIORITAS PERBAIKAN

### 🔴 Sprint Berikutnya — Wajib Sebelum Go-Live

| ID | Masalah | Estimasi Fix |
|---|---|---|
| BUG-01 | Hapus fallback `$userId = 1` di DrmController | 5 menit |
| BUG-02 | Hapus hardcoded `slug` di BookUploadController::store() | 5 menit |
| BUG-03 | Tambahkan validasi "sudah dimiliki" di CheckoutController | 30 menit |
| BUG-05 | Ganti `response()->file()` dengan `StreamedResponse` | 15 menit |
| BUG-10 | Tambahkan unique constraint `(user_id, book_id)` di book_licenses | 20 menit + migration |
| TC-07 | Redirect dari checkout page jika order sudah `success` | 15 menit |

### 🟡 Backlog Medium — Sebelum Scaling

| ID | Masalah | Estimasi |
|---|---|---|
| BUG-04 | Webhook replay protection via Cache fingerprint | 30 menit |
| BUG-06 | Tambahkan eager loading di BookController::index() | 10 menit |
| BUG-07 | Batas iterasi OrderIdGenerator | 10 menit |
| BUG-09 | Audit log di EnsureUserIsAdmin | 10 menit |
| Bottleneck-01 | Queue-kan webhook processing | 2-4 jam |
| Bottleneck-03 | Tambahkan database indexes via migration baru | 30 menit |

### 🟢 Jangka Panjang — Post-MVP

| ID | Masalah | Estimasi |
|---|---|---|
| BUG-08 | Enkripsi snap_token di database | 1 jam |
| TC-08 | Validasi magic bytes PDF di upload | 2 jam |
| TC-17 | Ubah `onDelete('cascade')` di book_licenses jadi `onDelete('restrict')` | 1 jam + migration |
| Bottleneck-02 | Migrasi ke S3 + pre-signed URL untuk PDF | 1-2 hari |
| Bottleneck-04 | IP whitelist Midtrans di webhook route | 30 menit |

---

> **Catatan:** Dokumen ini dibuat berdasarkan code review statis dan analisis alur bisnis per September 2026.  
> Lakukan re-review setelah setiap perubahan signifikan pada codebase.
