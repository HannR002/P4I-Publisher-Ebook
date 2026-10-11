# Isu dan tindakan manual tersisa

| Prioritas | Isu/gate | Pemilik/tindakan |
| --- | --- | --- |
| P0 | Data akun/sesi masih berada pada history/master/refs lama meski branch fix menghapus file | Pemilik repository mengonfirmasi asal akun, mengoordinasi rotasi/invalidasi sesi dan menyetujui history remediation; GitHub Support/fork owners jika relevan. Tidak dilakukan rewrite/force push. |
| P1 | Status rotasi password sementara admin Hotfix5 tidak dapat dibuktikan dari source | Operator produksi memverifikasi bukti atau melakukan reset/invalidasi melalui persetujuan eksplisit; jangan memakai seeder. |
| P1 | Historical data produksi belum diuji pada preflight baru | Staging privat dengan data/engine setara; review setiap mismatch gross/payout/link, tanpa repair otomatis. Trigger creation privilege dan DDL recovery juga harus diverifikasi Hostinger. |
| P1 | Backfill rekening lama belum dilakukan live | Operator mengambil backup data/APP_KEY, menjalankan command hanya setelah approval, memeriksa hasil agregat dan decrypt round trip di staging. Plaintext lama tetap readable selama transisi. |
| P1 | Runtime hosting, queue/cron/storage/config aktual belum diverifikasi | Rehearsal exact PHP8.2.33/DB11.8.9 yang dilaporkan; queue database sama, cron, permissions, secure session/debug/HTTPS dan release bundle. |
| P2 | SMTP masih bisa menduplikasi email pada crash setelah provider menerima sebelum DB marker | Monitor; solusi exactly-once membutuhkan provider idempotency/integrasi terpisah. Ledger/payout tidak bergantung pada email untuk saldo. |
| P2 | Midtrans/SMTP live dan load test tidak dilakukan | Uji sandbox/provider, traffic, browser support sebelum pengaktifan fitur. Feature flags tetap false. |
| P2 | Tailwind4 mengubah minimum browser dukungan | Chromium desktop/mobile lulus; review Safari/Firefox/browser organisasi serta konten/form riil. |
| P2 | Royalty flag off melewatkan royalty event historical | Aktivasi tidak otomatis backfill. Jika diperlukan, rancang reconciliation/backfill tersendiri dengan data dan approval yang jelas. |

GitHub CI pada commit PR harus diperiksa langsung; evidence lokal bukan pengganti checks remote. Draft dipertahankan sampai P0/P1 dan review/migration gates diterima pemilik. Tidak ada kelemahan partial payout lama yang sengaja dibiarkan; batas yang tersisa memerlukan data/server/provider atau tindakan yang tidak diizinkan otomatis dalam tugas ini.

Keputusan saat ini: **GO review, NO-GO auto-merge/deployment/feature activation**.
