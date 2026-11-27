<?php

/**
 * Home hero layout (docs/design/prototype/home.html) and page transitions.
 * Geometry is measured with reduced motion, so nothing is mid-animation.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    config(['club.social' => [
        'instagram' => 'https://www.instagram.com/example',
        'facebook' => 'https://www.facebook.com/example',
        'whatsapp' => 'https://wa.me/14045550123',
    ]]);
});

/**
 * Boxes of the hero parts, in viewport pixels (null when the part is not shown).
 *
 * @return array<string, mixed>
 */
function heroLayout(mixed $page): array
{
    return $page->script(<<<'JS'
        () => {
            const box = (selector) => {
                const element = document.querySelector(selector);

                if (!element || getComputedStyle(element).display === 'none') {
                    return null;
                }

                const { top, right, bottom, left, width } = element.getBoundingClientRect();

                return { top, right, bottom, left, width };
            };
            const frame = document.querySelector('[data-test="hero-frame"]');

            return {
                frameWidth: frame.clientWidth,
                giant: box('[data-test="hero-giant"]'),
                header: box('[data-test="site-header"]'),
                title: box('#hero-title'),
                ctas: box('[data-test="hero-ctas"]'),
                facts: box('[data-test="hero-facts"]'),
                notch: box('[data-test="hero-social"]'),
                coach: box('[data-test="hero-coach-card"]'),
                ticker: box('[data-test="ticker"]'),
                viewportHeight: window.innerHeight,
            };
        }
    JS);
}

/**
 * @param  array{top: float, right: float, bottom: float, left: float}  $a
 * @param  array{top: float, right: float, bottom: float, left: float}  $b
 */
function boxesOverlap(array $a, array $b): bool
{
    return $a['left'] < $b['right'] && $b['left'] < $a['right'] && $a['top'] < $b['bottom'] && $b['top'] < $a['bottom'];
}

test('the hero parts fit together', function (string $url, int $width, int $height, bool $isMobile) {
    $page = visitHome([
        'viewport' => ['width' => $width, 'height' => $height],
        'reducedMotion' => 'reduce',
        'isMobile' => $isMobile,
        'hasTouch' => $isMobile,
    ], $url);
    $layout = heroLayout($page);

    // The giant name spans the frame: it neither overflows nor falls short.
    expect($layout['giant']['width'])->toBeLessThanOrEqual($layout['frameWidth'])
        ->and($layout['giant']['width'])->toBeGreaterThanOrEqual($layout['frameWidth'] * 0.97);

    // The title starts clearly below the header.
    expect($layout['title']['top'])->toBeGreaterThan($layout['header']['bottom'] + 16);

    // The key facts (when shown) end above the giant name's box.
    if ($layout['facts'] !== null) {
        expect($layout['facts']['bottom'])->toBeLessThanOrEqual($layout['giant']['top']);
    }

    // The social notch is there and clear of the CTAs; so is the coach card (desktop only).
    expect($layout['notch'])->not->toBeNull()
        ->and(boxesOverlap($layout['notch'], $layout['ctas']))->toBeFalse();

    if ($width >= 1100) {
        expect($layout['coach'])->not->toBeNull()
            ->and(boxesOverlap($layout['coach'], $layout['ctas']))->toBeFalse()
            ->and(boxesOverlap($layout['coach'], $layout['title']))->toBeFalse();
    } else {
        expect($layout['coach'])->toBeNull();
    }

    // The yellow ticker starts right below the hero: it does not cover it on load.
    expect(round($layout['ticker']['top']))->toEqual($layout['viewportHeight']);

    $page->assertNoJavaScriptErrors();
})->with([
    'en 1920×1080' => ['/', 1920, 1080, false],
    'en 1440×900' => ['/', 1440, 900, false],
    'en 1280×650' => ['/', 1280, 650, false],
    'en 390×844' => ['/', 390, 844, true],
    'es 1920×1080' => ['/es', 1920, 1080, false],
    'es 1440×900' => ['/es', 1440, 900, false],
    'es 1280×650' => ['/es', 1280, 650, false],
    'es 390×844' => ['/es', 390, 844, true],
]);

