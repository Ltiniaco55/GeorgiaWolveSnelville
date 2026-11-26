<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
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

    // Registration templates (free training, monthly, annual): 5 submissions a minute per IP.
    Route::get(Localization::slug('free_training', $locale), [RegistrationController::class, 'freeTraining'])->name('free_training');
    Route::post(Localization::slug('free_training', $locale), [RegistrationController::class, 'store'])->middleware('throttle:forms')->name('free_training.store');
    Route::get(Localization::slug('free_training_thanks', $locale), [RegistrationController::class, 'received'])->name('free_training_thanks');
    Route::get(Localization::slug('register', $locale), [RegistrationController::class, 'register'])->name('register');
    Route::post(Localization::slug('register', $locale), [RegistrationController::class, 'store'])->middleware('throttle:forms')->name('register.store');
    Route::get(Localization::slug('register_success', $locale), [RegistrationController::class, 'received'])->name('register_success');

    // Legal pages: one template, texts in lang/{locale}/legal.php (drafts pending legal review).
    foreach (['privacy', 'terms', 'refund_policy', 'liability_waiver', 'media_release', 'accessibility'] as $document) {
        Route::inertia(Localization::slug($document, $locale), 'LegalPage', ['document' => $document])->name($document);
    }
});

if (app()->environment('local')) {
    Route::inertia('/styleguide', 'Styleguide')->name('styleguide');
}
