<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Support\Localization;
use Illuminate\Support\Facades\Route;

/*
 * Public pages: registered once per locale (English without prefix, Spanish under /es),
 * with the translated slugs of lang/{locale}/routes.php. Route names: en.home, es.home…
 */
Localization::routes(function (string $locale): void {
    Route::get(Localization::slug('home', $locale), HomeController::class)->name('home');
    Route::get(Localization::slug('gallery', $locale), GalleryController::class)->name('gallery');
    Route::inertia(Localization::slug('programs', $locale), 'ComingSoon', ['section' => 'programs'])->name('programs');
});

if (app()->environment('local')) {
    Route::inertia('/styleguide', 'Styleguide')->name('styleguide');
}
