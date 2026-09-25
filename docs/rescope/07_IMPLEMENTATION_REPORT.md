# Rescope Implementation Report

**Date:** 2026-09-22  
**Scope:** local implementation only; no deployment or production database/environment modification.

## Baseline

The first raw run could not measure application health because this checkout had no app key: 62 failed, 1 passed. With an ephemeral process-only test key, the meaningful baseline matched the audit gate: 64 tests passed and the stock `Feature/ExampleTest` failed because it did not migrate its in-memory database (188 assertions total). PHPUnit was confirmed isolated on SQLite `:memory:`.

## Delivered

- Fixed AUD-SYS-001 with `AuthorDashboardController`; route listing and route caching succeed.
- Added feature configuration and defense-in-depth guards for Midtrans, royalty, payout, and financial KYC.
- Removed duplicate royalty listener registration.
- Added generic library schema/models, policy-based access service, private file delivery, global search/filtering, public catalog/detail pages, admin collection pages, and legacy book projection/backfill.
- Added manual orders, admin-managed payment methods, private proofs, verification/rejection, and verified digital access grants.
- Added privacy-conscious analytics events and admin MVP dashboard.
- Repositioned homepage/navigation toward P4I Digital Library while retaining the dark/purple Tailwind direction.
- Added focused rescope tests and a safe `.env.testing` without production secrets.

## Migrations and schema changes

1. `2026_09_22_100000_create_library_domain.php`: library items, creators, files, category pivot, editions, access grants; nullable unique `books.library_item_id`; legacy backfill.
2. `2026_09_22_100100_create_manual_payment_domain.php`: payment methods, manual orders/items, payment submissions.
3. `2026_09_22_100200_create_analytics_events_table.php`: bounded analytics event store and indexes.

All are additive. No historical financial table or upload is deleted.

## File inventory

Created:

- Configuration/environment: `config/features.php`, `config/library.php`, `.env.testing`.
- Domain/models: all `Library*` models, `BookEdition`, manual order/payment models, and `AnalyticsEvent`.
- Services: `LibraryAccessService`, `LibraryItemSynchronizer`, `AnalyticsRecorder`.
- HTTP: feature middleware, public library/manual-payment controllers, author dashboard controller, admin library/payment/analytics controllers.
- Views: new library, manual order, admin library/payment/payment-method/analytics views; replacement homepage and author-registration page.
- Tests: `FeatureFlagsRescopeTest`, `LibraryCatalogTest`, `ManualPaymentTest`, `LibraryAnalyticsTest`.
- Three migrations and all eight requested rescope/deployment documents.

Changed:

- `bootstrap/app.php`, `routes/web.php`, `phpunit.xml`, `AppServiceProvider`, legacy checkout/webhook job/controller/listener, payout service, author KYC/submission middleware/requests/controllers.
- `Book`, `Category`, and `User` relationships; book upload/curation synchronization; legacy book detail compatibility.
- Public, authenticated, admin, and author navigation/layouts plus checkout Snap loading guard.
- Vite production manifest/CSS output after a successful locked dependency build.

## New route groups

- Public: `/library`, library detail/read/download/select.
- Authenticated: manual order creation/detail/proof upload.
- Admin: library CRUD MVP, payments/proofs/verify/reject, payment methods, analytics.
- Legacy gateway/KYC/payout endpoints remain named for compatibility but return intentional 404 through feature middleware when disabled.

## New models/services/controllers/pages

Models include `LibraryItem`, `LibraryItemCreator`, `LibraryItemFile`, `BookEdition`, `LibraryAccessGrant`, `PaymentMethod`, `ManualOrder`, `ManualOrderItem`, `PaymentSubmission`, and `AnalyticsEvent`.

Services include `LibraryAccessService`, `LibraryItemSynchronizer`, and `AnalyticsRecorder`.

Public pages: respecified homepage, global library catalog, item detail, manual-order payment page. Admin pages: all collections/form, payment verification, payment methods, analytics. Author registration and dashboard now match the no-KYC book-publishing flow.

## Status by area

| Area | Status |
|---|---|
| Global title/creator/description/synopsis/abstract/identifier/publisher/category/year search | Implemented MVP |
| Type/category/access/format/creator/publisher/year/language filters | Implemented architecture/UI for primary filters; creator/publisher/language accepted by endpoint |
| Newest/read/download/trending/A–Z sorting | Implemented |
| Direct policy-derived read/download/external actions | Implemented |
| Manual digital payment and access grants | Implemented MVP |
| Physical fulfilment and publishing-service payments | Schema boundary only / remaining |
| Analytics inventory/engagement/search/financial MVP | Implemented |
| OJS sync | Contract documented; not implemented |

## Verification

- `php artisan route:list`: success (105 routes in compatibility-enabled test environment).
- `php artisan route:cache`: success; cache cleared after verification.
- Additive migrations: success on local testing SQLite.
- PHP syntax checks: success.
- Final full tests: **83 passed (262 assertions)** in 5.89 seconds. The only notices are existing PHPUnit 12 metadata deprecations in `EbookBvaTest`.

## Known remaining work and risk

- Run a staging MySQL migration rehearsal on a recent anonymized production backup; SQLite success does not replace this gate.
- Verify production book slugs/data quality and storage paths before backfill.
- Complete shipping/address/stock/edition management and publishing-service manual orders.
- Add OJS API importer, daily analytics aggregation/retention, broken-link/file operational checks, and fuller admin navigation/design-system consolidation.
- Review Indonesian copy/accessibility and browser-test file responses on Hostinger/Apache.
- Configure real active payment accounts and official QR assets in admin after deployment; none are hardcoded or seeded.
- Once new-domain data exists, rollback is data-destructive for those new tables; restore from backup or forward-fix.

## Readiness

**READY FOR LOCAL CODE/PRODUCT REVIEW. NOT READY FOR PRODUCTION DEPLOYMENT.** Production readiness requires stakeholder acceptance, staging MySQL rehearsal, backups, real payment-method configuration, storage verification, and smoke/security testing using the deployment plan.
