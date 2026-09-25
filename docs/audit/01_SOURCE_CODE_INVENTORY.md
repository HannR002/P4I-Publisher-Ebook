# 01 — SOURCE CODE INVENTORY

**Audit Date:** 2026-09-21  
**Method:** Direct source code analysis (NOT from documentation)

---

## 1. Technology Stack

| Component | Technology | Version |
|---|---|---|
| Framework | Laravel | ^12.0 |
| PHP | PHP | ^8.2 |
| Frontend CSS | Tailwind CSS | ^3.1.0 |
| Frontend JS | Alpine.js | ^3.4.2 |
| Build Tool | Vite | ^7.0.7 |
| HTTP Client | Axios | ^1.11.0 |
| Payment | Midtrans (midtrans-php) | ^2.6 |
| Auth Scaffold | Laravel Breeze | ^2.4 (dev) |
| Testing | PHPUnit | ^11.5.50 |
| Database | SQLite (dev) / MySQL (prod) | — |
| Queue | Configurable (sync in dev) | — |
| Mail | Configurable | — |
| Session | Configurable (database/file) | — |
| Cache | Configurable (array in test) | — |
| PDF Reader | PDF.js (CDN) | 2.16.105 |

---

## 2. Application Components Inventory

### 2.1 Models (12 files)

| # | Model | File | Relationships | Used | Notes |
|---|---|---|---|---|---|
| 1 | User | `app/Models/User.php` | hasMany(BookLicense, Order, Review), hasOne(Author) | ✅ | Core auth model |
| 2 | Book | `app/Models/Book.php` | hasMany(BookLicense, RoyaltyLedger, Review), belongsTo(Author), belongsToMany(Category) | ✅ | Core product model |
| 3 | Order | `app/Models/Order.php` | belongsTo(User), hasMany(OrderItem) | ✅ | String PK (ULID), snap_token encrypted |
| 4 | OrderItem | `app/Models/OrderItem.php` | belongsTo(Order, Book) | ✅ | Price locked at checkout |
| 5 | BookLicense | `app/Models/BookLicense.php` | belongsTo(User, Book, Order) | ✅ | DRM entitlement |
| 6 | Category | `app/Models/Category.php` | belongsToMany(Book) | ✅ | Pivot table book_category |
| 7 | Review | `app/Models/Review.php` | belongsTo(User, Book) | ✅ | Star rating + comment |
| 8 | Author | `app/Models/Author.php` | belongsTo(User), hasMany(Book, BookSubmission, RoyaltyLedger, PayoutRequest) | ✅ | KYC entity, `id_card_number` encrypted |
| 9 | BookSubmission | `app/Models/BookSubmission.php` | belongsTo(Author, Category, Book), hasMany(SubmissionReview) | ✅ | Manuscript workflow |
| 10 | SubmissionReview | `app/Models/SubmissionReview.php` | belongsTo(BookSubmission, User) | ✅ | Review/feedback history |
| 11 | RoyaltyLedger | `app/Models/RoyaltyLedger.php` | belongsTo(Author, OrderItem, Book, PayoutRequest) | ✅ | Per-sale royalty record |
| 12 | PayoutRequest | `app/Models/PayoutRequest.php` | belongsTo(Author), belongsTo(User, 'processed_by') | ✅ | ULID PK, bank snapshot |

### 2.2 Controllers (27 files across 4 namespaces)

| # | Controller | Namespace | Methods | Used | Notes |
|---|---|---|---|---|---|
| 1 | Controller | Http\Controllers | — | ✅ | Base class |
| 2 | BookController | Http\Controllers | index, show, storeReview | ✅ | Public catalog |
| 3 | CheckoutController | Http\Controllers | store, show, success, pending | ✅ | Payment flow |
| 4 | MidtransWebhookController | Http\Controllers | handle | ✅ | Webhook entry |
| 5 | DrmController | Http\Controllers | generateReaderUrl, streamPdf, saveProgress | ✅ | Reader/DRM |
| 6 | LibraryController | Http\Controllers | index, orders | ✅ | My Library / Orders |
| 7 | ProfileController | Http\Controllers | edit, update, destroy | ✅ | Breeze default |
| 8 | **ReaderController** | Http\Controllers | — | ❌ DEAD | **Empty class, no methods** |
| 9 | AuthenticatedSessionController | Http\Controllers\Auth | create, store, destroy | ✅ | Login/Logout |
| 10 | RegisteredUserController | Http\Controllers\Auth | create, store | ✅ | Registration |
| 11-17 | Other Auth Controllers | Http\Controllers\Auth | Various | ✅ | Breeze standard |
| 18 | BookUploadController | Http\Controllers\Admin | index, create, store, togglePublish | ✅ | Admin book CRUD |
| 19 | UserController | Http\Controllers\Admin | index, show, toggleStatus, revokeLicense, restoreLicense | ✅ | Admin users |
| 20 | ReportController | Http\Controllers\Admin | index, exportOrders, exportBooks | ✅ | Admin reports |
| 21 | AuthorKycController | Http\Controllers\Admin | index, show, streamIdCard, approve, reject | ✅ | Admin KYC |
| 22 | ManuscriptCurationController | Http\Controllers\Admin | index, show, streamManuscript, requestRevision, reject, approveAndPublish | ✅ | Admin curation |
| 23 | AdminPayoutController | Http\Controllers\Admin | index, show, complete, reject | ✅ | Admin payouts |
| 24 | AuthorProfileController | Http\Controllers\Author | create, store, kycStatus, dashboard | ✅ | Author registration |
| 25 | BookSubmissionController | Http\Controllers\Author | index, create, store, show, edit, update | ✅ | Author submissions |
| 26 | AuthorPayoutController | Http\Controllers\Author | index, store | ✅ | Author payouts |
| 27 | **AuthorDashboardController** | Http\Controllers\Author | — | ❌ **MISSING** | **Referenced in routes but file does not exist** |

