# 📘 Blueprint Arsitektur & Dokumentasi Sistem P4I Publisher
*Dokumen ini dirancang khusus sebagai referensi (prompt) bagi Anda untuk menghasilkan Use Case Diagram, Sequence Diagram, Class Diagram, dan dokumentasi rekayasa perangkat lunak lainnya.*

---

## BAGIAN 1: Analisis Terhadap Standar Dokumen Pengujian Anda

Contoh dokumen yang Anda berikan adalah **standar pengujian rekayasa perangkat lunak akademik/enterprise yang sangat terstruktur**. Dokumen tersebut membagi pengujian ke dalam 4 pilar yang presisi:

1. **Unit Testing (Class Invariant):** Fokus pada integritas data di level Model/Database (misal: format email harus valid, status default harus `pending`, stok tidak boleh negatif). Ini setara dengan pengujian aturan validasi dan fungsionalitas model di Laravel.
2. **Integration Testing (Use Case Test):** Fokus pada interaksi antar-modul dan pertukaran data (misal: Tamu -> Login -> Dashboard).
3. **System Testing (Performance/Stress):** Mengukur *Load Time* (Waktu Muat) absolut dengan ambang batas (seperti `< 2 detik` atau `< 3 detik`) di skenario mirip produksi.
4. **User Acceptance Testing (UAT):** Pengujian bahasa manusia (non-teknis) berupa *checklist* kesesuaian bisnis yang dilakukan langsung oleh aktor (Admin/User).

**💡 Kesimpulan untuk P4I Publisher:** 
Jika Anda ingin membuat dokumen pengujian P4I Publisher dengan format yang sama, kita bisa menerjemahkan `test_plan_blackbox.md` yang saya buat sebelumnya ke dalam 4 tabel tersebut (Tabel Invariant, Tabel Integration, Tabel Performance, dan Tabel UAT).

---

## BAGIAN 2: Blueprint Sistem P4I Publisher (Bahan Pembuatan Diagram)
*Salin bagian ini dan berikan ke AI pembuat diagram untuk mendapatkan Use Case, Activity, dan Sequence diagram yang sangat akurat untuk proyek ini.*

### 1. Daftar Aktor (Actors)
1. **Guest (Tamu):** Pengguna internet yang belum melakukan proses autentikasi (login).
2. **User (Pelanggan/Pembaca):** Pengguna terdaftar yang dapat melakukan transaksi dan memiliki koleksi buku pribadi.
3. **Admin:** Pengelola sistem internal publisher yang memiliki hak istimewa (`is_admin = true`).
4. **Midtrans Gateway (Sistem Eksternal):** Aktor sistem eksternal (API) yang memproses pembayaran dan mengirimkan notifikasi (*webhook*).

### 2. Daftar Use Case Utama (Use Case List)
Berikut adalah fungsionalitas sistem (*Use Cases*) yang terpetakan berdasarkan Aktor:

* **Guest:**
  - UC-01: Melihat Katalog E-Book (Public)
  - UC-02: Melihat Detail E-Book
  - UC-03: Mendaftar Akun Baru (Register)
  - UC-04: Masuk ke Sistem (Login)

* **User (Pelanggan):**
  - UC-05: Melakukan Checkout / Pembelian Buku
  - UC-06: Membayar via Payment Gateway (Midtrans Snap)
  - UC-07: Melihat Riwayat Transaksi (My Orders)
  - UC-08: Mengakses Perpustakaan Pribadi (My Library)
  - UC-09: Membaca E-Book via DRM Reader

* **Admin:**
  - UC-10: Login sebagai Administrator
  - UC-11: Melihat Dashboard Manajemen Buku
  - UC-12: Mengunggah E-Book Baru (File PDF & Cover)
  - UC-13: Mengubah Status Publikasi (Publish/Unpublish)

* **Midtrans (Sistem Eksternal):**
  - UC-14: Mengirim Webhook Status Pembayaran (Settlement/Expire/Cancel)

### 3. Deskripsi Alur Bisnis (Untuk Sequence & Activity Diagram)

