<?php

use App\Http\Controllers\Admin\BookUploadController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DrmController;
use App\Http\Controllers\LibraryCatalogController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ManualPaymentController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\ProfileController;
use App\Models\LibraryItem;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'latestItems' => LibraryItem::published()->with('creators')->latest('published_at')->take(8)->get(),
        'featuredBooks' => LibraryItem::published()->where('type', 'book')->with('creators')->latest('published_at')->take(4)->get(),
        'latestResearch' => LibraryItem::published()->whereIn('type', ['journal', 'journal_article'])->with('creators')->latest('published_at')->take(4)->get(),
    ]);
})->name('home');

// Static Pages
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/submission', 'pages.submission')->name('submission');
Route::view('/help-center', 'pages.help-center')->name('help-center');
Route::view('/how-to-buy', 'pages.how-to-buy')->name('how-to-buy');
Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
Route::view('/terms', 'pages.terms')->name('terms');

// Redirect dashboard default Breeze ke katalog produk (Books)
Route::get('/dashboard', function () {
    return redirect('/books');
})->name('dashboard');

use App\Http\Controllers\Admin\AdminPayoutController;
use App\Http\Controllers\Admin\AnalyticsDashboardController;
use App\Http\Controllers\Admin\AuthorKycController;
use App\Http\Controllers\Admin\FullAnalyticsController;
use App\Http\Controllers\Admin\LibraryItemController;
use App\Http\Controllers\Admin\ManuscriptCurationController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Author\AuthorBookController;
use App\Http\Controllers\Author\AuthorDashboardController;
use App\Http\Controllers\Author\AuthorPayoutController;
use App\Http\Controllers\Author\AuthorProfileController;
use App\Http\Controllers\Author\BookSubmissionController;
use App\Http\Controllers\BookController;
use App\Http\Middleware\CheckMidtransIp;

// Halaman Katalog (Public)
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{slug}', [BookController::class, 'show'])->name('books.show');

Route::get('/library', [LibraryCatalogController::class, 'index'])->name('library.index');
Route::get('/library/{libraryItem}', [LibraryCatalogController::class, 'show'])->name('library.show');
Route::get('/library/{libraryItem}/read', [LibraryCatalogController::class, 'read'])->name('library.read');
Route::get('/library/{libraryItem}/download', [LibraryCatalogController::class, 'download'])->name('library.download');
Route::get('/library/{libraryItem}/select', [LibraryCatalogController::class, 'trackClick'])->name('library.select');

// Helper route untuk redirect guest ke login dan kembali lagi ke halaman sebelumnya
Route::get('/checkout/login', function () {
    session()->put('url.intended', url()->previous());

    return redirect()->route('login');
})->name('checkout.login');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Legacy gateway routes remain available only when explicitly enabled.
    Route::middleware('feature:midtrans')->group(function () {
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:10,1');
        Route::get('/checkout/{order_id}', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::get('/payment/success', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/payment/pending', [CheckoutController::class, 'pending'])->name('checkout.pending');
    });

    Route::post('/library/{libraryItem}/order', [ManualPaymentController::class, 'store'])->name('manual-orders.store');
    Route::get('/manual-orders/{manualOrder}', [ManualPaymentController::class, 'show'])->name('manual-orders.show');
    Route::post('/manual-orders/{manualOrder}/payment-proof', [ManualPaymentController::class, 'submitProof'])->name('manual-orders.proof')->middleware('throttle:10,1');

    // User Portal — Library & Orders
    Route::get('/my-library', [LibraryController::class, 'index'])->name('my-library');
    Route::get('/my-orders', [LibraryController::class, 'orders'])->name('my-orders');

    // Secure Reader Routes
    Route::get('/reader/{bookId}', [DrmController::class, 'generateReaderUrl'])->name('drm.reader');
    Route::get('/drm/stream/{license_key}', [DrmController::class, 'streamPdf'])->name('drm.stream')->middleware('signed');
    Route::post('/drm/progress', [DrmController::class, 'saveProgress'])->name('drm.progress');

    // Reviews
    Route::post('/books/{book}/review', [BookController::class, 'storeReview'])->name('books.review.store');
});

