# Rencana Pengembangan Selanjutnya (Fase 4 & 5)

Berikut adalah cetak biru (*blueprint*) prioritas untuk mengembangkan P4I Publisher menjadi platform E-Book kelas Enterprise (*Scale-Up*).

---

## 🚀 Fase 4: Skalabilitas Infrastruktur Server

### 1. Migrasi Penyimpanan ke Amazon S3 (Object Storage)
**Masalah Saat Ini:** File E-Book saat ini berdiam di folder lokal `storage/app/private_books`. Jika aplikasi membesar dengan 10,000+ pengguna dan 1000+ buku PDF (masing-masing 10MB+), ruang disk *Hostinger* akan habis, dan CPU server akan kelelahan melakukan proses `fpassthru()` untuk *streaming*.
**Solusi:**
- Menghubungkan Laravel ke *AWS S3* atau *Cloudflare R2* menggunakan `League\Flysystem\AwsS3V3`.
- Mengubah fungsi di `DrmController@streamPdf`. Daripada server kita yang melakukan *streaming*, server kita akan membuat *S3 Pre-signed URL* langsung ke klien. PDF.js akan mengunduh bongkahan file *langsung dari server Amazon*, menjadikan beban jaringan server aplikasi P4I adalah 0%.

### 2. Implementasi Redis Caching (Database Speed)
**Masalah Saat Ini:** Setiap kali pengguna membuka halaman katalog (`/books`), MySQL memproses *query* relasi *Book*, *Author*, dan *Categories*. Saat ratusan pengguna membuka katalog secara bersamaan, MySQL bisa mengalami *bottleneck*.
**Solusi:**
- Mengaktifkan `CACHE_DRIVER=redis` (Membutuhkan Redis Server / Docker).
- Me-*wrap* *query* katalog utama: `Cache::remember('public_catalog_books', 3600, function () { return Book::with('categories')->where('is_published', true)->get(); });`
- Setiap kali admin menambah/mengubah buku, *cache* akan dihapus otomatis (Cache Invalidation).

---

## 📈 Fase 5: Fitur Pemasaran & Aplikasi Seluler

### 1. Mesin Promo & Kupon Diskon
- Pembuatan tabel `promocodes` dengan atribut `code`, `discount_percentage`, `max_usage`, dan `expires_at`.
- Integrasi ke alur *Checkout*: Pengguna dapat memasukkan kode promo sebelum menekan "Beli Sekarang", yang akan secara dinamis mengurangi nilai `gross_amount` yang dikirim ke Midtrans.

### 2. Program Afiliasi (Referral System)
- Pengguna yang mendaftar mendapatkan *Unique Referral Link*. 
- Jika pengunjung membeli buku melalui tautan tersebut, komisi (misalnya 10%) otomatis dicatat ke dalam dompet (*wallet*) afiliasi si pengguna.

### 3. API Headless untuk Mobile App (React Native/Flutter)
- Sistem web P4I yang dibangun dengan Blade/Tailwind sangat bagus untuk Desktop/Web. Namun untuk aplikasi Android/iOS natif, kita perlu membuat rute API khusus (`routes/api.php`).
- Menggunakan Laravel Sanctum untuk autentikasi token berbasis *mobile*. Endpoint akan merespons dengan JSON murni (Daftar Buku, Detail Buku, Proses Checkout).
- Untuk DRM di *Mobile App*, kita dapat menggunakan *Webview* khusus yang menonaktifkan fitur tangkapan layar (Screenshot) level OS (*SECURE_FLAG* di Android).
