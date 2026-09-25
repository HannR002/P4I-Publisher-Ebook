# Eksekusi Fase 3: UI Polish, Dark Mode, & Keamanan Ekstra

- `[/]` **Tahap 1: Keamanan & Infrastruktur Dasar**
  - `[ ]` Ubah `OrderIdGenerator` menggunakan ULID (`Str::ulid()`).
  - `[ ]` Tambahkan *Rate Limiting* (throttle) pada rute Checkout untuk mencegah eksploitasi bot Rp0.
  - `[ ]` Ikat IP Address ke dalam *Signed URL* DRM PDF di `DrmController` untuk mencegah *session hijacking*.
- `[ ]` **Tahap 2: Perbaikan Bug & Reader**
  - `[ ]` Perbaiki *bug* *auto-scroll* di `secure-reader.js` (tunggu semua halaman dirender sebelum *scroll*).
  - `[ ]` Ubah desain *watermark* PDF agar lebih transparan dan berada di *footer* (*bottom center*).
- `[ ]` **Tahap 3: Polesan UI Publik**
  - `[ ]` Ubah tombol "Perpustakaan Saya" menjadi biru muda dan lebih kecil.
  - `[ ]` Implementasikan efek *Navbar* mengecil dan melayang saat *scroll*.
  - `[ ]` Perbarui *Footer* menjadi lebih lengkap.
  - `[ ]` Implementasikan fitur *Dark Mode Toggle* di layout publik.
- `[ ]` **Tahap 4: Admin Dashboard & Dokumentasi**
  - `[ ]` Tambahkan Grafik Visual (Chart.js) penjualan bulanan di Admin Dashboard.
  - `[ ]` Buat dokumen penjelasan fitur terbaru (`ekstraksi_sistem_p4i.md`).
  - `[ ]` Buat dokumen rencana perancangan selanjutnya (`rencana_pengembangan_selanjutnya.md`).
