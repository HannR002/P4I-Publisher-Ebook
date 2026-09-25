# 04 — FRONTEND / UI / UX AUDIT

**Audit Date:** 2026-09-21

---

## 1. Frontend Stack Summary

| Component | Technology | Notes |
|---|---|---|
| CSS Framework | Tailwind CSS v3 | Via `@tailwind` directives, compiled with Vite |
| JS Framework | Alpine.js v3 | For interactivity (dropdowns, dark mode, mobile menu) |
| Build Tool | Vite v7 | With `laravel-vite-plugin` |
| Fonts | Inter (via fonts.bunny.net) | Used in public-layout |
| Tailwind Config Font | Figtree | In `tailwind.config.js` — ⚠️ conflicts with Inter used in views |
| PDF Viewer | PDF.js v2.16 (CDN) | In reader.blade.php |
| Charts | Chart.js | Used in admin/books (via CDN, not in package.json) |
| Forms Plugin | @tailwindcss/forms | Installed |
| Dark Mode | Class-based (`darkMode: 'class'`) | Alpine.js toggle with localStorage |

---

## 2. Layout Architecture

| Layout | Used By | Notes |
|---|---|---|
| `x-public-layout` | Welcome, Books, Book Detail, Library, Orders, Checkout, Pages | Full public navigation with dark mode |
| `x-app-layout` | Dashboard, Profile | Breeze default with authenticated navigation |
| `x-author-layout` | Author Dashboard, Submissions, Payouts, KYC Status | Author-specific sidebar layout |
| `x-guest-layout` | Login, Register, Password Reset | Centered card layout |
| Standalone HTML | reader.blade.php | No layout — standalone PDF viewer |

**Issue:** Admin pages likely extend `x-app-layout` or `x-public-layout` (need to verify each) — no dedicated admin layout exists as a component.

---

## 3. Page-by-Page Audit

### 3.1 Homepage (`welcome.blade.php`)

| Aspect | Rating | Notes |
|---|---|---|
| Visual Design | ⭐⭐⭐⭐ | Professional gradient hero, animated blobs, dark mode |
| Responsiveness | ⭐⭐⭐⭐ | sm/md breakpoints used properly |
| Content | ⭐⭐ | Only shows 4 latest books, no "recently added", no categories |
| CTA | ⭐⭐⭐ | "Mulai Membaca" and "Jelajahi Katalog" both go to /books |
| Features Section | ⭐⭐⭐⭐ | Three feature cards with hover effects |
| Missing | — | No search, no popular books, no free books section, no author spotlight |

### 3.2 Book Catalog (`books/index.blade.php`)

| Aspect | Rating | Notes |
|---|---|---|
| Search | ⭐⭐⭐ | Text search across title, author, category |
| Pagination | ⭐⭐⭐ | 12 per page with query string preservation |
| Owned Badge | ⭐⭐⭐⭐ | Shows "Dimiliki" badge for owned books |
| Category Filter | ❌ | No category filter UI (search only) |
| Sort Options | ❌ | No sort (newest, price, popular) |
| Price Display | ⭐⭐⭐ | Shows price, "Gratis" for free books |
| Empty State | ❓ | Need to check blade |

### 3.3 Book Detail (`books/show.blade.php`)

| Aspect | Rating | Notes |
|---|---|---|
| Layout | ⭐⭐⭐⭐ | Cover image + details side by side |
| Price/CTA | ⭐⭐⭐ | Buy/Read/Owned states |
| Description | ⭐⭐⭐ | Displayed if present |
| Reviews | ⭐⭐⭐ | Star rating + comments, ownership verified |
| Metadata | ⭐⭐ | Basic info only |
| Missing | — | No related books, no author page link, no reading preview |

### 3.4 Checkout Pages

| Page | Rating | Notes |
|---|---|---|
| checkout/show | ⭐⭐⭐ | Midtrans Snap integration |
| checkout/success | ⭐⭐⭐ | Success confirmation |
| checkout/pending | ⭐⭐⭐ | Pending payment info |
| Missing | — | No checkout/create page for cart review |

