<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import BrandIntro from '../Components/BrandIntro.vue';
import HomePanel from '../Components/HomePanel.vue';
import { shouldPlayBrandIntro } from '../Composables/useBrandIntro';
import { vReveal } from '../Composables/useReveal';
import { useTranslations } from '../Composables/useTranslations';
import AchievementsTicker from '../Sections/AchievementsTicker.vue';
import CategoriesSection from '../Sections/CategoriesSection.vue';
import CoachSection from '../Sections/CoachSection.vue';
import HeroSection from '../Sections/HeroSection.vue';
import PricingSection from '../Sections/PricingSection.vue';

/**
 * Home (order in docs/design/home-interacciones.md; reference:
 * docs/design/prototype/home.html): brand intro → hero → achievements ticker → categories → pricing →
 * coach → …
 *
 * Page transition: the hero is sticky for this whole wrapper and every section
 * after it slides up over it (the ticker, then HomePanel sections alternating
 * dark and light). `[data-site-hero]` is an invisible box where the hero sits in
 * the flow, so the mobile CTA bar knows when the hero has been passed even though
 * the hero itself never scrolls away.
 *
 * The sections after the coach arrive in the next parts of phase B; a
 * placeholder panel stands in.
 */
const props = defineProps({
    /** Local only: ?intro=1 plays the intro again in the same session. */
    forceIntro: {
        type: Boolean,
        default: false,
    },
    /** Plan prices and the "When do you join?" calendar (HomePricing). */
    pricing: {
        type: Object,
        required: true,
    },
});

const { t } = useTranslations();
const isIntroPlaying = ref(shouldPlayBrandIntro(props.forceIntro));
</script>

<template>
    <Head :title="t('home.meta_title')" />

    <BrandIntro v-if="isIntroPlaying" @finish="isIntroPlaying = false" />

    <div class="relative">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-svh" aria-hidden="true" data-site-hero />

        <HeroSection :intro-playing="isIntroPlaying" />

        <AchievementsTicker />

        <CategoriesSection />

        <PricingSection :pricing="pricing" />

        <CoachSection />

        <HomePanel tone="light" class="flex min-h-svh items-center px-[calc(var(--gw-edge)+var(--gw-gutter))] py-24" aria-labelledby="next-title">
            <div class="flex w-full flex-col gap-4">
                <h2 id="next-title" v-reveal class="max-w-3xl font-display text-5xl leading-none font-extrabold uppercase sm:text-7xl">{{ t('home.next.title') }}</h2>
                <p v-reveal="2" class="max-w-prose text-lg text-gw-black/75">{{ t('home.next.body') }}</p>
            </div>
        </HomePanel>
    </div>
</template>
