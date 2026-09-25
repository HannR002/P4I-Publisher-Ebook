# Laporan Pengujian UI / UX P4I E-Book

**Tanggal Laporan:** 1 September 2026
**Tujuan:** Mengaudit kualitas visual, pengalaman navigasi pengguna, dan konsistensi lintas halaman (termasuk Dark Mode dan Responsivitas).

---

## 1. Analisis Estetika & Konsistensi Desain (Visual Audit)

Sistem P4I E-Book menerapkan desain "Modern Premium" dengan perpaduan warna solid, *glassmorphism*, dan bayangan halus (soft shadows).

| Aspek Desain | Temuan / Observasi | Penilaian |
| :--- | :--- | :--- |
| **Skema Warna (Color Palette)** | Warna mode terang (*Light Mode*) menggunakan putih `bg-white` dan abu muda `bg-gray-50` yang jernih. Mode gelap (*Dark Mode*) menggunakan biru sangat tua `bg-[#0f1117]` dan `bg-[#1a1d2e]`, menghindari hitam pekat murni yang membuat mata cepat lelah. Aksen menggunakan gradasi *Indigo-Purple*. | ⭐⭐⭐⭐⭐ (Sempurna) |
| **Tipografi** | Menggunakan *Google Font* `Inter` pada seluruh hierarki judul (`h1`, `h2`) dan paragraf. Kontras antara teks (hitam/putih) dan latar belakang berada pada ambang standar WCAG AA. | ⭐⭐⭐⭐⭐ (Sangat Baik) |
| **Ikonografi & *Whitespace*** | Ikon menggunakan *Feather/Heroicons* gaya garis (stroke/outline) yang elegan. Penggunaan *padding* `py-12` hingga `py-24` memberikan ruang napas (whitespace) yang sangat mewah. | ⭐⭐⭐⭐⭐ (Sangat Baik) |
| **Tombol (Buttons)** | Standarisasi ukuran (rounded-full/rounded-xl) diterapkan konsisten. Efek *hover* yang sedikit mengangkat `hover:-translate-y-1` dipadu dengan pendaran bayangan `shadow-lg` sukses membuat UI terasa hidup. | ⭐⭐⭐⭐⭐ (Sangat Baik) |

---

## 2. Pengujian Interaksi Pengguna (User Experience Audit)

*User Experience* (UX) dievaluasi dengan menyimulasikan perjalanan pembeli:

### A. Navigasi (*Sticky Navbar & Footer*)
- **Fungsionalitas:** Navbar otomatis mengecil (berkurang ketebalannya) ketika pengguna menggulir ke bawah, menutupi area konten secara transparan (*glassmorphism/blur*).
- **UX Insight:** Sangat tidak mengganggu visibilitas halaman. Tombol kembali ke atas (*Scroll to Top*) berwujud pita/pembatas buku cokelat memberikan kemudahan esensial bagi pengguna jika sedang menelusuri katalog panjang.

### B. Portal Pembaca & Pengalaman Membaca (Reader UX)
- **Fungsionalitas:** Buku PDF dirender di tengah kanvas berbayang dengan latar belakang menyesuaikan mode warna layar.
- **UX Insight:** Indikator nomor halaman (contoh: *Hal. 10 / 250*) di-*update* secara *real-time*. Tombol panah Kiri/Kanan ditempatkan di posisi ergonomis (bawah tengah) dan mendukung navigasi panah *keyboard*.
- **Kendala Teratasi:** Sebelumnya PDF di- *scroll* secara standar sehingga pembaca sulit mengetahui progres bacaan mereka. Kini dengan adanya fitur "Auto-Save Progress", UX menjadi setingkat dengan *Kindle* atau aplikasi *Apple Books*.

### C. Alur Pembayaran (*Checkout UX*)
- **Fungsionalitas:** Pengguna dituntun ke halaman Ringkasan (Tagihan) dengan tata letak satu kolom agar fokus tidak pecah. Saat tombol diklik, *Popup* Midtrans muncul di tengah (*Overlay*) tanpa membuka *tab* baru.
- **UX Insight:** Ini krusial. Mempertahankan pengguna di dalam *domain* P4I mencegah rasio pentalan (*drop-off rate*) yang biasanya tinggi saat dialihkan ke *website* bank eksternal.

---

## 3. Pengujian Responsivitas Lintas Perangkat (Cross-Device Testing)

Kami telah mensimulasikan tata letak (*layout grid*) pada berbagai *viewport* (ukuran layar):

1. **Desktop (>= 1024px / Layar Monitor):**
   - Footer pecah menjadi 4 kolom berjejer sejajar. Katalog buku berjejer 3 hingga 4 buku sebaris. Tampilan optimal dan tidak melebar tak wajar.
2. **Tablet (768px - 1024px / iPad):**
   - Katalog menyesuaikan menjadi 2 atau 3 buku per baris. Teks *Hero Section* otomatis mengecil (dari `text-7xl` ke `text-5xl`). UI tetap terbaca tanpa di-*zoom*.
3. **Mobile (<= 768px / iPhone & Android):**
   - Menu Navbar menyusut menjadi ikon "Hamburger" (Dropdown Toggle).
   - Katalog menjadi 1 atau maksimal 2 kolom vertikal (*stacked*).
   - Footer berubah dari sejajar horizontal menjadi vertikal ke bawah secara utuh, mencegah teks terpotong (overflow).

---

## 4. Evaluasi Sistem *Dark Mode* & Persistensi

- **Perbaikan Terbaru:** Masalah di mana kotak konten menghitam sementara latar utama tetap putih telah dituntaskan sepenuhnya dengan merestrukturisasi penempatan kelas ke akar `<html>` dan melakukan re-kompilasi Tailwind CSS.
- **Persistensi Data:** Mode gelap diikat pada penyimpanan lokal (`localStorage.getItem('theme')`). Saat pengguna merefresh *browser* atau berpindah rute, sistem tidak menimbulkan kilatan (*flicker*) cahaya sesaat karena *script check* dijalankan di `<head>` sebelum DOM selesai dimuat.

---

**Kesimpulan UI / UX:**
Antarmuka P4I E-Book memiliki kelas *World-Class Aesthetics*. Standar yang digunakan telah memenuhi kaidah kenyamanan literatur (*Reader-Centric Design*). Tidak ada perbaikan krusial (Mayor) yang dibutuhkan untuk visual antarmuka saat ini. Saran pengembangan hanya bersifat penambahan fitur baru (Skalabilitas UI pada Fase 2).
