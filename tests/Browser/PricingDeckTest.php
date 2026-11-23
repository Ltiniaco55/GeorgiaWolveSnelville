<?php

/**
 * PricingDeck on /styleguide: below 640 px the plans are a deck that turns with the
 * scroll (Annual → Monthly → Free trial); from 640 px they keep their normal order.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    registerStyleguideRoute();
});

/**
 * Scrolls to `$steps` card changes into the deck and reports which plan is on top
 * and where the sticky stage is.
 *
 * @return array{topPlan: string|null, stageTop: int}
 */
function scrollDeck(mixed $page, float $steps): array
{
    return $page->script(<<<JS
        async () => {
            const deck = document.querySelector('[data-test="pricing-deck"]');
            const stepPixels = (deck.offsetHeight - window.innerHeight) / 2;
            const deckTop = window.scrollY + deck.getBoundingClientRect().top;

            window.scrollTo(0, Math.round(deckTop + {$steps} * stepPixels));
            await new Promise((resolve) => setTimeout(resolve, 400));

            return {
                topPlan: deck.dataset.topPlan ?? null,
                stageTop: Math.round(deck.querySelector('.gw-deck__stage').getBoundingClientRect().top) + 0, // + 0 turns -0 into 0
            };
        }
    JS);
}

test('on mobile the deck starts with Annual and turns to Monthly and Free trial with the scroll', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 390, 'height' => 844]]);

    $domOrder = $page->script(<<<'JS'
        () => [...document.querySelectorAll('[data-test="pricing-deck"] [data-test^="plan-"]')].map((card) => card.dataset.test)
    JS);

    expect($domOrder)->toBe(['plan-annual', 'plan-monthly', 'plan-free_trial'])
        ->and(scrollDeck($page, 0))->toBe(['topPlan' => 'annual', 'stageTop' => 0])
        ->and(scrollDeck($page, 1))->toBe(['topPlan' => 'monthly', 'stageTop' => 0])
        ->and(scrollDeck($page, 2))->toBe(['topPlan' => 'free_trial', 'stageTop' => 0]);

    // After the last card the page scrolls on normally: the deck leaves the screen.
    $afterDeck = scrollDeck($page, 2.5);

    expect($afterDeck['topPlan'])->toBe('free_trial')
        ->and($afterDeck['stageTop'])->toBeLessThan(-100);

    $dots = $page->script(<<<'JS'
        () => [...document.querySelectorAll('.gw-deck__dot')].map((dot) => [dot.getAttribute('aria-label'), dot.getAttribute('aria-current')])
    JS);

    expect($dots)->toBe([['Plan 1 of 3', null], ['Plan 2 of 3', null], ['Plan 3 of 3', 'true']]);
    $page->assertNoJavaScriptErrors();
});

test('a card that receives keyboard focus comes to the top of the deck', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 390, 'height' => 844]]);

    scrollDeck($page, 0);

    $topPlans = $page->script(<<<'JS'
        async () => {
            const deck = document.querySelector('[data-test="pricing-deck"]');
            const focusPlan = async (plan) => {
                deck.querySelector(`[data-test="plan-${plan}"] .gw-cta`).focus();
                await new Promise((resolve) => setTimeout(resolve, 400));

                return deck.dataset.topPlan;
            };

            return [await focusPlan('free_trial'), await focusPlan('monthly'), await focusPlan('annual')];
        }
    JS);

    expect($topPlans)->toBe(['free_trial', 'monthly', 'annual']);
});

test('a horizontal swipe on the deck shows the next and the previous card', function () {
    $page = visitStyleguideAndWaitForCtas([
        'viewport' => ['width' => 390, 'height' => 844],
        'isMobile' => true,
        'hasTouch' => true,
    ]);

    scrollDeck($page, 0);

    $topPlans = $page->script(<<<'JS'
        async () => {
            const deck = document.querySelector('[data-test="pricing-deck"]');
            const cards = deck.querySelector('.gw-deck__cards');
            const y = cards.getBoundingClientRect().top + 200;
            const swipe = async (fromX, toX) => {
                cards.dispatchEvent(new PointerEvent('pointerdown', { bubbles: true, pointerType: 'touch', clientX: fromX, clientY: y }));
                cards.dispatchEvent(new PointerEvent('pointerup', { bubbles: true, pointerType: 'touch', clientX: toX, clientY: y + 8 }));
                await new Promise((resolve) => setTimeout(resolve, 1500));

                return deck.dataset.topPlan;
            };

            return [await swipe(300, 120), await swipe(300, 120), await swipe(80, 280)];
        }
    JS);

    expect($topPlans)->toBe(['monthly', 'free_trial', 'monthly']);
});

