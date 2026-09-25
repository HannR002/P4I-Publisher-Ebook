# 📊 Laporan Komprehensif Sistem P4I Publisher Ebook
**Folder:** `d:\P4I_Publisher_Ebook`  
**Jenis Sistem:** E-Book Marketplace dengan Author Portal, DRM, dan Royalty System  
**Framework:** Laravel 12 (PHP ^8.2)  
**Tanggal Analisis:** 10 September 2026  
**Analis:** Antigravity AI

---

## 📋 Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Stack Teknologi](#2-stack-teknologi)
3. [Struktur Folder](#3-struktur-folder)
4. [Arsitektur Sistem](#4-arsitektur-sistem)
5. [Skema Database & ERD](#5-skema-database--erd)
6. [Roles & Hak Akses](#6-roles--hak-akses)
7. [Use Case per Role](#7-use-case-per-role)
8. [Alur Sistem (Workflow)](#8-alur-sistem-workflow)
9. [Fitur-Fitur Sistem](#9-fitur-fitur-sistem)
10. [Sistem Keamanan & DRM](#10-sistem-keamanan--drm)
11. [Analisis Kualitas Kode](#11-analisis-kualitas-kode)
12. [Temuan & Rekomendasi](#12-temuan--rekomendasi)

---

## 1. Ringkasan Eksekutif

**P4I Publisher Ebook** adalah platform **e-book marketplace** berbasis web yang dibangun dengan Laravel 12. Sistem ini merupakan ekosistem lengkap dengan tiga aktor utama: **Pembeli**, **Penulis**, dan **Admin**. Platform ini menggabungkan:

- **E-commerce** — pembelian e-book dengan gateway pembayaran Midtrans
- **DRM (Digital Rights Management)** — kontrol akses PDF dengan signed URL + IP binding
- **Author Portal** — pendaftaran penulis, KYC, submit naskah, dan penarikan royalti
- **Curation System** — kurasi naskah editorial 7 tahap oleh tim admin
- **Royalty Engine** — kalkulasi dan distribusi royalti otomatis per transaksi

### Identitas Sistem
| Atribut | Detail |
|---------|--------|
| **Nama Sistem** | P4I Publisher Ebook |
| **Database Prod** | MySQL — `p4i_publisher` |
| **Database Dev** | SQLite (`database/database.sqlite`) |
| **Payment Gateway** | Midtrans Snap (Merchant ID: M585613710) |
| **Mode Payment** | Sandbox (belum produksi) |
| **Queue** | Sync (dev) → Database Queue (prod) |
| **Royalti Penulis** | 70% per penjualan (platform 30%) |
| **Minimum Payout** | Rp 100.000 |

---

## 2. Stack Teknologi

### Backend
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| **Framework** | Laravel | ^12.0 |
| **Bahasa** | PHP | ^8.2 |
| **Auth Scaffold** | Laravel Breeze | ^2.4 |
| **Payment Gateway** | Midtrans PHP SDK | ^2.6 |
| **ORM** | Eloquent (Laravel) | — |
| **Testing** | PHPUnit | ^11.5 |
| **Code Format** | Laravel Pint | ^1.24 |
| **Real-time Log** | Laravel Pail | ^1.2 |
| **Dev Server** | Laravel Sail | ^1.41 |

### Frontend
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| **CSS Framework** | TailwindCSS | ^3 |
| **Build Tool** | Vite | — |
| **PostCSS** | autoprefixer | — |
| **Templating** | Blade (Laravel) | — |

### Database & Storage
| Komponen | Detail |
|----------|--------|
| **DB Produksi** | MySQL @ 127.0.0.1:3306 |
| **DB Development** | SQLite |
| **Session** | Database |
| **Cache** | Database |
| **File Storage** | Local (`storage/app/`) — private |
| **Queue** | Sync (dev) |

### Penggunaan Bahasa

```
BAHASA           LOKASI                          PROPORSI
───────────────────────────────────────────────────────────
PHP 8.2          app/, routes/, database/        ~80%
Blade/HTML       resources/views/                ~15%
JavaScript       resources/js/, vite.config.js   ~3%
CSS/Tailwind     resources/css/, tailwind.config  ~2%
```

---

## 3. Struktur Folder

```
d:\P4I_Publisher_Ebook\
│
├── 📁 app/
│   ├── 📁 Events/
│   │   └── OrderPaidEvent.php              — Event dipicu setelah pembayaran sukses
│   ├── 📁 Http/
│   │   ├── 📁 Controllers/
│   │   │   ├── 📁 Admin/
│   │   │   │   ├── AdminPayoutController.php     — Kelola pencairan royalti penulis
│   │   │   │   ├── AuthorKycController.php       — Verifikasi KYC identitas penulis
│   │   │   │   ├── BookUploadController.php      — Upload & publish e-book (Admin)
│   │   │   │   ├── ManuscriptCurationController.php — Kurasi naskah submission
│   │   │   │   ├── ReportController.php          — Laporan & export CSV
│   │   │   │   └── UserController.php            — Kelola akun pengguna
│   │   │   ├── 📁 Author/
│   │   │   │   ├── AuthorDashboardController.php — Dashboard penulis terverifikasi
│   │   │   │   ├── AuthorPayoutController.php    — Request penarikan royalti
│   │   │   │   ├── AuthorProfileController.php   — Registrasi & profil penulis
│   │   │   │   └── BookSubmissionController.php  — CRUD submission naskah
│   │   │   ├── 📁 Auth/                          — Auth Laravel Breeze
│   │   │   ├── BookController.php                — Katalog & detail buku (publik)
│   │   │   ├── CheckoutController.php            — Proses checkout + Midtrans
│   │   │   ├── Controller.php                    — Base controller
│   │   │   ├── DrmController.php                 — Streaming PDF + DRM signed URL
│   │   │   ├── LibraryController.php             — Perpustakaan & riwayat pesanan
│   │   │   ├── MidtransWebhookController.php     — Webhook payment dari Midtrans
│   │   │   ├── ProfileController.php             — Edit profil akun
│   │   │   └── ReaderController.php              — Reader placeholder
│   │   ├── 📁 Middleware/
│   │   │   ├── CheckMidtransIp.php               — Validasi IP server Midtrans
│   │   │   ├── EnsureAccountIsActive.php         — Blokir akun non-aktif
│   │   │   ├── EnsureAuthorIsVerified.php        — Hanya penulis terverifikasi KYC
│   │   │   ├── EnsureUserIsAdmin.php             — Hanya admin (is_admin=true)
│   │   │   └── EnsureUserIsAuthor.php            — Hanya user yang punya profil penulis
│   │   └── 📁 Requests/
│   │       └── Author/
│   │           └── StoreSubmissionRequest.php    — Validasi submit naskah
│   ├── 📁 Jobs/
│   │   └── ProcessMidtransWebhook.php            — Background job proses webhook
│   ├── 📁 Listeners/
│   │   └── IssueLicenseOnOrderPaid.php           — Listener: issue lisensi setelah bayar
│   ├── 📁 Mail/                                  — Notifikasi email
│   ├── 📁 Models/
│   │   ├── Author.php          — Profil penulis + KYC + rekening bank
│   │   ├── Book.php            — Buku/e-book dengan auto-slug
│   │   ├── BookLicense.php     — Lisensi akses buku per user
│   │   ├── BookSubmission.php  — Submission naskah penulis
│   │   ├── Category.php        — Kategori buku
│   │   ├── Order.php           — Order transaksi
│   │   ├── OrderItem.php       — Item dalam order (harga dikunci saat beli)
│   │   ├── PayoutRequest.php   — Request pencairan royalti (ULID PK)
│   │   ├── Review.php          — Ulasan buku oleh pembeli
│   │   ├── RoyaltyLedger.php   — Ledger royalti per item terjual
│   │   ├── SubmissionReview.php — Log review editorial per submission
│   │   └── User.php            — Akun pengguna (is_admin, is_active)
│   ├── 📁 Providers/
│   ├── 📁 Services/
│   │   ├── OrderIdGenerator.php  — Generate order ID unik
│   │   └── PayoutService.php     — Logika payout FIFO + DB transaction
│   └── 📁 View/
│
├── 📁 database/
│   ├── 📁 factories/
│   ├── 📁 migrations/ (25 file)
│   └── 📁 seeders/
│
├── 📁 docs/
│   └── Laporan_Komprehensif_Sistem_P4I.md    — Dokumentasi lama
│
├── 📁 resources/
│   ├── 📁 css/
│   ├── 📁 js/
│   └── 📁 views/
│       ├── 📁 admin/                        — Semua view admin panel
│       ├── 📁 auth/                         — Login, register, reset password
│       ├── 📁 author/                       — Dashboard & portal penulis
│       ├── 📁 books/                        — Katalog & detail buku
│       ├── 📁 checkout/                     — Halaman checkout & payment
│       ├── 📁 components/                   — Komponen Blade reusable
│       ├── 📁 emails/                       — Template email notifikasi
│       ├── 📁 layouts/                      — Layout master (app, guest)
│       ├── 📁 pages/                        — Halaman statis (about, contact, dll)
│       ├── 📁 profile/                      — Edit profil
│       ├── dashboard.blade.php
│       ├── my-library.blade.php             — Perpustakaan buku pembeli
│       ├── my-orders.blade.php              — Riwayat pesanan
│       ├── reader.blade.php                 — Viewer PDF dalam browser
│       └── welcome.blade.php               — Homepage dengan 4 buku terbaru
│
├── 📁 routes/
│   ├── auth.php                             — Route auth Breeze
│   └── web.php                             — 141 baris, semua route utama
│
├── 📁 tests/
│   └── Unit/
│       └── AuthorArchitectureTest.php       — Unit test arsitektur author
│
├── 📁 testing_evidence/                     — Bukti testing (screenshot/log)
│
├── .env                                     — Konfigurasi lingkungan
├── composer.json                            — PHP dependencies
├── package.json                             — JS dependencies
├── server_deploy.sh                         — Script deploy ke server
├── hostinger_htaccess.stub                  — .htaccess template Hostinger
└── ngrok.exe                                — Tunnel untuk webhook test lokal
```

---

## 4. Arsitektur Sistem

### 4.1 Pola Arsitektur

MVC + Service Layer + Event-Listener + Queue Worker:

```
BROWSER / CLIENT
      │
      ▼
ROUTES (web.php, auth.php)
  ├── Public Routes (guest)
  ├── Auth Routes (middleware: auth)
  ├── Admin Routes (middleware: auth + admin)
  └── Author Routes (middleware: auth + author + author.verified)
      │
      ▼
MIDDLEWARE STACK
  ├── EnsureAccountIsActive      → Blokir akun dinonaktifkan
  ├── EnsureUserIsAdmin          → Guard admin panel
  ├── EnsureUserIsAuthor         → Guard author portal
  ├── EnsureAuthorIsVerified     → Guard setelah KYC approved
  └── CheckMidtransIp            → Guard webhook endpoint
      │
      ▼
CONTROLLERS
  ├── (Public) BookController, CheckoutController, DrmController
  ├── (Admin) BookUploadController, UserController, ManuscriptCurationController,
  │          AuthorKycController, ReportController, AdminPayoutController
  └── (Author) AuthorProfileController, BookSubmissionController, AuthorPayoutController
      │
      ▼
SERVICES & MODELS
  ├── PayoutService          → Bisnis logic payout FIFO
  ├── OrderIdGenerator       → Generate ID unik
  └── Eloquent Models (12)
      │
      ▼
EVENTS & LISTENERS
  ├── OrderPaidEvent         → Dipicu setelah pembayaran sukses
  └── IssueLicenseOnOrderPaid → Otomatis issue lisensi buku
      │
      ▼
JOBS (Queue)
  └── ProcessMidtransWebhook → Proses webhook async
      │
      ▼
DATABASE
  MySQL (prod) / SQLite (dev) — 14 tabel domain
```

### 4.2 Alur File PDF (DRM Architecture)

```
[Pembeli beli buku] → Lisensi diissue (book_licenses)
        │
[Klik "Baca"] → GET /reader/{bookId} → DrmController::generateReaderUrl()
        │
        ├── Cek lisensi aktif → Ada? Lanjut / Tidak? 403
        ▼
[Generate Signed URL] — berlaku 15 menit, terikat IP spesifik
        │
[Browser load reader.blade.php] — PDF tampil dalam iframe/embed
        │
[iframe request] → GET /drm/stream/{license_key} → DrmController::streamPdf()
        │
        ├── Verifikasi signature → Valid?
        ├── Verifikasi IP → cocok?
        ├── Cek lisensi masih aktif?
        │
[Stream PDF] → fpassthru() — efisien memori, tanpa load ke RAM
```

---

## 5. Skema Database & ERD

### 5.1 Daftar Tabel (14 Domain + Sistem)

| Tabel | Deskripsi | Catatan |
|-------|-----------|---------|
| `users` | Akun pengguna (is_admin, is_active) | Tabel auth utama |
| `authors` | Profil penulis + KYC + rekening bank | 1:1 dengan users |
| `books` | Koleksi e-book yang diterbitkan | Auto-slug |
| `categories` | Kategori/genre buku | — |
| `book_category` | Pivot buku-kategori | Many-to-Many |
| `book_submissions` | Naskah yang disubmit penulis | 7 status workflow |
| `submission_reviews` | Log review per aksi editorial | Audit trail |
| `orders` | Transaksi pembelian | snap_token Midtrans |
| `order_items` | Item dalam satu order (harga historis) | Harga dikunci saat beli |
| `book_licenses` | Lisensi akses buku per user | License key UUID |
| `royalty_ledgers` | Ledger royalti per item terjual | FIFO allocation |
| `payout_requests` | Permintaan pencairan royalti | ULID sebagai PK |
| `reviews` | Ulasan & rating buku oleh pembeli | — |
| `sessions` | Session database | Sistem Laravel |
| `cache` | Cache database | Sistem Laravel |
| `jobs` + `failed_jobs` | Queue jobs | Sistem Laravel |

### 5.2 Skema Tabel Lengkap

#### `users`
```sql
CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password        VARCHAR(255) NOT NULL,   -- Bcrypt, BCRYPT_ROUNDS=12
    is_admin        BOOLEAN DEFAULT FALSE,
    is_active       BOOLEAN DEFAULT TRUE,    -- Blokir akun
    remember_token  VARCHAR(100) NULL,
    created_at, updated_at TIMESTAMP
);
```

#### `authors` (Profil Penulis)
```sql
CREATE TABLE authors (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED UNIQUE NOT NULL, -- FK → users (CASCADE)
    pen_name        VARCHAR(150) NOT NULL,
    bio             TEXT NULL,
    id_card_number  TEXT NOT NULL,       -- ENCRYPTED di application layer
    id_card_path    VARCHAR(255) NULL,   -- Foto KTP tersimpan private
    bank_name       VARCHAR(50) NULL,
    bank_account    VARCHAR(50) NULL,
    bank_holder_name VARCHAR(150) NULL,
    kyc_status      ENUM('unverified','pending','verified','rejected') DEFAULT 'unverified',
    rejection_reason TEXT NULL,
    verified_at     TIMESTAMP NULL,
    created_at, updated_at TIMESTAMP
);
```

#### `books` (E-Book)
```sql
CREATE TABLE books (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) UNIQUE NOT NULL,   -- Auto-generated
    author          VARCHAR(255) NOT NULL,           -- Nama tampilan
    author_id       BIGINT UNSIGNED NULL,            -- FK → authors
    description     TEXT NULL,
    price           DECIMAL(10,2) NOT NULL,
    isbn            VARCHAR(50) NULL,
    pages           INT UNSIGNED NULL,
    publish_date    DATE NULL,
    cover_image_path VARCHAR(255) NULL,
    file_path       VARCHAR(255) NOT NULL,           -- Path PDF private
    is_published    BOOLEAN DEFAULT FALSE,
    created_at, updated_at TIMESTAMP
);
```

#### `book_submissions` (Naskah Penulis)
```sql
CREATE TABLE book_submissions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id       BIGINT UNSIGNED NOT NULL,   -- FK → authors (CASCADE)
    category_id     BIGINT UNSIGNED NULL,       -- FK → categories
    title           VARCHAR(255) NOT NULL,
    synopsis        TEXT NOT NULL,
    proposed_price  DECIMAL(12,2) DEFAULT 0,
    manuscript_path VARCHAR(255) NOT NULL,       -- PDF naskah (private)
    cover_preview_path VARCHAR(255) NULL,
    status          ENUM('draft','submitted','in_review','revision_requested',
                         'approved','rejected','published') DEFAULT 'draft',
    book_id         BIGINT UNSIGNED NULL,        -- FK → books (setelah terbit)
    created_at, updated_at TIMESTAMP
);
```

#### `orders` (Transaksi)
```sql
CREATE TABLE orders (
    id              VARCHAR(255) PRIMARY KEY,   -- Format khusus OrderIdGenerator
    user_id         BIGINT UNSIGNED NOT NULL,   -- FK → users (CASCADE)
    gross_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('pending','success','failed','expired') DEFAULT 'pending',
    payment_type    VARCHAR(50) NULL,           -- 'free', 'credit_card', dsb
    snap_token      TEXT NULL,                  -- Token Midtrans Snap
    created_at, updated_at TIMESTAMP
);
```

#### `order_items` (Item Transaksi)
```sql
CREATE TABLE order_items (
    id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(255) NOT NULL,  -- FK → orders
    book_id  BIGINT UNSIGNED NOT NULL, -- FK → books
    price    DECIMAL(10,2) NOT NULL,   -- Harga historis (dikunci saat beli)
    created_at, updated_at TIMESTAMP
);
```

#### `book_licenses` (Lisensi Akses Buku)
```sql
CREATE TABLE book_licenses (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,  -- FK → users (CASCADE)
    book_id     BIGINT UNSIGNED NOT NULL,  -- FK → books (CASCADE)
    order_id    VARCHAR(255) NULL,         -- FK → orders (SET NULL)
    license_key VARCHAR(255) UNIQUE NOT NULL, -- UUID, kunci DRM
    status      ENUM('active','revoked') DEFAULT 'active',
    valid_until TIMESTAMP NULL,
    last_read_page INT NULL,               -- Bookmark halaman terakhir
    revocation_reason TEXT NULL,
    UNIQUE(user_id, book_id)              -- 1 lisensi per user per buku
);
```

#### `royalty_ledgers` (Buku Besar Royalti)
```sql
CREATE TABLE royalty_ledgers (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id         BIGINT UNSIGNED NOT NULL, -- FK → authors (RESTRICT)
    order_item_id     BIGINT UNSIGNED UNIQUE NOT NULL, -- FK → order_items (RESTRICT)
    book_id           BIGINT UNSIGNED NOT NULL, -- FK → books (RESTRICT)
    payout_request_id VARCHAR(26) NULL,         -- FK → payout_requests
    gross_sale        DECIMAL(12,2) NOT NULL,
    author_percentage DECIMAL(5,2) DEFAULT 70.00,
    author_earning    DECIMAL(12,2) NOT NULL,   -- 70% dari gross_sale
    platform_earning  DECIMAL(12,2) NOT NULL,   -- 30% dari gross_sale
    status            ENUM('pending','available','withdrawn') DEFAULT 'available',
    INDEX(author_id, status)
);
```

#### `payout_requests` (Pencairan Royalti)
```sql
CREATE TABLE payout_requests (
    id                     VARCHAR(26) PRIMARY KEY, -- ULID
    author_id              BIGINT UNSIGNED NOT NULL,  -- FK → authors (RESTRICT)
    amount                 DECIMAL(12,2) NOT NULL,
    bank_name_snapshot     VARCHAR(50) NULL,           -- Snapshot rekening saat request
    bank_account_snapshot  VARCHAR(50) NULL,
    bank_holder_name_snapshot VARCHAR(150) NULL,
    status                 ENUM('requested','processing','completed','rejected'),
    transfer_proof_path    VARCHAR(255) NULL,           -- Bukti transfer admin
    reference_number       VARCHAR(100) NULL,
    admin_notes            TEXT NULL,
    processed_by           BIGINT UNSIGNED NULL,        -- FK → users (admin)
    processed_at           TIMESTAMP NULL,
    created_at, updated_at TIMESTAMP
);
```

### 5.3 Entity Relationship Diagram (ERD)

```
                    ┌──────────────────────────────────────────────┐
                    │                    users                      │
                    │ PK id | name | email | password               │
                    │       | is_admin | is_active                  │
                    └───┬──────────────┬───────────────────────────┘
                        │ 1            │ 1
                        │ has one      │ has many
                        │ N            │ N
              ┌─────────▼────────┐  ┌──▼──────────────┐
              │     authors      │  │     orders       │
              │ FK user_id UNIQ  │  │ PK order_id (str)│
              │ pen_name, bio    │  │ FK user_id       │
              │ id_card (enc)    │  │ gross_amount     │
              │ bank info        │  │ status, snap_tok │
              │ kyc_status ◄─────┤  └──────┬───────────┘
              │  unverified      │         │ 1 has many
              │  pending         │         │ N
              │  verified        │  ┌──────▼───────────┐
              │  rejected        │  │   order_items    │
              └──┬──────────┬───┘  │ FK order_id      │
                 │          │      │ FK book_id        │
           1 has │    1 has │      │ price (historis)  │
              many│      many│      └──────┬────────────┘
                 │          │             │ 1 has one
          ┌──────▼──────┐ ┌─▼──────────┐ │ N
          │book_submiss.│ │royalty_ledg│◄┘
          │ FK author_id│ │FK author_id│
          │ title,synops│ │FK order_itm│
          │ manuscript  │ │FK book_id  │
          │ status ENUM │ │gross, earn │
          │ (7 status)  │ │status ENUM │
          │ FK book_id ─┤ │FK payout_rq│
          └─────┬───────┘ └────────────┘
                │                │
      1 has many│                │ N belongs to 1
                │          ┌─────▼──────────────┐
    ┌───────────▼────────┐ │  payout_requests   │
    │submission_reviews  │ │ PK ULID            │
    │ FK submission_id   │ │ FK author_id       │
    │ FK reviewer_id     │ │ amount             │
    │ feedback, action   │ │ bank snapshot      │
    └────────────────────┘ │ status ENUM (4)    │
                           │ transfer_proof     │
                           │ FK processed_by    │
                           └────────────────────┘

┌──────────────────────────────────────────────────────┐
│                      books                            │
│ PK id | title | slug (UNIQ) | author (text)          │
│ FK author_id | price | file_path | is_published       │
│ isbn, pages, publish_date, description                │
└──────────┬───────────────────┬───────────────────────┘
           │                   │
     1 has │ N           N has │ M (pivot: book_category)
     many  │                   │
   ┌───────▼──────┐  ┌─────────▼─────────┐
   │book_licenses │  │    categories     │
   │FK user_id    │  │ id, name, slug    │
   │FK book_id    │  └───────────────────┘
   │FK order_id   │
   │license_key   │  ┌──────────────────┐
   │status (2)    │  │     reviews      │
   │last_read_page│  │ FK user_id       │
   │revoke_reason │  │ FK book_id       │
   └──────────────┘  │ rating, body     │
                     └──────────────────┘
```

### 5.4 Status Machine

#### BookSubmission (7 Status)
```
draft → submitted → in_review → revision_requested → submitted (ulang)
                             → approved → published (menjadi Book)
                             → rejected
```

#### RoyaltyLedger (3 Status)
```
available → pending (saat payout request)
         → withdrawn (setelah payout complete)
                                              ↑
pending → available (jika payout ditolak admin)
```

#### PayoutRequest (4 Status)
```
requested → processing → completed
                       → rejected → (ledger kembali ke available)
```

#### BookLicense (2 Status)
```
active → revoked (oleh admin, dengan alasan)
revoked → active (restore oleh admin)
```

---

## 6. Roles & Hak Akses

### 6.1 Daftar Roles

| Role | Identifier di DB | Keterangan |
|------|-----------------|------------|
| **Guest** | (tidak login) | Hanya bisa lihat katalog |
| **Pembeli (User)** | `is_admin=false` | Beli buku, baca, review |
| **Penulis (Author)** | `is_admin=false` + ada `authors` record + `kyc_status=verified` | Submit naskah, tarik royalti |
| **Admin** | `is_admin=true` | Full akses admin panel |

### 6.2 Middleware Guard

| Middleware | Kondisi Akses |
|------------|--------------|
| `auth` | Harus login |
| `admin` (`EnsureUserIsAdmin`) | `user->is_admin === true` |
| `author` (`EnsureUserIsAuthor`) | `user->authorProfile !== null` |
| `author.verified` (`EnsureAuthorIsVerified`) | `author->kyc_status === 'verified'` |
| `active` (`EnsureAccountIsActive`) | `user->is_active === true` |
| `CheckMidtransIp` | Request dari IP server Midtrans |

### 6.3 Matriks Hak Akses per Route

| Route | Guest | Pembeli | Penulis | Admin |
|-------|-------|---------|---------|-------|
| `GET /` (Homepage) | ✅ | ✅ | ✅ | ✅ |
| `GET /books` (Katalog) | ✅ | ✅ | ✅ | ✅ |
| `GET /books/{slug}` (Detail) | ✅ | ✅ | ✅ | ✅ |
| `POST /checkout` (Beli) | ❌ | ✅ | ✅ | ✅ |
| `GET /my-library` | ❌ | ✅ | ✅ | ✅ |
| `GET /my-orders` | ❌ | ✅ | ✅ | ✅ |
| `GET /reader/{bookId}` | ❌ | ✅ | ✅ | ✅ |
| `POST /books/{book}/review` | ❌ | ✅ | ✅ | ✅ |
| `GET /author/register` | ❌ | ✅ | ✅ | ❌ |
| `GET /author/dashboard` | ❌ | ❌ | ✅ | ❌ |
| `* /author/submissions/*` | ❌ | ❌ | ✅ | ❌ |
| `POST /author/payouts` | ❌ | ❌ | ✅ | ❌ |
| `GET /admin/*` | ❌ | ❌ | ❌ | ✅ |
| `POST /api/midtrans/webhook` | ✅ (IP check) | ❌ | ❌ | ❌ |

---

## 7. Use Case per Role

### 7.1 Use Case: Guest (Pengunjung Tidak Login)

```
GUEST
  │
  ├── UC-G01: Melihat homepage dengan 4 buku terbaru
  ├── UC-G02: Browse katalog buku (/books)
  ├── UC-G03: Melihat detail buku (deskripsi, harga, kategori, ulasan)
  ├── UC-G04: Melihat halaman statis (About, Contact, Submission, Help, Privacy, Terms)
  ├── UC-G05: Login / Register
  └── UC-G06: Klik beli → redirect ke login dengan return URL
```

### 7.2 Use Case: Pembeli (User Terautentikasi)

```
PEMBELI
  │
  ├── UC-B01: Beli e-book (satu atau lebih)
  │    └── Cek kepemilikan → buat order → Midtrans Snap payment
  │
  ├── UC-B02: Beli buku GRATIS (harga 0)
  │    └── Bypass Midtrans → lisensi langsung diissue
  │
  ├── UC-B03: Melihat halaman pembayaran (checkout show)
  │    └── Dengan auto-renew snap token jika > 50 menit
  │
  ├── UC-B04: Melihat riwayat pesanan (/my-orders)
  │    └── Status: pending, success, failed, expired
  │
  ├── UC-B05: Melihat perpustakaan buku (/my-library)
  │    └── Buku aktif yang dimiliki
  │
  ├── UC-B06: Membaca e-book (DRM Reader)
  │    └── Stream PDF via signed URL (15 menit) + IP binding
  │
  ├── UC-B07: Bookmark halaman (auto-save saat membaca)
  │    └── POST /drm/progress
  │
  ├── UC-B08: Menulis ulasan & rating buku
  │
  ├── UC-B09: Edit profil akun (nama, email, password)
  │
  ├── UC-B10: Daftar sebagai Penulis (/author/register)
  │    └── Isi pen name, bio, nomor KTP, foto KTP, info rekening bank
  │    └── Status KYC: unverified → pending (setelah submit)
  │
  ├── UC-B11: Melihat status KYC (/author/kyc-status)
  │    └── Menunggu persetujuan admin
  │
  └── UC-B12: Logout
```

### 7.3 Use Case: Penulis (Author — KYC Verified)

```
PENULIS (TERVERIFIKASI)
  │
  ├── UC-A01: Melihat dashboard penulis
  │    └── Statistik: total buku, total royalti, saldo tersedia
  │
  ├── UC-A02: Submit naskah baru
  │    └── Upload PDF manuskrip + cover preview + synopsis + harga usulan
  │    └── Simpan sebagai draft atau langsung submit
  │
  ├── UC-A03: Melihat daftar submission saya
  │    └── Filter berdasarkan status
  │
  ├── UC-A04: Melihat detail submission + feedback editor
  │
  ├── UC-A05: Edit submission (jika masih draft)
  │
  ├── UC-A06: Kirim ulang submission (setelah revisi)
  │    └── Status: revision_requested → submitted
  │
  ├── UC-A07: Hapus submission (jika masih draft)
  │
  ├── UC-A08: Melihat riwayat royalti
  │    └── Per buku terjual: gross, persentase, earning
  │
  ├── UC-A09: Melihat saldo royalti tersedia
  │    └── Sum royalty_ledgers WHERE status='available'
  │
  ├── UC-A10: Request penarikan royalti
  │    └── Minimal Rp 100.000
  │    └── Harus KYC verified + data rekening lengkap
  │    └── FIFO ledger allocation dengan pessimistic locking
  │
  └── UC-A11: Melihat riwayat penarikan
       └── Status: requested, processing, completed, rejected
```

### 7.4 Use Case: Admin

```
ADMIN
  │
  ├── [Manajemen Buku]
  │   ├── UC-AD01: Upload e-book baru (judul, cover, PDF, harga, kategori)
  │   ├── UC-AD02: Lihat daftar semua buku
  │   └── UC-AD03: Toggle publish/unpublish buku
  │
  ├── [Manajemen User]
  │   ├── UC-AD04: Lihat daftar semua user
  │   ├── UC-AD05: Lihat detail profil user (termasuk lisensi & pesanan)
  │   ├── UC-AD06: Aktifkan / Nonaktifkan akun user
  │   ├── UC-AD07: Cabut (revoke) lisensi buku user + alasan
  │   └── UC-AD08: Pulihkan (restore) lisensi yang dicabut
  │
  ├── [KYC Verifikasi Penulis]
  │   ├── UC-AD09: Lihat daftar pengajuan KYC
  │   ├── UC-AD10: Lihat detail KYC (data, foto KTP via secure stream)
  │   ├── UC-AD11: Setujui KYC penulis → status: verified
  │   └── UC-AD12: Tolak KYC penulis + alasan → status: rejected
  │
  ├── [Kurasi Naskah / Curation]
  │   ├── UC-AD13: Lihat antrian naskah (diurutkan: submitted → revision → lainnya)
  │   ├── UC-AD14: Buka detail naskah (status otomatis → in_review)
  │   ├── UC-AD15: Stream baca PDF naskah secara aman
  │   ├── UC-AD16: Request revisi + feedback → status: revision_requested
  │   ├── UC-AD17: Tolak naskah + alasan → status: rejected
  │   └── UC-AD18: Setujui & terbitkan naskah → status: published (jadi Book)
  │        └── Set harga final, file dipindahkan ke private_books/
  │
  ├── [Payout Royalti]
  │   ├── UC-AD19: Lihat semua request penarikan royalti
  │   ├── UC-AD20: Lihat detail request (info bank snapshot, jumlah)
  │   ├── UC-AD21: Proses payout complete (+ nomor ref + bukti transfer)
  │   └── UC-AD22: Tolak payout + alasan → ledger kembali available
  │
  └── [Laporan]
      ├── UC-AD23: Dashboard laporan (overview statistik)
      ├── UC-AD24: Export data orders ke CSV
      └── UC-AD25: Export data buku ke CSV
```

---

## 8. Alur Sistem (Workflow)

### 8.1 Alur Pembelian E-Book (Checkout + Midtrans)

```
[Pembeli] → Klik "Beli" di halaman buku
         → POST /checkout {book_ids: [1,2,...]}
         → Validasi:
              - Buku published?
              - Sudah punya lisensi aktif? → ERROR
         → Hitung total harga
         │
         ├─[Harga = 0]──────────────────────────────────────────┐
         │  → Order status 'success', payment_type 'free'       │
         │  → Issue BookLicense langsung (UUID key)             │
         │  → Dispatch OrderPaidEvent                           │
         │  → Redirect ke /my-library                          │
         └──────────────────────────────────────────────────────┘
         │
         ├─[Harga > 0]─────────────────────────────────────────┐
         │  → Order status 'pending'                           │
         │  → OrderItems dibuat (harga dikunci)                │
         │  → Call Midtrans Snap API → dapat snap_token        │
         │  → Simpan snap_token ke order                       │
         │  → Redirect ke /checkout/{order_id}                 │
         │      → Tampilkan Midtrans Snap popup                │
         │  → Pembeli bayar melalui Midtrans                   │
         │                                                      │
         │  [Midtrans Webhook]                                  │
         │  → POST /api/midtrans/webhook                       │
         │  → Validasi signature SHA512                        │
         │  → Cek replay protection (cache 5 menit)            │
         │  → Dispatch ProcessMidtransWebhook job              │
         │  → Job: update order status                         │
         │  → Jika success: Dispatch OrderPaidEvent            │
         │  → IssueLicenseOnOrderPaid listener:                │
         │      → Issue BookLicense per buku                   │
         │      → Buat RoyaltyLedger per item (70% author)     │
         └──────────────────────────────────────────────────────┘
```

### 8.2 Alur DRM — Baca E-Book

```
[Pembeli] → Klik "Baca" di /my-library
          → GET /reader/{bookId}
          → DrmController::generateReaderUrl():
               1. Cek auth (401 jika tidak login)
               2. Cek BookLicense {user_id, book_id, status=active} (403 jika tidak ada)
               3. Generate Temporary Signed URL:
                  - Route: drm.stream, key: {license_key}
                  - Expire: 15 menit
                  - IP binding: ip={request->ip()}
               4. Return view reader.blade.php dengan streamUrl
          │
          → Browser load reader + iframe src = streamUrl
          │
          → GET /drm/stream/{license_key}?signature=...&ip=...
          → DrmController::streamPdf():
               1. hasValidSignature() → 401 jika gagal / expired
               2. IP match check → 403 jika beda (anti URL hijacking)
               3. Cari BookLicense aktif
               4. Cari file PDF di storage/app/private/
               5. StreamedResponse via fpassthru() (hemat RAM)
          │
          → PDF tampil inline di browser (no download, no cache)
          → Auto-save progress: POST /drm/progress {page: N}
```

### 8.3 Alur Author Registration & KYC

```
[User Login] → GET /author/register
            → Isi form: pen_name, bio, id_card_number, foto KTP, bank info
            → POST /author/register
            → Author record dibuat, kyc_status = 'pending'
            → User diarahkan ke /author/kyc-status (menunggu)
            │
[Admin] → GET /admin/kyc (lihat antrian)
       → GET /admin/kyc/{author} (lihat detail)
            → Stream foto KTP via GET /admin/kyc/{author}/stream-id-card
            → Admin review dokumen
            │
            ├─ Approve → PATCH /admin/kyc/{author}/approve
            │            → kyc_status = 'verified', verified_at = now()
            │            → Penulis bisa akses /author/* routes
            │
            └─ Reject → PATCH /admin/kyc/{author}/reject + alasan
                        → kyc_status = 'rejected', rejection_reason = ...
                        → Penulis bisa daftar ulang (submit revisi data)
```

### 8.4 Alur Submission Naskah (7 Status)

```
[Penulis Verified] → GET /author/submissions/create
                  → Upload: PDF manuskrip, cover preview, judul, synopsis, harga
                  → POST /author/submissions → status: draft

[Penulis] → Edit draft → PUT /author/submissions/{id}
[Penulis] → Submit → status: 'submitted'
          → Notifikasi ke admin

[Admin] → GET /admin/curation (lihat antrian, diurutkan: submitted dulu)
       → GET /admin/curation/{submission}
            → status otomatis berubah: submitted → in_review
            → Admin baca PDF naskah via secure stream
            │
            ├─ Request Revisi → POST /admin/curation/{}/request-revision
            │   → SubmissionReview log dibuat (action: request_revision)
            │   → status: 'revision_requested'
            │   → Penulis bisa edit & resubmit
            │
            ├─ Tolak → POST /admin/curation/{}/reject + alasan
            │   → SubmissionReview log dibuat (action: reject)
            │   → status: 'rejected'
            │
            └─ Setujui & Terbitkan → POST /admin/curation/{}/approve-publish
                → Final_price diisi admin
                → PDF dipindahkan ke private/private_books/ (nama random 40 char)
                → Book record dibuat (is_published=true)
                → Kategori di-sync
                → SubmissionReview log dibuat (action: approve)
                → status submission: 'published', book_id diisi
                → Buku langsung tampil di katalog
```

### 8.5 Alur Royalti & Payout

```
[Setelah Pembayaran Sukses]
→ IssueLicenseOnOrderPaid::handle(OrderPaidEvent $event)
→ Foreach order_items:
    - Hitung royalti: author_earning = price * 70%
    - Buat RoyaltyLedger: status = 'available'

[Penulis] → GET /author/payouts (lihat saldo & riwayat)
          → POST /author/payouts {amount: 150000}
          → PayoutService::requestPayout():
               1. Cek KYC verified
               2. Cek amount >= 100.000
               3. Cek rekening bank lengkap
               4. DB Transaction + LOCK FOR UPDATE (anti race condition)
               5. Cek saldo tersedia >= amount
               6. Buat PayoutRequest (ULID) status: 'requested'
               7. FIFO allocation:
                  - Ambil ledger 'available' dari yang paling lama (asc)
                  - Tandai 'pending' + link ke payout_request_id
                  - Jika ledger partial → split menjadi 2 ledger

[Admin] → GET /admin/payouts (lihat antrian)
       → GET /admin/payouts/{payout} (detail)
            │
            ├─ Complete → POST /admin/payouts/{}/complete
            │   → Upload bukti transfer + nomor referensi
            │   → PayoutService::completePayout():
            │       - Ledger pending → withdrawn
            │       - PayoutRequest status → completed
            │
            └─ Reject → POST /admin/payouts/{}/reject + alasan
                → PayoutService::rejectPayout():
                    - Ledger pending → available (dana kembali)
                    - PayoutRequest status → rejected
```

### 8.6 Alur Laporan (Report)

```
[Admin] → GET /admin/reports
       → Overview dashboard: total buku, total penjualan, total royalti, dll
       → GET /admin/reports/export/orders → Download CSV semua orders
       → GET /admin/reports/export/books  → Download CSV performa per buku
```

---

## 9. Fitur-Fitur Sistem

### 9.1 E-Commerce & Payment

| No | Fitur | Detail |
|----|-------|--------|
| F01 | Katalog buku publik | Filter, browse, pagination |
| F02 | Halaman detail buku | Deskripsi, harga, kategori, ulasan |
| F03 | Multi-book checkout | Beli beberapa buku sekaligus |
| F04 | Free book bypass | Harga 0 → langsung dapat lisensi |
| F05 | Midtrans Snap payment | Credit card, transfer, GoPay, dll |
| F06 | Auto snap-token renewal | Refresh token jika > 50 menit |
| F07 | Webhook payment notification | Async via Queue job |
| F08 | Anti-replay webhook | Cache fingerprint 5 menit |
| F09 | Midtrans signature validation | SHA512 server-side |
| F10 | Anti-repurchase protection | Cek lisensi aktif sebelum checkout |
| F11 | Historical price locking | Harga disimpan di order_items saat beli |
| F12 | Riwayat pesanan | Status tracking per order |

### 9.2 DRM & Reader

| No | Fitur | Detail |
|----|-------|--------|
| F13 | Digital Rights Management | Signed URL + IP binding |
| F14 | Temporary signed URL | Expire 15 menit per sesi baca |
| F15 | IP address binding | Cegah sharing link DRM |
| F16 | Streaming PDF (memory-efficient) | fpassthru() — tidak load ke RAM |
| F17 | Cache disabled pada stream | no-cache, no-store headers |
| F18 | Bookmark halaman | Auto-save last read page |
| F19 | Inline PDF reader | Tampil di browser (tidak download) |
| F20 | Lisensi revocation | Admin bisa cabut akses + alasan |
| F21 | Lisensi restore | Admin bisa pulihkan lisensi yang dicabut |

### 9.3 Author Portal

| No | Fitur | Detail |
|----|-------|--------|
| F22 | Registrasi penulis | Form KYC: pen name, KTP, rekening bank |
| F23 | Enkripsi nomor KTP | `id_card_number` encrypted di DB |
| F24 | Upload foto KTP | Secure, private storage |
| F25 | Status KYC tracking | unverified → pending → verified/rejected |
| F26 | Dashboard penulis | Statistik buku, royalti, payout |
| F27 | Submit naskah (manuskrip) | PDF + cover + synopsis + harga usulan |
| F28 | CRUD submission | Create, read, update, delete (draft only) |
| F29 | Status tracking submission | 7 tahap editorial workflow |
| F30 | Feedback editor | Log review per submission visible ke penulis |
| F31 | Request penarikan royalti | Minimum Rp 100.000 |
| F32 | Saldo royalti real-time | Kalkulasi dari ledger 'available' |
| F33 | Riwayat payout | Status per request |
| F34 | Snapshot rekening bank | Data rekening disimpan saat request payout |

### 9.4 Admin Panel

| No | Fitur | Detail |
|----|-------|--------|
| F35 | Upload buku (admin) | PDF + cover + metadata |
| F36 | Toggle publish | Aktifkan/nonaktifkan buku dari katalog |
| F37 | Manajemen user | Lihat profil, lisensi, pesanan |
| F38 | Aktifkan/nonaktifkan akun | Blokir user bermasalah |
| F39 | Revoke & restore lisensi | Kontrol penuh atas akses buku |
| F40 | Antrian KYC | Verifikasi penulis baru |
| F41 | Stream foto KTP admin | Akses secure dokumen identitas |
| F42 | Approve/reject KYC | Dengan alasan penolakan |
| F43 | Antrian kurasi naskah | Diurutkan prioritas |
| F44 | Stream naskah PDF | Baca manuskrip secara aman |
| F45 | Request revisi naskah | Feedback ke penulis |
| F46 | Tolak naskah | Dengan alasan |
| F47 | Setujui & terbitkan naskah | Set harga final → buku langsung live |
| F48 | Antrian payout royalti | Lihat request penarikan |
| F49 | Complete payout | Upload bukti transfer + nomor ref |
| F50 | Reject payout | Dana kembali ke saldo penulis |
| F51 | Dashboard laporan | Statistik platform |
| F52 | Export CSV orders | Download data transaksi |
| F53 | Export CSV books | Download performa buku |

### 9.5 Keamanan & Sistem

| No | Fitur | Detail |
|----|-------|--------|
| F54 | CSRF protection | Laravel built-in |
| F55 | Rate limiting checkout | throttle: 10 request/menit |
| F56 | Middleware RBAC | 5 middleware guard berlapis |
| F57 | Account activation guard | `is_active` check |
| F58 | DB Transaction checkout | Rollback jika ada error |
| F59 | Pessimistic locking payout | `lockForUpdate()` anti race condition |
| F60 | FIFO royalty allocation | Ledger tertua dialokasikan pertama |
| F61 | Partial ledger splitting | Payout tidak perlu exact match |
| F62 | ULID primary key (payout) | ID tersortir secara waktu |
| F63 | Auto-slug generator | Unique slug buku |
| F64 | Queue job (webhook) | Async processing Midtrans |

**Total: 64 fitur teridentifikasi**

---

## 10. Sistem Keamanan & DRM

### 10.1 Lapisan Keamanan DRM

```
LAYER 1: Authentication
  → Harus login untuk akses reader
  → Tidak ada fallback ke user ID 1 (BUG-01 FIX)

LAYER 2: License Check
  → Cek book_licenses {user_id, book_id, status='active'}
  → Tidak ada lisensi = 403 Forbidden

LAYER 3: Signed URL
  → URL temporary + signature HMAC
  → Expire setelah 15 menit
  → Tidak bisa diperpanjang manual

LAYER 4: IP Binding
  → IP pembuat URL disematkan di parameter
  → Jika IP berubah = 403 (BUG-06 FIX)
  → Cegah forward/sharing URL

LAYER 5: Response Headers
  → Cache-Control: no-cache, no-store, must-revalidate
  → X-Accel-Buffering: no (disable nginx buffer)
  → Content-Disposition: inline (bukan attachment)

LAYER 6: Memory-Efficient Streaming
  → fpassthru() vs response()->file()
  → PHP tidak muat seluruh PDF ke RAM (BUG-05 FIX)
```

### 10.2 Keamanan Webhook Midtrans

```
LAYER 1: IP Whitelist (CheckMidtransIp middleware)
  → Hanya terima dari IP server Midtrans

LAYER 2: Signature Validation
  → SHA512(order_id + status_code + gross_amount + server_key)
  → Tolak jika tidak cocok

LAYER 3: Replay Protection (BUG-04 FIX)
  → Cache fingerprint SHA256 seluruh body
  → Ignore duplicate webhook dalam 5 menit
```

### 10.3 Keamanan Data Penulis

```
Nomor KTP (id_card_number):
  → Dienkripsi menggunakan Laravel Encrypted Cast
  → Tersimpan ciphertext di database
  → Hanya dapat didekripsi oleh aplikasi dengan APP_KEY

Foto KTP (id_card_path):
  → Disimpan di private storage (bukan public/)
  → Hanya diakses via route admin yang terautentikasi
  → Tidak ada URL publik

PDF Naskah (manuscript_path):
  → Disimpan di private storage
  → Hanya diakses via admin route yang terautentikasi

PDF Buku Terbit (file_path):
  → Disimpan di private/private_books/
  → Nama file random 40 karakter (tidak predictable)
  → Hanya diakses melalui DRM signed URL
```

---

## 11. Analisis Kualitas Kode

### 11.1 Kekuatan Arsitektur

| Aspek | Nilai | Keterangan |
|-------|-------|------------|
| **DRM Multi-Layer** | ⭐⭐⭐⭐⭐ | 6 lapisan perlindungan PDF |
| **Royalty Engine FIFO** | ⭐⭐⭐⭐⭐ | Pessimistic locking, partial split |
| **Curation Workflow** | ⭐⭐⭐⭐⭐ | 7 status, audit trail lengkap |
| **KYC Encryption** | ⭐⭐⭐⭐⭐ | Nomor KTP dienkripsi otomatis |
| **Service Layer** | ⭐⭐⭐⭐ | PayoutService terisolasi dari controller |
| **Event-Listener** | ⭐⭐⭐⭐ | OrderPaid → IssueLicense decoupled |
| **Bug Fixes Documented** | ⭐⭐⭐⭐⭐ | BUG-01 s/d BUG-06 terdokumentasi dalam kode |
| **Testing** | ⭐⭐⭐⭐ | PHPUnit tests tersedia |
| **Middleware RBAC** | ⭐⭐⭐⭐ | 5 middleware berlapis |
| **Historical Price Lock** | ⭐⭐⭐⭐⭐ | Harga di OrderItem dikunci saat transaksi |

### 11.2 Bug Fixes yang Terdokumentasi

| Bug ID | Masalah | Solusi |
|--------|---------|--------|
| **BUG-01** | Fallback user ID ke `1` jika tidak login → akses buku siapapun | Hapus fallback, langsung 401 |
| **BUG-04** | Webhook replay — Midtrans kirim webhook duplikat, order di-update 2x | Cache fingerprint 5 menit |
| **BUG-05** | `response()->file()` load seluruh PDF ke RAM PHP | Ganti ke `fpassthru()` streaming |
| **BUG-06** | Signed URL bisa di-forward ke user lain | IP binding di URL + validasi |
| **BVA-02** | Buku gratis (price=0) tetap dipanggil ke Midtrans | Bypass jika grossAmount == 0 |
| **TC-07** | Order sudah paid tapi halaman checkout masih tampil "Bayar" | Redirect ke library jika status success |
| **TC-10** | Snap token expired setelah 1 jam, payment popup error | Auto-renew token jika > 50 menit |
| **BUG-03** | User bisa beli buku yang sudah dimiliki | Cek lisensi aktif sebelum checkout |

---

## 12. Temuan & Rekomendasi

### 12.1 ⚠️ Isu Teknis

| No | Isu | Prioritas | Detail |
|----|-----|-----------|--------|
| I01 | **Midtrans masih Sandbox** | 🔴 Prod | `MIDTRANS_IS_PRODUCTION=false` — belum live |
| I02 | **Queue masih Sync** | 🟡 Sedang | `QUEUE_CONNECTION=sync` — webhook diproses sinkron di dev |
| I03 | **Mail masih Log** | 🟡 Sedang | `MAIL_MAILER=log` — email tidak benar-benar terkirim |
| I04 | **BCRYPT_ROUNDS=12** | 🟢 Rendah | Aman, tapi mungkin lambat di server rendah |
| I05 | **Session driver Database** | 🟢 Rendah | Perlu maintenance table sessions agar tidak membengkak |
| I06 | **ngrok.exe di root folder** | 🟡 Sedang | File binary 33MB tidak perlu ada di repo production |
| I07 | **AuthorDashboardController tidak di list_dir** | 🟡 Sedang | Controller direferensikan di routes tapi tidak terlist — perlu dicek |
| I08 | **FIFO split ledger belum sempurna** | 🟡 Sedang | Komentar dalam kode mengakui logika partial split bisa diperbaiki |

### 12.2 💡 Rekomendasi Pengembangan

| No | Saran | Keterangan |
|----|-------|------------|
| R01 | **Aktifkan Midtrans Production** | Ganti `MIDTRANS_IS_PRODUCTION=true` + server key prod |
| R02 | **Aktifkan Queue Worker** | Ganti ke `database` atau `redis` queue di production |
| R03 | **Konfigurasi Email SMTP** | Gunakan SMTP (Mailgun/SES/Resend) agar notifikasi email terkirim |
| R04 | **Notifikasi email penulis** | Email saat KYC disetujui/ditolak, submission disetujui/ditolak, payout selesai |
| R05 | **Watermark pada PDF** | Tambahkan email/nama user sebagai watermark per-session baca |
| R06 | **Search & filter katalog** | Full-text search, filter kategori, sort harga |
| R07 | **Rating summary di detail buku** | Rata-rata bintang dari reviews |
| R08 | **Admin bulk actions** | Bulk approve KYC, bulk publish buku |
| R09 | **Soft delete** | Gunakan SoftDeletes untuk orders, submissions (bukan hapus permanen) |
| R10 | **Halaman kebijakan royalti** | Tampilkan 70/30 split secara transparan ke penulis |
| R11 | **HTTPS & CDN** | Pastikan produksi pakai HTTPS + CDN untuk cover image |
| R12 | **Rate limiting lebih granular** | Tambahkan throttle ke login, register, submission |

### 12.3 Cara Menjalankan Sistem

```bash
# 1. Setup awal (via composer script)
composer run setup

# 2. Development — jalankan semua serentak (via concurrently)
composer run dev
# Menjalankan serentak:
# - php artisan serve       (web server :8000)
# - php artisan queue:listen (queue worker)
# - php artisan pail        (real-time log viewer)
# - npm run dev             (Vite dev server)

# 3. Testing
composer run test

# 4. Webhook testing lokal (dengan ngrok)
./ngrok.exe http 8000
# Copy URL ngrok → set sebagai Webhook URL di dashboard Midtrans Sandbox
```

---

## Ringkasan Final

| Dimensi | Detail |
|---------|--------|
| **Nama Sistem** | P4I Publisher Ebook |
| **Jenis** | E-Book Marketplace dengan Author Portal & DRM |
| **Framework** | Laravel 12 / PHP 8.2 |
| **Database** | MySQL (prod) / SQLite (dev) — 14 tabel domain |
| **Payment** | Midtrans Snap (Sandbox, belum production) |
| **Queue** | Sync (dev) — perlu database queue di prod |
| **Total Migrasi** | 25 file migrasi |
| **Total Model** | 12 model Eloquent |
| **Total Controllers** | 11 controller (6 admin, 3 author, 2 umum) |
| **Total Middleware** | 5 middleware guard |
| **Total Services** | 2 service (PayoutService, OrderIdGenerator) |
| **Total Routes** | ~40 route |
| **Total Fitur** | 64 fitur teridentifikasi |
| **Royalti Penulis** | 70% per penjualan |
| **Min. Payout** | Rp 100.000 |
| **DRM** | Signed URL (15 menit) + IP binding + fpassthru |
| **Bug Fixes** | 8 bug terdokumentasi & diselesaikan |
| **Testing** | PHPUnit tersedia |
| **Status** | Development/Staging — siap uji menuju production |
