# P4I Digital Library & Publishing — Product Rescope

## Product definition

P4I is now a digital library first and a book-publishing service second. The public catalog covers books, journals, journal articles, general articles, proceedings, reports, modules, monographs, and other publications. Book publishing continues for digital, printed softcover, and printed hardcover outputs.

The separate P4I OJS installation remains the system of record for journal submission, peer review, editorial workflow, issues, and journal publication. This application stores display/search metadata and links users to OJS; it does not recreate OJS.

## Access principles

Reading is no longer gated by “Add to My Library.” Each `LibraryItem.access_policy` determines the visible and permitted actions:

| Policy | Guest/user action |
|---|---|
| `public_read_download` | Read and download immediately |
| `public_read_only` | Read immediately; no download |
| `registered_read_download` | Login, then read/download |
| `registered_read_only` | Login, then read |
| `manual_purchase` | Manual order; access only after admin verification |
| `external` | Open the validated HTTP(S) source/OJS URL |
| `physical_only` | Create a printed-book order |

My Library remains a legacy convenience for licensed books. It is not used as a public-content access gate.

## Information architecture delivered

- Homepage positioning: P4I Digital Library, global search, recent collections, books, journals/articles, and publishing CTA.
- Public library: a single catalog and search endpoint across all types.
- Metadata detail: creators, category, publisher, identifiers, year, description/synopsis/abstract, and short controlled excerpt.
- Author Center: dashboard and manuscript workflow; KTP, bank details, and financial KYC are not required when the feature is disabled.
- Admin: generic collection management, book publishing curation, manual payment methods and verification, analytics, users, and legacy book management.

## MVP boundary

Implemented now: metadata catalog, access decisions, protected file delivery, external links, search/filtering, manual digital purchase/access grants, private payment proofs, payment-method administration, event tracking, and high-value analytics.

Deferred: OJS importer, publishing-service orders, full shipping/fulfilment, stock workflows, daily analytics aggregation, favorites/history redesign, automated URL health checks, and complete admin design-system consolidation.
