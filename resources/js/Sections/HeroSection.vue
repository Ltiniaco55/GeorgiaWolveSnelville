<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CtaButton from '../Components/CtaButton.vue';
import HeroStage from '../Components/HeroStage.vue';
import SocialIcon from '../Components/SocialIcon.vue';
import { useFitText } from '../Composables/useFitText';
import { useReducedMotion } from '../Composables/useReducedMotion';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Home hero (docs/design/direccion-de-diseno.md, "Hero"; reference:
 * docs/design/prototype/home.html).
 *
 * A rounded frame (34 px, #262626 border) 14 px inside the window, with:
 * - HeroStage in "photo" mode: the photo on the right, faded into the black, lit
 *   around the pointer (flashlight). On mobile the photo goes on top.
 * - The content from the top: padding = header height + clamp(32px, 9vh, 90px).
 *   Title, subtitle, CTAs and key facts (hidden below 780 px of height).
 * - "GEORGIA WOLVES" huge at the bottom, cut by the frame's edge, always fitted to
 *   the frame's width (useFitText). Decorative.
 * - Bottom left, a notch in the page colour with concave corners and the social
 *   icons (only the networks with a URL in config/club.php; none, no notch).
 * - Bottom right, the "Meet the coach" card (from 1100 px), linking to #coach.
 *
 * Page transition (not with reduced motion): the hero is sticky and, while the
 * next sections slide over it, it shrinks a little and darkens (one rAF per scroll
 * frame, transform and opacity only).
 *
 * `.gw-hero-reveal` items come in staggered during the brand intro; while it plays
 * the CTAs stay folded (`introPlaying`) and roll open when it ends.
 */
defineProps({
    introPlaying: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const { t } = useTranslations();
const prefersReducedMotion = useReducedMotion();
const navigation = computed(() => page.props.navigation ?? {});
const socialLinks = computed(() => page.props.socialLinks ?? []);

const heroImage = {
    src: '/images/home/hero-team-talk-566.webp',
    width: 566,
    height: 812,
    sizes: '(max-width: 760px) 100vw, min(58vw, 860px)',
    srcset: {
        avif: '/images/home/hero-team-talk-400.avif 400w, /images/home/hero-team-talk-566.avif 566w',
        webp: '/images/home/hero-team-talk-400.webp 400w, /images/home/hero-team-talk-566.webp 566w',
    },
};

const facts = [
    { key: 'days', icon: 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z' },
    { key: 'place', icon: 'M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z' },
    { key: 'free', icon: 'M20 6L9 17l-5-5' },
];

const frame = ref(null);
const shade = ref(null);
const giantText = ref(null);

useFitText(frame, giantText, { ratio: 0.985 });

/* Recede on scroll: scale 1 → 0.92, 20 px down, corners 34 → 48 px, 55 % darker. */
let scrollFrame = null;
let lastProgress = null;

function paintRecede() {
    scrollFrame = null;

    if (!frame.value || !shade.value) {
        return;
    }

    const progress = Math.min(1, Math.max(0, window.scrollY / window.innerHeight));

    if (progress === lastProgress) {
        return;
    }

    lastProgress = progress;
    frame.value.style.transform = progress ? `translate3d(0, ${(progress * 20).toFixed(1)}px, 0) scale(${(1 - progress * 0.08).toFixed(4)})` : '';
    frame.value.style.borderRadius = progress ? `${(34 + progress * 14).toFixed(1)}px` : '';
    shade.value.style.opacity = (progress * 0.55).toFixed(3);
}

function onScroll() {
    scrollFrame ??= requestAnimationFrame(paintRecede);
}

function startRecede() {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    paintRecede();
}

function stopRecede() {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', onScroll);

    if (scrollFrame !== null) {
        cancelAnimationFrame(scrollFrame);
        scrollFrame = null;
    }

    lastProgress = null;

    if (frame.value && shade.value) {
        frame.value.style.transform = '';
        frame.value.style.borderRadius = '';
        shade.value.style.opacity = '0';
    }
}

onMounted(() => {
    if (!prefersReducedMotion.value) {
        startRecede();
    }
});

watch(prefersReducedMotion, (reduce) => (reduce ? stopRecede() : startRecede()));
onBeforeUnmount(stopRecede);
</script>

<template>
    <div class="gw-hero-pin" :class="{ 'gw-hero-pin--sticky': !prefersReducedMotion }" data-test="hero-pin">
        <section class="gw-hero" aria-labelledby="hero-title" data-test="hero">
            <div ref="frame" class="gw-hero__frame" data-test="hero-frame">
                <HeroStage mode="photo" :image="heroImage" :alt="t('home.hero.photo_alt')" />

                <div class="gw-hero__giant" aria-hidden="true">
                    <span ref="giantText" class="gw-hero__giant-text" data-test="hero-giant">{{ t('home.hero.giant_name') }}</span>
                </div>

                <div class="gw-hero__content">
                    <h1 id="hero-title" class="gw-hero-reveal gw-hero__title" style="--gw-reveal-index: 0">
                        {{ t('home.hero.title_lead') }}
                        <span class="text-gw-yellow">{{ t('home.hero.title_accent') }}</span>
                    </h1>

                    <p class="gw-hero-reveal gw-hero__subtitle" style="--gw-reveal-index: 1" data-test="hero-subtitle">{{ t('home.hero.subtitle') }}</p>

                    <div class="gw-hero-reveal gw-hero__ctas" style="--gw-reveal-index: 2" data-test="hero-ctas">
                        <CtaButton variant="primary" surface="dark" :href="navigation.free_training" :hold="introPlaying" data-test="hero-cta-primary">
                            {{ t('home.hero.cta_primary') }}
                        </CtaButton>
                        <CtaButton variant="secondary" surface="dark" href="#categories" :hold="introPlaying">{{ t('home.hero.cta_secondary') }}</CtaButton>
                    </div>

                    <ul class="gw-hero-reveal gw-hero__facts" :aria-label="t('home.hero.facts_label')" style="--gw-reveal-index: 3" data-test="hero-facts">
                        <li v-for="fact in facts" :key="fact.key" class="gw-hero__fact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path :d="fact.icon" />
                            </svg>
                            {{ t(`home.hero.facts.${fact.key}`) }}
                        </li>
                    </ul>
                </div>

                <nav v-if="socialLinks.length" class="gw-hero__notch" :aria-label="t('common.social.label')" data-test="hero-social">
                    <ul class="flex gap-3">
                        <li v-for="link in socialLinks" :key="link.network">
                            <a :href="link.url" class="gw-hero__social" target="_blank" rel="noopener" :aria-label="t(`common.social.${link.network}`)">
                                <SocialIcon :network="link.network" />
                            </a>
                        </li>
                    </ul>
                </nav>

                <a href="#coach" class="gw-hero-reveal gw-hero__coach" style="--gw-reveal-index: 4" data-test="hero-coach-card">
                    <picture>
                        <source type="image/avif" srcset="/images/home/coach-thumb-168.avif 168w, /images/home/coach-thumb-266.avif 266w" sizes="84px" />
                        <source type="image/webp" srcset="/images/home/coach-thumb-168.webp 168w, /images/home/coach-thumb-266.webp 266w" sizes="84px" />
                        <img src="/images/home/coach-thumb-168.webp" alt="" width="84" height="84" loading="lazy" decoding="async" class="gw-hero__coach-photo" />
                    </picture>
                    <span class="min-w-0">
                        <span class="gw-hero__coach-eyebrow">{{ t('home.hero.coach_card.eyebrow') }}</span>
                        <span class="gw-hero__coach-name">{{ t('home.hero.coach_card.name') }}</span>
                        <span class="gw-hero__coach-meta">{{ t('home.hero.coach_card.meta') }}</span>
                    </span>
                </a>

                <div ref="shade" class="gw-hero__shade" aria-hidden="true" />
            </div>
        </section>
    </div>
</template>

<style scoped>
/*
 * The pin is sticky for the whole page wrapper (Home.vue), so the sections that
 * follow (z-index 2) slide over the hero. Not with reduced motion.
 */
.gw-hero-pin {
    position: relative;
    z-index: 0;
}

.gw-hero-pin--sticky {
    position: sticky;
    top: 0;
}

.gw-hero {
    --gw-focus-ring: var(--color-gw-yellow);

    height: 100svh;
    padding: var(--gw-edge);
    background: var(--color-gw-black);
    color: var(--color-gw-white);
}

.gw-hero__frame {
    position: relative;
    isolation: isolate;
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--color-gw-charcoal);
    border-radius: 34px;
    background: #0d0d0d;
    box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.06);
    transform-origin: 50% 0;
}

/* Layers inside the frame: photo 0, giant name 1, content 2, notch and card 3, shade 4. */
.gw-hero__giant {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    display: flex;
    justify-content: center;
    pointer-events: none;
}

.gw-hero__giant-text {
    display: block;
    flex: none;
    font-family: var(--gw-font-barlow);
    font-size: 200px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.01em;
    white-space: nowrap;
    text-transform: uppercase;
    color: transparent;
    background: linear-gradient(180deg, rgb(255 212 0 / 0.17) 0%, rgb(255 255 255 / 0.05) 60%, rgb(255 255 255 / 0) 100%);
    background-clip: text;
    -webkit-text-stroke: 1px rgb(255 212 0 / 0.14);

    /* Cut by the frame's bottom edge. */
    transform: translateY(0.17em);
}

.gw-hero__content {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;
    max-width: 900px;
    padding: calc(var(--gw-header-h) + clamp(32px, 9vh, 90px)) var(--gw-gutter) 150px;
}

.gw-hero__title {
    font-family: var(--gw-font-barlow);
    font-size: clamp(56px, min(9.4vw, 13.5vh), 150px);
    font-weight: 800;
    line-height: 0.84;
    text-transform: uppercase;
}

.gw-hero__subtitle {
    max-width: 620px;
    margin-top: 28px;
    color: #d4d4d4;
    font-size: clamp(16px, 1.4vw, 20px);
    line-height: 1.6;
}

.gw-hero__ctas {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 32px;
}

.gw-hero__facts {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 28px;
}

.gw-hero__fact {
    display: inline-flex;
    gap: 10px;
    align-items: center;
    padding: 11px 18px;
    border-radius: 999px;
    background: rgb(22 22 22 / 0.9);
    box-shadow:
        5px 5px 12px var(--color-gw-neu-dark-shade),
        -3px -3px 9px var(--color-gw-neu-dark-highlight);
    font-size: 15px;
}

.gw-hero__fact svg {
    flex: none;
    width: 17px;
    height: 17px;
    color: var(--color-gw-yellow);
}

/* Notch in the page colour, with concave corners above it and to its right. */
.gw-hero__notch {
    position: absolute;
    bottom: -1px;
    left: -1px;
    z-index: 3;
    padding: 16px 22px 18px 24px;
    border-top-right-radius: 26px;
    background: var(--color-gw-black);
}

.gw-hero__notch::before,
.gw-hero__notch::after {
    position: absolute;
    width: 26px;
    height: 26px;
    background: radial-gradient(circle at 100% 0, transparent 25.5px, var(--color-gw-black) 26px);
    content: '';
}

.gw-hero__notch::before {
    bottom: 100%;
    left: 0;
}

.gw-hero__notch::after {
    bottom: 0;
    left: 100%;
}

.gw-hero__social {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: var(--color-gw-neu-dark);
    box-shadow:
        4px 4px 10px var(--color-gw-neu-dark-shade),
        -3px -3px 8px var(--color-gw-neu-dark-highlight);
    color: #ddd;
    transition:
        color 0.3s,
        box-shadow 0.3s;
}

.gw-hero__social svg {
    width: 18px;
    height: 18px;
}

@media (hover: hover) and (pointer: fine) {
    .gw-hero__social:hover {
        box-shadow: var(--shadow-gw-inset-dark-sm);
        color: var(--color-gw-yellow);
    }
}

/* "Meet the coach": raised dark neumorphic card, 64 px above the frame's bottom. */
.gw-hero__coach {
    position: absolute;
    right: 32px;
    bottom: 64px;
    z-index: 3;
    display: flex;
    gap: 14px;
    align-items: center;
    width: 330px;
    padding: 12px;
    border-radius: 24px;
    background: var(--color-gw-neu-dark);
    box-shadow:
        8px 8px 20px var(--color-gw-neu-dark-shade),
        -5px -5px 14px var(--color-gw-neu-dark-highlight);
    color: var(--color-gw-white);
    text-decoration: none;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@media (hover: hover) and (pointer: fine) {
    .gw-hero__coach:hover {
        transform: scale(1.035);
    }
}

.gw-hero__coach-photo {
    flex: none;
    width: 84px;
    height: 84px;
    border-radius: 16px;
    object-fit: cover;
}

.gw-hero__coach-eyebrow {
    display: block;
    color: var(--color-gw-yellow);
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.gw-hero__coach-name {
    display: block;
    margin: 4px 0;
    font-family: var(--gw-font-barlow);
    font-size: 22px;
    font-weight: 700;
    line-height: 1.05;
    text-transform: uppercase;
}

.gw-hero__coach-meta {
    display: block;
    color: #bdbdbd;
    font-size: 13px;
}

.gw-hero__shade {
    position: absolute;
    inset: -1px;
    z-index: 4;
    background: #000;
    opacity: 0;
    pointer-events: none;
}

@media (max-width: 1099px) {
    .gw-hero__coach {
        display: none;
    }
}

/* Short desktop screens: no key facts, tighter spacing. */
@media (max-height: 780px) and (min-width: 761px) {
    .gw-hero__facts {
        display: none;
    }

    .gw-hero__subtitle {
        margin-top: 18px;
        font-size: 16px;
    }

    .gw-hero__ctas {
        margin-top: 22px;
    }

    .gw-hero__content {
        padding-bottom: 120px;
    }
}

/* Mobile: the photo on top (HeroStage), the text below it. */
@media (max-width: 760px) {
    .gw-hero__frame {
        border-radius: 26px;
    }

    .gw-hero__content {
        right: 0;
        justify-content: flex-end;
        padding: 0 var(--gw-gutter) 110px;
    }

    .gw-hero__title {
        font-size: 64px;
    }

    .gw-hero__subtitle {
        margin-top: 16px;
        font-size: 15px;
    }

    .gw-hero__ctas {
        gap: 10px;
        margin-top: 20px;
    }

    .gw-hero__facts {
        display: none;
    }

    .gw-hero__notch {
        padding: 12px 16px 14px 14px;
    }

    .gw-hero__notch ul {
        gap: 10px;
    }

    .gw-hero__social {
        width: 38px;
        height: 38px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-hero__social,
    .gw-hero__coach {
        transition: none;
    }
}
</style>
