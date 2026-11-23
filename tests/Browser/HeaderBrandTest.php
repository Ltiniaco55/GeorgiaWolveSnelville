<?php

/**
 * Header brand: crest + wordmark at the top, round emblem (44 px) and no wordmark
 * once the header turns solid, and back at the top again.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();
});

/**
 * Brand state after scrolling to `$scrollY` and letting the 0.35 s transition end.
 *
 * @return array<string, mixed>
 */
function brandStateAt(mixed $page, int $scrollY): array
{
    return $page->script(<<<JS
        async () => {
            window.scrollTo(0, {$scrollY});
            await new Promise((resolve) => setTimeout(resolve, 600));

            const round = (value) => Math.round(value * 100) / 100;
            const header = document.querySelector('[data-test="site-header"]');
            const crest = document.querySelector('[data-intro-crest]').getBoundingClientRect();
            const emblem = document.querySelector('[data-test="brand-emblem"]');
            const wordmark = document.querySelector('[data-test="brand-wordmark"]');
            const link = document.querySelector('[data-test="brand-link"]');
            const navLinks = [...header.querySelectorAll('nav a')].map((element) => round(element.getBoundingClientRect().left));

            return {
                isSolid: header.classList.contains('gw-header--solid'),
                headerHeight: Math.round(header.getBoundingClientRect().height),
                crestOpacity: getComputedStyle(document.querySelector('[data-intro-crest]')).opacity,
                crestBox: [round(crest.left), round(crest.top), round(crest.width), round(crest.height)],
                emblemOpacity: getComputedStyle(emblem).opacity,
                emblemHeight: Math.round(emblem.getBoundingClientRect().height),
                wordmarkOpacity: getComputedStyle(wordmark).opacity,
                wordmarkWidth: Math.round(wordmark.getBoundingClientRect().width),
                linkName: link.getAttribute('aria-label'),
                navLinks,
            };
        }
    JS);
}

test('the header brand folds into the round emblem on scroll and comes back at the top', function (int $width, int $height) {
    $page = visitHome(['viewport' => ['width' => $width, 'height' => $height]]);
    $page->keys('@skip-intro', 'Escape');

    $top = brandStateAt($page, 0);
    $scrolled = brandStateAt($page, 500);
    $backAtTop = brandStateAt($page, 0);

    expect($top)->toMatchArray([
        'isSolid' => false,
        'crestOpacity' => '1',
        'emblemOpacity' => '0',
        'wordmarkOpacity' => '1',
        'linkName' => 'Georgia Wolves Soccer Club – Home',
    ])
        // The brand intro lands on this box: 72 px tall crest, its usual width.
        ->and(array_slice($top['crestBox'], 2))->toBe([61.45, 72])
        ->and($top['wordmarkWidth'])->toBeGreaterThan(100);

    expect($scrolled)->toMatchArray([
        'isSolid' => true,
        'crestOpacity' => '0',
        'emblemOpacity' => '1',
        'emblemHeight' => 44,
        'wordmarkOpacity' => '0',
        'wordmarkWidth' => 0,
        'linkName' => 'Georgia Wolves Soccer Club – Home',
    ])
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
        () => ['[data-test="brand-wordmark"]', '[data-test="brand-emblem"]', '[data-intro-crest]']
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
