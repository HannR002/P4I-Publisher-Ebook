# 11 UI Functional Review

## Scope
This review covers the functionality introduced in the digital library front-end (`show.blade.php`) and associated UI workflows surrounding journals, hierarchy, and payment operations.

## Completed Integrations

### Journal Hierarchy Navigation
- **Parent/Child Discovery**: 
  - If a user is viewing a `journal_issue`, the UI correctly presents a backward navigational link to the parent `journal`.
  - If viewing a `journal_article`, it links to its immediate parent (issue or journal).
  - The UI iterates through available `$item->children` to prominently render nested lists (e.g., listing all published issues under a journal, or all articles inside an issue).
- **Metadata Rendering**: Extract metadata blocks such as `$item->metadata['pages']`, `$item->metadata['volume']`, and `$item->metadata['number']` to display specific hierarchical contextual information cleanly.

### Access Control Rendering
- The blade template correctly hooks into `$actions` provided by `LibraryAccessService`.
- Contextual buttons switch seamlessly between:
  - "Baca Sekarang" / "Download" (Free / Owned)
  - "Beli / Dapatkan Akses" (Paid Digital)
  - "Pesan Buku Cetak" (Physical Book)
  - "Masuk untuk Membeli / Membaca" (Guest state restriction)
  - "Baca di Sumber / OJS" (External Source)

### Safety Validations
- **Data Validation**: File sizes, extensions, and amounts for manual orders are enforced server-side before persisting database records, but the UI leverages Laravel's native validation exception handling to present clear errors (e.g. `$errors->first('amount')`).
- **Hidden Storage**: Direct file paths for purchased or privately hosted files are completely abstracted behind `library.read` or `library.download` named routes, eliminating the risk of unauthenticated directory traversal via the browser.

## Recommendations for Future Iteration
- Implement JavaScript-based dynamic polling or WebSocket events for `payment_submitted` status so users don't have to manually refresh the page when an admin approves their payment.
- Introduce pagination for `$item->children` if a journal accumulates hundreds of articles over the years.
