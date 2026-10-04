# Phase 3A: Public Library UX Redesign

## 1. Identity & Shell
**Brand Name:** P4I Digital Library
**Navigation:** Desktop/Mobile responsive, keyboard-accessible, Light/Dark adaptive. Includes Home, Library, Books, Journals, Articles, Book Publishing, and About.
**Theme Switcher:** Integrated into shell.

## 2. Homepage Architecture
**Hero Section:** Replaces legacy marketplace claims. Focuses on discovering knowledge in one library platform. Includes a prominent Global Search bar.
**Data Sections:**
- Terbaru (Latest)
- Jelajahi Berdasarkan Jenis (Browse by Type)
- Topik / Kategori (Topics/Categories)

## 3. Global Search & Catalog
**Search:** Reuses existing `LibraryCatalogController`. Preserves query parameters in URL (`?q=...&type=...&category=...`).
**Filters:** Type, Category, Access Policy, Format, Year, Sort.
**Results Display:** Uses `<x-publication-card>` within a responsive grid layout.
**Empty State:** Informative `<x-empty-state>` encouraging query adjustments.
**Pagination:** Server-side pagination retaining all URL parameters.

## 4. Visual Themes
**Light Mode:** Soft neutral background (`bg-background`), clean surfaces (`bg-surface`), clear typography (`text-text-primary`), purple accents (`text-primary`).
**Dark Mode:** Deep charcoal (`bg-background`), off-white text, restrained borders (`border-border`), and purple accents.

## 5. Accessibility & Responsive
- Semantic HTML (landmarks, headings, buttons vs. links).
- Scalable flex/grid containers for mobile (375px), tablet, and desktop views.
- Focus rings on interactive elements.
- Meaningful labels and structural clarity.
