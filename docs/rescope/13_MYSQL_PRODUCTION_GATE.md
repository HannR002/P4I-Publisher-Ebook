# 13 MySQL Production Gate

## MySQL 8 / Hostinger Compatibility Review

The unreviewed rescope migrations have been evaluated against MySQL 8 defaults and Hostinger's standard shared hosting constraints.

### 1. Migration Order & Foreign Keys
- **Migration Order**: New migrations (`2026_09_22_100000_create_library_domain`, `100100_manual_payment`, etc.) are correctly sequenced after base application setup.
- **Foreign Key Definitions**: Foreign keys safely reference `id` columns which default to `unsignedBigInteger` in Laravel 12.
- **Parent ID Self-Reference**: `parent_id` on `library_items` is added in a separate migration (`2026_09_28_114448_add_parent_id_to_library_items_table.php`) referencing its own table. This avoids circular reference problems during table creation. It uses `nullOnDelete()`, preventing cascading deletion of an entire hierarchical tree when a parent is removed.

### 2. Constraints & Data Types
- **Cascade/Restrict Behavior**: `book` to `library_items` mapping uses `nullOnDelete()`, while mapping tables (`library_item_creators`, `library_item_files`) correctly use `cascadeOnDelete()` since they are owned entirely by the `LibraryItem`.
- **Nullable Foreign Keys**: Supported appropriately across the domain (e.g. `library_item_id` on `books`).
- **Index Lengths & Unique Indexes**: Slug strings are constrained and unique. Indices are appropriately placed on lookup columns without exceeding MySQL limits (max 767 bytes for older versions, up to 3072 for InnoDB dynamic formats, handled natively by Laravel strings maxing out at 255).
- **JSON Columns**: `metadata` uses Laravel's `json()` type, supported comprehensively in MySQL 8+.
- **DECIMAL**: Financial amounts correctly use `decimal('price', 12, 2)` preventing floating-point precision loss.
- **Booleans & Timestamps**: Managed safely through Laravel defaults (`tinyint` and standard timestamps).
- **Nullable Encrypted Data**: (N/A for these specific tables, but `id_card_number` is correctly cast to nullable string).

### 3. Rollback / Down Behavior
- `down()` methods are explicitly defined to drop foreign keys (`dropConstrainedForeignId`) before dropping tables or columns, adhering to MySQL foreign key enforcement logic.

## Legacy Backfill Checks

During the staging rehearsal (and eventual production rollout), the one-way projection script (`backfillBooks`) must be verified.

**Pre-Migration Check:**
```sql
SELECT COUNT(id) FROM books;
```

**Post-Migration Check:**
1. Verify 1:1 mapping mapping size.
```sql
SELECT COUNT(id) FROM books WHERE library_item_id IS NOT NULL;
```
*(Should equal the pre-migration count).*

2. Verify no duplicates.
```sql
SELECT library_item_id, COUNT(*) FROM books GROUP BY library_item_id HAVING COUNT(*) > 1;
```
*(Should return empty).*

3. Verify categories carried over.
```sql
SELECT cli.library_item_id, c.name FROM category_library_item cli
JOIN categories c ON c.id = cli.category_id;
```

## Legacy Book Deletion Safeties
As assessed, a hard delete of `books` currently cascades and destroys the `library_items` projection, alongside historical access grants and manual orders.
**Mitigation implemented:** `LibraryItemSynchronizer::remove()` intercepts `Book::deleted()` events and updates the `LibraryItem` status to `archived` rather than executing a hard `.delete()`. This preserves analytics and financial associations.

## Conclusion & Action Items
Currently, the tests execute via an in-memory SQLite database (`MYSQL_REHEARSAL_REQUIRED=true`). A MySQL rehearsal environment *must* be initialized to definitively prove these migrations succeed on a Hostinger-like MySQL daemon.
