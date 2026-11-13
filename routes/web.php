<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Site;
use Illuminate\Support\Facades\Route;

/*
 * Reimagin OS routing.
 *
 * The admin panel lives under /admin and is mounted first. Storefront routes
 * sit at the domain root and are deliberately last: /{slug} is the catch-all
 * for CMS pages, so anything more specific must be declared above it.
 */

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/* ------------------------------------------------------------------ admin */

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    /* Website ------------------------------------------------------------ */
    Route::resource('pages', Admin\PageController::class);
    Route::get('pages/{page}/builder', [Admin\PageBuilderController::class, 'edit'])->name('pages.builder');
    Route::post('pages/{page}/content', [Admin\PageBuilderController::class, 'update'])->name('pages.content');
    Route::post('pages/{page}/publish', [Admin\PageBuilderController::class, 'publish'])->name('pages.publish');
    Route::post('pages/{page}/unpublish', [Admin\PageBuilderController::class, 'unpublish'])->name('pages.unpublish');
    Route::post('pages/{page}/duplicate', [Admin\PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::get('pages/{page}/preview', [Admin\PageBuilderController::class, 'preview'])->name('pages.preview');
    Route::get('pages/{page}/revisions', [Admin\PageController::class, 'revisions'])->name('pages.revisions');
    Route::post('pages/{page}/revisions/{revision}/restore', [Admin\PageController::class, 'restore'])->name('pages.revisions.restore');

    Route::resource('posts', Admin\BlogPostController::class);
    Route::resource('services', Admin\ServiceController::class)->except('show');
    Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
    Route::resource('projects', Admin\ProjectController::class)->except('show');
    Route::middleware('role:manager')->group(function () {
        Route::get('navigation', [Admin\NavigationController::class, 'index'])->name('navigation.index');
        Route::post('navigation', [Admin\NavigationController::class, 'store'])->name('navigation.store');
        Route::put('navigation/{item}', [Admin\NavigationController::class, 'update'])->name('navigation.update');
        Route::delete('navigation/{item}', [Admin\NavigationController::class, 'destroy'])->name('navigation.destroy');
    });

    Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
    Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
    Route::put('media/{medium}', [Admin\MediaController::class, 'update'])->name('media.update');
    Route::delete('media/{medium}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('media/picker', [Admin\MediaController::class, 'picker'])->name('media.picker');

    /* Commerce ----------------------------------------------------------- */
    Route::resource('products', Admin\ProductController::class)->except('show');
    Route::resource('categories', Admin\CategoryController::class)->except('show');
    Route::resource('orders', Admin\OrderController::class)->only(['index', 'show', 'update']);
    Route::post('orders/{order}/payments', [Admin\OrderController::class, 'storePayment'])->name('orders.payments.store');
    Route::get('quotations', [Admin\QuotationController::class, 'index'])->name('quotations.index');
    Route::get('quotations/create', [Admin\QuotationController::class, 'create'])->name('quotations.create');
    Route::post('quotations', [Admin\QuotationController::class, 'store'])->name('quotations.store');
    Route::get('quotations/{quotation}', [Admin\QuotationController::class, 'show'])->name('quotations.show');
    Route::put('quotations/{quotation}', [Admin\QuotationController::class, 'update'])->name('quotations.update');
    Route::post('quotations/{quotation}/send', [Admin\QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/approve', [Admin\QuotationController::class, 'approve'])->name('quotations.approve');
    Route::post('quotations/{quotation}/reject', [Admin\QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('quotations/{quotation}/convert', [Admin\QuotationController::class, 'convert'])->name('quotations.convert');
    Route::resource('appointments', Admin\AppointmentController::class)->except('show');

    /* Customers ---------------------------------------------------------- */
    Route::resource('leads', Admin\LeadController::class)->except('destroy');
    Route::post('leads/{lead}/status', [Admin\LeadController::class, 'changeStatus'])->name('leads.status');
    Route::post('leads/{lead}/assign', [Admin\LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/{lead}/convert', [Admin\LeadController::class, 'convert'])->name('leads.convert');
    Route::post('leads/{lead}/note', [Admin\LeadController::class, 'note'])->name('leads.note');
    Route::resource('customers', Admin\CustomerController::class)->except('destroy');
    Route::get('conversations', [Admin\ConversationController::class, 'index'])->name('conversations.index');
    Route::get('conversations/{conversation}', [Admin\ConversationController::class, 'show'])->name('conversations.show');
    Route::post('conversations/{conversation}/reply', [Admin\ConversationController::class, 'reply'])->name('conversations.reply');
    Route::post('conversations/{conversation}/status', [Admin\ConversationController::class, 'status'])->name('conversations.status');
    Route::middleware('role:manager')->group(function () {
        Route::get('ai', [Admin\AiAgentController::class, 'index'])->name('ai.index');
        Route::put('ai', [Admin\AiAgentController::class, 'update'])->name('ai.update');
    });

    /* Operations --------------------------------------------------------- */
    Route::middleware('role:manager')->group(function () {
        Route::get('inventory', [Admin\InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/{product}/adjust', [Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('purchasing', [Admin\PurchasingController::class, 'index'])->name('purchasing.index');
        Route::post('purchasing', [Admin\PurchasingController::class, 'store'])->name('purchasing.store');
        Route::post('purchasing/{purchaseOrder}/receive', [Admin\PurchasingController::class, 'receive'])->name('purchasing.receive');
        Route::get('production', [Admin\ProductionController::class, 'index'])->name('production.index');
        Route::post('production/{order}/advance', [Admin\ProductionController::class, 'advance'])->name('production.advance');
        Route::post('production/stages/{stage}', [Admin\ProductionController::class, 'stageStatus'])->name('production.stage');
        Route::get('finance', [Admin\FinanceController::class, 'index'])->name('finance.index');
    });

    /* Growth ------------------------------------------------------------- */
    Route::middleware('role:manager')->group(function () {
        Route::get('seo', [Admin\SeoController::class, 'index'])->name('seo.index');
        Route::put('seo/{page}', [Admin\SeoController::class, 'update'])->name('seo.update');
        Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    });

    /* System ------------------------------------------------------------- */
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', Admin\UserController::class)->except('show');
        Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('security', [Admin\SecurityController::class, 'index'])->name('security.index');
        Route::delete('security/sessions/{session}', [Admin\SecurityController::class, 'destroySession'])->name('security.sessions.destroy');
    });
});

/* -------------------------------------------------------------- storefront */

Route::get('/', Site\HomeController::class)->name('site.home');
Route::get('/shop', [Site\ShopController::class, 'index'])->name('site.shop');
Route::get('/shop/{product:slug}', [Site\ShopController::class, 'show'])->name('site.products.show');
Route::get('/category/{category:slug}', [Site\ShopController::class, 'category'])->name('site.categories.show');

Route::get('/cart', [Site\CartController::class, 'index'])->name('site.cart');
Route::post('/cart', [Site\CartController::class, 'store'])->name('site.cart.store');
Route::patch('/cart/{row}', [Site\CartController::class, 'update'])->name('site.cart.update');
Route::delete('/cart/{row}', [Site\CartController::class, 'destroy'])->name('site.cart.destroy');
Route::get('/checkout', [Site\CheckoutController::class, 'create'])->name('site.checkout');
Route::post('/checkout', [Site\CheckoutController::class, 'store'])->name('site.checkout.store');
Route::get('/order/{reference}', [Site\CheckoutController::class, 'show'])->name('site.order');

Route::get('/journal', [Site\BlogController::class, 'index'])->name('site.blog');
Route::get('/journal/{post:slug}', [Site\BlogController::class, 'show'])->name('site.blog.show');
Route::get('/services', [Site\ServiceController::class, 'index'])->name('site.services');
Route::get('/projects', [Site\ProjectController::class, 'index'])->name('site.projects');
Route::get('/projects/{project:slug}', [Site\ProjectController::class, 'show'])->name('site.projects.show');

Route::get('/contact', [Site\ContactController::class, 'index'])->name('site.contact');
Route::post('/contact', [Site\ContactController::class, 'store'])->middleware('throttle:6,1')->name('site.contact.store');
Route::post('/appointments', [Site\ContactController::class, 'book'])->middleware('throttle:6,1')->name('site.appointments.store');

/* CMS pages are last: this catch-all must not shadow anything above it. */
Route::get('/{page:slug}', [Site\PageController::class, 'show'])->name('site.pages.show');
