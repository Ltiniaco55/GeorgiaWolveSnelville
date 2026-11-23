<?php

/**
 * Language dropdown in the header (desktop) and in the mobile menu panel.
 * Reduced motion keeps the brand intro out of the way.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();
});

/**
 * State of the dropdown inside `$scope` (a CSS selector).
 *
 * The list closes on the next frame (it sits in a <Transition>), so callers wait a moment.
 *
 * @return array{expanded: string, listVisible: bool, focused: string|null}
 */
function languageMenuState(mixed $page, string $scope = '[data-test="site-header"]'): array
{
    return $page->script(<<<JS
        () => {
            const menu = document.querySelector('{$scope} [data-test="language-menu"]');

            return {
                expanded: menu.querySelector('[data-test="language-button"]').getAttribute('aria-expanded'),
                listVisible: getComputedStyle(menu.querySelector('[data-test="language-list"]')).display !== 'none',
                focused: document.activeElement.dataset.test ?? null,
            };
        }
    JS);
}

test('the desktop dropdown sits after Register and lists both languages with their URLs', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $markup = $page->script(<<<'JS'
        () => {
            const header = document.querySelector('[data-test="site-header"]');
            const register = header.querySelector('[data-test="header-register"]');
            const menu = header.querySelector('[data-test="language-menu"]');
            const button = menu.querySelector('[data-test="language-button"]');

            return {
                afterRegister: Boolean(register.compareDocumentPosition(menu) & Node.DOCUMENT_POSITION_FOLLOWING),
                haspopup: button.getAttribute('aria-haspopup'),
                expanded: button.getAttribute('aria-expanded'),
                name: button.textContent.trim(),
                options: [...menu.querySelectorAll('[role="menuitemradio"]')].map((option) => ({
                    lang: option.getAttribute('lang'),
                    path: new URL(option.href).pathname,
                    checked: option.getAttribute('aria-checked'),
                    text: option.textContent.trim(),
                    hasSvgIcon: option.querySelector('svg') !== null,
                })),
            };
        }
    JS);

    expect($markup)->toBe([
        'afterRegister' => true,
        'haspopup' => 'true',
        'expanded' => 'false',
        'name' => 'Language: English',
        'options' => [
            ['lang' => 'en', 'path' => '/', 'checked' => 'true', 'text' => 'English', 'hasSvgIcon' => true],
            ['lang' => 'es', 'path' => '/es', 'checked' => 'false', 'text' => 'Español', 'hasSvgIcon' => true],
        ],
    ]);
});

test('arrow keys move through the options and Esc closes the dropdown and returns the focus', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->click('@language-button');

    expect(languageMenuState($page))->toBe(['expanded' => 'true', 'listVisible' => true, 'focused' => 'language-option-en']);

    $page->keys(':focus', 'ArrowDown');
    expect(languageMenuState($page)['focused'])->toBe('language-option-es');

    $page->keys(':focus', 'ArrowDown');
    expect(languageMenuState($page)['focused'])->toBe('language-option-en');

    $page->keys(':focus', 'End');
    expect(languageMenuState($page)['focused'])->toBe('language-option-es');

    $page->keys(':focus', 'Home');
    expect(languageMenuState($page)['focused'])->toBe('language-option-en');

    $page->keys(':focus', 'Escape');
    $page->wait(0.4);
    expect(languageMenuState($page))->toBe(['expanded' => 'false', 'listVisible' => false, 'focused' => 'language-button']);
});

test('the keyboard opens the dropdown and Enter switches the page to Spanish', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->script('() => document.querySelector("[data-test=\'site-header\'] [data-test=\'language-button\']").focus()');
    $page->keys(':focus', 'ArrowUp');

    expect(languageMenuState($page))->toBe(['expanded' => 'true', 'listVisible' => true, 'focused' => 'language-option-es']);

    $page->keys(':focus', 'Enter');
    $page->script(<<<'JS'
        async () => {
            while (document.documentElement.lang !== 'es' || !document.querySelector('[data-test="hero"]')) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
    JS);

    expect($page->script('() => [location.pathname, document.querySelector("[data-test=\'site-header\'] [data-test=\'language-button\']").textContent.trim()]'))
        ->toBe(['/es', 'Idioma: Español']);
});

test('a click outside closes the dropdown', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->click('@language-button');
    expect(languageMenuState($page)['expanded'])->toBe('true');

    $page->click('#hero-title');
    $page->wait(0.4);
    expect(languageMenuState($page))->toMatchArray(['expanded' => 'false', 'listVisible' => false]);
});

test('on mobile the dropdown is inside the menu panel and Esc closes only the dropdown', function () {
    $page = visitHome([
        'viewport' => ['width' => 390, 'height' => 844],
        'isMobile' => true,
        'hasTouch' => true,
        'reducedMotion' => 'reduce',
    ]);

    expect($page->script('() => getComputedStyle(document.querySelector("[data-test=\'site-header\'] [data-test=\'language-menu\']").parentElement).display'))
        ->toBe('none');

    $page->click('@menu-button');
    $page->click('[data-test="mobile-menu"] [data-test="language-button"]');

    expect(languageMenuState($page, '[data-test="mobile-menu"]'))
        ->toBe(['expanded' => 'true', 'listVisible' => true, 'focused' => 'language-option-en']);

    $page->keys(':focus', 'Escape');
    $page->wait(0.4);

    expect(languageMenuState($page, '[data-test="mobile-menu"]'))
        ->toBe(['expanded' => 'false', 'listVisible' => false, 'focused' => 'language-button'])
        ->and($page->script('() => document.querySelector("[data-test=\'mobile-menu\']") !== null'))->toBeTrue();

    $page->keys(':focus', 'Escape');
    // The panel leaves with a 0.3 s transition before it is removed.
    $page->wait(0.5);

    expect($page->script('() => [document.querySelector("[data-test=\'mobile-menu\']") !== null, document.activeElement.dataset.test]'))
        ->toBe([false, 'menu-button']);
});
