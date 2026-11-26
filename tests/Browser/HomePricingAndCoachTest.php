<?php

use Carbon\CarbonImmutable;

/**
 * Home, phase B part 2: pricing (cards, uniform note, "When do you join?") and
 * the coach section. The calendar is pinned to November 2026.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    $this->travelTo(CarbonImmutable::parse('2026-11-10 12:00', 'America/New_York'));
});

/**
 * @return array{amount: string, text: string, selected: string|null}
 */
function joinResult(mixed $page): array
{
    return $page->script(<<<'JS'
        () => ({
            amount: document.querySelector('[data-test="join-result"] p').textContent.trim(),
            text: document.querySelector('[data-test="join-result"] p:last-child').textContent.trim(),
            selected: document.querySelector('[data-test="join-calculator"] [aria-pressed="true"]')?.dataset.day ?? null,
        })
    JS);
}

test('the plans show the prices and notes from config/billing.php, and the uniform note', function () {
    config(['billing.prices.monthly' => 11_000, 'billing.prices.uniform' => 9_000]);

    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    expect($page->script(<<<'JS'
        () => ['free_trial', 'monthly', 'annual'].map((id) => document.querySelector(`[data-test="plan-${id}"] .gw-plan__price`).textContent.trim())
    JS))->toBe(['$0', '$110', '$1,000'])
        ->and($page->script('() => document.querySelector("[data-test=\'plan-annual\']").textContent'))->toContain('Save $320')
        ->and($page->script('() => document.querySelector("[data-test=\'plan-monthly\']").textContent'))->toContain('Join after the 15th')
        ->and($page->script('() => document.querySelector("[data-test=\'uniform-note\']").textContent.trim()'))->toBe('Uniform $90 · one-time payment · Joma kit with the club crest');
});

test('the calculator starts on today and explains each day on hover, tap and keyboard', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);
    $page->script('() => document.querySelector("[data-test=\'join-calculator\']").scrollIntoView({ block: "center" })');

    expect(joinResult($page))->toBe([
        'amount' => '$100 today',
        'text' => 'Joining on November 10: you pay November now, and your monthly plan renews on December 1.',
        'selected' => '10',
    ])
        // Days after the 15th are in another tone.
        ->and($page->script('() => [15, 16].map((day) => document.querySelector(`[data-day="${day}"]`).classList.contains("gw-calc__day--late"))'))->toBe([false, true]);

    $page->hover('[data-day="20"]');

    expect(joinResult($page))->toBe([
        'amount' => '$0 today',
        'text' => 'Joining on November 20: the rest of November is on us. Your first $100 charge is on December 1.',
        'selected' => '20',
    ]);

    $page->click('[data-day="3"]');
    expect(joinResult($page)['selected'])->toBe('3');

    // One tab stop; the arrows move the selection and the focus.
    $page->keys('[data-day="3"]', 'ArrowDown');
    $page->keys(':focus', 'ArrowRight');

    expect(joinResult($page)['selected'])->toBe('11')
        ->and($page->script('() => document.activeElement.dataset.day'))->toBe('11')
        ->and($page->script('() => document.querySelectorAll("[data-test=\'join-calculator\'] button[tabindex=\'0\']").length'))->toBe(1)
        ->and($page->script('() => document.querySelector("[data-test=\'join-result\']").getAttribute("aria-live")'))->toBe('polite');

    $page->keys(':focus', 'End');
    expect(joinResult($page)['selected'])->toBe('30');
});

test('on mobile the plans are the deck with Annual on top', function () {
    $page = visitHome(['viewport' => ['width' => 390, 'height' => 844], 'isMobile' => true, 'hasTouch' => true]);
    $page->keys('@skip-intro', 'Escape');

    expect($page->script('() => document.querySelector("[data-test=\'pricing-deck\']").dataset.topPlan'))->toBe('annual');
});

test('the coach card tilts towards the pointer and shows the responsive photo', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900]]);
    $page->keys('@skip-intro', 'Escape');

    $state = $page->script(<<<'JS'
        async () => {
            const card = document.querySelector('#coach [data-test="coach-card"]');
            card.scrollIntoView({ block: 'center' });
            await new Promise((resolve) => setTimeout(resolve, 300));
            const rect = card.getBoundingClientRect();
            const options = { bubbles: true, pointerType: 'mouse', clientX: rect.right - 20, clientY: rect.top + 20 };
            card.dispatchEvent(new PointerEvent('pointerover', options));
            card.dispatchEvent(new PointerEvent('pointermove', options));
            await new Promise((resolve) => setTimeout(resolve, 400));
            const photo = card.querySelector('[data-test="coach-photo"]');

            return {
                tilted: getComputedStyle(card).transform !== 'none',
                photo: photo.currentSrc.split('/').pop(),
                alt: photo.alt,
            };
        }
    JS);

    expect($state['tilted'])->toBeTrue()
        ->and($state['photo'])->toStartWith('hero-team-talk-')
        ->and($state['alt'])->toBe('Coach Christian Tiniaco talking to his players');
});

test('the hero coach card links to the coach section', function () {
    $page = visitHome(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->click('[data-test="hero-coach-card"]');
    $page->wait(0.3);

    // Just below the fixed header pill (scroll-padding-top).
    expect($page->script(<<<'JS'
        () => {
            const coach = document.querySelector('#coach').getBoundingClientRect().top;
            const header = document.querySelector('[data-test="site-header"]').getBoundingClientRect().bottom;

            return coach >= header && coach < header + 30;
        }
    JS))->toBeTrue();
});
