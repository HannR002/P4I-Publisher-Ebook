# Library Domain Architecture

## Aggregate

`LibraryItem` is the public catalog aggregate. Database values are flexible strings plus application validation so future types/policies do not require destructive ENUM changes.

Core tables:

- `library_items`: type, descriptive metadata, identifiers, source, access policy, price, lifecycle status, JSON metadata, publication timestamp.
- `library_item_creators`: ordered people/organizations and roles.
- `library_item_files`: local private path or external URL, MIME/type, visibility, primary marker, and download permission.
- `category_library_item`: admin-managed categories/genres. “Novel” is a category; it is not a content type.
- `book_editions`: book-only digital/softcover/hardcover commercial formats.
- `library_access_grants`: verified user entitlement for paid digital items.

`LibraryItem.type` application values are `book`, `journal`, `journal_article`, `article`, `proceeding`, `report`, `module`, `monograph`, and `other`.

## File security

Local publication files and payment proofs use Laravel's local/private disk. Public routes never reveal storage paths. Read/download controllers first evaluate policy and user grants, then validate file existence and return a streamed/file response. An external file uses only an HTTP(S) URL. Neither local path nor external URL is universally required, but at least one is expected before a local item is considered operational.

## Search and filters

One global query searches title, description, synopsis, abstract, ISBN, ISSN, DOI, publisher, keywords, publication year, creator names, and category names. Filters cover type, category, access grouping, digital/print format, creator, publisher, year, and language. Sorts include newest, reads, downloads, trending, and A–Z.

Current SQL uses portable `LIKE` and relationship queries for MySQL/SQLite compatibility. When inventory/traffic outgrows this approach, introduce MySQL full-text indexes or an external search index behind the same query contract.

## Access service

`LibraryAccessService` is the single application decision point for `canRead`, `canDownload`, paid grants, and UI actions. Controllers enforce the same decisions shown by Blade; UI hiding alone is never authorization.

## Lifecycle

Items have `draft`, `published`, or `archived` status in application validation. Public queries require `published` and a non-future `published_at` when present.

## Source-of-truth rule

- Linked legacy books (`source_type=legacy_book`): `books` remains the temporary editing source; `LibraryItemSynchronizer` maintains a one-way public projection.
- New generic/non-legacy records: `library_items` is authoritative.
- Admin generic editing intentionally rejects linked legacy book edits to prevent two independent copies.
