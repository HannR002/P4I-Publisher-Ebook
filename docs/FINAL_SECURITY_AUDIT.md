# Audit akhir keamanan P4I Publisher Ebook

Tanggal: 11 Oktober 2026. Repository: `HannR002/P4I-Publisher-Ebook`.
Basis pemeriksaan: `master` commit `44053817e821d22b0426cc7fa5e5fc77425912ae`.
Branch perbaikan: `fix/publisher-security-production-readiness`.

**Hasil: implementasi dan tes siap ditinjau; NO-GO deployment dan pengaktifan fitur finansial.** Paparan akun dalam riwayat Git dan verifikasi rotasi administrator masih terbuka. Tidak ada akses, migration, seeding, perubahan kredensial, deployment, atau perubahan cron produksi.

## Metode dan batas pemeriksaan

Pemeriksaan source Laravel, controller/routes/models, migration, dependency lockfiles, tests, dokumen `docs/audit/` dan `docs/deployment/`, riwayat Hotfix4/5, konfigurasi flag/queue/storage, serta berkas terlacak dan arsip. Validasi dilakukan menggunakan database sintetis terisolasi, PHP 8.2, SQLite, MariaDB, dan browser Chromium desktop/mobile. Ini bukan penetration test produksi, bukan bukti bahwa database historis produksi bersih, dan bukan uji integrasi langsung Midtrans/SMTP/Hostinger.

Temuan lama diverifikasi kembali: pendaftaran manual listener sudah dihapus dari `AppServiceProvider`, auto-discovery menghasilkan tepat dua listener; masalah dashboard author lama juga sudah diperbaiki pada basis master. Keduanya tidak diklaim sebagai bug baru. Hotfix4 mencatat acceptance PASS; Hotfix5 mencatat pemulihan admin dan password sementara. Catatan tersebut tidak membuktikan rotasi password sekarang. Catatan queue/scheduler terakhir menyatakan aktif, tetapi keadaan server saat ini belum diverifikasi.

## Temuan dan tindak lanjut

| ID | Prioritas | Bukti/root cause | Implementasi/status |
| --- | --- | --- | --- |
| SEC-01 | P0 | Dump `p4i_publisher.sql` terlacak, berisi INSERT akun/sesi/transaksi dan bcrypt hash | Salinan privat dibatasi aksesnya, dump dihapus dari branch, ignore/scanner/CI ditambahkan. **Riwayat masih mengandung data; remediasi pemilik wajib.** |
| SEC-02 | P0 | Partial payout mereplikasi ledger dengan `order_item_id` unique dan mengubah pendapatan awal | Alokasi terpisah, ledger immutable, uang fixed point, author-first locking, idempotency, audit, state guard dan tes konkurensi. Unique order item tetap berlaku. |
| SEC-03 | P1 | Password sementara admin disebut dalam deployment, status rotasi tidak terbukti | Seeder produksi dilarang, development membutuhkan input eksplisit dan tidak menimpa akun. Verifikasi/rotasi live tetap manual dengan persetujuan. |
| SEC-04 | P1 | Webhook tidak cukup memvalidasi nominal/status; dedup sebelum enqueue dapat kehilangan retry | Signature constant-time, schema ketat, konfigurasi key wajib, nominal persis, fraud/status check, terminal state guard, row lock dan outbox durable. |
| SEC-05 | P1 | Listener/retry dan kegagalan mail dapat menyebabkan delivery tidak konsisten | Royalti berdasarkan order item unique dan order lock; outbox satu per order; marker konfirmasi dan retry queue. Batas SMTP dijelaskan dalam laporan payment. |
| SEC-06 | P1 | Signed URL DRM hanya terikat IP, bukan pemilik; akses konten belum terbit/progress revoked tidak ditolak penuh | Pemilik lisensi wajib, lisensi aktif, published-only pada katalog/read/download/purchase, private/no-store dan nosniff. |
| SEC-07 | P1 | Stream KYC/manuskrip menggunakan root storage yang berbeda; rekening plaintext dan kolom terlalu pendek untuk ciphertext | Stream root disk lokal yang benar, admin gate, respons private; cast enkripsi bank baru dan migration TEXT. Backfill lama tersedia sebagai command, tidak dijalankan produksi. |
| SEC-08 | P1 | Advisory Laravel/CommonMark dan dependency frontend | Laravel 12.69.3, CommonMark 2.10.3, patch dependency, Tailwind 4.3.3. Audit Composer/npm: 0 advisory pada waktu pemeriksaan. |
| SEC-09 | P1 | Proof nominal/state tidak diperiksa ulang dengan lock; account delete berpotensi menghapus riwayat finansial | Nominal decimal presisi, state recheck transaction, proof cleanup, verification guard, akun dengan author/order/admin tidak bisa self-delete. |
| SEC-10 | P2 | Sesi lama tetap dapat dipakai setelah perubahan/reset password | AuthenticateSession ditambahkan; revoke sesi database lain pada perubahan password dan seluruh sesi pada reset. |
| SEC-11 | P2 | Detail exception/query URL berpotensi masuk output/log; rate limit belum lengkap | Output checkout generik, log kelas exception, path tanpa query pada admin-denial; throttle registrasi/reset/proof/payout. |
| SEC-12 | P2 | Form pencarian home dan filter admin overflow mobile | Min-width input diperbaiki dan form admin responsif; 34 browser checks lulus. |
| SEC-13 | P2 | Arsip aplikasi lama 28 MB masih terlacak dengan source usang/nested archive | Backup privat diverifikasi, arsip dihapus dari branch dan scanner arsip ditambahkan. Tidak ada klaim bahwa arsip tersebut terbukti memuat data akun asli. |

