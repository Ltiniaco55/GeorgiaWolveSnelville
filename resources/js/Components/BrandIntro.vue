<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { markBrandIntroSeen } from '../Composables/useBrandIntro';
import { useTranslations } from '../Composables/useTranslations';
import emblemSvg from '../../svg/wolf-yellow.svg?raw';

/**
 * Brand intro, 2.6 s (docs/design/direccion-de-diseno.md, "Intro de marca y home").
 *
 * 0–0.8 s      the yellow emblem pops in at the centre with a bounce.
 * 0.85–1.8 s   the page is revealed through a circle that grows from the emblem's
 *              size (clip-path on header and main) with a 3 px yellow ring.
 * 0.8–2.35 s   the emblem rolls along an arc to the menu crest, shrinking, with
 *              2.5 anticlockwise turns, and lands on the crest's centre circle
 *              (x 50 %, y 47.4 % of the crest; diameter 62.4 % of its width).
 * 1.25 s       the hero content comes in staggered.
 * 2.35–2.6 s   the emblem fades out and the crest pops (0.7 → 1.14 → 1).
 *
 * The parent decides whether it plays (useBrandIntro). While it runs, scroll is
 * locked and the page is inert; "Skip intro", Esc, Space or Enter end it at once.
 * Everything moves with transform/opacity in requestAnimationFrame.
 */
const emit = defineEmits(['finish']);

const DURATION = 2.6;
const ENTRY_END = 0.8;
const REVEAL_START = 0.85;
const REVEAL_END = 1.8;
const ROLL_START = 0.8;
const ROLL_END = 2.35;
const HERO_IN = 1.25;
const TURNS = 2.5;
const CREST_CENTER_Y = 0.474;
const CREST_CIRCLE_RATIO = 0.624;

const { t } = useTranslations();
const emblem = ref(null);
const ring = ref(null);
const skipButton = ref(null);
const root = document.documentElement;
// Inlined (not lazy like BrandLogo) so the emblem is there from the first frame.
const emblemMarkup = emblemSvg.replace('<svg ', '<svg aria-hidden="true" focusable="false" ');

let geometry = null;
let frame = null;
let startTime = null;
let isFinished = false;

root.classList.add('gw-intro-active');
root.style.setProperty('--gw-intro-radius', '0px');
markBrandIntroSeen();

const clamp01 = (value) => Math.min(1, Math.max(0, value));
const lerp = (from, to, progress) => from + (to - from) * progress;
const easeInOutCubic = (x) => (x < 0.5 ? 4 * x * x * x : 1 - (-2 * x + 2) ** 3 / 2);
const easeOutBack = (x) => 1 + 2.70158 * (x - 1) ** 3 + 1.70158 * (x - 1) ** 2;

function pageElements() {
    return document.querySelectorAll('.gw-intro-clip');
}

function measure() {
    const width = window.innerWidth;
    const height = window.innerHeight;
    const startSize = Math.min(Math.min(width, height) * 0.42, 340);
    const crest = document.querySelector('[data-intro-crest]')?.getBoundingClientRect();
    const target = crest
        ? { x: crest.left + crest.width / 2, y: crest.top + crest.height * CREST_CENTER_Y, size: crest.width * CREST_CIRCLE_RATIO }
        : { x: width / 2, y: height / 2, size: startSize };

    geometry = {
        startSize,
        start: { x: width / 2, y: height / 2 },
        target,
        // Arc: up first, then over to the crest, like a ball rolling to the corner.
        control: { x: lerp(width / 2, target.x, 0.2), y: lerp(height / 2, target.y, 0.95) },
        maxRadius: Math.hypot(width, height) / 2 + 8,
    };
}

/**
 * Draws the intro at time `seconds`.
 *
 * @param {number} seconds
 */
function render(seconds) {
    const { startSize, start, target, control, maxRadius } = geometry;
    let x = start.x;
    let y = start.y;
    let scale;
    let rotation;
    let opacity = 1;

    if (seconds < ROLL_START) {
        const entry = clamp01(seconds / ENTRY_END);
        scale = lerp(0.35, 1, easeOutBack(entry));
        rotation = lerp(-40, 0, easeOutBack(entry));
        opacity = clamp01(seconds / 0.25);
    } else {
        const roll = easeInOutCubic(clamp01((seconds - ROLL_START) / (ROLL_END - ROLL_START)));
        const inverse = 1 - roll;
        x = inverse * inverse * start.x + 2 * inverse * roll * control.x + roll * roll * target.x;
        y = inverse * inverse * start.y + 2 * inverse * roll * control.y + roll * roll * target.y;
        scale = lerp(1, target.size / startSize, roll);
        rotation = -360 * TURNS * roll;
        opacity = 1 - clamp01((seconds - ROLL_END) / (DURATION - ROLL_END));
    }

    if (emblem.value) {
        emblem.value.style.width = `${startSize}px`;
        emblem.value.style.transform = `translate3d(${(x - startSize / 2).toFixed(2)}px, ${(y - startSize / 2).toFixed(2)}px, 0) rotate(${rotation.toFixed(2)}deg) scale(${scale.toFixed(4)})`;
        emblem.value.style.opacity = opacity.toFixed(3);
    }

    const reveal = clamp01((seconds - REVEAL_START) / (REVEAL_END - REVEAL_START));
    const radius = seconds < REVEAL_START ? 0 : lerp(startSize / 2, maxRadius, easeInOutCubic(reveal));
    root.style.setProperty('--gw-intro-radius', `${radius.toFixed(1)}px`);

    if (ring.value) {
        ring.value.setAttribute('r', radius.toFixed(1));
        ring.value.style.opacity = seconds < REVEAL_START ? '0' : (1 - reveal).toFixed(3);
    }

    root.classList.toggle('gw-intro-hero-in', seconds >= HERO_IN);
    root.classList.toggle('gw-intro-landed', seconds >= ROLL_END);
}

