<?php

use Illuminate\Support\Facades\Route;

/**
 * Regression tests for CtaButton on /styleguide (the route only exists locally).
 *
 * The slot around each button always reserves the open pill's width, and the
 * button opens/folds with hysteresis, so the state must never flap and the
 * layout must never shift.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    app()->detectEnvironment(fn (): string => 'local');
    Route::middleware('web')->group(base_path('routes/web.php'));
    Route::getRoutes()->refreshNameLookups();
});

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

test('the CTAs on the "On black" card do not flap at 390 px while the page is still', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 390, 'height' => 844]]);

    // This scroll position used to make "View programs" open, wrap, fold and reopen forever.
    $page->script(<<<'JS'
        () => document.querySelector('[data-test="cta-primary-light"]').scrollIntoView({ block: 'center' })
    JS);
    $page->wait(2.5);

    $page->script(<<<'JS'
        () => {
            window.gwClassChanges = 0;
            const card = document.querySelector('[data-test="cta-primary-dark"]').closest('div:has(> h3)');
            const observer = new MutationObserver((mutations) => { window.gwClassChanges += mutations.length; });
            card.querySelectorAll('.gw-cta').forEach((cta) => observer.observe(cta, { attributes: true, attributeFilter: ['class'] }));
        }
    JS);
    $page->wait(6);

    expect($page->script('() => window.gwClassChanges'))->toBe(0);
    $page->assertNoJavaScriptErrors();
});

test('each CTA variant stays open with its label visible after a 3 s hover', function (string $button) {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 1440, 'height' => 900]]);

    $page->script(<<<JS
        () => document.querySelector('[data-test="{$button}"]').scrollIntoView({ block: 'center' })
    JS);
    $page->wait(2);
    $page->hover("@{$button}");
    $page->wait(3);

    $state = $page->script(<<<JS
        () => {
            const cta = document.querySelector('[data-test="{$button}"]');
            const label = cta.querySelector('.gw-cta__label');

            return {
                isOpen: cta.classList.contains('gw-cta--open'),
                isHovered: cta.matches(':hover'),
                width: cta.offsetWidth,
                openWidth: label.scrollWidth,
                labelOpacity: getComputedStyle(label).opacity,
            };
        }
    JS);

    expect($state['isHovered'])->toBeTrue()
        ->and($state['isOpen'])->toBeTrue()
        ->and($state['width'])->toBe($state['openWidth'])
        ->and($state['labelOpacity'])->toBe('1');
})->with([
    'primary on light' => 'cta-primary-light',
    'secondary on light' => 'cta-secondary-light',
    'primary on dark' => 'cta-primary-dark',
    'secondary on dark' => 'cta-secondary-dark',
]);

test('scrolling through the styleguide causes no layout shift', function (int $width, int $height) {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => $width, 'height' => $height]]);

    $page->script(<<<'JS'
        () => {
            window.gwLayoutShift = 0;
            new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (!entry.hadRecentInput) {
                        window.gwLayoutShift += entry.value;
                    }
                });
            }).observe({ type: 'layout-shift', buffered: true });
        }
    JS);

    // Down and back up in 200 px steps, giving each CTA time to open and fold.
    $page->script(<<<'JS'
        async () => {
            const pause = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
            const height = document.documentElement.scrollHeight;

            for (let y = 0; y <= height; y += 200) {
                window.scrollTo(0, y);
                await pause(120);
            }
            await pause(1600);

            for (let y = height; y >= 0; y -= 200) {
                window.scrollTo(0, y);
                await pause(120);
            }
            await pause(1600);
        }
    JS);

    expect($page->script('() => window.gwLayoutShift'))->toEqual(0);
})->with([
    'mobile' => [390, 844],
    'desktop' => [1440, 900],
]);

test('with reduced motion the CTAs render already open', function () {
    $page = visitStyleguideAndWaitForCtas([
        'viewport' => ['width' => 390, 'height' => 844],
        'reducedMotion' => 'reduce',
    ]);

    $state = $page->script(<<<'JS'
        () => [...document.querySelectorAll('.gw-cta')].map((cta) => ({
            isOpen: cta.classList.contains('gw-cta--open'),
            fullWidth: cta.offsetWidth === cta.querySelector('.gw-cta__label').scrollWidth,
        }))
    JS);

    expect($state)->not->toBeEmpty()
        ->each->toBe(['isOpen' => true, 'fullWidth' => true]);
});
