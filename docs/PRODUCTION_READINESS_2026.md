# Kesiapan produksi dan prosedur perubahan

**NO-GO deployment/pengaktifan fitur saat laporan dibuat.** PR hanya menyiapkan source; tidak merge/deploy/migrate/seed/change credentials/cron live.

Basis master `44053817`; Hotfix4 mencatat visual acceptance PASS, Hotfix5 mencatat pemulihan administrator (source `8fc6c306`). PHP 8.2.33/MariaDB 11.8.9 disebut laporan lama, belum diperiksa live pada pekerjaan ini. Catatan terakhir queue/scheduler aktif, status sekarang masih perlu verifikasi operator.

## Gate sebelum perubahan produksi

1. Pemilik menutup/menyetujui rencana insiden repository: asal akun, reset/invalidasi sesi, bukti rotasi password sementara admin, penanganan history/caches/forks. PR draft karena P0/P1 manual tersebut belum ditutup.
2. Reviewer menyetujui code/migration; CI pada SHA yang akan dipakai lulus. Simpan SHA dan artifact release. Tidak mengandalkan tes dari SHA lama.
3. Staging privat memakai snapshot produksi yang disetujui atau data anonim yang mewakili relasi historis, PHP/DB/ekstensi/collation/SQL mode dan hosting permissions setara. Jangan memasukkan snapshot ke Git atau staging publik.
4. Rehearsal historical preflight/migrations/backfill/rollback, positive constraints/triggers/FKs dan full suite di staging lulus. Bila historical split/reconciliation gagal, jangan menghapus constraint atau menebak perbaikannya.
5. Uji manual admin/author/reader/publishing/submission/file/pdf/payment dengan fitur yang memang diizinkan; uji sandbox gateway/SMTP jika kelak akan diaktifkan, tanpa mengaktifkannya di produksi otomatis.
6. Konfirmasi shared-database queue, worker timeout/retry_after, failed jobs, cron scheduler, root disk dan private uploads. Pastikan cron sudah menjalankan schedule:run agar outbox baru bisa dipulihkan. Verifikasi ini dilakukan operator; tidak mengubah cron live dalam tugas ini.
7. Setujui change window, maintenance, backup/restore rehearsal dan rollback/forward-fix; tetapkan siapa berwenang membatalkan rilis.

## Backup sebelum approved deployment

Simpan database konsisten, private/public uploads, existing release, konfigurasi `.env`, APP_KEY dan daftar cron/queue ke backup privat terenkripsi dengan checksums dan restore rehearsal. Jangan menaruh backup di webroot, Git, atau artifact publik. Tahan write transaksi dan jalankan maintenance sesuai mekanisme hosting agar schema dan data tidak berubah selama preflight. Jangan meregenerasi APP_KEY, menjalankan seeder, atau memakai dump development sebagai restore produksi.

## Runbook operator — hanya setelah persetujuan eksplisit

1. Pada staging/CI, build release dari approved SHA: `composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader`, `npm ci`, `npm run build`. Sertakan hasil `public/build`; local testing menggunakan output terpisah agar build lama/preexisting `public/hot` tidak ditimpa. Jangan menyertakan `.env`, SQL/ZIP backup, hot file atau test database.
2. Periksa `composer check-platform-reqs` dengan PHP Hostinger sebenarnya. Pertahankan kredensial dan APP_KEY existing. Document root harus `public`, debug false, HTTPS/session secure sesuai hosting. Tidak ada folder private/storage/.env dapat diakses langsung melalui web server.
3. Pertahankan `MIDTRANS_ENABLED=false`, `ROYALTY_ENABLED=false`, `PAYOUT_ENABLED=false`, `AUTHOR_KYC_ENABLED=false`. Test-only flag true bukan setting produksi. Jangan memanggil gateway/bank dari tahap ini.
4. Masuk maintenance dan hentikan write/worker sesuai approved plan; ambil backup terakhir. Tinjau historical reconciliation secara privat sebelum DDL. Jalankan tiga migration **hanya setelah staging sukses dan izin produksi**; MariaDB DDL auto-commit berarti failure parsial harus diinspeksi, bukan retry buta.
5. Verifikasi tables/indexes/check constraints/immutable triggers serta agregat saldo sebelum/sesudah. Snapshot legacy ledger tidak berubah. Jika preflight gagal, batalkan deployment dan rekonsiliasi manual di staging; jangan memperbaiki data live spontan.
6. Backfill encryption bank optional terpisah: setelah schema TEXT, key backup dan owner approval, jalankan `php artisan security:encrypt-financial-data --approved-production`. Simpan hanya jumlah record. Jangan menjalankan `key:generate`; jangan menonaktifkan cast untuk memperbaiki error ciphertext tanpa investigasi.
7. Pada release yang benar, kelola config/route/view cache sesuai hosting. Laravel local `config:cache`, `route:cache`, `view:cache` diuji; hindari cache terbangun dengan environment test. `storage:link` hanya mempublikasikan `storage/app/public`, tidak disk private. Jangan chmod private file menjadi world-readable.
8. Restart/reload worker memakai prosedur Hostinger yang disetujui; connection database queue harus sama dengan order/outbox. Pastikan timeout worker kurang dari retry_after dan failed jobs terpantau. Scheduler `payments:dispatch-events` setiap menit tanpa overlap; tidak perlu mengubah cron bila existing schedule:run sudah benar.
9. Smoke HTTPS publik/login/admin/author, PDF publik/terproteksi, upload privat dan manual payment; pastikan tidak ada 500. Verifikasi unauthorized403/404, expiration, debug/log tidak membocorkan nilai, scheduler/queue sehat, feature flags tetap false. Pemilik menilai hasil sebelum maintenance dibuka.
10. Pantau error/outbox/jobs/financial aggregates tanpa PII. Pengaktifan fitur finansial adalah change request terpisah setelah sandbox/live review dan rekening/consent/operator readiness terpenuhi.

## Rollback yang menjaga data

Clean down/up ketiga migration lulus di database test. Setelah new payout/audit/ciphertext/outbox/confirmation writes, migration down **sengaja menolak** kehilangan data. Jangan menyebut downgrade selalu aman hanya karena method down ada.

Sebelum new writes dan setelah rehearsal, gunakan prosedur rollback release/schema yang disetujui. Sesudah new writes, hentikan write, ambil snapshot tambahan dan pilih forward fix atau recovery snapshot dengan rekonsiliasi delta transaksi; restore snapshot lama tanpa mempertahankan delta dapat kehilangan pembayaran. Source lama tidak memahami allocations, sehingga mengganti kode lama saja sesudah partial payout baru juga tidak aman. Jangan menonaktifkan trigger/drop tabel untuk memaksa rollback.

## Status keputusan

GO review; NO-GO auto-merge; NO-GO deployment sekarang. Gate yang belum terbukti ada di [REMAINING_ISSUES](REMAINING_ISSUES.md). Persetujuan menjalankan tugas source/PR ini bukan persetujuan untuk mengubah server produksi.
