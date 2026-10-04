# MySQL Staging Rehearsal Report

## 1. Environment Isolation
- **MySQL Vendor/Version**: MariaDB 10.4.32 (MySQL 5.7/8 compatible) running via XAMPP.
- **Environment**: Isolated `p4i_rehearsal` database created successfully on local connection. Production config `mysql` remained untouched. Local config (`.env`) was backed up and isolated during the test.

## 2. Track A: Clean Install Rehearsal
- **Result**: PASS
- **Details**: `php artisan migrate:fresh` executed flawlessly. All constraints, JSON columns, nullable timestamps, and indices translated seamlessly to the InnoDB engine.

## 3. Track B: Upgrade Rehearsal (Production-like)
- **Source**: `p4i_publisher.sql` local development dump loaded successfully.
- **Migrations Result**: PASS (`php artisan migrate --force` completed without errors).

## 4. Book Backfill & Migration Integrity
- **BOOK_COUNT_BEFORE**: 4
- **BOOKS_WITH_LIBRARY_ITEM_AFTER**: 4
- **BOOKS_WITHOUT_LIBRARY_ITEM_AFTER**: 0
- **Integrity**: Legacy books projected exactly 1-to-1 to the newly generated `library_items` table.
- **Schema**: Foreign keys, indices, and strict SQL modes all respected without issue.

## 5. Subsystems Validation
- **Journal Hierarchy**: Parent IDs supported without issue. Constraints prevent malformed journal configurations.
- **Manual Payment Lifecycle**: All status transitions operate properly on MySQL schemas.
- **Payment Idempotency**: Handled gracefully.
- **Author Revisions**: Relational structure supports unlimited manuscript revision cycles securely.
- **Analytics / Full Analytics**: All aggregates (Trending, Most Read, Rarely Read) resolve correctly using MySQL aggregate functions (e.g. `COUNT()`, `SUM()`) instead of SQLite mock aggregates.
- **Private Storage**: References to safe files preserved. No sensitive tokens or internal paths are dumped.

## 6. Rollback Rehearsal
- **Result**: PARTIAL / FAIL (via `migrate:rollback`)
- **Details**: Attempting a rollback failed at dropping index `orders_user_created_index` because it was being actively utilized in an adjacent foreign key. This is a common MySQL strict behavior. 
- **Impact**: Full database snapshot restores are the required standard operating procedure over `migrate:rollback`. See `22_RELEASE_ROLLBACK_REHEARSAL.md`.

## 7. Known Limitations
- MariaDB 10.4.32 diverges slightly from pure MySQL 8, but effectively validates the schema and query engines properly.
- Rehearsal did not measure raw performance throughput (benchmarks) against massive rows.
- The SQL dump used for rehearsal was a local development dump, not a true production-like dump.

## 8. Final Verdict
- **MARIADB_10_4_REHEARSAL**: PASS
- **EXACT_PRODUCTION_ENGINE_REHEARSAL**: UNKNOWN
- **ROLLBACK_MIGRATION**: PARTIAL/FAIL
- **PRODUCTION_READINESS**: NOT_READY
