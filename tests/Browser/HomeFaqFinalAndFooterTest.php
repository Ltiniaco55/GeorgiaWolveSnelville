<?php

use Database\Seeders\FaqSeeder;

/**
 * Home, phase B part 4: the FAQ accordion, the magnetic final CTA and the footer.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    $this->seed(FaqSeeder::class);
});

/**
 * State of the FAQ item number `index` (0-based).
 *
 * @return array{expanded: string, inert: bool, plusTurned: bool}
 */
function faqItem(mixed $page, int $index): array
{
    return $page->script(<<<JS
        () => {
            const item = document.querySelectorAll('[data-test="faq-item"]')[{$index}];

            return {
                expanded: item.querySelector('button').getAttribute('aria-expanded'),
                inert: item.querySelector('[role="region"]').inert,
                plusTurned: getComputedStyle(item.querySelector('.gw-faq__plus')).transform !== 'none',
            };
        }
    JS);
}

test('the FAQ questions come from the database and open and close with the mouse and the keyboard', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);
    $page->script('() => document.querySelector("#faq").scrollIntoView()');

    expect($page->script('() => [...document.querySelectorAll("[data-test=\'faq-item\'] button")].map((button) => button.textContent.trim())'))->toBe([
        'What age can my child start?+',
        'Is the first training really free?+',
        'How does the 15th-of-the-month rule work?+',
        'Where and when do you train?+',
        'Can I cancel anytime?+',
    ])
        ->and(faqItem($page, 0))->toBe(['expanded' => 'false', 'inert' => true, 'plusTurned' => false]);

    $page->click('[data-test="faq-item"]:nth-child(1) button');
    expect(faqItem($page, 0))->toBe(['expanded' => 'true', 'inert' => false, 'plusTurned' => true]);

    $page->keys('[data-test="faq-item"]:nth-child(1) button', 'Enter');
    expect(faqItem($page, 0))->toBe(['expanded' => 'false', 'inert' => true, 'plusTurned' => false]);

    // The billing figures of the answer come from config/billing.php.
    $page->click('[data-test="faq-item"]:nth-child(3) button');
    expect($page->script('() => document.querySelector("[data-test=\'faq-item\']:nth-child(3) [role=\'region\']").textContent.trim()'))->toContain('($100)');
});

test('hovering a question fills it with yellow from the left', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');
    $page->script('() => document.querySelector("#faq").scrollIntoView()');

    $question = '[data-test="faq-item"]:nth-child(2) button';
    $fill = '() => new DOMMatrixReadOnly(getComputedStyle(document.querySelector(\'[data-test="faq-item"]:nth-child(2) button\'), "::before").transform).a';

    expect($page->script($fill))->toBe(0);

    $page->hover($question);
    $page->wait(0.6);

    expect($page->script($fill))->toBe(1)
        ->and($page->script('() => getComputedStyle(document.querySelector(\'[data-test="faq-item"]:nth-child(2) button\'), "::before").transformOrigin.startsWith("0px")'))->toBeTrue();
});

test('the final CTA is pulled towards a pointer within 220 px and springs back', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');

    $offset = fn (int $dx, int $dy): string => <<<JS
        async () => {
            const magnet = document.querySelector('[data-test="magnet"]');
            magnet.scrollIntoView({ block: 'center' });
            await new Promise((resolve) => requestAnimationFrame(resolve));
            const rect = magnet.getBoundingClientRect();
            window.dispatchEvent(new PointerEvent('pointermove', { pointerType: 'mouse', clientX: rect.left + rect.width / 2 + {$dx}, clientY: rect.top + rect.height / 2 + {$dy} }));
            await new Promise((resolve) => setTimeout(resolve, 1500));
            const matrix = new DOMMatrixReadOnly(getComputedStyle(magnet).transform);

            return [Math.round(matrix.m41), Math.round(matrix.m42)];
        }
    JS;

    // 100 px right and 40 px down: 35 % of the way.
    expect($page->script($offset(100, 40)))->toBe([35, 14])
        // Further than 220 px: back to its place.
        ->and($page->script($offset(400, 0)))->toBe([0, 0]);
});

test('with reduced motion the final CTA stays put and there is no hint', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    expect($page->script(<<<'JS'
        async () => {
            const magnet = document.querySelector('[data-test="magnet"]');
            const rect = magnet.getBoundingClientRect();
            window.dispatchEvent(new PointerEvent('pointermove', { pointerType: 'mouse', clientX: rect.left + 10, clientY: rect.top + 10 }));
            await new Promise((resolve) => setTimeout(resolve, 500));

            return [getComputedStyle(magnet).transform, document.querySelector('[data-test="final-cta"] [data-test="section-hint"]') !== null];
        }
    JS))->toBe(['none', false]);
});

test('the footer shows the club address and the sitemap links, and clears the mobile bar', function () {
    $page = visitHome(['viewport' => ['width' => 390, 'height' => 844], 'isMobile' => true, 'hasTouch' => true], '/es');
    $page->keys('@skip-intro', 'Escape');

    $footer = $page->script(<<<'JS'
        async () => {
            window.scrollTo(0, document.body.scrollHeight);
            await new Promise((resolve) => setTimeout(resolve, 800));
            const copyright = document.querySelector('[data-test="footer-copyright"]').getBoundingClientRect();
            const bar = document.querySelector('[data-test="mobile-bar"]').getBoundingClientRect();

            return {
                address: document.querySelector('[data-test="footer-address"]').textContent.replace(/\s+/g, ' ').trim(),
                privacy: new URL(document.querySelector('[data-test="footer-privacy"]').href).pathname,
                gysl: new URL(document.querySelector('[data-test="footer-gysl"]').href).pathname,
                maps: document.querySelector('[data-test="footer-maps"]').href.startsWith('https://www.google.com/maps/'),
                clearsBar: copyright.bottom <= bar.top,
            };
        }
    JS);

    expect($footer)->toBe([
        'address' => 'Georgia Wolves Soccer Club South Gwinnett High School 2288 Main St E, Snellville, GA 30078',
        'privacy' => '/es/privacidad',
        'gysl' => '/es/ligas/gysl',
        'maps' => true,
        'clearsBar' => true,
    ]);
});
