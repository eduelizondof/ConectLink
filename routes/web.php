<?php

use App\Http\Controllers\ProfilePageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Sitemap for SEO
Route::get('/sitemap.xml', function () {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Homepage
    $sitemap .= '  <url>' . "\n";
    $sitemap .= '    <loc>https://link.cnva.mx/</loc>' . "\n";
    $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $sitemap .= '    <changefreq>daily</changefreq>' . "\n";
    $sitemap .= '    <priority>1.0</priority>' . "\n";
    $sitemap .= '  </url>' . "\n";
    
    // TODO: Add dynamic profile pages here when needed
    // You can query your database for public profiles and add them dynamically
    
    $sitemap .= '</urlset>';
    
    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';

/*
|--------------------------------------------------------------------------
| Public Profile Routes
|--------------------------------------------------------------------------
|
| These routes handle the public-facing profile pages (like Linktree).
| They should be defined last to avoid conflicts with other routes.
|
*/

// Track link clicks
Route::post('/api/links/{link}/click', [ProfilePageController::class, 'trackClick'])
    ->name('links.track');

// Public QR data endpoint (for generating QR on client)
Route::get('/api/{orgSlug}/qr-data', [ProfilePageController::class, 'getQrData'])
    ->name('profile.qr-data')
    ->where('orgSlug', '[a-z0-9\-]+');

Route::get('/api/{orgSlug}/{profileSlug}/qr-data', [ProfilePageController::class, 'getQrData'])
    ->name('profile.employee.qr-data')
    ->where(['orgSlug' => '[a-z0-9\-]+', 'profileSlug' => '[a-z0-9\-]+']);

// Download vCard - Organization primary profile
Route::get('/{orgSlug}/vcard', [ProfilePageController::class, 'downloadVcard'])
    ->name('profile.vcard')
    ->where('orgSlug', '[a-z0-9\-]+');

// Download vCard - Employee profile
Route::get('/{orgSlug}/{profileSlug}/vcard', [ProfilePageController::class, 'downloadVcard'])
    ->name('profile.employee.vcard')
    ->where(['orgSlug' => '[a-z0-9\-]+', 'profileSlug' => '[a-z0-9\-]+']);

// Organization primary profile page
Route::get('/{orgSlug}', [ProfilePageController::class, 'showOrganization'])
    ->name('profile.show')
    ->where('orgSlug', '[a-z0-9\-]+');

// Employee profile page
Route::get('/{orgSlug}/{profileSlug}', [ProfilePageController::class, 'showEmployee'])
    ->name('profile.employee.show')
    ->where(['orgSlug' => '[a-z0-9\-]+', 'profileSlug' => '[a-z0-9\-]+']);
