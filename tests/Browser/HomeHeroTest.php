<?php

/**
 * Home: brand intro, header, mobile menu and mobile CTA bar.
 * Each test gets a fresh browser context, so its session storage starts empty
 * and the intro plays on the first visit.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();
});

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

/**
 * State of the intro and of the page it hides.
 *
 * @return array{isPlaying: bool, scrollLocked: bool, mainInert: bool, heroTitleOpacity: string}
 */
function introState(mixed $page): array
{
    return $page->script(<<<'JS'
        () => ({
            isPlaying: document.querySelector('[data-test="brand-intro"]') !== null,
            scrollLocked: getComputedStyle(document.documentElement).overflow === 'hidden',
            mainInert: document.querySelector('#main').inert,
            heroTitleOpacity: getComputedStyle(document.querySelector('#hero-title')).opacity,
        })
    JS);
}

test('the intro plays on the first visit, locks the scroll and Esc skips it', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);

    expect(introState($page))->toMatchArray(['isPlaying' => true, 'scrollLocked' => true, 'mainInert' => true])
        ->and($page->script('() => document.activeElement.dataset.test'))->toBe('skip-intro');

    $page->keys('@skip-intro', 'Escape');
    $page->wait(0.3);

    expect(introState($page))->toBe([
        'isPlaying' => false,
        'scrollLocked' => false,
        'mainInert' => false,
        'heroTitleOpacity' => '1',
    ]);
    $page->assertNoJavaScriptErrors();
});

test('the intro ends by itself after 2.6 s and lands on the menu crest', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);

    expect(introState($page)['isPlaying'])->toBeTrue();

    $page->wait(3.2);

    expect(introState($page))->toBe([
        'isPlaying' => false,
        'scrollLocked' => false,
        'mainInert' => false,
        'heroTitleOpacity' => '1',
    ])->and($page->script('() => getComputedStyle(document.querySelector("[data-intro-crest]")).opacity'))->toBe('1');
});

test('the intro does not play again on the second visit of the session', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);

    expect(introState($page)['isPlaying'])->toBeTrue();

    $page->keys('@skip-intro', 'Escape');
    $page->refresh();
    $page->script(<<<'JS'
        async () => {
            while (!document.querySelector('[data-test="hero"]')) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
    JS);

    expect(introState($page))->toBe([
        'isPlaying' => false,
        'scrollLocked' => false,
        'mainInert' => false,
        'heroTitleOpacity' => '1',
    ]);
});

test('with reduced motion there is no intro', function () {
    $page = visitHome([
        'viewport' => ['width' => 1440, 'height' => 900],
        'reducedMotion' => 'reduce',
    ]);

    expect(introState($page))->toBe([
        'isPlaying' => false,
        'scrollLocked' => false,
        'mainInert' => false,
        'heroTitleOpacity' => '1',
    ])->and($page->script('() => document.documentElement.className'))->toBe('');
});

test('the mobile menu keeps the focus inside, closes with Esc and gives the focus back', function () {
    $page = visitHome([
        'viewport' => ['width' => 390, 'height' => 844],
        'isMobile' => true,
        'hasTouch' => true,
    ]);

    $page->keys('@skip-intro', 'Escape');
    $page->click('@menu-button');
    $page->wait(0.4);

    expect($page->script('() => document.querySelector("[data-test=\'menu-button\']").getAttribute("aria-expanded")'))->toBe('true')
        ->and($page->script('() => document.querySelector("[data-test=\'mobile-menu\']").contains(document.activeElement)'))->toBeTrue();

    $focusableCount = $page->script(<<<'JS'
        () => document.querySelectorAll('[data-test="mobile-menu"] a[href], [data-test="mobile-menu"] button').length
    JS);

    $outside = [];

    // Two full laps forwards and one backwards.
    foreach ([...array_fill(0, $focusableCount * 2, 'Tab'), ...array_fill(0, $focusableCount, 'Shift+Tab')] as $key) {
        $page->keys(':focus', $key);

        if (! $page->script('() => document.querySelector("[data-test=\'mobile-menu\']").contains(document.activeElement)')) {
            $outside[] = $key;
        }
    }

    expect($outside)->toBe([]);

    $page->keys(':focus', 'Escape');
    $page->wait(0.4);

    expect($page->script(<<<'JS'
        () => ({
            isOpen: document.querySelector('[data-test="mobile-menu"]') !== null,
            expanded: document.querySelector('[data-test="menu-button"]').getAttribute('aria-expanded'),
            focused: document.activeElement.dataset.test ?? null,
        })
    JS))->toBe(['isOpen' => false, 'expanded' => 'false', 'focused' => 'menu-button']);
});

test('the mobile CTA bar appears once the hero has been passed', function () {
    $page = visitHome([
        'viewport' => ['width' => 390, 'height' => 844],
        'isMobile' => true,
        'hasTouch' => true,
    ]);

    $page->keys('@skip-intro', 'Escape');

    $barState = <<<'JS'
        () => {
            const bar = document.querySelector('[data-test="mobile-bar"]');

            return { visible: getComputedStyle(bar).visibility === 'visible', inert: bar.inert };
        }
    JS;
    $scrollPastHero = <<<'JS'
        async () => {
            const hero = document.querySelector('[data-site-hero]');
            window.scrollTo(0, window.scrollY + hero.getBoundingClientRect().bottom + 40);
            await new Promise((resolve) => setTimeout(resolve, 700));
        }
    JS;

    expect($page->script($barState))->toBe(['visible' => false, 'inert' => true]);

    $page->script($scrollPastHero);

    expect($page->script($barState))->toBe(['visible' => true, 'inert' => false])
        ->and($page->script('() => [...document.querySelectorAll("[data-test=\'mobile-bar\'] a")].map((link) => link.textContent.trim() || link.getAttribute("aria-label"))'))
        ->toBe(['Register', 'Free training', 'Chat with us on WhatsApp']);

    $page->script('async () => { window.scrollTo(0, 0); await new Promise((resolve) => setTimeout(resolve, 700)); }');

    expect($page->script($barState))->toBe(['visible' => false, 'inert' => true]);
});

test('on desktop there is no mobile bar or hamburger and the header Register CTA is open', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);

    $page->keys('@skip-intro', 'Escape');

    expect($page->script('() => getComputedStyle(document.querySelector("[data-test=\'mobile-bar\']")).display'))->toBe('none')
        ->and($page->script('() => getComputedStyle(document.querySelector("[data-test=\'menu-button\']")).display'))->toBe('none')
        ->and($page->script('() => document.querySelector("[data-test=\'header-register\']").classList.contains("gw-cta--open")'))->toBeTrue();
});
