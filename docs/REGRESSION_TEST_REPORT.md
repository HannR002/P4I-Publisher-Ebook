# Laporan regression testing

Tanggal 11 Oktober 2026, source branch perbaikan dari `44053817`. Semua data test sintetis; tidak menggunakan dump yang terpapar atau koneksi produksi.

## Hasil terverifikasi

| Pemeriksaan | Hasil |
| --- | --- |
| Baseline master, PHP 8.4 | 128 tests / 427 assertions lulus sebelum perubahan |
| PHP 8.2.34 + SQLite in-memory | 177 lulus, 580 assertions, 4 skipped khusus konkurensi MySQL |
| PHP 8.2.34 + MariaDB 11.8.6 | 181 lulus, 621 assertions, termasuk empat tes multi-process locking |
| Scanner unit tests Python | 4 lulus |
| Composer validate strict / platform requirements | Lulus; dependency resolution ditargetkan PHP 8.2.33 |
| Composer audit | 0 advisory pada versi lockfile yang diperiksa |
| npm audit | 0 vulnerability pada lockfile yang diperiksa |
| PHP lint dan Laravel Pint | 59 berkas PHP baru/diubah lulus |
| Vite production build | Lulus dengan Tailwind 4.3.3 dan plugin Tailwind Vite |
| Browser Chromium | 34 checks lulus; desktop 1440 dan mobile 390; aset hasil build, public/reader/author/admin, akses admin reader 403, tanpa overflow halaman |
| Migration/constraint | Fresh migration, historical backfill, clean down/up ketiga migration, guard rollback, unique/FK/positive amount dan immutable triggers teruji |
| Routes/config/scheduler | 108 routes terdaftar; config/route/view cache, migration status, schedule:list dan recovery command lulus pada environment test; cache sementara dibersihkan |
| Queue | Database queue worker menghabiskan dua jobs tanpa failed jobs, satu email, satu ledger; failure/retry/rollback outbox teruji |

Evidence agregat: [verification.json](security-verification/verification.json), [browser-smoke.json](security-verification/browser-smoke.json). JUnit dan log lokal hanya disimpan di evidence privat workspace; CI menyediakan bukti ulang pada commit PR. Status CI tidak diasumsikan berdasarkan tes lokal; lihat checks PR.

## Cakupan tes baru

`PayoutIntegrityTest`: full/partial/FIFO, insufficient funds, duplicate key/key mismatch, completion/rejection/repeated admin action/invalid transition, non-admin, feature disabled, exact reconciliation, audit, transaction failure, money edge cases, database constraints dan model/SQL immutability.

`PayoutMigrationTest`: data historical full payout tetap identik, mismatch abort sebelum DDL, rollback menolak data finansial baru, semua migration baru down/up pada database kosong.

`MysqlConcurrencyTest`: dua proses dengan koneksi DB terpisah dan start barrier; competing requests tidak double-spend, duplicate request menghasilkan ID yang sama, conflicting admin actions satu pemenang, concurrent webhook satu lisensi/outbox. Ini uji contention nyata, bukan request serial atau SQLite approximation.

`PaymentIntegrityTest/PaymentDeliveryTest`: malformed/no-key/bad signature, nominal mismatch rollback, fraud challenge, paid/unpaid event, repeated callbacks, terminal state, rounding 70%, auto-discovery tanpa listener ganda, delivery mail retry, outbox rollback, database worker.

`SecurityHardeningTest` dan existing suite: ownership DRM walau IP sama, revoked progress, signed expiry, unpublished access, KYC/manuscript admin/correct storage, bank encryption/backfill, account retention, auth/registration escalation rejection, production seeder refusal, password/session invalidation, manual payment, public library, dashboards dan fitur rescope.

## Reproduksi aman

Install dependency dari lockfile, lalu jalankan pada workspace test dengan PHP 8.2 dan ekstensi PDO SQLite/MySQL, XML, cURL, fileinfo, gd/zip yang relevan. Jangan memakai `.env` produksi. `tests/TestCase.php` memeriksa environment **testing** dan effective config sebelum trait migration: SQLite hanya `:memory:`, MySQL hanya nama berawalan `p4i_security_test`, tanpa DB_URL. Cache config lama harus dibersihkan pada workspace test.

```sh
composer install --no-interaction --prefer-dist
composer validate --strict
composer check-platform-reqs
composer audit
php artisan test --compact
# Jalankan di MariaDB test terisolasi yang sudah dibuat operator:
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_DATABASE=p4i_security_test P4I_MYSQL_CONCURRENCY=1 php artisan test --compact
python3 -m unittest discover -s tests/security -v
python3 scripts/check_repository_security.py
npm ci
npm audit
npm run build
```

Credentials test disediakan melalui environment lokal, tidak ditulis di laporan. Nama DB test guard bukan izin menghubungi DB produksi. Tes migration/concurrency menggunakan migrate:fresh/db:wipe pada DB test; jangan menonaktifkan guard untuk menjalankannya.

Browser smoke: `tests/Browser/smoke.py` membutuhkan Playwright Python dan Chromium serta server **loopback** dengan fixture sintetis reader/author/admin `example.test`, password test sintetis dan publikasi slug `smoke-publication`. Database fixture harus terpisah; proses smoke tidak membuat pengguna pada server produksi. Set `P4I_SMOKE_URL` dan `P4I_SMOKE_OUTPUT` bila diperlukan. Semua request eksternal diblokir; production bundle digunakan, bukan Vite dev server.

## Batas validasi

PHP produksi yang dilaporkan 8.2.33 dan MariaDB 11.8.9 belum direplikasi persis; lokal PHP 8.2.34/MariaDB 11.8.6 membuktikan jalur PHP 8.2 dan semantics InnoDB pada minor terkait. Rehearsal exact hosting/schema/permissions/traffic tetap diperlukan. Tidak diuji gateway Midtrans sandbox/live, SMTP live, transfer bank, cron produksi, load endurance, seluruh browser lama atau setiap kombinasi konten riil. Chromium smoke bukan audit aksesibilitas penuh. Tailwind 4 memerlukan browser modern; review browser dukungan organisasi sebelum deploy.