Auth/RBAC menggunakan middleware admin/author/active, ownership dan route model checks. CSRF tetap aktif pada web flow; hanya webhook gateway memakai pengecualian yang memang membutuhkan signature. Query pencarian memakai parameter binding; output Blade biasa escaped. Upload existing memakai validator MIME/size dan private disk untuk KYC/manuskrip/proof. Tidak ditemukan bukti eksploit SQL injection/XSS tambahan dalam scope source yang diperiksa; ini tidak membuktikan ketiadaan semua kerentanan.

## Ringkasan implementasi

Tiga migration baru menambah alokasi/audit payout, memperluas kolom ciphertext, dan menambah outbox/marker email. Semua default feature flags tetap `false`; konfigurasi test saja memakai fitur aktif. Payout admin hanya mencatat penyelesaian transfer manual, tidak memanggil bank atau memindahkan uang.

[Payout dan migration](ROYALTY_PAYOUT_FIX.md), [remediasi paparan](DATABASE_EXPOSURE_REMEDIATION.md), [tes](REGRESSION_TEST_REPORT.md), [prosedur produksi](PRODUCTION_READINESS_2026.md), dan [isu terbuka](REMAINING_ISSUES.md) merupakan bagian laporan ini.

## Keputusan

- GO untuk review source/PR draft.
- NO-GO merge otomatis; review wajib, CI harus lulus, dan pemilik menyetujui penanganan SEC-01/SEC-03 beserta perubahan schema.
- NO-GO deployment atau mengaktifkan Midtrans/royalty/payout/KYC sebelum seluruh gate staging dan keamanan akun terpenuhi.

## Daftar berkas perubahan

Daftar di bagian akhir dihasilkan dari diff terhadap basis master; berkas toolchain lokal, `.env`, database test, salinan bukti privat, vendor/node_modules, serta `public/hot` lama tidak dimasukkan.

Total 81 paths (termasuk dua penghapusan).