test('the key facts are hidden on short screens', function () {
    $page = visitHome(['viewport' => ['width' => 1280, 'height' => 650], 'reducedMotion' => 'reduce']);

    expect(heroLayout($page)['facts'])->toBeNull();
});

test('the social notch is cut out of the frame, outside its outline', function (int $width, int $height, bool $isMobile) {
    $page = visitHome([
        'viewport' => ['width' => $width, 'height' => $height],
        'reducedMotion' => 'reduce',
        'isMobile' => $isMobile,
        'hasTouch' => $isMobile,
    ]);

    // Hit-testing follows clip-path: the notch's top strip (above the icons) is not
    // part of the frame's contents, while just above the notch it is.
    expect($page->script(<<<'JS'
        () => {
            const notch = document.querySelector('[data-test="hero-social"]').getBoundingClientRect();
            const clip = document.querySelector('[data-test="hero-clip"]');
            const inContents = (x, y) => clip.contains(document.elementFromPoint(x, y));
            const middle = notch.left + notch.width / 2;

            return {
                notchIsOutside: inContents(middle, notch.top + 3),
                aboveNotchIsInside: inContents(middle, notch.top - 8),
                rightOfNotchIsInside: inContents(notch.right + 8, notch.bottom - 30),
                outlineFollowsNotch: (document.querySelector('[data-test="hero-outline"] path').getAttribute('d').match(/A/g) ?? []).length,
            };
        }
    JS))->toBe([
        'notchIsOutside' => false,
        'aboveNotchIsInside' => true,
        'rightOfNotchIsInside' => true,
        // Three rounded corners and the notch's three.
        'outlineFollowsNotch' => 6,
    ]);
})->with([
    'desktop 1440×900' => [1440, 900, false],
    'mobile 390×844' => [390, 844, true],
]);

test('without social URLs there is no notch', function () {
    config(['club.social' => ['instagram' => null, 'facebook' => null, 'whatsapp' => null]]);

    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    expect(heroLayout($page)['notch'])->toBeNull();
});

test('the giant name is refitted when the window is resized', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->resize(1024, 768);
    $page->wait(0.3);
    $layout = heroLayout($page);

    expect($layout['giant']['width'])->toBeLessThanOrEqual($layout['frameWidth'])
        ->and($layout['giant']['width'])->toBeGreaterThanOrEqual($layout['frameWidth'] * 0.97);
});

test('the flashlight lights the photo around the pointer', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    // The lamp follows the pointer, so it is off with reduced motion…
    expect($page->script(<<<'JS'
        async () => {
            window.dispatchEvent(new PointerEvent('pointermove', { pointerType: 'mouse', clientX: 1100, clientY: 400 }));
            await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

            return document.querySelector('[data-test="hero-stage"]').classList.contains('gw-photo-stage--lit');
        }
    JS))->toBeFalse();

    // …and on with motion allowed and a mouse.
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');

    expect($page->script(<<<'JS'
        async () => {
            window.dispatchEvent(new PointerEvent('pointermove', { pointerType: 'mouse', clientX: 1100, clientY: 400 }));
            await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
            const stage = document.querySelector('[data-test="hero-stage"]');
            const photo = document.querySelector('[data-test="hero-photo"]').getBoundingClientRect();

            return {
                isLit: stage.classList.contains('gw-photo-stage--lit'),
                x: Math.round(parseFloat(document.querySelector('[data-test="hero-photo"]').style.getPropertyValue('--gw-lamp-x')) + photo.left),
            };
        }
    JS))->toBe(['isLit' => true, 'x' => 1100]);
});

