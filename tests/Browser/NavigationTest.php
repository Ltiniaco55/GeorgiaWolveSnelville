<?php

/**
 * Site navigation: Pricing, About, Leagues and FAQ are sections of the home page;
 * Programs is a "Coming soon" page; Teams and Contact are not linked.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();
});

test('on the home page the menu jumps to its sections without reloading the page', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);
    $page->script('() => { window.gwSameDocument = true; }');

    $page->click('[data-test="site-header"] nav a[href$="#pricing"]');
    $page->wait(0.3);

    expect($page->script(<<<'JS'
        () => ({
            sameDocument: window.gwSameDocument === true,
            hash: location.hash,
            // Clear of the fixed header pill (scroll-padding-top).
            top: Math.round(document.querySelector('#pricing').getBoundingClientRect().top),
            headerBottom: Math.round(document.querySelector('[data-test="site-header"]').getBoundingClientRect().bottom),
        })
    JS))->toMatchArray(['sameDocument' => true, 'hash' => '#pricing'])
        ->and($page->script('() => document.querySelector("#pricing").getBoundingClientRect().top >= document.querySelector("[data-test=\'site-header\']").getBoundingClientRect().bottom'))->toBeTrue();
});

test('from another page a home section link lands on that section', function () {
    $page = visit('/gallery', ['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->click('[data-test="site-header"] nav a[href$="#coach"]');
    $page->script(<<<'JS'
        async () => {
            while (!document.querySelector('#coach')) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
            await new Promise((resolve) => setTimeout(resolve, 300));
        }
    JS);

    expect($page->script('() => [location.pathname, location.hash]'))->toBe(['/', '#coach'])
        ->and($page->script('() => Math.abs(document.querySelector("#coach").getBoundingClientRect().top - 94) < 30'))->toBeTrue();
});

test('the mobile menu has no Teams or Contact and closes when it jumps to a section', function () {
    $page = visitHome(['viewport' => ['width' => 390, 'height' => 844], 'isMobile' => true, 'hasTouch' => true, 'reducedMotion' => 'reduce']);

    $page->click('@menu-button');

    expect($page->script('() => [...document.querySelectorAll(".gw-menu__link")].map((link) => link.textContent.trim())'))
        ->toBe(['Programs', 'Pricing', 'About', 'Leagues', 'Gallery', 'FAQ']);

    $page->click('[data-test="mobile-menu"] a[href$="#faq"]');
    $page->wait(0.3);

    expect($page->script('() => [document.querySelector("[data-test=\'mobile-menu\']"), location.hash]'))->toBe([null, '#faq']);
});

test('Programs is a Coming soon page that search engines do not index', function () {
    $page = visit('/es/programas', ['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->script(<<<'JS'
        async () => {
            while (!document.querySelector('[data-test="coming-soon"]')) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
    JS);

    expect($page->script('() => document.querySelector("#soon-title").textContent.replace(/\s+/g, " ").trim()'))->toBe('Muy pronto.')
        ->and($page->script('() => document.querySelector("meta[name=robots]")?.content'))->toBe('noindex')
        ->and($page->script('() => document.querySelector("[data-test=\'site-header\'] nav a[aria-current=page]").textContent.trim()'))->toBe('Programas');
    $page->assertNoJavaScriptErrors();
});

test('the footer links to the legal pages, which show the draft notice and jump to their sections', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->click('[data-test="footer-privacy"]');
    $page->script(<<<'JS'
        async () => {
            const deadline = Date.now() + 10000;
            while (!document.querySelector('[data-test="legal-page"]') && Date.now() < deadline) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
    JS);

    expect($page->script('() => [location.pathname, document.querySelector("#legal-title").textContent.trim()]'))->toBe(['/privacy', 'Privacy Policy'])
        ->and($page->script('() => document.querySelector("[data-test=\'legal-draft\']").textContent'))->toStartWith('DRAFT – pending legal review.');

    $page->click('a[href="#security"]');
    $page->wait(0.3);

    expect($page->script('() => document.querySelector("#security").getBoundingClientRect().top >= document.querySelector("[data-test=\'site-header\']").getBoundingClientRect().bottom'))->toBeTrue();
});
