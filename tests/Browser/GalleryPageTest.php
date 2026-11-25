<?php

use App\Models\Photo;
use Database\Seeders\PhotoSeeder;

/**
 * /gallery: masonry wall, filter tabs, captions and the full-screen viewer.
 * The sample photos: 9 in all, 4 of them "Champions".
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    $this->seed(PhotoSeeder::class);
});

/**
 * Opens /gallery and waits for the wall.
 *
 * @param  array<string, mixed>  $options
 */
function visitGallery(array $options, string $url = '/gallery'): mixed
{
    $page = visit($url, $options);

    $page->script(<<<'JS'
        async () => {
            while (!document.querySelector('[data-test="gallery-wall"]')) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
            await document.fonts.ready;
        }
    JS);

    return $page;
}

/**
 * @return array{columns: string, visible: int, hidden: int, hiddenInert: bool, overlaps: int, fits: bool, height: int}
 */
function wallState(mixed $page): array
{
    return $page->script(<<<'JS'
        () => {
            const wall = document.querySelector('[data-test="gallery-wall"]');
            const wallRect = wall.getBoundingClientRect();
            const items = [...wall.querySelectorAll('[data-test="wall-item"]')];
            const visible = items.filter((item) => !item.inert);
            const hidden = items.filter((item) => item.inert);
            const boxes = visible.map((item) => item.getBoundingClientRect());
            let overlaps = 0;

            boxes.forEach((a, i) => boxes.slice(i + 1).forEach((b) => {
                if (a.left < b.right - 1 && b.left < a.right - 1 && a.top < b.bottom - 1 && b.top < a.bottom - 1) {
                    overlaps++;
                }
            }));

            return {
                columns: wall.dataset.columns,
                visible: visible.length,
                hidden: hidden.length,
                hiddenInert: hidden.every((item) => item.getAttribute('aria-hidden') === 'true'),
                overlaps,
                fits: boxes.every((box) => box.left >= wallRect.left - 1 && box.right <= wallRect.right + 1 && box.bottom <= wallRect.bottom + 1),
                height: Math.round(wallRect.height),
            };
        }
    JS);
}

test('the wall is a masonry of 4, 3 or 2 columns without overlaps', function (int $width, int $height, bool $isMobile, string $columns) {
    $page = visitGallery(['viewport' => ['width' => $width, 'height' => $height], 'isMobile' => $isMobile, 'hasTouch' => $isMobile, 'reducedMotion' => 'reduce']);

    expect(wallState($page))->toMatchArray(['columns' => $columns, 'visible' => 9, 'hidden' => 0, 'overlaps' => 0, 'fits' => true]);
    $page->assertNoJavaScriptErrors();
})->with([
    'desktop' => [1440, 900, false, '4'],
    'tablet' => [800, 1000, false, '3'],
    'mobile' => [390, 844, true, '2'],
]);

test('a tab filters the wall, which re-lays out the photos and animates its height', function () {
    $page = visitGallery(['viewport' => ['width' => 1440, 'height' => 900]]);
    $before = wallState($page);

    $page->click('[data-tab="champions"]');
    $page->wait(1);
    $after = wallState($page);

    expect($after)->toMatchArray(['visible' => 4, 'hidden' => 5, 'hiddenInert' => true, 'overlaps' => 0, 'fits' => true])
        ->and($after['height'])->toBeLessThan($before['height'])
        ->and($page->script('() => getComputedStyle(document.querySelector("[data-test=\'gallery-wall\']")).transitionDuration'))->toBe('0.7s')
        ->and($page->script('() => document.querySelector("[data-tab=\'champions\']").getAttribute("aria-selected")'))->toBe('true')
        // The yellow indicator slid under the active tab.
        ->and($page->script(<<<'JS'
            () => {
                const tab = document.querySelector('[data-tab="champions"]').getBoundingClientRect();
                const indicator = document.querySelector('[data-test="tab-indicator"]').getBoundingClientRect();

                return Math.abs(tab.left - indicator.left) < 1 && Math.abs(tab.width - indicator.width) < 1;
            }
        JS))->toBeTrue();
});

