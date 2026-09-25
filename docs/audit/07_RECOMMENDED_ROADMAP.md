# 07 — RECOMMENDED ROADMAP

**Audit Date:** 2026-09-21

---

## PHASE 0 — Critical Safety & Data Integrity (P0)

**Priority:** MUST complete before ANY production use  
**Estimated effort:** 3-5 days  

---

### TASK 0.1 — Fix Royalty Ledger Design (AUD-ROY-001, AUD-ROY-002)

| Field | Value |
|---|---|
| **Task** | Redesign royalty/payout system to use `payout_allocations` table instead of ledger splitting |
| **Files** | `app/Services/PayoutService.php`, `app/Models/PayoutAllocation.php` (NEW), migration (NEW), `app/Models/RoyaltyLedger.php` |
| **Risk** | HIGH — changes financial data model |
| **Dependency** | None |
| **Acceptance Criteria** | 1. `royalty_ledgers` are immutable — never mutated after creation. 2. New `payout_allocations` table tracks partial/full allocation per ledger per payout. 3. `unique(order_item_id)` constraint preserved. 4. All monetary math uses `bcmath` or integer cents. 5. Existing ledger data migrated correctly. 6. Tests pass for: full payout, partial payout, rejection with rollback, concurrent requests. |

---

### TASK 0.2 — Register SendPurchaseConfirmationListener (AUD-PAY-001)

| Field | Value |
|---|---|
| **Task** | Register the email listener in `AppServiceProvider` |
| **Files** | `app/Providers/AppServiceProvider.php` |
| **Risk** | LOW |
| **Dependency** | None |
| **Acceptance Criteria** | Customers receive purchase confirmation email after successful payment. Test: place order → verify mail sent. |

---

### TASK 0.3 — Create AuthorDashboardController (AUD-SYS-001)

| Field | Value |
|---|---|
| **Task** | Create the missing controller class that routes reference |
| **Files** | `app/Http/Controllers/Author/AuthorDashboardController.php` (NEW) |
| **Risk** | LOW |
| **Dependency** | None |
| **Acceptance Criteria** | `/author/dashboard` route works. Shows: available balance, published books count, submissions in review count. |

---

### TASK 0.4 — Encrypt Bank Account Data (AUD-KYC-001)

| Field | Value |
|---|---|
| **Task** | Add `encrypted` cast to bank fields in Author and PayoutRequest models |
| **Files** | `app/Models/Author.php`, `app/Models/PayoutRequest.php`, data migration script |
| **Risk** | MEDIUM — existing plaintext data must be migrated to encrypted |
| **Dependency** | None |
| **Acceptance Criteria** | `bank_account`, `bank_holder_name` encrypted at rest. `bank_account_snapshot`, `bank_holder_name_snapshot` encrypted. Existing records migrated. Admin views still display decrypted values. |

---

### TASK 0.5 — Remove Sensitive Files from Repository

| Field | Value |
|---|---|
| **Task** | Remove `ngrok.exe`, `p4i_publisher.sql` from repo. Add to `.gitignore` |
| **Files** | Root directory, `.gitignore` |
| **Risk** | LOW |
| **Dependency** | None |
| **Acceptance Criteria** | Files not in version control. `.gitignore` updated. |

---

## PHASE 1 — Architecture Stabilization (P1)

**Priority:** Must complete before production  
**Estimated effort:** 5-8 days  

---

### TASK 1.1 — Fix Cascade Deletes (AUD-DB-002, AUD-DB-003)

| Field | Value |
|---|---|
| **Task** | Change `onDelete('cascade')` to `onDelete('restrict')` on financial tables. Add `SoftDeletes` to User, Book, Order, Author. |
| **Files** | New migration to alter FK constraints, models to add `SoftDeletes` |
| **Risk** | MEDIUM — needs careful migration |
| **Dependency** | None |
| **Acceptance Criteria** | Cannot delete user with orders. Cannot delete book with order items. Soft delete works correctly. |

---

### TASK 1.2 — Create Laravel Policies (AUD-AUTH-001)

| Field | Value |
|---|---|
| **Task** | Create Policy classes for core models. Refactor controllers to use `$this->authorize()`. |
| **Files** | `app/Policies/BookPolicy.php` (NEW), `BookSubmissionPolicy.php` (NEW), `OrderPolicy.php` (NEW), `BookLicensePolicy.php` (NEW), `PayoutRequestPolicy.php` (NEW), `AuthorPolicy.php` (NEW), `AuthServiceProvider.php` |
| **Risk** | MEDIUM — must not break existing functionality |
| **Dependency** | None |
| **Acceptance Criteria** | All ownership checks centralized in policies. IDOR tests pass. No controller has inline `if ($x->user_id !== auth()->id())`. |

