# P4I-Bench v1 Task Manifest

**Date:** 26 September 2026

This manifest details the 15 tasks evaluated in P4I-Bench v1. Each task is verified against the actual `P4I_Publisher_Ebook` repository structure. Because the `main` branch is production, we use `CONTROLLED_FIXTURE` for most coding tasks (injecting a flaw or missing feature into a disposable worktree) to maintain reproducible evaluation.

---

## 1. bug-01: Auth Fallback Fix
*   **Category**: bug-fix
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Http/Controllers/CheckoutController.php`
*   **Baseline Commit**: `main`
*   **Setup Procedure**: Remove the `auth()->check()` guard in `CheckoutController@process`.
*   **Success Criteria**: Model must re-add proper authentication check and return `401 Unauthorized`.
*   **Test Command**: `php artisan test --filter CheckoutAuthTest`
*   **Forbidden Changes**: Do not change the Midtrans integration logic.
*   **Expected Scope**: 1 file modified.
*   **Cleanup**: `git reset --hard` and delete worktree.

## 2. bug-02: N+1 Query in Library
*   **Category**: bug-fix
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Http/Controllers/LibraryController.php`
*   **Setup Procedure**: Modify `index()` to load `LibraryItem::all()` and iterate authors without eager loading.
*   **Success Criteria**: Model must add `->with('book.author')`.
*   **Test Command**: `php artisan test --filter LibraryN1Test`
*   **Forbidden Changes**: Do not change the JSON structure returned.

## 3. bug-03: Null Exception in PDF Generator
*   **Category**: bug-fix
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Http/Controllers/DrmController.php`
*   **Setup Procedure**: Inject a flaw where `$license` is accessed without null check.
*   **Success Criteria**: Model must add a null check and return `404` or `403`.
*   **Test Command**: `php artisan test --filter DrmControllerTest`
*   **Forbidden Changes**: Do not modify the watermark generation logic.

## 4. feat-01: Add "Published Date" to Book
*   **Category**: feature
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Models/Book.php`, `database/migrations/*_create_books_table.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Model must create a new migration to add `published_at` (nullable datetime) and add it to `$fillable` in `Book.php`.
*   **Test Command**: `php artisan migrate:rollback && php artisan migrate`

## 5. feat-02: Soft Delete for Reviews
*   **Category**: feature
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Models/Review.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Use `SoftDeletes` trait in `Review.php` and create migration for `deleted_at`.
*   **Test Command**: `php artisan test --filter ReviewSoftDeleteTest`

## 6. feat-03: Category REST API
*   **Category**: feature
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `routes/api.php`, `app/Http/Controllers/CategoryController.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Implement `index` and `show` endpoints returning JSON for `App\Models\Category`.
*   **Test Command**: `php artisan test --filter CategoryApiTest`

## 7. refact-01: Extract Payment Logic
*   **Category**: refactoring
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Http/Controllers/CheckoutController.php`, `app/Services/PaymentService.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Move Midtrans Snap token generation to a dedicated `PaymentService` class.
*   **Test Command**: `php artisan test --filter CheckoutPaymentServiceTest`

## 8. refact-02: Nested If-Else in Submission
*   **Category**: refactoring
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Models/BookSubmission.php`
*   **Setup Procedure**: Inject a deeply nested status check method.
*   **Success Criteria**: Model flattens the conditionals using early returns.
*   **Test Command**: `php artisan test --filter BookSubmissionRefactorTest`

## 9. test-01: OrderItem Relations Test
*   **Category**: test-gen
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `tests/Unit/OrderItemTest.php`, `app/Models/OrderItem.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Generate PHPUnit test asserting `OrderItem` belongsTo `Order` and `Book`.
*   **Test Command**: `vendor/bin/phpunit tests/Unit/OrderItemTest.php`

## 10. test-02: Profile Update Flow
*   **Category**: test-gen
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `tests/Feature/ProfileTest.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Write a feature test that validates a user can successfully update their bio.
*   **Test Command**: `vendor/bin/phpunit tests/Feature/ProfileTest.php`

## 11. sec-01: Sanitize Profile Input
*   **Category**: security
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Http/Controllers/ProfileController.php`
*   **Setup Procedure**: Inject code that echoes unescaped user input (bio) back in JSON.
*   **Success Criteria**: Implement `strip_tags` or proper escaping before saving/returning.
*   **Test Command**: `php artisan test --filter ProfileXssTest`

## 12. sec-02: Mass Assignment Guard
*   **Category**: security
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `app/Models/User.php`
*   **Setup Procedure**: Change `User.php` to use `$guarded = []`.
*   **Success Criteria**: Model must revert to strict `$fillable` list (excluding `is_admin` or `role`).
*   **Test Command**: `php artisan test --filter UserMassAssignmentTest`

## 13. db-01: Polymorphic Tags
*   **Category**: db-migration
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `database/migrations/*_create_taggables_table.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Create a migration for `tags` and `taggables` (polymorphic relation).
*   **Test Command**: `php artisan migrate:rollback && php artisan migrate`

## 14. ui-01: Alpine.js Interactive Table
*   **Category**: frontend
*   **Status**: `CONTROLLED_FIXTURE`
*   **Source Files**: `resources/views/admin/books.blade.php`
*   **Setup Procedure**: Provide a static HTML table blade template.
*   **Success Criteria**: Integrate Alpine.js `x-data` to allow sorting rows by title.
*   **Test Command**: Manual review or DOM assertion in Pest/Dusk (Mocked for now).

## 15. arch-01: Order State Machine
*   **Category**: arch-docs
*   **Status**: `VERIFIED_REAL`
*   **Source Files**: `app/Models/Order.php`
*   **Setup Procedure**: Base repo.
*   **Success Criteria**: Return a Markdown document explaining the statuses (pending, paid, expired) derived from the model or controller logic.
*   **Test Command**: Evaluated via human review (Readability, Accuracy).