function tick(now) {
    startTime ??= now;
    const seconds = (now - startTime) / 1000;

    if (seconds >= DURATION) {
        finish();

        return;
    }

    render(seconds);
    frame = requestAnimationFrame(tick);
}

function setPageInert(isInert) {
    pageElements().forEach((element) => {
        element.inert = isInert;
    });
}

function cleanUp() {
    if (frame !== null) {
        cancelAnimationFrame(frame);
        frame = null;
    }

    document.removeEventListener('keydown', onKeydown, true);
    window.removeEventListener('resize', measure);
    root.classList.remove('gw-intro-active', 'gw-intro-hero-in', 'gw-intro-landed');
    root.style.removeProperty('--gw-intro-radius');
    setPageInert(false);
}

function finish() {
    if (isFinished) {
        return;
    }

    isFinished = true;
    cleanUp();
    emit('finish');
}

/**
 * @param {KeyboardEvent} event
 */
function onKeydown(event) {
    if (['Escape', ' ', 'Spacebar', 'Enter'].includes(event.key)) {
        event.preventDefault();
        finish();
    }
}

onMounted(() => {
    window.scrollTo(0, 0);
    setPageInert(true);
    measure();
    render(0);
    document.addEventListener('keydown', onKeydown, true);
    window.addEventListener('resize', measure);
    skipButton.value?.focus({ preventScroll: true });
    frame = requestAnimationFrame(tick);
});

onBeforeUnmount(() => {
    if (!isFinished) {
        isFinished = true;
        cleanUp();
    }
});
</script>

<template>
    <!-- Outside <main>: main is clipped by the reveal circle, the intro must not be. -->
    <Teleport to="body">
        <div class="gw-brand-intro" data-test="brand-intro">
            <svg class="gw-brand-intro__ring" aria-hidden="true" focusable="false">
                <circle ref="ring" cx="50%" cy="50%" r="0" />
            </svg>
            <div ref="emblem" class="gw-brand-intro__emblem" aria-hidden="true" v-html="emblemMarkup" />
            <button ref="skipButton" type="button" class="gw-brand-intro__skip" data-test="skip-intro" @click="finish">
                {{ t('home.intro.skip') }}
                <span class="gw-brand-intro__keys">{{ t('home.intro.skip_keys') }}</span>
            </button>
        </div>
    </Teleport>
</template>

<style scoped>
.gw-brand-intro {
    position: fixed;
    inset: 0;
    z-index: 60;
    pointer-events: none;
}

.gw-brand-intro__ring {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: visible;
}

.gw-brand-intro__ring circle {
    fill: none;
    stroke: var(--color-gw-yellow);
    stroke-width: 3;
    opacity: 0;
}

.gw-brand-intro__emblem {
    position: absolute;
    top: 0;
    left: 0;
    transform-origin: 50% 50%;
    opacity: 0;
    will-change: transform, opacity;
}

.gw-brand-intro__emblem :deep(svg) {
    display: block;
    width: 100%;
    height: auto;
    color: var(--color-gw-yellow);
}

.gw-brand-intro__skip {
    --gw-focus-ring: var(--color-gw-yellow);

    position: absolute;
    right: max(1rem, env(safe-area-inset-right));
    bottom: max(1rem, env(safe-area-inset-bottom));
    display: inline-flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.125rem;
    padding: 0.75rem 1.25rem;
    border: 1px solid rgb(255 255 255 / 0.35);
    border-radius: 999px;
    background: rgb(10 10 10 / 0.6);
    color: var(--color-gw-white);
    font-size: 0.9375rem;
    font-weight: 600;
    line-height: 1.1;
    cursor: pointer;
    pointer-events: auto;
}

.gw-brand-intro__keys {
    color: rgb(255 255 255 / 0.7);
    font-size: 0.6875rem;
    font-weight: 500;
}

@media (hover: hover) and (pointer: fine) {
    .gw-brand-intro__skip:hover {
        border-color: var(--color-gw-yellow);
        color: var(--color-gw-yellow);
    }
}
</style>
