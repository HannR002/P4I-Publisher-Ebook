# Legacy Book Migration Strategy

## Additive transition

Migration `2026_09_22_100000_create_library_domain.php` creates the library schema and adds nullable unique `books.library_item_id`. It does not drop or rename `books`, `book_licenses`, `orders`, `order_items`, `reviews`, categories, royalty ledgers, or payout requests.

During migration, every legacy book without a link is backfilled to one `LibraryItem(type=book)` with:

- matching title, slug, description/synopsis, ISBN, cover, date, price, and publication state;
- one ordered author creator;
- one private primary file;
- copied category links;
- `manual_purchase` for price above zero, otherwise `public_read_download`.

The backfill selects only rows whose `library_item_id` is null and processes chunks of 100. The unique link prevents duplicate projections. Existing slugs are safe on the initial run because the new table starts empty.

## Runtime compatibility

New/updated legacy `Book` models synchronize their projection through `LibraryItemSynchronizer`. Legacy `/books/{slug}` remains resolvable and redirects to the published library detail when linked. Existing DRM/licenses and historical orders continue to use `books`.

## Deployment sequencing

1. Back up MySQL and both public/private uploads.
2. Deploy code with legacy flags disabled.
3. Run additive migrations once with `php artisan migrate --force`.
4. Compare counts: every `books.id` must have a non-null valid `library_item_id`.
5. Inspect a sample of free/paid, published/draft, categorized, and authored books.

Suggested checks:

```sql
SELECT COUNT(*) FROM books WHERE library_item_id IS NULL;
SELECT COUNT(*) FROM library_items WHERE source_type = 'legacy_book';
SELECT COUNT(*) FROM books;
```

## Rollback risk

The migration `down()` removes the new library tables and the book link but does not touch legacy data. However, any newly authored generic collection, payment, grant, or analytics data would be lost by migration rollback. After real traffic begins, rollback must be code-forward or database-restore based; do not casually run `migrate:rollback`.

No production `migrate:fresh`, `db:wipe`, truncation, or automatic deploy is permitted.