### 3.5 My Library (`my-library.blade.php`)

| Aspect | Rating | Notes |
|---|---|---|
| Book Grid | ⭐⭐⭐ | Displays owned books with covers |
| Read Button | ⭐⭐⭐ | Links to reader |
| Empty State | ❓ | Need to verify |
| Missing | — | No search within library, no reading progress, no "last read", no favorites, no collections |

### 3.6 My Orders (`my-orders.blade.php`)

| Aspect | Rating | Notes |
|---|---|---|
| Order List | ⭐⭐⭐ | Paginated with status badges |
| Order Detail | ⭐⭐ | Inline expansion |
| Missing | — | No re-pay for pending orders, no order detail page |

### 3.7 Reader (`reader.blade.php`)

| Aspect | Rating | Notes |
|---|---|---|
| PDF Rendering | ⭐⭐⭐ | PDF.js canvas rendering with watermark |
| Auto-Bookmark | ⭐⭐⭐⭐ | IntersectionObserver-based page tracking |
| Resume Reading | ⭐⭐⭐ | Scrolls to last read page |
| UI | ⭐ | Minimal — dark background, no toolbar, no page navigation, no zoom controls |
| Mobile | ⭐⭐ | PDF.js canvas is responsive but no touch gestures |
| Missing | — | No page numbers, no TOC, no zoom, no fullscreen, no font size, no night mode in reader |

### 3.8 Author Center Pages

| Page | Status | Notes |
|---|---|---|
| Author Register | ✅ EXISTS | KYC form |
| KYC Status | ✅ EXISTS | Status display |
| Author Dashboard | ⚠️ BROKEN | Controller missing, route crashes |
| Submissions List | ✅ EXISTS | Paginated list |
| Submission Create | ✅ EXISTS | Upload form |
| Submission Show | ✅ EXISTS | Detail + review history |
| Submission Edit | ✅ EXISTS | Edit for draft/revision |
| Payouts List | ✅ EXISTS | Balance + request form |
| Missing | — | No "My Books" page, no sales details, no earnings chart, no profile edit |

### 3.9 Admin Pages

| Page | Status | Notes |
|---|---|---|
| Admin Books | ✅ EXISTS | List + analytics + chart |
| Admin Book Upload | ✅ EXISTS | Upload form |
| Admin Users | ✅ EXISTS | List with search/filter |
| Admin User Detail | ✅ EXISTS | Orders + licenses |
| Admin KYC List | ✅ EXISTS | Filter by status |
| Admin KYC Detail | ✅ EXISTS | View ID card |
| Admin Curation | ✅ EXISTS | Submission list |
| Admin Curation Detail | ✅ EXISTS | Review + actions |
| Admin Payouts | ✅ EXISTS | List with filter |
| Admin Payout Detail | ✅ EXISTS | Ledger breakdown |
| Admin Reports | ✅ EXISTS | Date range + CSV export |
| Missing | — | No admin dashboard, no admin sidebar navigation component |

---

## 4. Design System Analysis

### Current State: NO FORMAL DESIGN SYSTEM

| Aspect | Finding |
|---|---|
| Color Palette | Ad-hoc: indigo-500/600, purple-500/600, pink, gray scales. No custom theme colors in tailwind.config.js |
| Typography | Font conflict: `tailwind.config.js` declares `Figtree`, but `public-layout` loads `Inter` |
| Buttons | Inconsistent: gradient buttons in public layout, Breeze default buttons in admin, custom buttons elsewhere |
| Cards | Multiple card styles: rounded-3xl in homepage, rounded-xl elsewhere, varying shadows and borders |
| Forms | Using `@tailwindcss/forms` plugin but with ad-hoc overrides |
| Spacing | No consistent spacing tokens — varies per view |
| Icons | Inline SVG only — no icon library, icons duplicated across files |
| Dark Mode | Well-implemented via Alpine.js + Tailwind `dark:` classes, consistent dark palette `#0f1117`, `#1a1d2e`, `#2d3147` |

