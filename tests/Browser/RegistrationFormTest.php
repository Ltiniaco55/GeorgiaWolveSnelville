<?php

use App\Models\Registration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

/**
 * Registration templates in the browser: errors, players, plan and the full
 * free training flow up to the thank-you page.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    Mail::fake();
    config(['club.season_end_year' => 2027]);
    $this->travelTo(CarbonImmutable::parse('2026-11-24 10:00', 'America/New_York'));
});

/**
 * @param  array<string, mixed>  $options
 */
function visitRegistration(string $url, array $options = []): mixed
{
    $page = visit($url, ['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce', ...$options]);

    $page->script(<<<'JS'
        async () => {
            const deadline = Date.now() + 10000;
            while (!document.querySelector('[data-test="registration-form"]') && Date.now() < deadline) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
    JS);

    return $page;
}

test('sending it empty lists the errors in page order, focuses the list and marks the fields', function () {
    $page = visitRegistration('/register?plan=monthly');

    $page->click('[data-test="registration-submit"]');
    $page->script(<<<'JS'
        async () => {
            const deadline = Date.now() + 10000;
            while (!document.querySelector('[data-test="error-summary"]') && Date.now() < deadline) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
            await new Promise((resolve) => setTimeout(resolve, 100));
        }
    JS);

    expect($page->script('() => document.activeElement.dataset.test'))->toBe('error-summary')
        ->and($page->script('() => [...document.querySelectorAll("[data-test=\'error-summary\'] li")].slice(0, 2).map((item) => item.textContent.trim())'))
        ->toBe(['Please fill in player 1 first name.', 'Please fill in player 1 last name.'])
        ->and($page->script('() => [document.querySelector("#f-guardian-email").getAttribute("aria-invalid"), document.querySelector("#f-guardian-email").getAttribute("aria-describedby")]'))
        ->toBe(['true', 'f-guardian-email-error']);
});

test('players can be added and removed, and the uniform size only shows when the uniform is needed', function () {
    $page = visitRegistration('/register?plan=annual');

    $page->click('@add-player');

    expect($page->script('() => document.querySelectorAll("[data-test=\'player\']").length'))->toBe(2)
        ->and($page->script('() => document.activeElement.id'))->toBe('f-players-1-first_name')
        ->and($page->script('() => document.querySelector("[data-test=\'plan-note\']").textContent.trim()'))->toBe('One payment for the year. Same 15th-of-the-month rule for your start date.');

    $page->script('() => document.querySelectorAll("[data-test=\'player\']")[1].querySelector(".gw-choice input").click()');
    expect($page->script('() => document.querySelector("#f-players-1-shirt_size")'))->toBeNull();

    $page->click('[data-test="plan-monthly"]');
    expect($page->script('() => document.querySelector("[data-test=\'plan-note\']").textContent.trim()'))->toBe('Joining today: $0 today, first monthly charge on December 1.');
});

test('a family books a free training and lands on the thank-you page', function () {
    $page = visitRegistration('/free-training', ['viewport' => ['width' => 390, 'height' => 844], 'isMobile' => true, 'hasTouch' => true]);

    // Date inputs do not take typed text reliably: set the value and fire "input".
    $page->script('() => { const input = document.querySelector("#f-players-0-date_of_birth"); input.value = "2017-05-10"; input.dispatchEvent(new Event("input", { bubbles: true })); }');

    $page->type('#f-players-0-first_name', 'Mateo')
        ->type('#f-players-0-last_name', 'García')
        ->type('#f-guardian-first_name', 'Ana')
        ->type('#f-guardian-last_name', 'García')
        ->select('#f-guardian-relationship', 'mother')
        ->type('#f-guardian-email', 'ana@example.com')
        ->type('#f-guardian-phone', '(404) 555-0123')
        ->type('#f-address-street', '123 Oak St')
        ->type('#f-address-zip', '30078')
        ->type('#f-emergency-name', 'Rosa López')
        ->type('#f-emergency-relationship', 'Aunt')
        ->type('#f-emergency-phone', '404 555 0199')
        ->click('[data-test="media-yes"]')
        ->check('#f-accepts_liability_waiver')
        ->check('#f-accepts_terms')
        ->type('#f-signature_name', 'Ana García')
        ->click('[data-test="registration-submit"]');

    $page->script(<<<'JS'
        async () => {
            const deadline = Date.now() + 10000;
            while (!document.querySelector('[data-test="registration-received"]') && Date.now() < deadline) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
    JS);

    expect($page->script('() => [...document.querySelectorAll("[data-test=\'error-summary\'] li")].map((item) => item.textContent.trim())'))->toBe([]);
    expect($page->script('() => [location.pathname, Math.round(window.scrollY)]'))->toBe(['/free-training/thanks', 0])
        ->and($page->script('() => document.querySelector("[data-test=\'received-message\']").textContent.trim()'))
        ->toStartWith('Your free training is booked for Thursday, November 26.')
        ->and($page->script('() => document.querySelector(".gw-received__chip").textContent.trim()'))->toBe('Mateo · U10')
        ->and(Registration::sole()->media_release)->toBeTrue();
});
