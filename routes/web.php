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
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('public.home');

// SEO — declared before the CMS catch-all so they aren't swallowed by it.
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('public.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('public.robots');

Route::controller(BlogController::class)->group(function () {
    Route::get('/blog', 'index')->name('public.blog.index');
    Route::get('/blog/{post:slug}', 'show')->name('public.blog.show');
});

// ---- Public admission pipeline ----
Route::controller(RegistrationController::class)->group(function () {
    Route::get('/register', 'create')->name('public.register.create');
    Route::post('/register', 'store')->name('public.register.store');
    Route::get('/register/success', 'success')->name('public.register.success');
});

Route::controller(ResultController::class)->group(function () {
    Route::get('/result', 'form')->name('public.result.form');
    // Rate-limited to deter phone-number enumeration.
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

Route::controller(CheckoutController::class)->group(function () {
    // Reached only via a temporary signed URL from the Result Gateway.
    Route::get('/checkout/{registration}', 'show')->middleware('signed')->name('public.checkout');
    Route::post('/checkout/{registration}/razorpay', 'razorpay')->name('public.checkout.razorpay');
    Route::post('/checkout/{registration}/upi', 'upiClaim')->name('public.checkout.upi');
});

// Inbound gateway webhooks (signature-verified inside the driver; CSRF-exempt — see bootstrap/app.php).
Route::post('/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('payments.webhook');

// One-time web installer for no-CLI hosts. Inert unless INSTALL_TOKEN is set in
// .env and the ?token= matches. REMOVE INSTALL_TOKEN after installing.
Route::get('/__setup', InstallController::class)->name('app.setup');

// CMS pages — MUST stay last. Single-segment catch-all that resolves a published
// Page by slug. The negative-lookahead prevents it from ever shadowing the
// reserved top-level paths above.
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', '^(?!admin|blog|gallery|register|result|checkout|payments|__setup|up|storage).*$')
    ->name('public.page');
