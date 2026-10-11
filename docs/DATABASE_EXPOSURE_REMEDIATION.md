# Remediasi paparan database

## Bukti agregat dan klasifikasi

Dump terlacak `p4i_publisher.sql`: 35.039 byte, 21 tabel, 6 baris users, 2 authors, 4 books, 3 orders, 3 order_items, 3 book_licenses, 3 book_submissions, 1 payout_requests, 2 royalty_ledgers, 1 sessions, 1 reviews. Terdapat 6 bcrypt hash dan 7 alamat email unik; satu alamat memakai domain example yang dicadangkan, enam lainnya tidak. Nilai email, hash, sesi, rekening dan isi INSERT tidak ditampilkan.

Dokumen deployment menyebut dump development lokal. Bukti tersebut **tidak memastikan seluruh isi dummy**, ataupun membuktikan bahwa ini salinan produksi. Perlakukan sebagai data potensial asli/campuran sampai pemilik memverifikasi asal dan pemilik akun. Sesi dan hash dapat meningkatkan risiko pengambilalihan akun; hash bukan password plaintext, tetapi tetap sensitif dan bisa ditebak offline.

Path masuk riwayat sejak commit `6653d0fa` tanggal 26 September 2026 dan masih dapat diambil dari commit lama. Penghapusan pada branch pekerjaan tidak menghapus salinan di `master`, ref lain, fork, clone, cache, atau PR lama. Tidak ada rewrite/force push/perubahan visibilitas yang dilakukan.

Arsip aplikasi `tidak bole di zip#publisher ebook/p4i_core_ready.zip` juga dihapus setelah backup privat diverifikasi. Ukuran 28.338.753 byte; 438 entri, termasuk nested archive/source usang. Tidak ada SQL pada nama entri luar yang diperiksa; isi oversized/nested tidak dianggap aman hanya berdasarkan nama. Arsip docs yang tidak menghasilkan temuan scanner tetap dipertahankan. SQL vendor yang pernah muncul pada ref lama dibedakan dari dump akun aplikasi; tidak semua file berakhiran SQL merupakan insiden akun.

## Mitigasi pada branch

1. Salinan asli dump dan arsip dicocokkan checksum sebelum penghapusan tracking, disimpan sementara di workspace privat dengan direktori mode 0700 dan berkas mode 0600; tidak masuk Git/artifact publik. Pemilik harus memindahkannya ke vault/backup terenkripsi sesuai retensi, membatasi penerima dan menghapus salinan sementara setelah persetujuan retensi.
2. `.gitignore` diperluas untuk dump/compressed dump, SQLite/DB, backup, key dan private uploads. `.gitignore` sebelumnya sudah mengandung `*.sql`, tetapi tidak menghapus berkas yang telanjur terlacak.
3. `scripts/check_repository_security.py` memeriksa index kandidat commit atau tracked working files, forbidden paths, dump akun, bcrypt, key/token/credential URL yang dikenali, dan entri ZIP/DOCX. Output hanya path/label. Scan bukan jaminan universal menemukan setiap jenis secret.
4. Workflow CI menjalankan scanner dan tes scanner. Sebelum commit gunakan `python3 scripts/check_repository_security.py --staged`. Fixtures Laravel sintetis menggantikan kebutuhan dump nyata; tidak ada dump anonim baru yang diperlukan.

## Tindakan akun yang memerlukan pemilik

- Tetapkan asal data dan daftar akun terdampak dalam sistem privat, tanpa menambah PII ke issue/PR.
- Verifikasi rotasi password sementara administrator Hotfix5 secara langsung. Jika tidak ada bukti yang dapat dipercaya, lakukan reset melalui prosedur persetujuan produksi; jangan menjalankan seeder untuk recovery.
- Untuk akun nyata: reset password/reused password yang relevan, invalidasi sesi database/file dan remember tokens sesuai session backend, tinjau login/audit, serta rotasi token/credentials yang memang terpapar. Source menyediakan pencabutan sesi pada change/reset password, tetapi tidak melakukan pencabutan massal live.
- Bcrypt hash di repository harus dianggap bocor; menghapus file tidak menggantikan reset password. Jika dump hanya development, periksa apakah password/hash/token dipakai ulang di produksi.
- Tentukan kebutuhan pemberitahuan kepada pemilik data berdasarkan fakta insiden dan kewajiban organisasi. Keputusan ini belum dilakukan.
- `APP_KEY` tidak terbukti ada dalam dump. Jangan melakukan `key:generate` pada aplikasi produksi dengan data terenkripsi. Backup key dan data wajib; rotasi key, bila diperlukan, membutuhkan rencana decrypt/re-encrypt teruji.

## Prosedur history remediation — BELUM DIJALANKAN

Persetujuan eksplisit pemilik diperlukan karena rewrite mengubah SHA dan seluruh kolaborator harus menyesuaikan clone. Lakukan rotasi akun terlebih dahulu; jangan menunggu rewrite untuk menghentikan reuse.

1. Bekukan push sementara; inventaris semua branch/tag/PR/fork/ref dan blob sensitif. Catat refs awal, backup Git privat terenkripsi, backup database/uploads/key terpisah, dan daftar penerima clone. Pastikan backup tetap sah dan terbatas.
2. Pada clone mirror privat baru (bukan checkout kerja ini), setelah scope path disetujui, jalankan:

```sh
# Contoh untuk operator setelah persetujuan; tidak otomatis dijalankan.
git clone --mirror https://github.com/HannR002/P4I-Publisher-Ebook.git publisher-remediation.git
cd publisher-remediation.git
git filter-repo --path p4i_publisher.sql --path 'tidak bole di zip#publisher ebook/p4i_core_ready.zip' --invert-paths
```

3. Cari kembali path/blob/isi akun pada seluruh refs hasil rewrite memakai alat redaksi; jangan menampilkan nilai. Periksa ref yang tidak termasuk normal clone, perubahan signature commit, PR links, dan pipeline/deployment SHA pins. Tambahkan path lain hanya berdasarkan inventaris yang disetujui.
4. Siapkan daftar ref yang akan diganti dan jadwal, review dengan pemilik. Push penggantian ref hanya melalui persetujuan khusus dan change window; **dokumen ini tidak mengizinkan force push**. Jangan menjalankan `push --mirror` secara membabi buta karena bisa menghapus ref sah.
5. Hubungi GitHub Support untuk cached views/PR refs yang masih menyimpan data sensitif sesuai prosedur GitHub. Koordinasikan pemilik fork/clone; rewrite di upstream tidak membersihkan salinan mereka.
6. Kolaborator re-clone atau reset secara terkoordinasi tanpa mengembalikan blob lama; scanner wajib pada kontribusi baru. Validasi refs publik setelah remediasi dan simpan hanya metrik/hash bukti yang aman.
7. Tutup insiden setelah bukti reset akun, invalidasi sesi, hasil scope history, tindakan cache/fork, dan persetujuan retensi dicatat secara privat.

**Status: mitigasi source selesai; insiden/riwayat belum ditutup.**
