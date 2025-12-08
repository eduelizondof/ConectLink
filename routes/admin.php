<?php

use App\Http\Controllers\Admin\CustomLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FloatingAlertController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\PlansController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\QrBusinessCardController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\VcardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for the admin panel (dashboard, organizations management).
| All routes require authentication.
|
*/

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Plans & Pricing
    Route::get('/plans', [PlansController::class, 'index'])->name('plans.index');

    // Organizations
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::match(['put', 'post'], '/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
    Route::post('/organizations/suggest-slug', [OrganizationController::class, 'suggestSlug'])->name('organizations.suggest-slug');

    // Profiles (within organization)
    Route::post('/organizations/{organization}/profiles', [ProfileController::class, 'store'])->name('profiles.store');
    Route::match(['put', 'post'], '/profiles/{profile}', [ProfileController::class, 'update'])->name('profiles.update');
    Route::put('/profiles/{profile}/settings', [ProfileController::class, 'updateSettings'])->name('profiles.settings');
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy'])->name('profiles.destroy');

    // Social Links
    Route::post('/profiles/{profile}/social-links', [SocialLinkController::class, 'store'])->name('social-links.store');
    Route::put('/social-links/{socialLink}', [SocialLinkController::class, 'update'])->name('social-links.update');
    Route::delete('/social-links/{socialLink}', [SocialLinkController::class, 'destroy'])->name('social-links.destroy');
    Route::post('/profiles/{profile}/social-links/reorder', [SocialLinkController::class, 'reorder'])->name('social-links.reorder');

    // Custom Links
    Route::post('/profiles/{profile}/custom-links', [CustomLinkController::class, 'store'])->name('custom-links.store');
    Route::put('/custom-links/{customLink}', [CustomLinkController::class, 'update'])->name('custom-links.update');
    Route::delete('/custom-links/{customLink}', [CustomLinkController::class, 'destroy'])->name('custom-links.destroy');
    Route::post('/profiles/{profile}/custom-links/reorder', [CustomLinkController::class, 'reorder'])->name('custom-links.reorder');

    // Floating Alerts
    Route::post('/profiles/{profile}/floating-alerts', [FloatingAlertController::class, 'store'])->name('floating-alerts.store');
    Route::put('/floating-alerts/{floatingAlert}', [FloatingAlertController::class, 'update'])->name('floating-alerts.update');
    Route::delete('/floating-alerts/{floatingAlert}', [FloatingAlertController::class, 'destroy'])->name('floating-alerts.destroy');

    // vCard
    Route::put('/profiles/{profile}/vcard', [VcardController::class, 'update'])->name('vcard.update');

    // QR Code & Business Cards
    Route::put('/profiles/{profile}/qr-settings', [QrBusinessCardController::class, 'updateQrSettings'])->name('qr.update');
    Route::get('/profiles/{profile}/qr-config', [QrBusinessCardController::class, 'getQrConfig'])->name('qr.config');
    Route::get('/profiles/{profile}/qr-generate', [QrBusinessCardController::class, 'generateQr'])->name('qr.generate');
    Route::get('/profiles/{profile}/qr-download', [QrBusinessCardController::class, 'downloadQr'])->name('qr.download');
    Route::get('/profiles/{profile}/business-card', [QrBusinessCardController::class, 'generateBusinessCard'])->name('business-card.download');
    Route::get('/profiles/{profile}/business-card/preview', [QrBusinessCardController::class, 'previewBusinessCard'])->name('business-card.preview');

    // Product Categories
    Route::post('/organizations/{organization}/categories', [ProductCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [ProductCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');

    // Products
    Route::post('/organizations/{organization}/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/organizations/{organization}/products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');
});





