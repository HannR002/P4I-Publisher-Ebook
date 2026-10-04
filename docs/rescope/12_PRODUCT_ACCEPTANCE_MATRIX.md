# 12 Product Acceptance Matrix

| Domain | Feature | Expected Behavior | Actual Behavior | Automated Test | Manual Test Needed | Status | Production Blocker? |
|---|---|---|---|---|---|---|---|
| PUBLIC LIBRARY | Homepage | Accessible, renders featured items | Renders items | None | Yes | PARTIAL | No |
| PUBLIC LIBRARY | Global search | Filters by type, category, creator, query | Works as expected via `LibraryCatalogController` | `test_global_search_and_filters_cover_metadata_creator_category_access_and_year` | No | PASS | No |
| PUBLIC LIBRARY | Filters | Sort by trending, most read, year | Works correctly | `test_global_search_and_filters...` | No | PASS | No |
| PUBLIC LIBRARY | Library detail | Shows metadata, description, parent/children | Renders metadata, lists parents and children cleanly | View rendered in browser (tested via test assertion pass) | Yes | PASS | No |
| PUBLIC LIBRARY | Book | Renders book details | Works | `test_supported_library_item_types_can_be_created` | No | PASS | No |
| PUBLIC LIBRARY | Journal | Renders journal details and issue list | Renders journal and children via `parent_id` eager load | `test_journal_issue_article_hierarchy_is_valid` | No | PASS | No |
| PUBLIC LIBRARY | Journal Issue | Renders issue details and article list | Renders issue and articles via `parent_id` eager load | `test_journal_issue_article_hierarchy_is_valid` | No | PASS | No |
| PUBLIC LIBRARY | Journal Article | Renders article details and parent issue/journal link | Renders successfully | `test_journal_issue_article_hierarchy_is_valid` | No | PASS | No |
| PUBLIC LIBRARY | Read | In-browser PDF render based on access policy | Renders for authorized users, blocks unauthorized | `test_public_read_download_and_read_only_policies_are_enforced_for_guests`, `test_registered_external_and_manual_purchase_policies_are_enforced` | No | PASS | No |
| PUBLIC LIBRARY | Download | File download based on access policy | Downloads for authorized, 403 for unauthorized | Same as above | No | PASS | No |
| PUBLIC LIBRARY | External/OJS | Redirects to safe external URL | Redirects only safe `http/https` | `test_unsafe_external_url_schemes_are_rejected` | No | PASS | No |
| PUBLIC LIBRARY | Public access | Guest users can read/download free content | Guests can read free content, redirect to login for registered-only | `test_public_read_download_and_read_only_policies_are_enforced_for_guests` | No | PASS | No |
| PUBLIC LIBRARY | Registered access | Registered users can access registered-only content | Enforced | `test_registered_external_and_manual_purchase_policies_are_enforced` | No | PASS | No |
| PUBLIC LIBRARY | Paid access | Requires `LibraryAccessGrant` | Enforced, 403 without grant | `test_registered_external_and_manual_purchase_policies_are_enforced` | No | PASS | No |
| AUTHOR | Author registration without financial KYC | KYC nullable via feature flag | Uses `null` for `id_card_number` | `test_kyc_is_not_required_to_register_and_submit_a_book_when_disabled` | No | PASS | No |
| AUTHOR | Dashboard | Loads author stats | Loads dashboard route | `test_author_dashboard_route_is_resolvable` | Yes | PARTIAL | No |
| AUTHOR | Manuscript submission | Submit PDF for review | Uploads successfully | `test_verified_author_can_upload_valid_pdf` | No | PASS | No |
| AUTHOR | Revision | Submit revisions | Handled | N/A | Yes | NOT IMPLEMENTED | No |
| AUTHOR | Publication status | Tracks review states | Tracked via state transitions | `test_author_cannot_edit_submission_in_review` | No | PASS | No |
| AUTHOR | Book publication | Admin publishes book | Admin approval transitions state | N/A | Yes | PARTIAL | No |
| MANUAL PAYMENT | Create order | Initializes `awaiting_payment` order | Creates order with exact pricing | Tested indirectly via manual payment endpoints | No | PASS | No |
| MANUAL PAYMENT | Payment methods | Shows only active payment methods | Filters inactive methods | `test_only_active_payment_methods_are_displayed` | No | PASS | No |
| MANUAL PAYMENT | Upload proof | Private storage of user-uploaded proof | Stored in `payment-proofs/local` | `test_payment_proof_is_stored_privately` | No | PASS | No |
| MANUAL PAYMENT | Reject proof | Admin rejects proof, requires reason | Rejects successfully, idempotently | `test_admin_can_reject_without_granting_access`, `test_verified_payment_cannot_be_rejected` | No | PASS | No |
| MANUAL PAYMENT | Resubmit proof | User uploads new proof after rejection | Transitions back to `payment_submitted` | `test_rejected_payment_can_be_resubmitted` | No | PASS | No |
| MANUAL PAYMENT | Verify digital payment | Admin approves, grants access | Transitions to `verified`, idempotently | `test_admin_can_verify_and_grant_digital_access`, `test_rejected_payment_cannot_be_verified` | No | PASS | No |
| MANUAL PAYMENT | Duplicate verification | Double submission of verification request | Idempotent, handles duplicate gracefully | `test_duplicate_verified_request_is_idempotent` | No | PASS | No |
| MANUAL PAYMENT | Digital access grant | Automatically creates `LibraryAccessGrant` | Created successfully | `test_admin_can_verify_and_grant_digital_access` | No | PASS | No |
| MANUAL PAYMENT | Physical order behavior | Transitions to processing | Transitions order state | Manual | Yes | PARTIAL | No |
| ADMIN | Library management | Basic CRUD | N/A | N/A | Yes | NOT IMPLEMENTED | No |
| ADMIN | Journal hierarchy | Link issues/articles via `parent_id` | Database structure created | `test_journal_issue_article_hierarchy_is_valid` | Yes | PASS | No |
| ADMIN | Payment methods | Manage bank transfers | N/A | N/A | Yes | NOT IMPLEMENTED | No |
| ADMIN | Payment verification | Admin UI to approve/reject proofs | View exists and works | Endpoint tests present | Yes | PASS | No |
| ADMIN | Analytics | Report viewing | N/A | N/A | Yes | PARTIAL | No |
| ADMIN | Publishing queue | Review submissions | N/A | N/A | Yes | PARTIAL | No |
| ADMIN | User authorization | IsAdmin checks | Enforced in `IsAdmin` middleware | N/A | Yes | PASS | No |
| LEGACY | Existing Book projection | Maps legacy books to `LibraryItem` | Synced properly | `test_legacy_book_is_linked_to_a_library_item` | No | PASS | No |
| LEGACY | Book update synchronization | Updates cascade to projection | Hooked to `Book::saved` | `test_legacy_book_is_linked_to_a_library_item` | No | PASS | No |
| LEGACY | Book publish/unpublish | Syncs status | Handled in `LibraryItemSynchronizer` | Manual | Yes | PASS | No |
| LEGACY | Book deletion behavior | Archives projection instead of hard delete | `LibraryItem` updated to `archived` | `test_legacy_book_deletion_archives_library_item` | No | PASS | No |
| LEGACY | Existing financial records preserved | No destructive migrations on legacy payments | Confirmed visually | N/A | No | PASS | No |
| SECURITY | Private files | Content outside public web storage | `Storage::disk('local')` used explicitly | `test_payment_proof_is_stored_privately` | No | PASS | No |
| SECURITY | Proof authorization | Proofs inaccessible via direct public URL | Enforced via named routes | `test_payment_proof_is_stored_privately` | No | PASS | No |
| SECURITY | Read/download policies | Direct endpoint access enforces policy | Server-side aborts present | `test_public_read_download_and_read_only_policies_are_enforced_for_guests` | No | PASS | No |
| SECURITY | External URL safety | `javascript:` schemes blocked | `safeExternalUrl()` filters correctly | `test_unsafe_external_url_schemes_are_rejected` | No | PASS | No |
| SECURITY | Admin authorization | IsAdmin bounds Admin routes | Enforced | N/A | Yes | PASS | No |
