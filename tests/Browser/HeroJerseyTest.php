<?php

/**
 * 3D jersey stage (HeroStage mode "jersey"), shown on /styleguide since the home
 * hero became a photo: poster first, Three.js and the model only once the stage is
 * on screen, and only with motion allowed and WebGL available.
 */
beforeEach(function () {
    // TestCase disables Vite for HTTP tests; a real browser needs the real assets.
    $this->useRealViteAssets();

    registerStyleguideRoute();
});

/**
 * Scrolls the jersey stage (at the end of the styleguide) into view.
 */
const SCROLL_TO_STAGE = <<<'JS'
    async () => {
        document.querySelector('[data-test="hero-stage"]').scrollIntoView({ block: 'center' });
        await new Promise((resolve) => requestAnimationFrame(resolve));
    }
JS;

/**
 * Stage state: jersey state, canvas, poster and whether the 3D code or the model
 * were requested.
 *
 * @return array{state: string, hasCanvas: bool, posterVisible: bool, posterAlt: string, requested3d: bool}
 */
function jerseyStage(mixed $page): array
{
    return $page->script(<<<'JS'
        () => {
            const stage = document.querySelector('[data-test="hero-stage"]');
            const poster = stage.querySelector('[data-test="jersey-poster"]');
            const resources = performance.getEntriesByType('resource').map((entry) => entry.name);

            return {
                state: stage.dataset.jerseyState,
                hasCanvas: stage.querySelector('canvas') !== null,
                posterVisible: poster.complete && poster.naturalWidth > 0 && getComputedStyle(poster).opacity === '1',
                posterAlt: poster.getAttribute('alt'),
                requested3d: resources.some((name) => name.includes('jerseyScene') || name.endsWith('.glb')),
            };
        }
    JS);
}

/**
 * Waits (up to 15 s) until the jersey state is one of `$states`.
 *
 * @param  array<int, string>  $states
 */
function waitForJerseyState(mixed $page, array $states): void
{
    $expected = json_encode($states);

    $page->script(<<<JS
        async () => {
            const states = {$expected};
            const stage = document.querySelector('[data-test="hero-stage"]');
            const deadline = Date.now() + 15000;

            while (!states.includes(stage.dataset.jerseyState) && Date.now() < deadline) {
                await new Promise((resolve) => setTimeout(resolve, 100));
            }
        }
    JS);
}

const POSTER_ALT = 'The Georgia Wolves jersey from the front: yellow with black shoulders and sleeves, the club crest on the chest and the Joma logo.';

test('with reduced motion there is no canvas and the 3D code is never loaded', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 1440, 'height' => 900], 'reducedMotion' => 'reduce']);

    $page->script(SCROLL_TO_STAGE);
    $page->wait(2);

    expect(jerseyStage($page))->toBe([
        'state' => 'poster',
        'hasCanvas' => false,
        'posterVisible' => true,
        'posterAlt' => POSTER_ALT,
        'requested3d' => false,
    ]);
});

test('while the stage is off screen only the poster is there and nothing 3D is loaded', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 1440, 'height' => 900]]);

    $page->wait(1);

    expect(jerseyStage($page))->toMatchArray([
        'state' => 'poster',
        'hasCanvas' => false,
        'posterAlt' => POSTER_ALT,
        'requested3d' => false,
    ])
        ->and($page->script('() => document.querySelector("[data-test=\'jersey-poster\']").getAttribute("alt")'))->toBe(POSTER_ALT);
});

test('on screen the jersey spins on a hidden-from-AT canvas, pauses off screen, with no console errors', function () {
    $page = visitStyleguideAndWaitForCtas(['viewport' => ['width' => 1440, 'height' => 900]]);

    // Record console errors and warnings before anything 3D starts (the stage is off screen).
    $page->script(<<<'JS'
        () => {
            window.gwConsoleProblems = [];
            for (const level of ['error', 'warn']) {
                const original = console[level];
                console[level] = (...args) => {
                    window.gwConsoleProblems.push(`${level}: ${args.map(String).join(' ')}`);
                    original.apply(console, args);
                };
            }
        }
    JS);
    // Same check as the stage: only hardware-accelerated WebGL counts (the headless
    // browser in Sail renders WebGL on the CPU, so there it must keep the poster).
    $hasWebGL = $page->script(<<<'JS'
        () => {
            const context = document.createElement('canvas').getContext('webgl2', { failIfMajorPerformanceCaveat: true });
            const debugInfo = context?.getExtension('WEBGL_debug_renderer_info');
            const renderer = debugInfo ? String(context.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL)) : '';

            return Boolean(context) && !/swiftshader|llvmpipe|softpipe|software|basic render/i.test(renderer);
        }
    JS);

    $page->script(SCROLL_TO_STAGE);
    waitForJerseyState($page, $hasWebGL ? ['running'] : ['poster']);

    if ($hasWebGL) {
        expect(jerseyStage($page))->toMatchArray(['state' => 'running', 'hasCanvas' => true, 'requested3d' => true])
            ->and($page->script('() => document.querySelector("[data-test=\'jersey-canvas\']").getAttribute("aria-hidden")'))->toBe('true')
            ->and($page->script('() => getComputedStyle(document.querySelector("[data-test=\'jersey-canvas\']")).pointerEvents'))->toBe('none');

        $page->script('() => window.scrollTo(0, 0)');
        waitForJerseyState($page, ['paused']);
        expect(jerseyStage($page)['state'])->toBe('paused');

        $page->script(SCROLL_TO_STAGE);
        waitForJerseyState($page, ['running']);
        expect(jerseyStage($page)['state'])->toBe('running');
    } else {
        // No hardware WebGL in this browser: the poster stays, no canvas is left behind
        // and neither Three.js nor the model are downloaded.
        expect(jerseyStage($page))->toMatchArray(['state' => 'poster', 'hasCanvas' => false, 'posterVisible' => true, 'requested3d' => false]);
    }

    expect($page->script('() => window.gwConsoleProblems'))->toBe([]);
    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