### 2.3 Middleware (5 custom files)

| # | Middleware | Alias | Used | Notes |
|---|---|---|---|---|
| 1 | EnsureUserIsAdmin | `admin` | ✅ | `is_admin` boolean check |
| 2 | EnsureUserIsAuthor | `author` | ✅ | Redirects non-authors to register |
| 3 | EnsureAuthorIsVerified | `author.verified` | ✅ | Requires `kyc_status === 'verified'` |
| 4 | EnsureAccountIsActive | — (global web) | ✅ | Logs out suspended users |
| 5 | CheckMidtransIp | — (per-route) | ✅ | IP whitelist in production |

### 2.4 Services (2 files)

| # | Service | Used | Notes |
|---|---|---|---|
| 1 | OrderIdGenerator | ✅ | ULID generation with uniqueness check |
| 2 | PayoutService | ✅ | requestPayout, rejectPayout, completePayout |

### 2.5 Events / Listeners / Jobs

| Type | Name | Queued | Used | Notes |
|---|---|---|---|---|
| Event | OrderPaidEvent | No | ✅ | Dispatched after payment success |
| Listener | RecordAuthorRoyaltyListener | Yes (ShouldQueue) | ✅ | Registered in AppServiceProvider |
| Listener | SendPurchaseConfirmationListener | Yes (ShouldQueue) | ⚠️ **NOT REGISTERED** | Imported but not in Event::listen() |
| Job | ProcessMidtransWebhook | Yes (ShouldQueue) | ✅ | Handles webhook asynchronously |

### 2.6 Form Requests (6 files)

| # | Request | Used |
|---|---|---|
| 1 | LoginRequest | ✅ |
| 2 | ProfileUpdateRequest | ✅ |
| 3 | RegisterAuthorRequest | ✅ |
| 4 | StorePayoutRequest | ✅ |
| 5 | StoreSubmissionRequest | ✅ |
| 6 | UpdateSubmissionRequest | ✅ |

### 2.7 Migrations (25 files)

| # | Migration | Table | Notes |
|---|---|---|---|
| 1 | 0001_01_01_000000 | users | Standard Laravel |
| 2 | 0001_01_01_000001 | cache, cache_locks | Standard Laravel |
| 3 | 0001_01_01_000002 | jobs, job_batches, failed_jobs | Standard Laravel |
| 4 | 2026_08_31_024127 | books | Core book table |
| 5 | 2026_08_31_024128 | orders | String PK |
| 6 | 2026_08_31_024129 | order_items | FK to orders, books |
| 7 | 2026_08_31_024130 | book_licenses | FK to users, books, orders |
| 8 | 2026_08_31_150000 | users (alter) | Add is_admin |
| 9 | 2026_09_01_023554 | book_licenses (alter) | Add unique constraint |
| 10 | 2026_09_01_025645 | Multiple (alter) | Performance indexes |
| 11 | 2026_09_01_070613 | categories | — |
| 12 | 2026_09_01_070615 | book_category | Pivot table |
| 13 | 2026_09_01_070616 | reviews | — |
| 14 | 2026_09_01_070618 | book_licenses (alter) | Add last_read_page |
| 15 | 2026_09_01_121528 | books (alter) | Add isbn, pages, publish_date |
| 16 | 2026_09_03_065408 | book_licenses (alter) | Change book_id FK cascade behavior |
| 17 | 2026_09_03_071658 | users (alter) | Add is_active |
| 18 | 2026_09_03_071704 | book_licenses (alter) | Add revocation_reason |
| 19 | 2026_09_03_073507 | authors | KYC entity |
| 20 | 2026_09_03_073514 | books (alter) | Add author_id FK |
| 21 | 2026_09_03_073522 | book_submissions | Manuscript workflow |
| 22 | 2026_09_03_073528 | submission_reviews | Action enum: request_revision, approve, reject |
| 23 | 2026_09_03_073533 | royalty_ledgers | unique(order_item_id), FK to authors, order_items, books |
| 24 | 2026_09_03_073537 | payout_requests | ULID PK, FK to authors, users |
| 25 | 2026_09_04_042504 | payout_requests + royalty_ledgers (alter) | Add bank snapshots, reference_number, payout_request_id |

