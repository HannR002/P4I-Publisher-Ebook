# PHASE 3B: PUBLICATION DETAIL UX & DIGITAL READER

## 1. Overview
This phase restructures the deeper public experience, shifting from a generic library item display to a highly specific, academic-first view based on the exact publication type.

## 2. Component Architecture
We split the monolithic `library.show` into semantic partials:
- `resources/views/library/show.blade.php`: The routing shell and breadcrumb wrapper.
- `partials/book-detail.blade.php`: Optimizes for covers, editions, and purchase formats.
- `partials/journal-detail.blade.php`: Academic landing page style showing volumes and issues.
- `partials/issue-detail.blade.php`: Displays issue metadata and the table of contents (Daftar Artikel).
- `partials/article-detail.blade.php`: Dedicated abstract and citation view.
- `partials/generic-detail.blade.php`: Fallback for unknown or legacy types.

## 3. Digital Reader Identity
The legacy DRM reader (`reader.blade.php`) was redesigned to match the Phase 2 dual-theme semantic tokens. It is now explicitly branded as "Pembaca Digital". The layout features a top control bar (with a Back button and Theme Switcher) and a clean, centered PDF surface, prioritizing the reading experience over complex DRM messaging.

## 4. Manual Payment UX
The `manual-orders/show.blade.php` view was completely redesigned to provide clear feedback paths for users buying digital or physical goods.
- Explicit status mapping (awaiting_payment -> Menunggu Pembayaran).
- Integrated `x-data="{ copied: false }"` for account numbers.
- Handles rejection states gracefully with error messaging.
- Verifies and routes physical vs digital fulfillments.
