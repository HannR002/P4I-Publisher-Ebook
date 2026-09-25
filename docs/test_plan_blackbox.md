# 🧪 Skenario Pengujian Black Box - P4I Publisher Platform (End-to-End)
**Tipe Pengujian:** Black-Box Testing (UI/E2E) & Security/Boundary Testing  
**Pelaksana:** Agen Otomasi (Browser Subagent)  
**Tujuan:** Memvalidasi seluruh alur bisnis platform dari awal hingga akhir, memverifikasi batasan hak akses (Roles), serta menguji kekebalan sistem terhadap potensi *bug* dan penyalahgunaan.

> [!IMPORTANT]
> **Mohon Review Anda:** Dokumen ini adalah DRAFT Test Plan yang telah diperluas untuk mencakup **seluruh alur dan role** dalam sistem. Silakan periksa kelengkapannya. Jika disetujui, saya akan mengeksekusi tes ini menggunakan browser subagent. Waktu akses aktual dan tangkapan layar akan diisi secara otomatis selama eksekusi.

---

### Tabel Eksekusi Test Case (Komprehensif)

| ID Tes | Role | Skenario / Fitur | Pre-kondisi | Langkah Pengujian (Input) | Hasil yang Diharapkan (Expected) | Hasil Aktual | Waktu Akses | Status | Tangkapan Layar |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **GUEST-01** | Guest | Navigasi Katalog & Detail | User belum login. | 1. Buka Homepage (Katalog).<br>2. Klik "Lihat Detail" pada salah satu buku. | Katalog dan detail buku tampil sempurna tanpa memerlukan otentikasi. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **GUEST-02** | Guest | Beli Tanpa Login (Auth Guard) | User belum login. | 1. Di halaman detail buku, klik "Beli Sekarang". | Sistem memblokir aksi dan langsung mengarahkan (redirect) ke halaman Login. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **GUEST-03** | Guest | Akses Paksa URL DRM | User belum login. | 1. Ketik URL `/reader/1` secara manual di browser. | Sistem menolak dengan error 401 Unauthenticated (Bug-01 Fixed). | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-01** | User | Registrasi & Login | Data user baru disiapkan. | 1. Buka `/register`.<br>2. Isi nama, email valid, password.<br>3. Klik Daftar. | Registrasi berhasil, user otomatis login dan dialihkan ke Homepage. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-02** | User | Checkout E-Book (Happy Path) | Login sbg User biasa. Saldo/Sandbox Midtrans aktif. | 1. Klik "Beli" buku berbayar.<br>2. Di halaman Checkout, klik Bayar.<br>3. Selesaikan pembayaran di popup Midtrans. | Sistem mencatat order sukses. Redirect ke halaman Sukses. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-03** | User | Checkout Buku Gratis (Bypass API) | Login sbg User biasa. Ada buku Rp 0. | 1. Klik "Beli" buku gratis (Rp 0).<br>2. Proses checkout. | Bypass Midtrans langsung sukses. Lisensi otomatis dibuat, redirect ke "My Library". | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-04** | User | Cegah Beli Ulang (Bug-03) | User **sudah punya** lisensi Buku A. | 1. Buka detail Buku A di katalog.<br>2. Coba klik "Beli Sekarang". | Validasi menolak proses checkout ("Anda sudah memiliki lisensi aktif..."). | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-05** | User | Riwayat Transaksi (My Orders) | User memiliki minimal 1 transaksi. | 1. Buka `/my-orders`. | Menampilkan daftar order, status pembayaran, dan nominal yang sesuai. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-06** | User | Akses Koleksi (My Library) | User punya 1 lisensi aktif. | 1. Buka `/my-library`. | Buku yang sudah dibeli/gratis tampil di perpustakaan pribadi dengan tombol "Baca Sekarang". | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-07** | User | DRM Reader (Stream PDF) | User klik "Baca Sekarang". | 1. Sistem generate Signed URL.<br>2. Iframe meload PDF (menggunakan `fpassthru`). | PDF tampil di layar tanpa mendownload (tombol download di-disable/hidden). Status HTTP 200. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-08** | User | DRM Expired URL (Boundary) | User memiliki Signed URL DRM yang sah. | 1. Ambil URL Signed DRM.<br>2. Tunggu/Manipulasi waktu browser lewat dari 15 menit. | Saat URL diakses ulang (atau di-refresh), sistem menolak dengan 403 Invalid Signature. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **USER-09** | User | Akses Paksa Area Admin | User biasa (non-admin). | 1. Ketik URL `/admin/books` secara manual. | Sistem menolak (403 Forbidden) + Mencatat di Log Audit Security (Bug-09 Fixed). | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **ADMIN-01** | Admin | Login Admin | Akun admin (`is_admin=true`) tersedia. | 1. Login menggunakan kredensial Admin. | Berhasil masuk. Terdapat menu ekstra (opsional) atau izin akses ke `/admin`. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **ADMIN-02** | Admin | Dashboard Kelola Buku | Login sbg Admin. | 1. Akses URL `/admin/books`. | Menampilkan daftar seluruh buku, jumlah lisensi terjual, dan status publikasi. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **ADMIN-03** | Admin | Upload Buku (Happy Path) | Login sbg Admin. File PDF dan Cover valid. | 1. Akses `/admin/books/upload`.<br>2. Isi form, harga Rp 150.000, lampirkan PDF.<br>3. Submit. | Buku baru tersimpan di database, file masuk ke `storage/app/private_books`, redirect success. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **ADMIN-04** | Admin | Validasi Harga Negatif | Login sbg Admin. | 1. Isi form upload.<br>2. Masukkan harga `Rp -50000`.<br>3. Submit. | Sistem menolak proses form (Validasi error `price must be at least 0`). | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |
| **ADMIN-05** | Admin | Toggle Publish Status | Buku B saat ini berstatus 'Published'. | 1. Di halaman `/admin/books`, klik tombol 'Unpublish' pada Buku B. | Status berubah jadi 'Hidden', buku tersebut hilang dari halaman katalog publik. | *[Menunggu Eksekusi]* | *[0.00s]* | ⏳ PENDING | *[Slot Screenshot]* |

---

### Prosedur Eksekusi (Automation)
Setelah dokumen draf ini **disetujui (Approved)** oleh Anda:
1. Agen (Browser Subagent) akan dihidupkan untuk mengeksekusi ke-17 skenario di atas satu per satu di UI/Browser yang sesungguhnya.
2. Setiap langkah yang mengubah status database (seperti sukses checkout) akan memicu *screenshot*.
3. Kolom **Waktu Akses**, **Hasil Aktual**, dan **Status** (PASS/FAIL) akan terisi dengan data sebenarnya (berdasarkan observasi bot terhadap DOM HTML dan load time).
4. Hasil akhir akan diperbarui di dokumen artefak ini.
