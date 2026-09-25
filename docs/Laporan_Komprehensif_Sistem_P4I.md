# Laporan Komprehensif Sistem Penerbitan Digital P4I (E-Book Publisher)

Dokumen ini merupakan laporan arsitektural, struktural, dan historis yang merangkum seluruh hasil pengembangan platform E-Book P4I dari Fase 1 hingga penyelesaian Fase 5D, beserta analisis kualitas perangkat lunak (*Software Quality Analysis*).

---

## 1. Arsitektur Sistem (System Architecture)
Platform ini dibangun dengan arsitektur **Monolith MVC (Model-View-Controller)** menggunakan *framework* Laravel 11.
* **Frontend:** Laravel Blade, komponen interaktif, Tailwind CSS 3.4 (JIT compiler), dan Vanilla JavaScript murni.
* **Backend:** PHP 8.2+, sistem *Routing*, *Middleware* protektif, dan Eloquent ORM.
* **Database:** SQLite (Bisa dimigrasi mulus ke MySQL/PostgreSQL tanpa mengubah kode berkat *Query Builder* dan *Migrations*).
* **Eksternal:** Terintegrasi dengan **Midtrans Payment Gateway** via *server-to-server HTTP Webhook* yang dilindungi tanda tangan HMAC-SHA512.
* **Keamanan Dokumen:** Menggunakan modul **Secure DRM** (Digital Rights Management). File biner asli disembunyikan di luar zona publik (`storage/app/private/`), lalu dialirkan (di-*stream*) ke memori peramban hanya jika pengguna memegang lisensi aktif (`BookLicense`).

## 2. Struktur Folder & Utilitas Berkas
Sistem ini mematuhi standar hirarki Laravel dengan modifikasi injeksi *domain-driven*:
* `app/Models/`: Memuat entitas bisnis (*Book, User, Author, Order, RoyaltyLedger*, dsb).
* `app/Http/Controllers/`: Pemroses logika HTTP, dipisah dalam subfolder `Admin/`, `Author/`, dan `Frontend/`.
* `app/Services/`: **(Krusial)** Pusat logika finansial berat (contoh: `PayoutService.php` menangani transaksi uang, penguncian *database*, dan alokasi buku besar royalti).
* `app/Http/Middleware/`: Memuat penjaga gerbang seperti `EnsureUserIsAuthor`, `EnsureAccountIsActive`, `CheckMidtransIp`.
* `database/migrations/`: Cetak biru perancangan ERD.
* `resources/views/`: Tata letak UI/UX dipilah menjadi `admin/`, `author/`, `components/`, dan `layouts/`.
* `storage/app/private/`: Folder rahasia tempat file PDF berharga milik yayasan dan penulis, serta foto KTP disimpan. File di sini **mustahil** diakses dari URL internet publik.
* `public/js/secure-reader.js`: Otak dari sistem anti-pembajakan di peramban pembaca (menonaktifkan klik kanan, blokir print, perenderan kanvas).

## 3. Entity-Relationship Diagram (ERD) & Relasi Data
* **Users (Tabel Pusat):** Menyimpan kredensial. Memiliki relasi 1-to-1 dengan profil `Authors`.
* **Authors:** Menyimpan informasi pena, verifikasi KYC, dan nomor rekening. Berelasi 1-to-M (Satu-ke-Banyak) dengan `Books` dan `BookSubmissions`.
* **Books & Categories:** Relasi *Many-to-Many*. Sebuah buku bisa memiliki banyak kategori, dan sebaliknya (dijembatani tabel `book_category`).
* **Siklus Belanja (Orders & OrderItems):** `User` (Pembeli) menciptakan 1 `Order` (Keranjang). 1 `Order` bisa berisi banyak `OrderItems` (Buku).
* **BookLicenses:** Setelah pesanan lunas, sistem mencetak Lisensi (*1-to-1* per pengguna-buku). Lisensi ini menyimpan titik baca terakhir (`last_read_page`) dan alasan pencabutan lisensi.
* **Mesin Royalti (RoyaltyLedgers & PayoutRequests):** Setiap `OrderItem` lunas memicu kelahiran 1 baris `RoyaltyLedger` (Buku besar) bagi Penulis. Saat Penulis menarik dana, banyak `RoyaltyLedger` akan dikelompokkan ke dalam 1 `PayoutRequest` (Relasi *Many-to-1*).
* **Kurasi (BookSubmissions & SubmissionReviews):** `BookSubmission` (Naskah Mentah) bisa dikomentari berulang kali oleh Admin lewat entitas `SubmissionReviews` (Relasi *1-to-Many*).

## 4. Analisis Hak Akses (RBAC) & Fitur
Setiap aktor memiliki batas otoritas absolut (*Strict Authorization*):

### A. Guest (Pengunjung Anonim)
* **Bisa dilakukan:** Melihat beranda, mencari buku di katalog publik, melihat detail buku.
* **Tidak bisa dilakukan:** Membeli buku, membaca E-Book, mengakses keranjang belanja. Mereka akan dipaksa _login_.

### B. Reguler User (Pembaca/Pembeli)
* **Bisa dilakukan:** *Checkout* pembelian (Midtrans), membaca E-Book secara _streaming_ di "Rak Buku Saya", menyimpan progres halaman bacaan, mengubah profil biasa.
* **Tidak bisa dilakukan:** Masuk ke Dasbor Penulis, mengunggah naskah, melihat data pesanan orang lain.

### C. Penulis (Author) - *Pending KYC*
* **Bisa dilakukan:** Mendaftar dengan NIK dan berkas KTP, mengakses dasbor statis.
* **Tidak bisa dilakukan:** Menarik dana royalti, menerbitkan naskah, melihat menu *Payout* (Akses akan di-_redirect_ kembali ke halaman status verifikasi KYC).