test('on scroll the hero stays pinned, shrinks and darkens while the sections slide over it', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');

    $state = <<<'JS'
        async () => {
            await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
            const frame = document.querySelector('[data-test="hero-frame"]');
            const heading = document.querySelector('#final-title');

            return {
                pin: getComputedStyle(document.querySelector('[data-test="hero-pin"]')).position,
                heroTop: Math.round(document.querySelector('[data-test="hero"]').getBoundingClientRect().top),
                transformed: frame.style.transform !== '',
                shade: Number(getComputedStyle(frame.querySelector('.gw-hero__shade')).opacity),
                headingRevealed: heading.classList.contains('gw-reveal--in'),
            };
        }
    JS;

    expect($page->script($state))->toMatchArray([
        'pin' => 'sticky',
        'heroTop' => 0,
        'transformed' => false,
        'shade' => 0,
        'headingRevealed' => false,
    ]);

    $page->script('() => window.scrollTo(0, 450)');

    expect($page->script($state))->toMatchArray(['heroTop' => 0, 'transformed' => true, 'shade' => 0.275]);

    // Mid-page, under the coach section, the hero is still pinned (hidden).
    $page->script('() => document.querySelector("#coach").scrollIntoView({ block: "start" })');
    $page->wait(0.2);

    expect($page->script($state)['heroTop'])->toBe(0);

    // The final heading fades in when it arrives. (Near the end, the footer, outside
    // the pinned wrapper, pushes the hidden hero away.)
    $page->script('() => document.querySelector("#final-title").scrollIntoView({ block: "center" })');
    $page->wait(0.2);

    expect($page->script($state)['headingRevealed'])->toBeTrue();
    $page->assertNoJavaScriptErrors();
});

test('with reduced motion the hero scrolls away normally and nothing fades in', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->script('() => window.scrollTo(0, 450)');
    $page->wait(0.2);

    expect($page->script(<<<'JS'
        () => ({
            pin: getComputedStyle(document.querySelector('[data-test="hero-pin"]')).position,
            heroTop: Math.round(document.querySelector('[data-test="hero"]').getBoundingClientRect().top),
            transform: document.querySelector('[data-test="hero-frame"]').style.transform,
            revealClasses: document.querySelectorAll('.gw-reveal').length,
            tickerAnimation: getComputedStyle(document.querySelector('.gw-ticker__row')).animationName,
        })
    JS))->toBe([
        'pin' => 'relative',
        'heroTop' => -450,
        'transform' => '',
        'revealClasses' => 0,
        'tickerAnimation' => 'none',
    ]);
});

test('the header crest sits inside the hero frame and the solid header floats off the edges', function (int $width, int $height, bool $isMobile) {
    $page = visitHome([
        'viewport' => ['width' => $width, 'height' => $height],
        'reducedMotion' => 'reduce',
        'isMobile' => $isMobile,
        'hasTouch' => $isMobile,
    ]);

    $boxes = <<<'JS'
        () => {
            const box = (selector) => {
                const { top, right, bottom, left } = document.querySelector(selector).getBoundingClientRect();

                return { top, right, bottom, left };
            };

            return { crest: box('[data-test="brand-crest"]'), frame: box('[data-test="hero-frame"]'), header: box('[data-test="site-header"]') };
        }
    JS;

    $top = $page->script($boxes);

    // At least 12 px between the crest and the frame's top and left edges.
    expect($top['crest']['top'] - $top['frame']['top'])->toBeGreaterThanOrEqual(12)
        ->and($top['crest']['left'] - $top['frame']['left'])->toBeGreaterThanOrEqual(12)
        ->and($top['crest']['right'])->toBeLessThanOrEqual($top['frame']['right'])
        ->and($top['crest']['bottom'])->toBeLessThanOrEqual($top['frame']['bottom']);

    $page->script('() => window.scrollTo(0, 500)');
    $page->wait(0.2);
    $scrolled = $page->script($boxes);

    // Solid: a pill that keeps the same distance to the window as the frame.
    expect($scrolled['header']['top'])->toEqual($top['frame']['top'])
        ->and($scrolled['header']['left'])->toEqual($top['frame']['left'])
        ->and($page->script('() => getComputedStyle(document.querySelector("[data-test=\'site-header\']")).borderTopLeftRadius'))->toBe('999px');
})->with([
    '1920×1080' => [1920, 1080, false],
    '1440×900' => [1440, 900, false],
    '390×844' => [390, 844, true],
]);
