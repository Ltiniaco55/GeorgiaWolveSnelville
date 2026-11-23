<?php

use App\Support\Localization;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Register a localized "about" route (/about and /es/nosotros) for tests,
 * because only the home page exists for now.
 */
function registerAboutRoutes(): void
{
    Route::middleware('web')->group(function (): void {
        Localization::routes(function (string $locale): void {
            Route::get(Localization::slug('about', $locale), fn () => Inertia::render('Home'))->name('about');
        });
    });

    Route::getRoutes()->refreshNameLookups();
}

test('the home page without prefix is in English', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="en">', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('locale', 'en')
            ->where('translations.home.hero.title_accent', 'wolf.')
        );
});

test('the home page under /es is in Spanish', function () {
    $this->get('/es')
        ->assertOk()
        ->assertSee('<html lang="es">', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('locale', 'es')
            ->where('translations.home.hero.title_accent', 'lobo.')
        );
});

test('an unknown page under /es is a 404 in Spanish', function () {
    $this->get('/es/no-existe')
        ->assertNotFound()
        ->assertSee('<html lang="es">', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', 404)
            ->where('locale', 'es')
            ->where('homeUrl', url('/es'))
            ->where('translations.errors.404.title', 'Perdido de la manada.')
        );
});

test('an unknown page without prefix is a 404 in English', function () {
    $this->get('/does-not-exist')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('locale', 'en')
            ->where('homeUrl', url('/'))
            ->where('translations.errors.404.title', 'Lost from the pack.')
        );
});

test('the locale is detected from the first path segment only', function (string $path, string $locale) {
    expect(Localization::localeFromPath($path))->toBe($locale);
})->with([
    ['/', 'en'],
    ['es', 'es'],
    ['es/nosotros', 'es'],
    ['/es/', 'es'],
    ['about', 'en'],
    ['espana', 'en'],
    ['en/about', 'en'],
    ['fr/about', 'en'],
]);

test('localized route URLs use the translated slug of each locale', function () {
    registerAboutRoutes();

    expect(Localization::route('home', locale: 'en'))->toBe(url('/'))
        ->and(Localization::route('home', locale: 'es'))->toBe(url('/es'))
        ->and(Localization::route('about', locale: 'en'))->toBe(url('/about'))
        ->and(Localization::route('about', locale: 'es'))->toBe(url('/es/nosotros'));
});

test('a missing slug fails loudly instead of creating a wrong URL', function () {
    Localization::slug('does_not_exist', 'es');
})->throws(InvalidArgumentException::class);

test('the switcher receives the equivalent URL of the current page in each locale', function () {
    registerAboutRoutes();

    $this->get('/es/nosotros')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'es')
            ->where('localeUrls', ['en' => url('/about'), 'es' => url('/es/nosotros')])
        );
});

test('pages declare their canonical URL and the en, es and x-default alternates', function () {
    registerAboutRoutes();

    $this->get('/es/nosotros')
        ->assertSee('<link rel="canonical" href="'.url('/es/nosotros').'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/about').'">', false)
        ->assertSee('<link rel="alternate" hreflang="es" href="'.url('/es/nosotros').'">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/about').'">', false);
});

test('error pages have no canonical or alternates and the switcher points to each home', function () {
    $this->get('/es/no-existe')
        ->assertDontSee('rel="canonical"', false)
        ->assertDontSee('hreflang=', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('localeUrls', ['en' => url('/'), 'es' => url('/es')])
        );
});

test('the default locale stays English after a Spanish request', function () {
    $this->get('/es');

    expect(app()->getLocale())->toBe('es')
        ->and(Localization::defaultLocale())->toBe('en')
        ->and(Localization::localeFromPath('about'))->toBe('en');
});
