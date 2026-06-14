<?php

use App\Http\Controllers\InstallController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\ResultController;
use App\Http\Controllers\Public\SeoController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Single-segment slugs the CMS page catch-all must never claim.
$reserved = '^(?!admin|blog|gallery|register|result|checkout|payments|__setup|up|storage|sitemap|robots|hi|kn).*$';

/*
 | Public content + admission funnel. Defined once, then registered twice:
 |   - at the root for English (default, no prefix)
 |   - under /{locale} for hi|kn (URL::formatPathUsing keeps links in-locale)
 | Names stay canonical (public.*) at the root; the localized copies are given a
 | 'loc.' name prefix so url generation always resolves to the English names and
 | the path formatter adds the prefix when the active locale isn't English.
 */
$publicRoutes = function () {
    Route::get('/', [HomeController::class, 'index'])->name('public.home');

    Route::controller(BlogController::class)->group(function () {
        Route::get('/blog', 'index')->name('public.blog.index');
        Route::get('/blog/{post:slug}', 'show')->name('public.blog.show');
    });

    Route::controller(RegistrationController::class)->group(function () {
        Route::get('/register', 'create')->name('public.register.create');
        Route::post('/register', 'store')->name('public.register.store');
        Route::get('/register/success', 'success')->name('public.register.success');
    });

    Route::controller(ResultController::class)->group(function () {
        Route::get('/result', 'form')->name('public.result.form');
        Route::post('/result', 'lookup')->middleware('throttle:10,1')->name('public.result.lookup');
    });

    Route::controller(GalleryController::class)->group(function () {
        Route::get('/gallery', 'index')->name('public.gallery.index');
        Route::get('/gallery/{gallery:slug}', 'show')->name('public.gallery.show');
    });

    Route::controller(ContactController::class)->group(function () {
        Route::get('/contact', 'show')->name('public.contact');
        Route::post('/contact', 'store')->middleware('throttle:6,1')->name('public.contact.store');
    });
};

// English (default) — root, canonical names.
Route::group([], $publicRoutes);

// SEO — English canonical, declared before the catch-all.
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('public.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('public.robots');

// ---- Checkout (signed, transactional — kept English-only, never localized) ----
Route::controller(CheckoutController::class)->group(function () {
    Route::get('/checkout/{registration}', 'show')->middleware('signed')->name('public.checkout');
    Route::post('/checkout/{registration}/razorpay', 'razorpay')->name('public.checkout.razorpay');
    Route::post('/checkout/{registration}/upi', 'upiClaim')->name('public.checkout.upi');
});

// Inbound gateway webhooks (signature-verified inside the driver; CSRF-exempt — see bootstrap/app.php).
Route::post('/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('payments.webhook');

// One-time web installer for no-CLI hosts.
Route::get('/__setup', InstallController::class)->name('app.setup');

// Localized (hi|kn) — same funnel + a localized CMS page catch-all.
Route::prefix('{locale}')
    ->whereIn('locale', ['hi', 'kn'])
    ->middleware(SetLocale::class)
    ->name('loc.')
    ->group(function () use ($publicRoutes, $reserved) {
        $publicRoutes();
        Route::get('/{page}', [PageController::class, 'showLocalized'])->where('page', $reserved)->name('public.page');
    });

// CMS pages — MUST stay last. English single-segment catch-all.
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', $reserved)
    ->name('public.page');