### 2.8 Blade Views (~65+ files)

| Directory | Files | Notes |
|---|---|---|
| views/ (root) | welcome, dashboard, my-library, my-orders, reader | 5 files |
| views/auth/ | login, register, forgot-password, reset-password, confirm-password, verify-email | 6 files |
| views/books/ | index, show | 2 files |
| views/checkout/ | create, show, success, pending | 4 files |
| views/layouts/ | app, guest, navigation | 3 files |
| views/components/ | 15 component files | Includes public-layout, author-layout |
| views/admin/books/ | TBD | Book management |
| views/admin/users/ | TBD | User management |
| views/admin/kyc/ | TBD | KYC management |
| views/admin/submissions/ | TBD | Submission curation |
| views/admin/payouts/ | TBD | Payout management |
| views/admin/reports/ | TBD | Reports |
| views/author/ | dashboard, register, kyc-status | 3 files |
| views/author/submissions/ | TBD | Author submissions |
| views/author/payouts/ | TBD | Author payouts |
| views/emails/ | purchase_confirmation | 1 file |
| views/pages/ | about, contact, help-center, how-to-buy, privacy-policy, terms, submission | 7 files |
| views/profile/ | TBD | Profile editing |

### 2.9 Tests (11 files)

| # | Test File | Type | Coverage Area |
|---|---|---|---|
| 1 | ExampleTest.php | Feature | Basic page load |
| 2 | ProfileTest.php | Feature | Profile CRUD |
| 3 | EbookBvaTest.php | Feature | Book boundary value analysis |
| 4 | AdminUserManagementTest.php | Feature | Admin user operations |
| 5 | AdminCurationAndRoyaltyTest.php | Feature | Submission + royalty |
| 6 | AdminReportTest.php | Feature | Report generation |
| 7 | AuthorSubmissionFlowTest.php | Feature | Submission workflow |
| 8 | AuthorPayoutTest.php | Feature | Payout request |
| 9 | PurchaseNotificationTest.php | Feature | Email notification |
| 10 | AuthorArchitectureTest.php | Unit | Author model tests |
| 11 | ExampleTest.php | Unit | Basic assertion |
| Auth/ | Registration, Authentication, Password | Feature | Breeze defaults |

### 2.10 Routes

| File | Route Count | Protected |
|---|---|---|
| web.php | ~45 routes | Mixed (public + auth + admin + author) |
| auth.php | ~14 routes | Guest + Auth middleware |
| console.php | 1 command | — |
| **API routes** | **None** | No api.php file |

---

## 3. Dead / Unused Code

| Item | File | Reason |
|---|---|---|
| ReaderController | `app/Http/Controllers/ReaderController.php` | Empty class, no methods — DRM functionality is in DrmController |
| `checkout/webhook` CSRF exclusion | `bootstrap/app.php` L16 | Route does not exist (webhook is at `api/midtrans/webhook`) |
| Duplicate `is_published` validation rule | `Admin/BookUploadController.php` L68-69 | `'is_published'` key appears twice |
| ngrok.exe | Root directory | Development tool committed to repo |
| p4i_publisher.sql | Root directory | Database dump in repo — potential credential exposure |
| `tidak bole di zip#publisher ebook` | Root directory | Unknown directory with Indonesian note |
| `Laporan_Komprehensif_P4I_Publisher_Ebook.md` | Root directory | Old report — may be outdated |

---

## 4. Missing Critical Components

| Component | Status |
|---|---|
| API routes (api.php) | Not present |
| Laravel Policies | Not created |
| Gates | Not defined |
| Observers | Not used |
| Notifications | Not used (only Mailable) |
| Scheduler / Cron tasks | Not configured |
| Rate limiting config | Only on checkout (throttle:10,1) |
| CORS configuration | Default |
| Telescope / Horizon | Not installed |
| Backup strategy | Not implemented |
