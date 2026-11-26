<?php

use App\Models\Photo;
use Database\Seeders\PhotoSeeder;

/**
 * Home, phase B part 3: the "Associated leagues and partners" strip and the
 * "Life in the pack" gallery (photo trail, static grid on touch).
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();
});

/**
 * Look of a pill: logo filter and opacity, and whether it is sunk (inset shadow).
 *
 * @return array{filter: string, opacity: string, sunk: bool}
 */
function pillLook(mixed $page, string $selector): array
{
    return $page->script(<<<JS
        () => {
            const pill = document.querySelector('{$selector}');
            const logo = pill.querySelector('.gw-pill__logo');

            return {
                filter: getComputedStyle(logo).filter,
                opacity: getComputedStyle(logo).opacity,
                sunk: getComputedStyle(pill).boxShadow.includes('inset'),
            };
        }
    JS);
}

test('league crests are grey until hovered or focused, then get their colour and sink', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);
    $page->script('() => document.querySelector("#leagues").scrollIntoView()');

    expect(pillLook($page, '[data-test="league-gysl"]'))->toBe(['filter' => 'grayscale(1)', 'opacity' => '0.45', 'sunk' => false]);

    $page->hover('[data-test="league-gysl"]');
    expect(pillLook($page, '[data-test="league-gysl"]'))->toBe(['filter' => 'none', 'opacity' => '1', 'sunk' => true]);

    $page->hover('#leagues-title');
    $page->script('() => document.querySelector("[data-test=\'league-liga-interamericana\']").focus({ focusVisible: true })');
    $page->keys('[data-test="league-liga-interamericana"]', 'Shift');

    expect(pillLook($page, '[data-test="league-liga-interamericana"]')['sunk'])->toBeTrue()
        ->and($page->script('() => { const link = document.querySelector("[data-test=\'league-gysl\']"); return [link.getAttribute("href"), link.target, link.getAttribute("aria-label")]; }'))
        ->toBe(['https://www.instagram.com/gwinnettfutbol/', '_blank', 'Gwinnett Youth Soccer League on Instagram (opens in a new tab)']);
});

test('Joma and CER are placeholders, not links, until they have a logo and a URL', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    expect($page->script(<<<'JS'
        () => ['joma', 'cer'].map((id) => {
            const pill = document.querySelector(`[data-test="partner-${id}"]`);

            return [pill.tagName, pill.querySelector('[role="img"]').getAttribute('aria-label')];
        })
    JS))->toBe([['DIV', 'Joma logo coming soon'], ['DIV', 'CER logo coming soon']]);
});

test('moving the mouse over the gallery leaves a trail of rotated photos that fade out', function () {
    $this->seed(PhotoSeeder::class);

    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');

    $trail = $page->script(<<<'JS'
        async () => {
            const area = document.querySelector('[data-test="gallery"] [data-trail]');
            area.scrollIntoView();
            await new Promise((resolve) => requestAnimationFrame(resolve));
            const rect = area.getBoundingClientRect();
            const move = (x, y) => area.dispatchEvent(new PointerEvent('pointermove', { bubbles: true, pointerType: 'mouse', clientX: rect.left + x, clientY: rect.top + y }));
            const count = () => document.querySelectorAll('[data-test="trail-photo"]').length;

            // 5 px steps: one photo on entering, then one every 110 px.
            for (let x = 100; x <= 760; x += 5) {
                move(x, 300);
            }
            await new Promise((resolve) => requestAnimationFrame(resolve));
            const afterShortRun = count();
            const rotations = [...document.querySelectorAll('[data-test="trail-photo"]')].map((item) => item.style.getPropertyValue('--gw-rotation'));
            const decorative = document.querySelector('.gw-gallery__trail').getAttribute('aria-hidden');

            // A long run never keeps more than 14 at once.
            for (let x = 0; x <= 4000; x += 5) {
                move(100 + (x % 1100), 300 + Math.floor(x / 1100) * 60);
            }
            await new Promise((resolve) => requestAnimationFrame(resolve));
            const afterLongRun = count();

            await new Promise((resolve) => setTimeout(resolve, 1800));

            return { afterShortRun, afterLongRun, afterLifetime: count(), distinctRotations: new Set(rotations).size, decorative };
        }
    JS);

    expect($trail['afterShortRun'])->toBe(7)
        ->and($trail['afterLongRun'])->toBe(14)
        ->and($trail['afterLifetime'])->toBe(0)
        ->and($trail['distinctRotations'])->toBeGreaterThan(1)
        ->and($trail['decorative'])->toBe('true');
});

test('on touch screens the gallery is a static grid of photos with their alternative texts', function () {
    $this->seed(PhotoSeeder::class);

    $page = visitHome(['viewport' => ['width' => 390, 'height' => 844], 'isMobile' => true, 'hasTouch' => true]);

    expect($page->script('() => document.querySelector("[data-test=\'gallery\'] [data-trail]").dataset.trail'))->toBe('off')
        ->and($page->script('() => [...document.querySelectorAll("[data-test=\'gallery-grid\'] img")].map((image) => image.alt).filter(Boolean).length'))->toBe(6)
        ->and($page->script('() => document.querySelector("[data-test=\'gallery-cta\']").getAttribute("href")'))->toEndWith('/gallery');
});

test('photos without the media release never reach the page', function () {
    Photo::factory()->withoutMediaRelease()->create(['image' => 'images/gallery/team-photo']);

    $page = visitHome(['viewport' => ['width' => 390, 'height' => 844], 'isMobile' => true, 'hasTouch' => true]);

    expect($page->script('() => document.querySelector("[data-test=\'gallery-grid\']")'))->toBeNull()
        ->and($page->script('() => document.body.innerHTML.includes("team-photo")'))->toBeFalse();
});
