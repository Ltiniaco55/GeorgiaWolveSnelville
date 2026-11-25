<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Registers /styleguide, which only exists in the local environment, for browser tests.
 */
function registerStyleguideRoute(): void
{
    app()->detectEnvironment(fn (): string => 'local');
    Route::middleware('web')->group(base_path('routes/web.php'));
    Route::getRoutes()->refreshNameLookups();
}

/**
 * Opens /styleguide and waits until Vue has mounted the CTAs and the fonts are in.
 *
 * @param  array<string, mixed>  $options  Playwright browser-context options.
 */
function visitStyleguideAndWaitForCtas(array $options): mixed
{
    $page = visit('/styleguide', $options);

    $page->script(<<<'JS'
        async () => {
            while (!document.querySelector('.gw-cta')) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
            await document.fonts.ready;
        }
    JS);

    return $page;
}

/**
 * Opens the home page and waits until Vue has mounted the hero.
 *
 * @param  array<string, mixed>  $options  Playwright browser-context options.
 */
function visitHome(array $options = [], string $url = '/'): mixed
{
    $page = visit($url, $options);

    $page->script(<<<'JS'
        async () => {
            while (!document.querySelector('[data-test="hero"]')) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
            await document.fonts.ready;
        }
    JS);

    return $page;
}