// ─── ADMIN ROUTES ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Book management
    Route::get('/books', [BookUploadController::class, 'index'])->name('books.index');
    Route::get('/books/upload', [BookUploadController::class, 'create'])->name('books.create');
    Route::post('/books/upload', [BookUploadController::class, 'store'])->name('books.store');
    Route::patch('/books/{book}/toggle-publish', [BookUploadController::class, 'togglePublish'])->name('books.togglePublish');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::patch('/users/{user}/licenses/{license}/revoke', [UserController::class, 'revokeLicense'])->name('users.licenses.revoke');
    Route::patch('/users/{user}/licenses/{license}/restore', [UserController::class, 'restoreLicense'])->name('users.licenses.restore');

    // Reports (Phase 3C)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/orders', [ReportController::class, 'exportOrders'])->name('reports.export.orders');
    Route::get('/reports/export/books', [ReportController::class, 'exportBooks'])->name('reports.export.books');

    // Legacy financial KYC is hidden and inaccessible unless explicitly enabled.
    Route::middleware('feature:author_kyc')->group(function () {
        Route::get('/kyc', [AuthorKycController::class, 'index'])->name('kyc.index');
        Route::get('/kyc/{author}', [AuthorKycController::class, 'show'])->name('kyc.show');
        Route::get('/kyc/{author}/stream-id-card', [AuthorKycController::class, 'streamIdCard'])->name('kyc.stream-id-card');
        Route::patch('/kyc/{author}/approve', [AuthorKycController::class, 'approve'])->name('kyc.approve');
        Route::patch('/kyc/{author}/reject', [AuthorKycController::class, 'reject'])->name('kyc.reject');
    });

    // Submission Curation Routes
    Route::get('/curation', [ManuscriptCurationController::class, 'index'])->name('submissions.index');
    Route::get('/curation/{submission}', [ManuscriptCurationController::class, 'show'])->name('submissions.show');
    Route::get('/curation/{submission}/stream-manuscript', [ManuscriptCurationController::class, 'streamManuscript'])->name('submissions.stream');
    Route::post('/curation/{submission}/request-revision', [ManuscriptCurationController::class, 'requestRevision'])->name('submissions.request-revision');
    Route::post('/curation/{submission}/reject', [ManuscriptCurationController::class, 'reject'])->name('submissions.reject');
    Route::post('/curation/{submission}/approve-publish', [ManuscriptCurationController::class, 'approveAndPublish'])->name('submissions.approve-publish');

    // Disabled legacy payout subsystem.
    Route::middleware('feature:payout')->group(function () {
        Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/{payout}', [AdminPayoutController::class, 'show'])->name('payouts.show');
        Route::post('/payouts/{payout}/complete', [AdminPayoutController::class, 'complete'])->name('payouts.complete');
        Route::post('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('payouts.reject');
    });

    Route::get('/library', [LibraryItemController::class, 'index'])->name('library.index');
    Route::get('/library/create', [LibraryItemController::class, 'create'])->name('library.create');
    Route::post('/library', [LibraryItemController::class, 'store'])->name('library.store');
    Route::get('/library/{libraryItem}/edit', [LibraryItemController::class, 'edit'])->name('library.edit');
    Route::put('/library/{libraryItem}', [LibraryItemController::class, 'update'])->name('library.update');
    Route::get('/payments', [PaymentVerificationController::class, 'index'])->name('payments.index');
    Route::get('/payments/{paymentSubmission}', [PaymentVerificationController::class, 'show'])->name('payments.show');
    Route::get('/payments/{paymentSubmission}/proof', [PaymentVerificationController::class, 'proof'])->name('payments.proof');
    Route::post('/payments/{paymentSubmission}/verify', [PaymentVerificationController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{paymentSubmission}/reject', [PaymentVerificationController::class, 'reject'])->name('payments.reject');
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::get('/analytics', [AnalyticsDashboardController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/full', [FullAnalyticsController::class, 'index'])->name('analytics.full');
});
// ─────────────────────────────────────────────────────────────────────────────

// ─── AUTHOR PORTAL ROUTES (Phase 5C) ──────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/author/register', [AuthorProfileController::class, 'create'])->name('author.register');
    Route::post('/author/register', [AuthorProfileController::class, 'store']);
    Route::get('/author/kyc-status', [AuthorProfileController::class, 'kycStatus'])->name('author.kyc-status')->middleware('feature:author_kyc');
});

Route::middleware(['auth', 'author', 'author.verified'])->prefix('author')->name('author.')->group(function () {
    Route::get('/dashboard', [AuthorDashboardController::class, 'index'])->name('dashboard');

    // Payout routes are unavailable unless explicitly enabled.
    Route::middleware('feature:payout')->group(function () {
        Route::get('/payouts', [AuthorPayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts', [AuthorPayoutController::class, 'store'])->name('payouts.store')->middleware('throttle:10,1');
    });

    // Submission routes
    Route::resource('submissions', BookSubmissionController::class);

    // Published Books route
    Route::get('/books', [AuthorBookController::class, 'index'])->name('books.index');
});
// ─────────────────────────────────────────────────────────────────────────────

// Midtrans Webhook (Harus di-exclude dari CSRF di bootstrap/app.php)
Route::post('/api/midtrans/webhook', [MidtransWebhookController::class, 'handle'])->middleware(['feature:midtrans', CheckMidtransIp::class]);

require __DIR__.'/auth.php';
