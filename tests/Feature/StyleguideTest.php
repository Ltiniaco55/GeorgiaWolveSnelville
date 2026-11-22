<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('the styleguide route does not exist outside the local environment', function () {
    expect(Route::has('styleguide'))->toBeFalse();

    $this->get('/styleguide')->assertNotFound();
});

test('the styleguide renders in the local environment', function () {
    app()->detectEnvironment(fn (): string => 'local');
    Route::middleware('web')->group(base_path('routes/web.php'));
    Route::getRoutes()->refreshNameLookups();

    expect(Route::has('styleguide'))->toBeTrue();

    $this->get('/styleguide')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Styleguide'));
});