#### A. Alur Transaksi & Konfirmasi (Pembelian Buku)
1. **User** menekan tombol "Beli Sekarang" pada detail buku.
2. **Sistem (CheckoutController)** memvalidasi apakah *User* sudah memiliki buku tersebut. Jika sudah, tolak transaksi.
3. Jika belum memiliki, Sistem menghitung harga total (*gross amount*).
4. Jika harga = Rp 0, Sistem langsung membuat `Order` (Status: Success), menerbitkan `BookLicense`, dan mengarahkan User ke *My Library*. (Bypass Flow).
5. Jika harga > Rp 0, Sistem membuat `Order` (Status: Pending), menyimpan harga snapshot ke `OrderItem`, lalu memanggil API Midtrans untuk mendapatkan `snap_token`.
6. User melakukan pembayaran melalui *Pop-up UI Midtrans*.
7. **Midtrans (Sistem Eksternal)** memproses pembayaran secara asinkron lalu mengirimkan HTTP POST (Webhook) ke sistem lokal.
8. **Sistem (MidtransWebhookController)** memvalidasi tanda tangan (*SHA-512 Signature*), mengecek anti-replay (Cache), dan memperbarui status `Order` menjadi *Success*.
9. Sistem meng-generate `BookLicense` (lisensi unik) untuk User tersebut.

#### B. Alur Keamanan Baca Buku (Digital Rights Management / DRM)
1. **User** membuka *My Library* dan menekan tombol "Baca Sekarang" pada buku miliknya.
2. **Sistem (DrmController)** memvalidasi kepemilikan lisensi yang aktif.
3. Jika valid, Sistem men-generate `Signed URL` (URL beralamat unik dengan tanda tangan kriptografi) yang hanya berlaku selama 15 menit.
4. Sistem memuat halaman pembaca (PDF Viewer) dengan *iframe* yang merujuk ke *Signed URL* tersebut.
5. Browser meload URL DRM. Sistem memverifikasi kembali tanda tangan waktu (*Timestamp Validation*).
6. Jika batas waktu 15 menit belum lewat, Sistem menggunakan fungsi `fpassthru` (Streaming) untuk menyalurkan *binary* file PDF ke layar tanpa membebani RAM server, dan file tidak dapat di-download.

---

## BAGIAN 3: Laporan Rekomendasi Pengembangan Selanjutnya

Sistem P4I Publisher versi 1.0 (MVP) ini sudah memiliki pondasi arsitektur dan keamanan yang sangat solid (sudah melewati *patching* anti race-condition, mitigasi *memory leak*, dan *replay-attack*). 

Untuk **Fase 2 (Skalabilitas & Fitur)**, berikut adalah rancangan pengembangan selanjutnya yang harus dimasukkan ke dalam *Backlog*:

### 1. Skalabilitas Infrastruktur & Storage (Prioritas Tinggi)
* **Migrasi S3 (Object Storage):** Saat ini file PDF disimpan di lokal (`storage/app/private_books`). Jika pengguna bertambah dan file e-book sangat banyak, penyimpanan server akan penuh. Pindahkan penyimpanan PDF ke Amazon S3 atau layanan setara, lalu generate S3 *Pre-signed URL* langsung ke klien agar server Laravel tidak perlu melakukan *streaming* file sama sekali.
* **Database Caching:** Terapkan Redis untuk menyimpan (*cache*) hasil *query* katalog utama yang paling sering diakses (`Book::where('is_published', true)`).

### 2. Peningkatan Fitur Pengguna Pribadi (Prioritas Menengah)
* **Fitur Bookmark & Lanjut Baca:** Simpan progres halaman (halaman terakhir yang dibaca) milik user ke dalam tabel database. Saat mereka membuka buku via DRM Reader kembali, PDF Viewer otomatis lompat ke halaman tersebut.
* **Rating & Ulasan:** Tambahkan tabel `reviews` sehingga user yang **memiliki lisensi aktif** dapat memberikan ulasan 1-5 bintang yang akan tampil di katalog.

### 3. Keamanan Tingkat Lanjut (Prioritas Menengah)
* **Enkripsi Kredensial Database:** Enkripsi *Snap Token* milik pengguna sebelum disimpan ke database, memastikan kebocoran DB tidak berakibat pada pembajakan transaksi pembayaran.
* **Validasi Magic Bytes PDF:** Pada `BookUploadController`, validasi ekstensi file tidaklah cukup. Gunakan library validator untuk membedah *Magic Bytes* / *MIME Type Header* untuk memastikan admin benar-benar mengunggah file biner berformat PDF (bukan skrip PHP yang di-rename).

### 4. Modul Admin Baru (Prioritas Rendah - Operasional)
* **Dashboard Analitik Lanjut:** Buat chart (grafik) tren penjualan bulanan, e-book terlaris, dan rasio kesuksesan checkout.
* **Pengelolaan Kategori / Tags:** Tambahkan relasi *Many-to-Many* agar buku dapat difilter berdasarkan genre (Pendidikan, Teknologi, Fiksi).
