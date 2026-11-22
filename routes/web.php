<?php

use App\Support\Localization;
use Illuminate\Support\Facades\Route;

/*
 * Public pages: registered once per locale (English without prefix, Spanish under /es),
 * with the translated slugs of lang/{locale}/routes.php. Route names: en.home, es.home…
 */
Localization::routes(function (string $locale): void {
    Route::inertia(Localization::slug('home', $locale), 'Home')->name('home');
});

if (app()->environment('local')) {
    Route::inertia('/styleguide', 'Styleguide')->name('styleguide');
}
