<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CoachCard from '../Components/CoachCard.vue';
import HomePanel from '../Components/HomePanel.vue';
import SectionIntro from '../Components/SectionIntro.vue';
import { vReveal } from '../Composables/useReveal';
import { useTranslations } from '../Composables/useTranslations';

/**
 * The coach (#161616; prototype home.html, "#coach"): CoachCard with the spring
 * tilt and the slow photo zoom (fine pointer, motion allowed). The hero's
 * "Meet the coach" card links here (#coach).
 *
 * The photo is the prototype's sample (the same as the hero) until the real one.
 */
const page = usePage();
const { t, tList } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});

const coach = computed(() => {
    const key = 'home.coach.card';

    return {
        role: t(`${key}.role`),
        name: t(`${key}.name`),
        initials: t(`${key}.initials`),
        licence: t(`${key}.licence`),
        bio: t(`${key}.bio`),
        stats: tList(`${key}.stats`),
        coachingTitle: t(`${key}.coaching_title`),
        coachingCareer: tList(`${key}.coaching`),
        primaryCta: { label: t(`${key}.primary_cta`), href: '#pricing' },
        secondaryCta: { label: t(`${key}.secondary_cta`), href: navigation.value.about },
        photo: '/images/home/hero-team-talk-566.webp',
        photoAlt: t(`${key}.photo_alt`),
        photoSrcset: {
            avif: '/images/home/hero-team-talk-400.avif 400w, /images/home/hero-team-talk-566.avif 566w',
            webp: '/images/home/hero-team-talk-400.webp 400w, /images/home/hero-team-talk-566.webp 566w',
        },
        photoSizes: '(min-width: 1024px) 40vw, 100vw',
        photoPosition: '70% 20%',
    };
});
</script>

<template>
    <HomePanel tone="dark" id="coach" class="gw-section-pad" aria-labelledby="coach-title" data-test="coach">
        <div class="gw-wrap">
            <SectionIntro
                :eyebrow="t('home.coach.eyebrow')"
                :title-lead="t('home.coach.title_lead')"
                :title-accent="t('home.coach.title_accent')"
                title-id="coach-title"
            />

            <div v-reveal class="mt-14">
                <CoachCard :coach="coach" />
            </div>
        </div>
    </HomePanel>
</template>
