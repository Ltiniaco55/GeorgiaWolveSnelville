<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import BrandIntro from '../Components/BrandIntro.vue';
import { shouldPlayBrandIntro } from '../Composables/useBrandIntro';
import { useTranslations } from '../Composables/useTranslations';
import AchievementsTicker from '../Sections/AchievementsTicker.vue';
import CategoriesSection from '../Sections/CategoriesSection.vue';
import CoachSection from '../Sections/CoachSection.vue';
import FaqSection from '../Sections/FaqSection.vue';
import FinalCta from '../Sections/FinalCta.vue';
import GalleryTrail from '../Sections/GalleryTrail.vue';
import HeroSection from '../Sections/HeroSection.vue';
import LeaguesStrip from '../Sections/LeaguesStrip.vue';
import PricingSection from '../Sections/PricingSection.vue';

/**
 * Home (order in docs/design/home-interacciones.md; reference:
 * docs/design/prototype/home.html): brand intro → hero → achievements ticker → categories → pricing →
 * coach → leagues and partners → gallery → FAQ → final CTA (the footer is in
 * AppLayout).
 *
 * Page transition: the hero is sticky for this whole wrapper and every section
 * after it slides up over it (the ticker, then HomePanel sections alternating
 * dark and light). `[data-site-hero]` is an invisible box where the hero sits in
 * the flow, so the mobile CTA bar knows when the hero has been passed even though
 * the hero itself never scrolls away.
 *
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
    /** Up to 9 photos with the parents' media release (Photo::toCard). */
    galleryPhotos: {
        type: Array,
        default: () => [],
    },
    /** League crests of the partners strip: { slug, url }. */
    leagues: {
        type: Array,
        default: () => [],
    },
    /** Kit partner and sponsors (config/club.php). */
    partners: {
        type: Array,
        default: () => [],
    },
    /** Published questions for the home (Faq::toItem). */
    faqs: {
        type: Array,
        default: () => [],
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

        <LeaguesStrip :leagues="leagues" :partners="partners" />

        <GalleryTrail :photos="galleryPhotos" />

        <FaqSection :faqs="faqs" />

        <FinalCta />
    </div>
</template>