- `.github/workflows/security-regression.yml`
- `.gitignore`
- `app/Casts/EncryptedFinancialString.php`
- `app/Console/Commands/DispatchPaidOrderEvents.php`
- `app/Console/Commands/EncryptLegacyFinancialData.php`
- `app/Http/Controllers/Admin/AdminPayoutController.php`
- `app/Http/Controllers/Admin/AuthorKycController.php`
- `app/Http/Controllers/Admin/ManuscriptCurationController.php`
- `app/Http/Controllers/Admin/PaymentVerificationController.php`
- `app/Http/Controllers/Auth/NewPasswordController.php`
- `app/Http/Controllers/Auth/PasswordController.php`
- `app/Http/Controllers/Author/AuthorPayoutController.php`
- `app/Http/Controllers/CheckoutController.php`
- `app/Http/Controllers/DrmController.php`
- `app/Http/Controllers/LibraryCatalogController.php`
- `app/Http/Controllers/ManualPaymentController.php`
- `app/Http/Controllers/MidtransWebhookController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Middleware/EnsureUserIsAdmin.php`
- `app/Http/Requests/Author/StorePayoutRequest.php`
- `app/Jobs/ProcessMidtransWebhook.php`
- `app/Listeners/RecordAuthorRoyaltyListener.php`
- `app/Listeners/SendPurchaseConfirmationListener.php`
- `app/Models/Author.php`
- `app/Models/Order.php`
- `app/Models/PayoutAllocation.php`
- `app/Models/PayoutAudit.php`
- `app/Models/PayoutRequest.php`
- `app/Models/RoyaltyLedger.php`
- `app/Services/OrderPaidOutbox.php`
- `app/Services/PayoutService.php`
- `app/Services/RoyaltyBalanceService.php`
- `app/Services/SessionRevocationService.php`
- `app/Support/Money.php`
- `bootstrap/app.php`
- `composer.json`
- `composer.lock`
- `database/migrations/2026_10_11_000001_create_payout_allocations.php`
- `database/migrations/2026_10_11_000002_expand_encrypted_financial_columns.php`
- `database/migrations/2026_10_11_000003_create_order_paid_outbox.php`
- `database/seeders/AdminSeeder.php`
- `docs/DATABASE_EXPOSURE_REMEDIATION.md`
- `docs/FINAL_SECURITY_AUDIT.md`
- `docs/PRODUCTION_READINESS_2026.md`
- `docs/REGRESSION_TEST_REPORT.md`
- `docs/REMAINING_ISSUES.md`
- `docs/ROYALTY_PAYOUT_FIX.md`
- `docs/security-verification/browser-smoke.json`
- `docs/security-verification/verification.json`
- `p4i_publisher.sql` — dihapus
- `package-lock.json`
- `package.json`
- `postcss.config.js`
- `resources/css/app.css`
- `resources/views/admin/library/index.blade.php`
- `resources/views/admin/payouts/show.blade.php`
- `resources/views/author/payouts/index.blade.php`
- `resources/views/welcome.blade.php`
- `routes/auth.php`
- `routes/console.php`
- `routes/web.php`
- `scripts/check_repository_security.py`
- `tests/Browser/smoke.py`
- `tests/Feature/AuthorPayoutTest.php`
- `tests/Feature/EbookBvaTest.php`
- `tests/Feature/FeatureFlagsRescopeTest.php`
- `tests/Feature/MysqlConcurrencyTest.php`
- `tests/Feature/PaymentDeliveryTest.php`
- `tests/Feature/PaymentIntegrityTest.php`
- `tests/Feature/PayoutIntegrityTest.php`
- `tests/Feature/PayoutMigrationTest.php`
- `tests/Feature/PurchaseNotificationTest.php`
- `tests/Feature/SecurityHardeningTest.php`
- `tests/Feature/UiHotfixTest.php`
- `tests/Support/FinancialFixtures.php`
- `tests/Support/RebuildsIsolatedDatabase.php`
- `tests/TestCase.php`
- `tests/Unit/AuthorArchitectureTest.php`
- `tests/security/test_repository_security.py`
- `tidak bole di zip#publisher ebook/p4i_core_ready.zip` — dihapus
- `vite.config.js`