test('with reduced motion the mobile plans are stacked vertically without pinning', function () {
    $page = visitStyleguideAndWaitForCtas([
        'viewport' => ['width' => 390, 'height' => 844],
        'reducedMotion' => 'reduce',
    ]);

    $state = $page->script(<<<'JS'
        async () => {
            const deck = document.querySelector('[data-test="pricing-deck"]');
            const cards = [...deck.querySelectorAll('[data-test^="plan-"]')];
            const deckTop = window.scrollY + deck.getBoundingClientRect().top;

            window.scrollTo(0, Math.round(deckTop + 300));
            await new Promise((resolve) => setTimeout(resolve, 400));

            const rects = cards.map((card) => card.getBoundingClientRect());

            return {
                order: cards.map((card) => card.dataset.test),
                isPinned: getComputedStyle(deck.querySelector('.gw-deck__stage')).position === 'sticky',
                hasDeckHeight: deck.style.height !== '',
                hasTopPlan: 'topPlan' in deck.dataset,
                hasDots: deck.querySelector('.gw-deck__dot') !== null,
                hasInlineTransforms: [...deck.querySelectorAll('.gw-deck__card')].some((card) => card.style.transform !== ''),
                stackedVertically: rects.every((rect, index) => index === 0 || rect.top >= rects[index - 1].bottom),
            };
        }
    JS);

    expect($state)->toBe([
        'order' => ['plan-annual', 'plan-monthly', 'plan-free_trial'],
        'isPinned' => false,
        'hasDeckHeight' => false,
        'hasTopPlan' => false,
        'hasDots' => false,
        'hasInlineTransforms' => false,
        'stackedVertically' => true,
    ]);
});

test('from 640 px the plans keep the order Free trial, Monthly, Annual in a row', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 1440, 'height' => 900]]);

    $state = $page->script(<<<'JS'
        () => {
            const deck = document.querySelector('[data-test="pricing-deck"]');
            const cards = [...deck.querySelectorAll('[data-test^="plan-"]')];

            return {
                order: cards.map((card) => card.dataset.test),
                inOneRow: new Set(cards.map((card) => Math.round(card.getBoundingClientRect().top))).size === 1,
                isDeck: 'topPlan' in deck.dataset,
            };
        }
    JS);

    expect($state)->toBe([
        'order' => ['plan-free_trial', 'plan-monthly', 'plan-annual'],
        'inOneRow' => true,
        'isDeck' => false,
    ]);
});

test('on short phones each card fits on screen with the dots while the deck is pinned', function (int $width, int $height) {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => $width, 'height' => $height]]);

    $positions = $page->script(<<<'JS'
        async () => {
            const deck = document.querySelector('[data-test="pricing-deck"]');
            const results = [];

            // Start at the deck, so each dot only scrolls one card change.
            window.scrollTo(0, Math.round(window.scrollY + deck.getBoundingClientRect().top));
            await new Promise((resolve) => setTimeout(resolve, 400));

            for (const dot of deck.querySelectorAll('.gw-deck__dot')) {
                dot.click();
                await new Promise((resolve) => setTimeout(resolve, 1500));

                const top = deck.querySelector(`[data-test="plan-${deck.dataset.topPlan}"]`).getBoundingClientRect();
                const controls = deck.querySelector('.gw-deck__controls').getBoundingClientRect();
                const lowestCard = Math.max(...[...deck.querySelectorAll('[data-test^="plan-"]')].map((card) => card.getBoundingClientRect().bottom));

                results.push({
                    plan: deck.dataset.topPlan,
                    isPinned: Math.round(deck.querySelector('.gw-deck__stage').getBoundingClientRect().top) === 0,
                    cardOnScreen: top.top >= 0 && top.bottom <= controls.top,
                    dotsOnScreen: controls.bottom <= window.innerHeight,
                    deckAboveDots: lowestCard <= controls.top,
                });
            }

            return results;
        }
    JS);

    expect($positions)->toBe(array_map(fn (string $plan): array => [
        'plan' => $plan,
        'isPinned' => true,
        'cardOnScreen' => true,
        'dotsOnScreen' => true,
        'deckAboveDots' => true,
    ], ['annual', 'monthly', 'free_trial']));
})->with([
    'iPhone SE' => [375, 667],
    'small Android' => [360, 640],
]);
