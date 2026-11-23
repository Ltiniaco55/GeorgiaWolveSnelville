<?php

/**
 * Header brand: crest (72 px) + wordmark at the top; once the header turns solid,
 * the same crest at 48 px and no wordmark; back at the top, the initial state.
 * The round emblem is never used in the header.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();
});

/**
 * Brand state after scrolling to `$scrollY` and letting the 0.35 s transitions end.
 *
 * @return array<string, mixed>
 */
function brandStateAt(mixed $page, int $scrollY): array
{
    return $page->script(<<<JS
        async () => {
            const nextFrame = () => new Promise((resolve) => requestAnimationFrame(resolve));
            const header = document.querySelector('[data-test="site-header"]');

            window.scrollTo(0, {$scrollY});
            // Let the scroll event switch the state, then wait for its transitions to end
            // (not a fixed delay: on a loaded machine 0.35 s transitions run late).
            await nextFrame();
            await nextFrame();
            await Promise.race([
                Promise.all(header.getAnimations({ subtree: true }).map((animation) => animation.finished.catch(() => null))),
                new Promise((resolve) => setTimeout(resolve, 3000)),
            ]);

            const round = (value) => Math.round(value * 100) / 100;
            const crestElement = document.querySelector('[data-test="brand-crest"]');
            const crest = crestElement.getBoundingClientRect();
            const wordmark = document.querySelector('[data-test="brand-wordmark"]');
            const link = document.querySelector('[data-test="brand-link"]');
            const navLinks = [...header.querySelectorAll('nav a')].map((element) => round(element.getBoundingClientRect().left));

            return {
                isSolid: header.classList.contains('gw-header--solid'),
                headerHeight: Math.round(header.getBoundingClientRect().height),
                isIntroTarget: crestElement.hasAttribute('data-intro-crest'),
                logosInBrand: link.querySelectorAll('.gw-brand-logo').length,
                crestOpacity: getComputedStyle(crestElement).opacity,
                crestBox: [round(crest.left), round(crest.top), round(crest.width), round(crest.height)],
                wordmarkOpacity: getComputedStyle(wordmark).opacity,
                wordmarkWidth: Math.round(wordmark.getBoundingClientRect().width),
                linkName: link.getAttribute('aria-label'),
                navLinks,
            };
        }
    JS);
}

test('on scroll the header keeps the same crest, smaller, folds the wordmark and comes back at the top', function (int $width, int $height) {
    $page = visitHome(['viewport' => ['width' => $width, 'height' => $height]]);
    $page->keys('@skip-intro', 'Escape');

    $top = brandStateAt($page, 0);
    $scrolled = brandStateAt($page, 500);
    $backAtTop = brandStateAt($page, 0);

    expect($top)->toMatchArray([
        'isSolid' => false,
        'isIntroTarget' => true,
        // Crest + wordmark only: no round emblem in the header, in either state.
        'logosInBrand' => 2,
        'crestOpacity' => '1',
        'wordmarkOpacity' => '1',
        'linkName' => 'Georgia Wolves Soccer Club – Home',
    ])
        // The brand intro lands on this box: 72 px tall crest, its usual width.
        ->and(array_slice($top['crestBox'], 2))->toBe([61.45, 72])
        ->and($top['wordmarkWidth'])->toBeGreaterThan(100);

    expect($scrolled)->toMatchArray([
        'isSolid' => true,
        'isIntroTarget' => true,
        'logosInBrand' => 2,
        'crestOpacity' => '1',
        'wordmarkOpacity' => '0',
        'wordmarkWidth' => 0,
        'linkName' => 'Georgia Wolves Soccer Club – Home',
    ])
        ->and(array_slice($scrolled['crestBox'], 2))->toBe([40.97, 48])
        ->and($scrolled['headerHeight'])->toBeLessThan($top['headerHeight'])
        // The links on the right never move sideways.
        ->and($scrolled['navLinks'])->toBe($top['navLinks']);

    expect($backAtTop)->toBe($top);
})->with([
    'desktop' => [1440, 900],
    'mobile' => [390, 844],
]);

test('with reduced motion the brand changes state without a transition', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $durations = $page->script(<<<'JS'
        () => ['[data-test="brand-wordmark"]', '.gw-brand__mark']
            .map((selector) => getComputedStyle(document.querySelector(selector)).transitionDuration)
    JS);

    expect($durations)->each->toBe('0s');

    $page->script('() => window.scrollTo(0, 500)');
    $page->wait(0.1);

    expect($page->script('() => getComputedStyle(document.querySelector("[data-test=\'brand-wordmark\']")).opacity'))->toBe('0');
});

test('Snellville stays in the page title and the hero chip', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    expect($page->script('() => [document.title, document.querySelector("[data-test=\'hero\'] p").textContent.trim()]'))
        ->toBe(['Georgia Wolves Soccer Club Snellville', 'Registration open · Fall season · Snellville, GA']);
});