---

## 5. Key Frontend Issues

### AUD-UI-001 — No Design System / Component Library

| Field | Value |
|---|---|
| **Severity** | P2 MEDIUM |
| **Impact** | Visual inconsistency, high maintenance cost, slow development |
| **Recommendation** | Create a design system with: color tokens, typography scale, button variants, card variants, form styles. Extract reusable Blade components for: stat cards, status badges, data tables, form groups. |

### AUD-UI-002 — Font Family Conflict

| Field | Value |
|---|---|
| **Severity** | P3 LOW |
| **File** | `tailwind.config.js` L16 vs `public-layout.blade.php` L12, L17 |
| **Evidence** | Config declares `Figtree`, layout loads `Inter` and sets `font-family: 'Inter'` in inline style |
| **Impact** | Font rendering depends on which CSS loads — inconsistent across pages |
| **Recommendation** | Align to one font family. Update `tailwind.config.js` to use `Inter`. |

### AUD-UI-003 — Reader Lacks Basic Controls

| Field | Value |
|---|---|
| **Severity** | P2 MEDIUM |
| **File** | `resources/views/reader.blade.php` + `public/js/secure-reader.js` |
| **Impact** | Poor reading UX: no page numbers, no zoom, no TOC, no navigation toolbar |
| **Recommendation** | Add toolbar with: page counter, zoom controls, fullscreen toggle, back to library button. Consider using PDF.js viewer application (not just rendering API). |

### AUD-UI-004 — Static Pages Are Minimal Placeholders

| Field | Value |
|---|---|
| **Severity** | P3 LOW |
| **Files** | `resources/views/pages/*.blade.php` |
| **Evidence** | About (1.2KB), Contact (2.2KB), Help Center (1.2KB) — very small, likely placeholder content |
| **Impact** | Unprofessional appearance for a publishing platform |
| **Recommendation** | Flesh out content with real P4I information, contact forms, FAQ, etc. |

### AUD-UI-005 — Inline CSS in Reader

| Field | Value |
|---|---|
| **Severity** | P3 LOW |
| **File** | `resources/views/reader.blade.php` L8-12 |
| **Evidence** | Inline `<style>` block instead of using Tailwind or external CSS |
| **Impact** | Maintenance inconsistency |
| **Recommendation** | Move styles to a CSS file or use Tailwind classes |

### AUD-UI-006 — Chart.js Loaded via CDN (Not in package.json)

| Field | Value |
|---|---|
| **Severity** | P3 LOW |
| **File** | Admin book index view (likely) |
| **Evidence** | Chart.js used for monthly sales chart but not in package.json dependencies |
| **Impact** | CDN dependency, no version pinning, inconsistent with Vite build pipeline |
| **Recommendation** | Install Chart.js via npm or accept CDN risk |

---

## 6. Accessibility Assessment

| Area | Status |
|---|---|
| Semantic HTML | 🟡 Partial — uses `<nav>`, `<button>`, `<form>` correctly but `<main>`, `<article>`, `<section>` inconsistent |
| ARIA labels | ❌ Missing on most interactive elements |
| Keyboard navigation | 🟡 Partial — form elements work, custom dropdowns may not |
| Color contrast | ⚠️ Some low-opacity text (watermark at 0.15 opacity is by design) |
| Alt text | ❌ Book cover images likely missing alt text |
| Focus indicators | 🟡 Tailwind provides default focus rings |
| Screen reader | ❌ Not tested or designed for |

---

## 7. Mobile Responsiveness

| Page | Mobile | Tablet | Desktop |
|---|---|---|---|
| Homepage | ✅ | ✅ | ✅ |
| Catalog | ✅ | ✅ | ✅ |
| Book Detail | 🟡 | ✅ | ✅ |
| Checkout | ✅ | ✅ | ✅ |
| Reader | ⚠️ No controls | ⚠️ | ✅ |
| My Library | ✅ | ✅ | ✅ |
| Admin pages | ❓ | ❓ | ✅ |
| Author pages | ❓ | ❓ | ✅ |