### D. Penulis (Author) - *Verified KYC*
* **Bisa dilakukan:** Mengunggah draf naskah, membaca revisi dari Kurator, memantau metrik penjualan bukunya *real-time*, menambah nomor rekening bank, dan mencairkan uang dompet royalti.
* **Tidak bisa dilakukan:** Mengubah status naskahnya sendiri menjadi "Terbit", mengubah besaran pembagian royalti (70:30 adalah mutlak dari sisi sistem), mengedit judul buku yang telah terbit secara sepihak.

### E. Super Admin / Kurator
* **Bisa dilakukan:** Memegang kuasa dewa atas platform. Memverifikasi/Menolak KYC Penulis, meninjau naskah, meminta revisi, menerbitkan naskah menjadi Buku Katalog, menonaktifkan akun Pengguna sembarangan (blokir pembajak), menyetujui transfer uang (*Payout*).
* **Tidak bisa dilakukan:** Mencuri uang penulis (Sistem memblokir admin mengubah bank penulis atau meretas buku besar), membeli buku untuk dirinya sendiri melalui panel admin (harus melalui antarmuka publik).

## 5. Rangkuman Historis Tahapan Pengembangan
Sistem ini dibangun melalui lompatan iteratif (*Agile*):
* **Fase 1 (Arsitektur Dasar):** Setup Laravel, Tailwind, Breeze, dan modifikasi *layout* sistem premium (Dark Mode persisten).
* **Fase 2 (E-Commerce & DRM):** Mengunci URL PDF, membuat integrasi Midtrans Webhook, membangun *Secure E-Book Reader*, dan pencegahan pembelian ganda.
* **Fase 3 (Manajemen Admin):** CRUD Katalog Buku, Manajemen Pengguna tingkat lanjut (fitur Suspend/Blokir lisensi pembajak).
* **Fase 4 (Author Portal & KYC):** Pembentukan profil Penulis, prosedur pengunggahan KTP aman, dan antarmuka pengecekan identitas oleh Admin.
* **Fase 5 (Kurasi Naskah & Payout Engine):** Penyatuan pipa penerbitan (Pengajuan Naskah $\rightarrow$ Review $\rightarrow$ Buku Katalog) dan mesin finansial (Ledger Royalti otomatis berdasarkan persentase, serta Payout Service dengan pengamanan ganda).

## 6. Penjelasan "Fase Penyempurnaan Berikutnya"
Meskipun sistem secara teknis mencapai **100% Fungsi Esensial (MVP yang Sempurna)**, sebuah aplikasi level-Enterprise (perusahaan besar) selalu memiliki celah skalabilitas jika pengunjung melonjak ribuan orang. Maksud dari penyempurnaan (Eskalasi) adalah fitur mewah/opsional, seperti:
1. **Server-Side Watermarking Dinamis:** Saat ini *watermark* nama pembeli ada di level antarmuka HTML/JS. Ke depannya, kita bisa menggunakan modul `FPDI/FPDF` untuk *mengecap/menyablon* nama+email pembeli langsung secara biner ke tiap halaman PDF sebelum dikirim (*On-the-fly stamping*). Sangat aman dari _Screenshot_.
2. **Offloading File Storage (AWS S3/CDN):** Saat file PDF mencapai ratusan Gigabyte, *storage/private* lokal akan kehabisan ruang. Sistem bisa disempurnakan untuk menyimpan file naskah ke Cloud (seperti AWS S3) secara tertutup.
3. **Modul Kupon Diskon & Afiliasi:** Penulis belum bisa menyebarkan kode promo diskon khusus untuk pengikutnya.
4. **Antrean (Queueing) Email:** Pengiriman notifikasi (*Purchase Mail*, Payout Notification) saat ini dieksekusi sinkron (membuat *loading* tambah 2 detik). Sebaiknya disempurnakan dengan *Laravel Horizon* (Background Jobs).

## 7. Analisis Kemungkinan Bug & Apa yang Belum Dites
Sistem ini sudah menjalani *Test-Driven Development (TDD)* via PHPUnit dengan pengujian level _Database, Controller, dan BVA (Boundary Value Analysis)_, namun secara _Black-Box_ dunia nyata ada beberapa celah yang harus diawasi saat *Live/Production*:
1. **Batas Memori Unggah Naskah Besar (Bug Potensi):** Jika Penulis mengunggah PDF sebesar 200MB, server *PHP* bisa *Crash* (*Allowed memory size exhausted*) karena batas wajar `php.ini` biasanya hanya 2MB - 128MB. Ini belum dites dengan PDF sungguhan bervolume raksasa.
2. **Double Click / Race Condition pada Midtrans Callback:** Jika server Midtrans menembak Webhook sukses secara ganda di detik yang persis sama, meski kita punya mitigasi, dalam beban server ekstrem, ini perlu dites melalui *Stress Test* (seperti Apache JMeter) untuk memastikan Royalti tidak terganda.
3. **PDF Rendering Crash pada Ponsel Lawas:** *Library PDF.js* terkadang gagal memuat struktur dokumen rumit pada memori perangkat ponsel (*Low-End Mobile Devices*). Ini butuh *User Acceptance Testing (UAT)* manual lintas peramban (*Cross-browser testing*).
4. **Pembatalan *Refund* Payout:** Jika Admin menyetujui Payout (`completed`) tapi transfer Bank aslinya *Gagal (Bounced)*, sistem saat ini belum memiliki fitur *Rollback* status *completed* ke *pending* kembali. Admin harus meretas pangkalan data secara manual untuk mengembalikannya.