---

### TASK 1.3 — Add Payout Status Guards (AUD-ROY-005)

| Field | Value |
|---|---|
| **Task** | Add status validation before complete/reject payout operations |
| **Files** | `app/Services/PayoutService.php` |
| **Risk** | LOW |
| **Dependency** | TASK 0.1 (may be done together) |
| **Acceptance Criteria** | Cannot complete an already-completed/rejected payout. Cannot reject an already-completed payout. Tests verify both cases. |

---

### TASK 1.4 — Fix Webhook Security (AUD-PAY-002, AUD-PAY-003)

| Field | Value |
|---|---|
| **Task** | Increase replay protection TTL. Add gross_amount cross-verification. Store webhook fingerprints in DB. |
| **Files** | `app/Http/Controllers/MidtransWebhookController.php`, `app/Jobs/ProcessMidtransWebhook.php`, new migration for webhook_log |
| **Risk** | LOW |
| **Dependency** | None |
| **Acceptance Criteria** | Replayed webhooks rejected after initial processing. Amount mismatch logged and rejected. |

---

### TASK 1.5 — Fix submission_reviews Schema (AUD-DB-001)

| Field | Value |
|---|---|
| **Task** | Remove `'notes'` assignment from ManuscriptCurationController (it's already in `feedback`) |
| **Files** | `app/Http/Controllers/Admin/ManuscriptCurationController.php` |
| **Risk** | LOW |
| **Dependency** | None |
| **Acceptance Criteria** | Revision request, reject, and approve actions work without error. |

---

### TASK 1.6 — Add Missing Refund/Chargeback Status (AUD-PAY-005)

| Field | Value |
|---|---|
| **Task** | Add `refunded` to order status enum. Handle refund webhooks. |
| **Files** | Migration (alter enum), `ProcessMidtransWebhook.php` |
| **Risk** | MEDIUM |
| **Dependency** | TASK 1.1 (soft deletes) |
| **Acceptance Criteria** | Refund webhook: revokes licenses, reverses royalty, updates order status. |

---

### TASK 1.7 — Improve DRM Access Control (AUD-AUTH-003, AUD-DRM-001)

| Field | Value |
|---|---|
| **Task** | Replace IP binding with session-based token. Add user verification to stream endpoint. |
| **Files** | `app/Http/Controllers/DrmController.php`, `resources/views/reader.blade.php` |
| **Risk** | MEDIUM — affects all reader users |
| **Dependency** | None |
| **Acceptance Criteria** | Mobile users can read books. URL sharing doesn't grant access. Signed URL includes user identity. |

---

### TASK 1.8 — Add HTTPS Enforcement & Security Headers

| Field | Value |
|---|---|
| **Task** | Force HTTPS in production. Add X-Frame-Options, X-Content-Type-Options, CSP, HSTS headers. |
| **Files** | `app/Providers/AppServiceProvider.php`, middleware or server config |
| **Risk** | LOW |
| **Dependency** | SSL certificate configured |
| **Acceptance Criteria** | All HTTP requests redirect to HTTPS. Security headers present in responses. |

---

### TASK 1.9 — Comprehensive Test Suite

| Field | Value |
|---|---|
| **Task** | Add tests for: webhook security, duplicate payment, free book flow, DRM, payout concurrency, authorization IDOR |
| **Files** | `tests/Feature/` (multiple new test files) |
| **Risk** | LOW |
| **Dependency** | TASKs 0.1-0.5 and 1.1-1.8 |
| **Acceptance Criteria** | All critical paths covered. `php artisan test` passes 100%. |

---

## PHASE 2 — UI/UX & Design System

**Priority:** P2 — Important for user experience  
**Estimated effort:** 5-10 days  

---

### TASK 2.1 — Create Design System

| Field | Value |
|---|---|
| **Task** | Define color tokens, typography, button/card/form component variants in Tailwind config and Blade components |
| **Files** | `tailwind.config.js`, `resources/css/app.css`, Blade components |
| **Risk** | LOW |
| **Acceptance Criteria** | Consistent visual language across all pages. Single source of truth for colors, fonts, spacing. |

### TASK 2.2 — Fix Font Conflict

| Field | Value |
|---|---|
| **Task** | Align Tailwind config and layout to use same font family |
| **Files** | `tailwind.config.js`, `resources/views/components/public-layout.blade.php` |

### TASK 2.3 — Enhance Reader UI

| Field | Value |
|---|---|
| **Task** | Add toolbar with page counter, zoom, fullscreen, back button. Improve mobile UX. |
| **Files** | `public/js/secure-reader.js`, `resources/views/reader.blade.php` |

### TASK 2.4 — Build Admin Layout Component

| Field | Value |
|---|---|
| **Task** | Create dedicated admin sidebar/navigation layout component |
| **Files** | `resources/views/components/admin-layout.blade.php` (NEW) |

### TASK 2.5 — Polish Static Pages

| Field | Value |
|---|---|
| **Task** | Add real content to About, Contact, Help Center, Terms, Privacy Policy |
| **Files** | `resources/views/pages/*.blade.php` |

---

## PHASE 3 — Online Library Enhancement

**Priority:** P2-P3 — Feature development  
**Estimated effort:** 5-8 days  

---

### TASK 3.1 — Category Filter in Catalog

### TASK 3.2 — Sort Options (Newest, Price, Popular)

### TASK 3.3 — Search within My Library

### TASK 3.4 — "Continue Reading" Section

Add `last_accessed_at` to book_licenses. Sort library by most recently read.

### TASK 3.5 — Favorites / Wishlist

New table: `wishlists` (user_id, book_id). Heart icon on book cards.

### TASK 3.6 — Average Rating Display

Calculate and cache average rating for catalog display.

### TASK 3.7 — Public Author Profile Pages

New route: `/authors/{slug}` showing author bio and published books.

---

## PHASE 4 — Author Center Completion

**Priority:** P2 — Important for author experience  
**Estimated effort:** 5-8 days  

---

### TASK 4.1 — Fix Author Dashboard (TASK 0.3 extended)

### TASK 4.2 — "My Published Books" Page

Show author's published books with sales count, revenue.

### TASK 4.3 — Sales & Earnings Dashboard

Charts showing: monthly sales, earnings by book, total revenue.

### TASK 4.4 — Author Profile Edit

Allow editing pen_name, bio, bank details (with re-verification if needed).

### TASK 4.5 — Detailed Royalty Ledger View

Show per-sale breakdown: book, buyer (anonymized), amount, date, status.

### TASK 4.6 — Author Notifications

Email notifications for: KYC approved/rejected, submission status changes, payout completed.

---

## PHASE 5 — Physical Books Support

**Priority:** P3 — Future roadmap  
**Estimated effort:** 15-25 days  

---

### TASK 5.1 — Book Editions / Variants Model

Create `book_editions` table. Migrate existing book data to use editions.

### TASK 5.2 — Inventory Management

Stock tracking, low-stock alerts, stock movement log.

### TASK 5.3 — Shipping System

Shipping addresses, courier integration, shipping cost calculation.

### TASK 5.4 — Order Fulfillment Workflow

Packing → Shipped → In Transit → Delivered states.

### TASK 5.5 — Checkout Updates for Physical + Digital Mix

Cart system supporting both digital (instant) and physical (fulfillment) items.

---

## PHASE 6 — Production Hardening

**Priority:** Before public launch  
**Estimated effort:** 3-5 days  

---

### TASK 6.1 — RBAC Implementation

Roles: Super Admin, Editor, Finance, KYC Officer, Support.

### TASK 6.2 — Error Tracking Integration

Sentry or Bugsnag setup.

### TASK 6.3 — CI/CD Pipeline

GitHub Actions: test → lint → build → deploy.

### TASK 6.4 — Automated Backups

Database + file storage backup strategy.

### TASK 6.5 — Stale Order Cleanup

Scheduled command to expire pending orders after 24h.

### TASK 6.6 — Rate Limiting

Add rate limiting to: author registration, payout requests, review submission.

### TASK 6.7 — OJS Cross-Link

Add navigation link to OJS for journal access. Shared navigation element.

---

## Phase Timeline Summary

| Phase | Focus | Effort | Priority |
|---|---|---|---|
| **Phase 0** | Critical Safety | 3-5 days | ⛔ MUST |
| **Phase 1** | Architecture | 5-8 days | ⛔ MUST |
| **Phase 2** | UI/UX | 5-10 days | ⚠️ SHOULD |
| **Phase 3** | Library | 5-8 days | 🟡 NICE |
| **Phase 4** | Author Center | 5-8 days | ⚠️ SHOULD |
| **Phase 5** | Physical Books | 15-25 days | 🟡 FUTURE |
| **Phase 6** | Hardening | 3-5 days | ⚠️ SHOULD |
| **TOTAL** | — | **41-69 days** | — |
