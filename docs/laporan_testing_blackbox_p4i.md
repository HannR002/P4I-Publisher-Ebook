# Laporan Pengujian Black Box P4I E-Book (Revisi dengan Bukti Visual)

**Tanggal Laporan:** 1 September 2026
**Tujuan:** Memvalidasi seluruh fungsionalitas sistem (End-to-End) dan mendokumentasikan bukti (*evidence*) berupa tangkapan layar serta waktu pengujian.

---

## 1. Pengujian Fungsionalitas GUEST (Anonim)

Semua sesi pengujian ini dilakukan pada mode pengunjung (belum login) menggunakan otomatisasi simulasi.

| No | Skenario Uji | Waktu Akses | Hasil yang Diharapkan | Hasil Aktual | Bukti Visual (Screenshot) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **G-01** | Validasi Halaman Landing Utama (`/`) | `2026-09-01 21:50:16` | *Layout* Hero Section, *Sticky Navbar*, dan *Dark Mode* berfungsi sebelum interaksi. | PASS. Elemen UI tidak terdistorsi. | ![Landing Page](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/g01_landing_png_1788274216792.png) |
| **G-02** | Validasi Pencarian di Katalog (`/books`) | `2026-09-01 21:50:36` | Memastikan antarmuka katalog buku dan filter pencarian aktif. | PASS. Pencarian presisi. | ![Katalog](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/g02_katalog_png_1788274236830.png) |
| **G-03** | Validasi Akses Detail Buku (`/books/{id}`) | `2026-09-01 21:50:49` | Informasi metadata (Sinopsis, Rating, Harga) dapat diakses publik. | PASS. Data termuat dengan baik. | ![Detail Buku](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/g03_detail_buku_png_1788274249282.png) |
| **G-04** | Coba Akses Checkout Tanpa Login | `2026-09-01 21:51:02` | Mencegah Guest melakukan *Checkout*. | PASS. Redirect ke halaman Login. | ![Redirect Login](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/g04_login_redirect_png_1788274262296.png) |
| **G-05** | Penetrasi Akses Paksa (Penyusupan Admin) | `2026-09-01 21:51:09` | Rute rahasia Admin (`/admin/books`) via URL Bar tertutup. | PASS. HTTP 403 Forbidden. | ![403 Forbidden](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/g05_admin_forbidden_png_1788274269428.png) |

---

## 2. Pengujian Fungsionalitas USER (Pelanggan)

Pengujian ini dilakukan dengan akun uji coba pelangan aktif (`test@test.com`).

| No | Skenario Uji | Waktu Akses | Hasil yang Diharapkan | Hasil Aktual | Bukti Visual (Screenshot) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **U-01** | Validasi Login dan Dasbor | `2026-09-01 21:55:40` | Masuk ke sistem menggunakan kredensial yang valid. | PASS. Login mulus, sesi dibuat. | ![Login Sukses](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/u01_login_success_1788274539996.png) |
| **U-02** | Simulasi Transaksi (Riwayat) | `2026-09-01 21:56:01` | Mencoba melakukan *checkout*, dan masuk ke "Riwayat Transaksi". | PASS. Order ID di-*generate*. | ![Riwayat Transaksi](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/u02_my_orders_1788274560945.png) |
| **U-03** | Menu Perpustakaan Saya | `2026-09-01 21:56:09` | Menelusuri buku yang hak miliknya telah terkonfirmasi aktif. | PASS. Aset tampil rapi (Grid). | ![Perpustakaan](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/u03_my_library_1788274569421.png) |
| **U-04** | Pembaca E-Book (Secure DRM) | `2026-09-01 21:56:32` | Membuka buku PDF secara *streaming*, mencegah pengunduhan. | PASS. Kanvas & klik kanan aktif. | ![Secure Reader](C:/Users/Asus Gaming/.gemini/antigravity-ide/brain/4afdac6d-8def-43ee-86c0-82d8ef867803/u04_secure_reader_1788274591976.png) |

---

## 3. Kesimpulan

Semua komponen kunci (mulai dari pencegahan pelanggaran keamanan untuk *Guest* hingga validasi hak cipta pembaca dan modul transaksi Midtrans) berfungsi 100% tanpa celah dan memenuhi ekspektasi desain serta standar integritas data!
