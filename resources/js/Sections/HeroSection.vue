<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CtaButton from '../Components/CtaButton.vue';
import HeroStage from '../Components/HeroStage.vue';
import PitchLamp from '../Components/PitchLamp.vue';
import { useFinePointer } from '../Composables/useFinePointer';
import { useReducedMotion } from '../Composables/useReducedMotion';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Home hero (docs/design/direccion-de-diseno.md, "Hero"): black, with the
 * flashlight over the pitch behind the content. Left: chip, "TRAIN LIKE A WOLF.",
 * subtitle, CTAs and key facts. Right: the 4:5 stage with its two badges.
 *
 * `[data-site-hero]` tells the mobile CTA bar when the hero has been passed.
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
const hasFinePointer = useFinePointer();
const prefersReducedMotion = useReducedMotion();
const navigation = computed(() => page.props.navigation ?? {});
const showLampHint = computed(() => hasFinePointer.value && !prefersReducedMotion.value);

const facts = [
    { key: 'days', icon: 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z' },
    { key: 'place', icon: 'M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z' },
    { key: 'free', icon: 'M20 6L9 17l-5-5' },
];
</script>

<template>
    <section
        class="relative isolate overflow-hidden bg-gw-black text-gw-white [--gw-focus-ring:var(--color-gw-yellow)]"
        aria-labelledby="hero-title"
        data-site-hero
        data-test="hero"
    >
        <PitchLamp class="-z-10" />

        <div class="mx-auto grid min-h-svh max-w-7xl grid-cols-1 items-center gap-12 px-4 pt-32 pb-16 sm:px-8 lg:grid-cols-12 lg:gap-10 lg:pt-36">
            <div class="flex flex-col items-start gap-6 lg:col-span-7">
                <p class="gw-hero-reveal inline-flex items-center gap-2 rounded-full border border-gw-white/20 px-4 py-2 text-sm font-medium" style="--gw-reveal-index: 0">
                    <span class="size-2 rounded-full bg-gw-yellow" aria-hidden="true" />
                    {{ t('home.hero.chip') }}
                </p>

                <h1 id="hero-title" class="gw-hero-reveal font-display text-[clamp(3.75rem,10vw,8.75rem)] leading-[0.86] font-extrabold uppercase" style="--gw-reveal-index: 1">
                    {{ t('home.hero.title_lead') }}
                    <span class="text-gw-yellow">{{ t('home.hero.title_accent') }}</span>
                </h1>

                <p class="gw-hero-reveal max-w-xl text-lg text-gw-white/80" style="--gw-reveal-index: 2">{{ t('home.hero.subtitle') }}</p>

                <div class="gw-hero-reveal flex flex-wrap items-center gap-4" style="--gw-reveal-index: 3">
                    <CtaButton variant="primary" surface="dark" :href="navigation.free_training" :hold="introPlaying" data-test="hero-cta-primary">
                        {{ t('home.hero.cta_primary') }}
                    </CtaButton>
                    <CtaButton variant="secondary" surface="dark" :href="navigation.programs" :hold="introPlaying">{{ t('home.hero.cta_secondary') }}</CtaButton>
                </div>

                <ul class="gw-hero-reveal flex flex-wrap gap-3" :aria-label="t('home.hero.facts_label')" style="--gw-reveal-index: 4">
                    <li v-for="fact in facts" :key="fact.key" class="inline-flex items-center gap-2 rounded-full bg-gw-neu-dark px-4 py-2 text-sm shadow-gw-raised-dark-sm">
                        <svg class="size-4 flex-none text-gw-yellow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path :d="fact.icon" />
                        </svg>
                        {{ t(`home.hero.facts.${fact.key}`) }}
                    </li>
                </ul>

                <p v-if="showLampHint" class="gw-hero-reveal inline-flex items-center gap-2 text-sm font-medium text-gw-white/70" style="--gw-reveal-index: 5" data-test="lamp-hint">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M5 3l14 7-6 2-2 6z" />
                    </svg>
                    {{ t('home.hero.lamp_hint') }}
                </p>
            </div>

            <div class="gw-hero-reveal mx-auto w-full max-w-md lg:col-span-5 lg:max-w-none" style="--gw-reveal-index: 3">
                <HeroStage mode="jersey" :ready="!introPlaying" />
            </div>
        </div>
    </section>
</template>
