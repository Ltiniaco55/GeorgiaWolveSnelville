<?php

/**
 * Home, phase B part 1: the achievements ticker and the pinned horizontal
 * categories section.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();
});

/**
 * Scrolls to `fraction` (0–1) of the categories' pinned scroll and waits two frames.
 */
function scrollCategoriesTo(mixed $page, float $fraction): void
{
    $page->script(<<<JS
        async () => {
            const section = document.querySelector('#categories');
            const top = section.getBoundingClientRect().top + window.scrollY;
            window.scrollTo({ top: top + (section.offsetHeight - window.innerHeight) * {$fraction}, behavior: 'instant' });
            await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        }
    JS);
}

/**
 * @return array{progress: int, offset: float, lastRight: float, viewportRight: float}
 */
function categoriesState(mixed $page): array
{
    return $page->script(<<<'JS'
        () => {
            const track = document.querySelector('[data-test="categories-track"]');

            return {
                progress: Number(document.querySelector('[data-test="categories-progress"]').getAttribute('aria-valuenow')),
                offset: Math.round(new DOMMatrixReadOnly(getComputedStyle(track).transform).m41),
                lastRight: Math.round(track.lastElementChild.getBoundingClientRect().right),
                viewportRight: document.documentElement.clientWidth,
            };
        }
    JS);
}

test('the ticker shows the black wolf between phrases and pauses on hover and with its keyboard button', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);
    $page->script('() => window.scrollTo(0, 300)');

    $playState = '() => getComputedStyle(document.querySelector(".gw-ticker__row")).animationPlayState';

    expect($page->script('() => document.querySelectorAll("[data-test=\'ticker\'] li .text-gw-black").length'))->toBe(10);

    // Motion allowed from here on.
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');
    $page->script('() => window.scrollTo(0, 300)');

    expect($page->script($playState))->toBe('running');

    $page->hover('[data-test="ticker"]');

    expect($page->script($playState))->toBe('paused');

    $page->hover('#hero-title');
    $page->script('() => document.querySelector(".gw-ticker__toggle").focus()');
    $page->keys('.gw-ticker__toggle', 'Enter');

    expect($page->script($playState))->toBe('paused')
        ->and($page->script('() => document.querySelector(".gw-ticker__toggle").getAttribute("aria-pressed")'))->toBe('true');
});

test('the categories pin and slide sideways with the vertical scroll, up to the last panel', function (int $width, int $height, bool $isMobile) {
    $page = visitHome([
        'viewport' => ['width' => $width, 'height' => $height],
        'isMobile' => $isMobile,
        'hasTouch' => $isMobile,
    ]);
    $page->keys('@skip-intro', 'Escape');

    scrollCategoriesTo($page, 0);
    $start = categoriesState($page);

    scrollCategoriesTo($page, 0.5);
    $middle = categoriesState($page);

    scrollCategoriesTo($page, 1);
    $end = categoriesState($page);

    expect($start['progress'])->toBe(0)
        ->and($start['offset'])->toBe(0)
        ->and($middle['progress'])->toBe(50)
        ->and($middle['offset'])->toBeLessThan(0)
        ->and($end['progress'])->toBe(100)
        ->and($end['offset'])->toBeLessThan($middle['offset'])
        // The last panel ends inside the window, at the page margin.
        ->and($end['lastRight'])->toBeLessThanOrEqual($end['viewportRight'])
        ->and($end['lastRight'])->toBeGreaterThan($end['viewportRight'] - 80);

    // While pinned the section's stage stays at the top of the window.
    expect($page->script('() => Math.round(document.querySelector(".gw-cats__stage").getBoundingClientRect().top)'))->toBe(0);
    $page->assertNoJavaScriptErrors();
})->with([
    'desktop' => [1440, 900, false],
    'mobile' => [390, 844, true],
]);

test('the big number follows the mouse and the ghost number moves the other way', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');
    scrollCategoriesTo($page, 0.1);

    $offsets = $page->script(<<<'JS'
        async () => {
            const panel = document.querySelector('[data-test="category-panel"]');
            const rect = panel.getBoundingClientRect();
            panel.dispatchEvent(new PointerEvent('pointermove', { bubbles: true, pointerType: 'mouse', clientX: rect.right - 10, clientY: rect.bottom - 10 }));

            return {
                numberX: parseFloat(panel.style.getPropertyValue('--gw-number-x')),
                ghostX: parseFloat(panel.style.getPropertyValue('--gw-ghost-x')),
            };
        }
    JS);

    expect($offsets['numberX'])->toBeGreaterThan(0)
        ->and($offsets['ghostX'])->toBeLessThan(0);
});

test('focusing the closing CTA scrolls the page until its panel is in view', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');
    scrollCategoriesTo($page, 0);

    $page->script(<<<'JS'
        async () => {
            document.querySelector('[data-test="categories-cta"]').focus();
            await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        }
    JS);

    expect(categoriesState($page)['progress'])->toBe(100)
        ->and($page->script(<<<'JS'
            () => {
                const rect = document.querySelector('[data-test="categories-cta"]').getBoundingClientRect();

                return rect.left >= 0 && rect.right <= document.documentElement.clientWidth && rect.top >= 0 && rect.bottom <= window.innerHeight;
            }
        JS))->toBeTrue();
});

test('with reduced motion the categories are a swipeable carousel, not pinned', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    expect($page->script(<<<'JS'
        () => {
            const viewport = document.querySelector('.gw-cats__viewport');

            return {
                pinned: document.querySelector('.gw-cats').classList.contains('gw-cats--pinned'),
                overflowX: getComputedStyle(viewport).overflowX,
                snap: getComputedStyle(viewport).scrollSnapType,
                progressBar: document.querySelector('[data-test="categories-progress"]') !== null,
                canScroll: viewport.scrollWidth > viewport.clientWidth,
            };
        }
    JS))->toBe([
        'pinned' => false,
        'overflowX' => 'auto',
        'snap' => 'x mandatory',
        'progressBar' => false,
        'canScroll' => true,
    ]);
});
