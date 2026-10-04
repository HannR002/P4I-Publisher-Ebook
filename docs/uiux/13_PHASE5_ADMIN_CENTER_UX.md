# Phase 5: P4I Digital Library Admin Center UX

## Objective
Transform the Admin area into a coherent, dual-theme (Light/Dark) P4I Digital Library Admin Center focused on operations, publishing, manual payments, analytics, users, and content quality.

## Principles
1. **Unified Semantic Theme:** Use the `bg-background`, `bg-surface`, `text-text-primary`, and `primary` tokens. No isolated color systems.
2. **Actionable Executive Dashboard:** Answer "Apa yang perlu diketahui dan ditindaklanjuti P4I hari ini?"
3. **Restricted Financials:** Royalty, Payouts, and automated KYC remain disabled under feature flags.
4. **Data Integrity:** Do not change business logic. Use realistic queries for metrics, no fabricated SLA or fake analytics.

## Information Architecture
**Target Navigation:**
- Dashboard
- Perpustakaan (Semua Koleksi, Buku, Jurnal, Edisi Jurnal, Artikel Jurnal, Artikel, Prosiding, Laporan, Modul, Monograf, Lainnya, Kategori)
- Penerbitan (Naskah Masuk, Sedang Direview, Perlu Revisi, Disetujui, Terbit)
- Transaksi (Pesanan, Verifikasi Pembayaran, Metode Pembayaran)
- Buku Cetak (Edisi, Stok/Pesanan - ONLY when underlying data exists)
- Analitik
- Pengguna
- Pengaturan

## Functional Areas

### 1. Admin Shell
Provide a consistent shell across desktop (sidebar, topbar, breadcrumbs) and mobile (collapsible sidebar, horizontal scrolling for tables).

### 2. Executive Dashboard
Show Top Summary (Total Koleksi, Buku, Jurnal, Publikasi Baru, Dibaca/Download Hari Ini, Pembayaran Menunggu Verifikasi).
"Perlu Tindakan" section should highlight actionable items linking to relevant pages.

### 3. Library & Content Management
CRUD for LibraryItems using unified forms.
Clearly indicate legacy Book-linked items ("Dikelola melalui data Buku") and prevent generic editing.
Journal Management: Reflect hierarchy visually (Journal -> Edition -> Article) without modifying existing OJS logic.
Content Quality: Surface items missing metadata (covers, files, ISBN/ISSN).

### 4. Transactions (Manual Payments)
Payment Verification: Clear distinction between expected vs. submitted amount. Actions: Verifikasi/Tolak (requires reason).
Payment Methods: Edit, Enable/Disable (avoid destructive deletes).

### 5. Analytics & Search Insights
Display reads/downloads (Today, 7 Days, 30 Days), Trending, and Rarely Read (excluding items <30 days old).
Show Top Search Terms and Zero Result Searches based on real analytics data.

### 6. User Management
Manage roles, author statuses, and basic account flags. Keep existing RBAC.

## Development & Testing
- Use `DevelopmentVisualFixtureSeeder` to mock necessary metrics if the local db is empty.
- Maintain HTTP render smoke tests (do not rely solely on screenshot tools if they fail).
- Support responsive and accessibility standards.
