# 03 — SECURITY, PAYMENT, ROYALTY & PAYOUT AUDIT

**Audit Date:** 2026-09-21

---

## PART A: PAYMENT AUDIT

### AUD-PAY-001 — SendPurchaseConfirmationListener NOT REGISTERED

| Field | Value |
|---|---|
| **Severity** | **P0 CRITICAL** |
| **Domain** | Payment / Email |
| **File** | [`app/Providers/AppServiceProvider.php`](file:///d:/P4I_Publisher_Ebook/app/Providers/AppServiceProvider.php#L24-L30) |
| **Evidence** | `Event::listen()` at L26-29 only registers `RecordAuthorRoyaltyListener`. `SendPurchaseConfirmationListener` is imported (L7) but never registered. |
| **Current Behavior** | `OrderPaidEvent` fires but `SendPurchaseConfirmationListener` never executes. |
| **Impact** | Customers NEVER receive purchase confirmation emails after paying. |
| **Recommendation** | Add `Event::listen(OrderPaidEvent::class, SendPurchaseConfirmationListener::class)` in `boot()`. |

---

### AUD-PAY-002 — Webhook Replay Protection TTL Too Short

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Payment Security |
| **File** | [`app/Http/Controllers/MidtransWebhookController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/MidtransWebhookController.php#L37-L42) |
| **Evidence** | `Cache::put("webhook:{$webhookFingerprint}", true, now()->addMinutes(5))` — 5 minute TTL |
| **Current Behavior** | After 5 minutes, the same webhook payload can be replayed and processed again |
| **Impact** | If an attacker replays a webhook after cache expires but the job hasn't completed, or if Midtrans retries after >5 min, duplicate processing could occur. The `ProcessMidtransWebhook` job does check order status and uses `firstOrCreate`, providing secondary protection. |
| **Recommendation** | Increase TTL to at least 24 hours. Also store webhook fingerprints in the database for permanent deduplication, not volatile cache. |

---

### AUD-PAY-003 — No gross_amount Cross-Verification in Webhook

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Payment Security |
| **File** | [`app/Jobs/ProcessMidtransWebhook.php`](file:///d:/P4I_Publisher_Ebook/app/Jobs/ProcessMidtransWebhook.php#L33-L97) |
| **Evidence** | The job reads `transaction_status` and `payment_type` from payload but does NOT verify that `gross_amount` in the webhook matches `$order->gross_amount`. |
| **Current Behavior** | Signature validation in the webhook controller uses `$grossAmount` from the request, not from the database. An attacker with a valid signature could theoretically modify amounts. |
| **Impact** | Medium — the signature includes gross_amount so it would fail if tampered, BUT there's no explicit assertion that the paid amount equals what was expected. Midtrans could send partial payment notifications for some methods. |
| **Recommendation** | Add explicit check: `if ((float) $payload['gross_amount'] !== (float) $order->gross_amount) { log error; return; }` |

---

### AUD-PAY-004 — Snap Token Renewal Race Condition

| Field | Value |
|---|---|
| **Severity** | **P2 MEDIUM** |
| **Domain** | Payment |
| **File** | [`app/Http/Controllers/CheckoutController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/CheckoutController.php#L198-L227) |
| **Evidence** | `show()` method at L198 renews snap token if order `updated_at` is >50 minutes old. This calls `Snap::getSnapToken()` with a NEW order_id (the same order ID), but Midtrans may reject this if a transaction already exists for that order_id. |
| **Current Behavior** | If the original snap token was already used to initiate a transaction at Midtrans, re-generating a new snap token for the same order_id may create a conflict at Midtrans. |
| **Impact** | User may see errors on the payment page, or worse, Midtrans may associate the old transaction with the new token incorrectly. |
| **Recommendation** | Instead of regenerating, check Midtrans transaction status first. If a transaction is already initiated, don't regenerate. Consider cancelling the old Midtrans transaction before creating a new one. |

---

### AUD-PAY-005 — Missing `refunded` and `chargeback` Order States

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Payment |
| **File** | [`database/migrations/2026_08_31_024128_create_orders_table.php`](file:///d:/P4I_Publisher_Ebook/database/migrations/2026_08_31_024128_create_orders_table.php#L18) |
| **Evidence** | `enum('status', ['pending', 'success', 'failed', 'expired'])` — no `refunded` state |
| **Current Behavior** | If Midtrans sends a `refund` notification, the `ProcessMidtransWebhook` job has no handler for it — falls through without updating status. |
| **Impact** | Refunded orders remain as 'success', licenses stay active, royalty stays available. Financial reports show inflated revenue. |
| **Recommendation** | Add `refunded` status. Handle `refund` webhook by revoking licenses, reversing royalty entries. |

---

### AUD-PAY-006 — OrderItem Price Mismatch Risk

| Field | Value |
|---|---|
| **Severity** | **P2 MEDIUM** |
| **Domain** | Payment |
| **File** | [`app/Http/Controllers/CheckoutController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/CheckoutController.php#L67-L68) |
| **Evidence** | `$grossAmount = $books->sum('price')` (L68) and Midtrans receives `(int) $book->price` (L139). The `gross_amount` is calculated from decimal book prices but sent to Midtrans as integer. |
| **Current Behavior** | Midtrans requires integer amounts in IDR. If a book price is `Rp 50,000.50`, the decimal is truncated. The order stores the decimal sum but Midtrans receives the truncated integer. |
| **Impact** | Small rounding discrepancy between what's stored and what's charged. For IDR this is likely fine (prices should be whole numbers) but no validation enforces integer-only pricing. |
| **Recommendation** | Add validation that book prices in IDR must be whole numbers (integers). Add assertion that `(int) $grossAmount === array_sum(array_column($itemDetails, 'price'))`. |

---

## PART B: ROYALTY & PAYOUT AUDIT

### AUD-ROY-001 — Royalty Ledger Splitting Breaks Unique Constraint & Audit Trail

| Field | Value |
|---|---|
| **Severity** | **P0 CRITICAL** |
| **Domain** | Royalty / Payout |
| **File** | [`app/Services/PayoutService.php`](file:///d:/P4I_Publisher_Ebook/app/Services/PayoutService.php#L78-L114) |
| **Evidence** | Lines 95-112: When partial allocation occurs, the code MUTATES the original ledger's `author_earning`, `gross_sale`, `platform_earning` values AND creates a new ledger record via `$ledger->replicate()`. |
| **Current Behavior** | 1. Original ledger for order_item_id=X is mutated: `author_earning` reduced to partial amount. 2. A NEW ledger row is created with `$newLedger->save()`. This new row has the SAME `order_item_id` as the original — which violates the `unique(order_item_id)` constraint in the migration (L17 of `2026_09_03_073533`). |
| **Impact** | **Database constraint violation** — the `$newLedger->save()` will throw a unique constraint error, causing the entire payout transaction to fail. Even if the constraint didn't exist, mutating the original ledger destroys the immutable audit trail of the original sale transaction. |
| **Recommendation** | Redesign using a `payout_allocations` table: |

**Recommended Design:**
```
royalty_ledgers (IMMUTABLE — one per order_item)
    id, author_id, order_item_id (unique), book_id,
    gross_sale, author_percentage, author_earning, platform_earning
    status: available | fully_allocated | withdrawn
    
payout_allocations (NEW — many per payout)
    id, payout_request_id, royalty_ledger_id, amount
    
payout_requests
    id, author_id, amount, status, bank snapshots...
```

This way:
- Ledgers are NEVER mutated — they record the original sale fact
- Payout allocations track exactly how much of each ledger was allocated
- Partial allocations are natural: sum(allocations for ledger) ≤ ledger.author_earning
- No unique constraint violation
- Full audit trail preserved

---

### AUD-ROY-002 — Floating-Point Arithmetic on Money

| Field | Value |
|---|---|
| **Severity** | **P0 CRITICAL** |
| **Domain** | Royalty / Payout |
| **File** | [`app/Services/PayoutService.php`](file:///d:/P4I_Publisher_Ebook/app/Services/PayoutService.php#L99-L111) |
| **Evidence** | Lines 99-111 use floating-point division: `$remainingAmountToDeduct / $ledgerAmount` to calculate proportional splits of `gross_sale` and `platform_earning`. |
| **Current Behavior** | PHP float division creates IEEE 754 rounding errors. For example, `70000 * (30000 / 70000)` may not equal exactly `30000.00` — it could be `29999.999999999996`. Over many transactions, these errors accumulate. |
| **Impact** | Pennies (or even rupiahs) may appear or disappear from the system. Authors may see slightly incorrect balances. Financial reconciliation becomes impossible. |
| **Recommendation** | Use `bcmul()`, `bcdiv()`, `bcsub()` for all monetary calculations, or better yet, store amounts in integer cents (smallest currency unit). The recommended `payout_allocations` approach avoids this problem entirely since it stores exact allocation amounts. |

---

### AUD-ROY-003 — Hardcoded 70% Royalty Rate

| Field | Value |
|---|---|
| **Severity** | **P2 MEDIUM** |
| **Domain** | Royalty |
| **File** | [`app/Listeners/RecordAuthorRoyaltyListener.php`](file:///d:/P4I_Publisher_Ebook/app/Listeners/RecordAuthorRoyaltyListener.php#L36) |
| **Evidence** | `$authorPercentage = 70.00;` hardcoded at L36 |
| **Current Behavior** | All authors receive 70% royalty regardless of contract, book type, or promotion |
| **Impact** | Cannot offer different royalty rates for different authors or books. No way to handle promotional pricing or publisher agreements. |
| **Recommendation** | Store `royalty_percentage` on the `books` table or `authors` table (or a dedicated `contracts` table). Default to 70% but allow admin override per book/author. |

---

### AUD-ROY-004 — Free Book Creates Zero-Amount Royalty Entry

| Field | Value |
|---|---|
| **Severity** | **P3 LOW** |
| **Domain** | Royalty |
| **File** | [`app/Http/Controllers/CheckoutController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/CheckoutController.php#L104) |
| **Evidence** | `event(new OrderPaidEvent($order))` fires for free books (L104). `RecordAuthorRoyaltyListener` will create a royalty ledger with `gross_sale=0, author_earning=0`. |
| **Current Behavior** | Zero-value royalty records clutter the ledger table |
| **Impact** | Minor — no financial impact but adds noise to royalty reports |
| **Recommendation** | Skip royalty recording for `$item->price == 0` in `RecordAuthorRoyaltyListener` |

---

### AUD-ROY-005 — No Payout Status Check Before Complete/Reject

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Payout |
| **File** | [`app/Services/PayoutService.php`](file:///d:/P4I_Publisher_Ebook/app/Services/PayoutService.php#L130-L171) |
| **Evidence** | `rejectPayout()` and `completePayout()` don't verify `$payout->status === 'requested'` before proceeding. |
| **Current Behavior** | An admin could "complete" an already rejected payout, or "reject" an already completed one. The ledgers would be re-updated incorrectly. |
| **Impact** | Double payout, or reversal of a completed payout's ledger status |
| **Recommendation** | Add status guard: `if ($payout->status !== 'requested') throw new DomainException('...')` |

---

## PART C: AUTHENTICATION & AUTHORIZATION AUDIT

### AUD-AUTH-001 — No Laravel Policies, No Gates

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Authorization |
| **File** | Multiple controllers |
| **Evidence** | All ownership checks are manual `if` statements: `BookSubmissionController::show()` L52 checks `$submission->author_id !== Auth::user()->authorProfile->id`. `UserController::revokeLicense()` L82 checks `$license->user_id !== $user->id`. No Policy classes exist. |
| **Current Behavior** | Each controller implements its own ad-hoc authorization logic |
| **Impact** | Inconsistent, easy to miss, not centrally auditable. Easy to introduce IDOR in new controllers. |
| **Recommendation** | Create Policies for: Book, BookSubmission, Order, BookLicense, PayoutRequest, Author. Use `$this->authorize()` in controllers. |

---

### AUD-AUTH-002 — is_admin Boolean — No RBAC

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Authorization |
| **File** | [`app/Models/User.php`](file:///d:/P4I_Publisher_Ebook/app/Models/User.php#L26) / [`app/Http/Middleware/EnsureUserIsAdmin.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Middleware/EnsureUserIsAdmin.php) |
| **Evidence** | `is_admin` boolean is the only admin mechanism. All admin actions are available to any user with `is_admin=true`. |
| **Current Behavior** | A single admin can: manage books, manage users, approve KYC, curate manuscripts, approve payouts, view reports, export data |
| **Impact** | No separation of duties. A compromised admin account has access to everything. Cannot implement Finance-only, Editor-only, or KYC-officer-only roles. |
| **Recommended RBAC Design** (do not implement yet): |

```
Roles needed:
- Super Admin: Full access
- Book Editor: Manuscript curation, book management
- Finance: Payout approval, reports
- KYC Officer: Author verification
- Customer Support: User management, license management
```

---

### AUD-AUTH-003 — Missing Ownership Validation in DRM Stream

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | DRM / Authorization |
| **File** | [`app/Http/Controllers/DrmController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/DrmController.php#L74-L88) |
| **Evidence** | `streamPdf()` at L85-88 looks up license by `license_key` and `status='active'` but does NOT verify that the license belongs to the authenticated user. The method only verifies the signed URL and IP. |
| **Current Behavior** | If a signed URL is shared, anyone from the same IP can access the PDF (the signed URL contains the IP but there's no user verification in the stream method). |
| **Impact** | In a corporate/school network where many users share the same external IP, URL sharing could allow unauthorized access. |
| **Recommendation** | Add `->where('user_id', auth()->id())` to the license lookup in `streamPdf()`. However, note the stream route uses `signed` middleware not `auth` — so the user may not be authenticated during the stream request. Consider adding user_id to the signed URL parameters. |

---

## PART D: KYC / SENSITIVE DATA AUDIT

### AUD-KYC-001 — Bank Account Data Stored as Plaintext

| Field | Value |
|---|---|
| **Severity** | **P0 CRITICAL** |
| **Domain** | KYC / Privacy |
| **File** | [`app/Models/Author.php`](file:///d:/P4I_Publisher_Ebook/app/Models/Author.php#L22-L26) / [`database/migrations/2026_09_03_073507_create_authors_table.php`](file:///d:/P4I_Publisher_Ebook/database/migrations/2026_09_03_073507_create_authors_table.php#L21-L23) |
| **Evidence** | `id_card_number` has `'encrypted'` cast (L24 of Author model) ✅, but `bank_name`, `bank_account`, `bank_holder_name` have NO encryption — stored as plain `string(50/150)` in the database |
| **Current Behavior** | Bank account numbers and holder names are stored in plaintext |
| **Impact** | If the database is compromised, all author bank details are immediately exposed. This is a PII/financial data breach risk. |
| **Recommendation** | Add `'encrypted'` cast for `bank_account`, `bank_holder_name`. Consider encrypted cast for `bank_name` as well. Also apply encryption to `bank_account_snapshot` and `bank_holder_name_snapshot` in `PayoutRequest`. |

---

### AUD-KYC-002 — No Access Logging for Sensitive Document Viewing

| Field | Value |
|---|---|
| **Severity** | **P2 MEDIUM** |
| **Domain** | KYC / Compliance |
| **File** | [`app/Http/Controllers/Admin/AuthorKycController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/Admin/AuthorKycController.php#L46-L53) |
| **Evidence** | `streamIdCard()` serves the KTP document but does NOT log who viewed it |
| **Current Behavior** | No audit trail of which admin viewed which author's identity document |
| **Impact** | Cannot trace unauthorized viewing of sensitive identity documents for compliance/investigation |
| **Recommendation** | Add `Log::info('[KYC_DOCUMENT_VIEWED]', ['admin_id' => auth()->id(), 'author_id' => $author->id])` |

---

### AUD-KYC-003 — `streamIdCard()` Uses `response()->file()` Instead of Stream

| Field | Value |
|---|---|
| **Severity** | **P2 MEDIUM** |
| **Domain** | Security / Performance |
| **File** | [`app/Http/Controllers/Admin/AuthorKycController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/Admin/AuthorKycController.php#L52) |
| **Evidence** | `return response()->file(storage_path('app/' . $author->id_card_path))` loads file into memory |
| **Current Behavior** | Entire file loaded into PHP memory before sending |
| **Impact** | For large files, OOM risk. Also inconsistent with DrmController which correctly uses streaming. |
| **Recommendation** | Use `response()->stream()` with `fpassthru()` like DrmController |

---

## PART E: DRM / READER AUDIT

### AUD-DRM-001 — IP Binding Breaks on Mobile/Proxy Networks

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | DRM |
| **File** | [`app/Http/Controllers/DrmController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/DrmController.php#L37-L38) / L81-83 |
| **Evidence** | Signed URL includes `'ip' => $request->ip()` (L38). Stream endpoint validates `$request->ip() !== $request->query('ip')` (L81). |
| **Current Behavior** | Mobile users on 4G/LTE frequently change IP addresses between URL generation and PDF loading. Behind load balancers or CDN, `$request->ip()` may return the proxy IP. |
| **Impact** | Legitimate users may be locked out of reading their purchased books on mobile devices |
| **Recommendation** | Replace IP binding with session/token-based binding. Use an authenticated session + short-lived HMAC token instead of IP. |

---

### AUD-DRM-002 — Watermark is Client-Side Canvas Only

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | DRM |
| **File** | [`public/js/secure-reader.js`](file:///d:/P4I_Publisher_Ebook/public/js/secure-reader.js#L64-L68) |
| **Evidence** | Watermark at L64-68: `ctx.fillText(WATERMARK_TEXT, canvas.width / 2, canvas.height - 20)` with opacity 0.15. Rendered on HTML canvas after PDF page render. |
| **Current Behavior** | Watermark is: 1) Only rendered client-side (not embedded in PDF), 2) Very low opacity (0.15), 3) Removable by modifying JavaScript, 4) Not present if PDF is downloaded directly from the signed URL |
| **Impact** | No actual DRM protection. Anyone with DevTools access can modify the JS, remove watermark, or download the raw PDF from the stream URL before it renders. |
| **Recommendation** | **Term correction:** This is better described as "Controlled Digital Access" not "DRM". For actual protection, consider: server-side PDF watermarking (stamp user info into PDF before streaming), shorter URL TTLs, session-based token binding, device/session limiting. Do NOT implement yet. |

---

### AUD-DRM-003 — Anti-DevTools Measures Trivially Bypassable

| Field | Value |
|---|---|
| **Severity** | **P3 LOW** |
| **Domain** | DRM |
| **File** | [`public/js/secure-reader.js`](file:///d:/P4I_Publisher_Ebook/public/js/secure-reader.js#L5-L22) |
| **Evidence** | Key event blocking: F12, Ctrl+Shift+I, Ctrl+P, Ctrl+S. Context menu disabled. |
| **Current Behavior** | These can be bypassed by: opening DevTools before navigating, using browser extensions, using developer editions, or simply using the Network tab to download the PDF from the stream URL. |
| **Impact** | Security theater — provides false sense of protection |
| **Recommendation** | Accept that client-side JS protections are not real DRM. Focus on server-side controls (session limits, watermarking, access logging). |

---

## PART F: DATABASE INTEGRITY AUDIT

### AUD-DB-001 — `submission_reviews` Missing `notes` Column

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Database |
| **File** | [`database/migrations/2026_09_03_073528`](file:///d:/P4I_Publisher_Ebook/database/migrations/2026_09_03_073528_create_submission_reviews_table.php) vs [`ManuscriptCurationController.php`](file:///d:/P4I_Publisher_Ebook/app/Http/Controllers/Admin/ManuscriptCurationController.php#L78) |
| **Evidence** | Migration creates columns: `submission_id`, `reviewer_id`, `feedback`, `action`, `timestamps`. Controller writes: `'notes' => $request->feedback` at L78, L103, L155. The `notes` column does not exist in the migration. |
| **Impact** | If `$fillable` doesn't include `notes`, it's silently ignored. If it's NOT in `$fillable` and mass assignment is strict, it errors. The `SubmissionReview` model `$fillable` does NOT include `notes` — so the field is silently dropped. |
| **Recommendation** | Remove `'notes' => $request->feedback` from controller OR add `notes` column to migration. The `feedback` field already stores the same data. |

---

### AUD-DB-002 — Cascade Deletes on Financial Records

| Field | Value |
|---|---|
| **Severity** | **P1 HIGH** |
| **Domain** | Database Integrity |
| **Files** | `2026_08_31_024128` (orders), `2026_08_31_024129` (order_items), `2026_08_31_024130` (book_licenses) |
| **Evidence** | `orders.user_id → onDelete('cascade')`, `order_items.book_id → onDelete('cascade')`, `book_licenses.user_id → onDelete('cascade')` |
| **Impact** | Deleting a user cascades to orders → order_items → breaks royalty_ledger FK. Deleting a book cascades to order_items → breaks royalty_ledger FK. Financial records should NEVER be cascade-deleted. |
| **Recommendation** | Change to `onDelete('restrict')` or implement soft deletes. Never allow hard deletion of users or books that have financial transactions. |

---

### AUD-DB-003 — No Soft Deletes on Any Model

| Field | Value |
|---|---|
| **Severity** | **P2 MEDIUM** |
| **Domain** | Database |
| **Evidence** | No model uses `SoftDeletes` trait. No `deleted_at` columns in any migration. |
| **Impact** | Accidental deletion is permanent. Cannot "unpublish then delete" a book safely. Cannot "ban then clean up" a user safely. |
| **Recommendation** | Add `SoftDeletes` to: User, Book, Order, Author, BookSubmission. |

---

### AUD-DB-004 — ISBN Not Unique in Books Table

| Field | Value |
|---|---|
| **Severity** | **P3 LOW** |
| **Domain** | Database |
| **File** | [`database/migrations/2026_09_01_121528`](file:///d:/P4I_Publisher_Ebook/database/migrations/2026_09_01_121528_add_details_to_books_table.php#L15) |
| **Evidence** | `$table->string('isbn')->nullable()` — no unique constraint |
| **Impact** | Duplicate ISBNs can be entered, which violates ISBN's real-world uniqueness |
| **Recommendation** | Add `->unique()->nullable()` constraint. However, if multi-format books share the same record, each format would need its own ISBN — further argument for a `book_editions` table. |
