# Ekstraksi Sistem P4I Publisher (v2.0)

Dokumen ini merangkum seluruh arsitektur, alur data, dan mekanisme keamanan tingkat lanjut yang telah diimplementasikan dalam repositori P4I E-Book Publisher hingga Fase 3.

## 1. Arsitektur Database & Penyimpanan
Sistem menggunakan **MySQL** (Relasional) dengan struktur yang berpusat pada Entitas `Book`, `User`, `Order`, dan `BookLicense`.
- **Order ID Generation:** Menggunakan **ULID** (Universally Unique Lexicographically Sortable Identifier) yang secara bawaan di-generate melalui `Str::ulid()`. Berbeda dengan UUID v4 yang acak murni, ULID bisa diurutkan berdasarkan waktu (time-sortable) sehingga tidak merusak fragmentasi indeks *B-Tree* MySQL meskipun bertipe string.
- **Penyimpanan Berkas (Storage):** File PDF asli disimpan pada disk lokal (`storage/app/private_books`) yang *tidak dapat* diakses publik. File sampul (cover) disimpan pada disk `public` konvensional.

## 2. Alur Transaksi & Gateway (Midtrans)
Semua pembelian (bahkan yang Rp0) melewati tabel `orders`.
- **Bypass Rp0:** Jika total tagihan adalah Rp 0, sistem (melalui `CheckoutController`) langsung meloloskan transaksi dengan status `success` dan men-generate `BookLicense` tanpa membuat *Snap Token*.
- **Anti-Spam (Bot Protection):** Endpoint `/checkout` dilindungi oleh *Rate Limiting* (`throttle:10,1`), memastikan tidak ada *bot* kompetitor yang menembak ribuan klaim buku gratis dalam satu menit.
- **Pembayaran Berbayar:** Jika > Rp0, Midtrans Snap API dipanggil. Saat pembayaran sukses, **Midtrans Webhook Controller** menerima *callback*, memvalidasi *Signature Key* SHA512, lalu mengubah status order dan mencetak lisensi buku.

## 3. DRM (Digital Rights Management) & Secure Reader
Fitur membaca PDF tidak memungkinkan pengguna mengunduh file secara langsung.
- **Signed URL:** URL PDF dibuat secara dinamis menggunakan `URL::temporarySignedRoute` dengan umur 15 menit.
- **IP Binding (Anti-Hijacking):** URL yang di-generate mengikat **Alamat IP** klien. Jika URL ini disalin (lewat *Network Tab*) dan diakses oleh komputer lain, server akan menolaknya dengan HTTP 403 (Mismatch IP).
- **Stream Buffer:** Pengiriman biner PDF dari disk `private_books` ke klien menggunakan *Streamed Response* (`fpassthru`), menjaga penggunaan RAM server tetap rendah.
- **PDF.js Canvas Renderer:** File dirender sebagai `<canvas>` HTML5 (gambar/piksel) sehingga fungsi blok teks bawaan browser tidak bekerja. Tombol klik kanan, F12 (DevTools), dan CTRL+P (Print) diblokir menggunakan JavaScript.

## 4. Smart Bookmark (Auto-Save Progress)
- Klien JavaScript menggunakan `IntersectionObserver` untuk memantau aktivitas gulir (scroll) pengguna.
- Ketika lebih dari 50% sebuah halaman kanvas terlihat di layar, sistem mendeteksi *page number* tersebut.
- Data disimpan sementara di RAM klien, lalu dikirim via AJAX (dilindungi Token CSRF) dengan *debounce* 1 detik untuk menghindari *spam server*.
- Data halaman ini disimpan ke kolom `last_read_page` di tabel `book_licenses`. Saat pengguna masuk keesokan harinya, JavaScript (setelah semua halaman PDF dirender ke DOM) akan mengeksekusi `scrollIntoView()` untuk menggulung layar langsung ke halaman terakhir.

## 5. Keamanan Upload (Magic Bytes)
- Saat Admin mengunggah buku baru, validasi Laravel (`mimes:pdf`) tidak cukup (karena ekstensi file mudah dipalsukan).
- Sistem memanggil biner mentah file yang diunggah menggunakan `fopen` dan membaca 4 byte pertama. File tersebut wajib memiliki *header* `%PDF` untuk diizinkan masuk ke server.