test('the tabs work with the arrow keys and stick below the header', function () {
    $page = visitGallery(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->script('() => document.querySelector("[data-tab=\'all\']").focus()');
    $page->keys('[data-tab="all"]', 'ArrowRight');

    expect($page->script('() => [document.activeElement.dataset.tab, document.activeElement.getAttribute("aria-selected")]'))->toBe(['training', 'true'])
        ->and(wallState($page)['visible'])->toBe(3);

    $page->keys(':focus', 'End');
    expect($page->script('() => document.activeElement.dataset.tab'))->toBe('team');

    $page->script('() => window.scrollTo(0, 900)');
    $page->wait(0.3);

    expect($page->script(<<<'JS'
        () => {
            const tabs = document.querySelector('[data-test="gallery-tabs"]').getBoundingClientRect();
            const header = document.querySelector('[data-test="site-header"]').getBoundingClientRect();

            return tabs.top > header.bottom && tabs.top < header.bottom + 30;
        }
    JS))->toBeTrue();
});

test('the caption rises on hover with a mouse and is always visible on touch', function () {
    $caption = '() => new DOMMatrixReadOnly(getComputedStyle(document.querySelector(".gw-wall__caption")).transform).m42';

    $page = visitGallery(['viewport' => ['width' => 1440, 'height' => 900]]);
    expect($page->script($caption))->toBeGreaterThan(10);

    $page->hover('[aria-label="Open photo: Team talk"]');
    $page->wait(0.7);
    expect($page->script($caption))->toBe(0);

    $touch = visitGallery(['viewport' => ['width' => 390, 'height' => 844], 'isMobile' => true, 'hasTouch' => true]);
    expect($touch->script($caption))->toBe(0);
});

test('the viewer walks only through the active filter, with the keyboard, and gives the focus back', function () {
    $page = visitGallery(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->click('[data-tab="champions"]');
    $page->click('[aria-label="Open photo: GYSL champions"]');

    $viewer = '() => ({ counter: document.querySelector("[data-test=\'viewer-counter\']").textContent.trim(), alt: document.querySelector("[data-test=\'viewer-image\']").alt, modal: document.querySelector("[data-test=\'photo-viewer\']").getAttribute("aria-modal"), inside: document.querySelector("[data-test=\'photo-viewer\']").contains(document.activeElement) })';

    expect($page->script($viewer))->toBe([
        'counter' => '1 / 4',
        'alt' => 'Young players in grey kits with champions’ medals in front of the Gwinnett Youth Soccer League banner.',
        'modal' => 'true',
        'inside' => true,
    ]);

    $page->keys(':focus', 'ArrowRight');
    expect($page->script($viewer)['counter'])->toBe('2 / 4');

    $page->keys(':focus', 'ArrowLeft');
    $page->keys(':focus', 'ArrowLeft');
    expect($page->script($viewer)['counter'])->toBe('4 / 4');

    $page->keys(':focus', 'Escape');
    $page->wait(0.2);

    expect($page->script('() => document.querySelector("[data-test=\'photo-viewer\']")'))->toBeNull()
        ->and($page->script('() => document.activeElement.getAttribute("aria-label")'))->toBe('Open photo: GYSL champions');
});

test('photos without the media release are never on the page', function () {
    Photo::query()->where('image', 'images/gallery/half-time')->update(['has_media_release' => false]);

    $page = visitGallery(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    expect(wallState($page)['visible'])->toBe(8)
        ->and($page->script('() => document.body.innerHTML.includes("half-time")'))->toBeFalse()
        // Matchday had only that photo: its tab is gone.
        ->and($page->script('() => document.querySelector("[data-tab=\'matchday\']")'))->toBeNull();
});
