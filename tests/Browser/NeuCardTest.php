<?php

/**
 * NeuCard on /styleguide: pricing cards (hover expand) and the coach card (tilt).
 *
 * Pointer moves for the tilt are dispatched from the page because the browser
 * API only hovers the centre of an element, where the tilt is zero.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    registerStyleguideRoute();
});

/**
 * Script that moves a mouse pointer over the coach card, near its top-right corner.
 */
const MOVE_POINTER_TO_COACH_TOP_RIGHT = <<<'JS'
    async () => {
        const card = document.querySelector('[data-test="coach-card"]');
        card.scrollIntoView({ block: 'center' });
        await new Promise((resolve) => requestAnimationFrame(resolve));
        const rect = card.getBoundingClientRect();
        const options = { bubbles: true, pointerType: 'mouse', clientX: rect.right - 20, clientY: rect.top + 20 };
        card.dispatchEvent(new PointerEvent('pointerover', options));
        card.dispatchEvent(new PointerEvent('pointermove', options));
    }
JS;

test('the pricing cards fit inside the screen on mobile', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 390, 'height' => 844]]);

    $layout = $page->script(<<<'JS'
        () => {
            const viewportWidth = document.documentElement.clientWidth;
            const cards = [...document.querySelectorAll('[data-test^="plan-"]')];
            const isInside = (element) => {
                const rect = element.getBoundingClientRect();

                return rect.left >= 0 && rect.right <= viewportWidth;
            };

            return {
                cardCount: cards.length,
                cardsInside: cards.every(isInside),
                contentInside: cards.every((card) => [...card.querySelectorAll('*')].every((child) => child.getBoundingClientRect().width === 0 || isInside(child))),
                hasHorizontalScroll: document.documentElement.scrollWidth > viewportWidth,
            };
        }
    JS);

    expect($layout)->toBe([
        'cardCount' => 3,
        'cardsInside' => true,
        'contentInside' => true,
        'hasHorizontalScroll' => false,
    ]);
    $page->assertNoJavaScriptErrors();
});

test('the coach card tilts towards a mouse pointer and springs back', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 1440, 'height' => 900]]);

    $page->script(MOVE_POINTER_TO_COACH_TOP_RIGHT);
    $page->wait(1.5);

    $tilt = $page->script(<<<'JS'
        () => {
            const match = document.querySelector('[data-test="coach-card"]').style.transform
                .match(/perspective\(1000px\) rotateX\((-?[\d.]+)deg\) rotateY\((-?[\d.]+)deg\)/);

            return match ? { rotateX: Number(match[1]), rotateY: Number(match[2]) } : null;
        }
    JS);

    // Top-right corner sinks: top edge and right edge both go away, never past 4°.
    expect($tilt)->not->toBeNull()
        ->and($tilt['rotateX'])->toBeGreaterThan(2)->toBeLessThanOrEqual(4)
        ->and($tilt['rotateY'])->toBeGreaterThan(2)->toBeLessThanOrEqual(4);

    $page->script(<<<'JS'
        () => document.querySelector('[data-test="coach-card"]').dispatchEvent(new PointerEvent('pointerleave', { pointerType: 'mouse' }))
    JS);
    $page->wait(4);

    expect($page->script('() => document.querySelector(\'[data-test="coach-card"]\').style.transform'))->toBe('');
});

test('the coach card does not tilt on a touch screen', function () {
    $page = visitStyleguideAndWaitForCtas([
        'viewport' => ['width' => 390, 'height' => 844],
        'isMobile' => true,
        'hasTouch' => true,
    ]);

    expect($page->script('() => matchMedia("(hover: hover) and (pointer: fine)").matches'))->toBeFalse();

    // Even a mouse-type pointer event must not tilt the card when the device has no fine pointer.
    $page->script(MOVE_POINTER_TO_COACH_TOP_RIGHT);
    $page->hover('@coach-card');
    $page->wait(1.5);

    expect($page->script(<<<'JS'
        () => {
            const card = document.querySelector('[data-test="coach-card"]');

            return { inline: card.style.transform, computed: getComputedStyle(card).transform };
        }
    JS))->toBe(['inline' => '', 'computed' => 'none']);
});

test('a pricing card expands on hover with a mouse', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 1440, 'height' => 900]]);

    $page->script(<<<'JS'
        () => document.querySelector('[data-test="plan-monthly"]').scrollIntoView({ block: 'center' })
    JS);
    $page->hover('@plan-monthly');
    $page->wait(1);

    expect($page->script('() => getComputedStyle(document.querySelector(\'[data-test="plan-monthly"]\')).transform'))
        ->toBe('matrix(1.035, 0, 0, 1.035, 0, 0)');
});

test('with reduced motion the cards do not move', function () {
    $page = visitStyleguideAndWaitForCtas([
        'viewport' => ['width' => 1440, 'height' => 900],
        'reducedMotion' => 'reduce',
    ]);

    $page->script(<<<'JS'
        () => document.querySelector('[data-test="plan-annual"]').scrollIntoView({ block: 'center' })
    JS);
    $page->hover('@plan-annual');
    $page->wait(1);

    $planTransform = $page->script('() => getComputedStyle(document.querySelector(\'[data-test="plan-annual"]\')).transform');

    $page->script(MOVE_POINTER_TO_COACH_TOP_RIGHT);
    $page->hover('@coach-card');
    $page->wait(1.5);

    $coach = $page->script(<<<'JS'
        () => {
            const card = document.querySelector('[data-test="coach-card"]');

            return {
                inline: card.style.transform,
                computed: getComputedStyle(card).transform,
                photo: getComputedStyle(card.querySelector('.gw-coach__photo')).transform,
            };
        }
    JS);

    expect($planTransform)->toBe('none')
        ->and($coach)->toBe(['inline' => '', 'computed' => 'none', 'photo' => 'none']);
});
