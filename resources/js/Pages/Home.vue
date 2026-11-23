<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import BrandIntro from '../Components/BrandIntro.vue';
import { shouldPlayBrandIntro } from '../Composables/useBrandIntro';
import { useTranslations } from '../Composables/useTranslations';
import HeroSection from '../Sections/HeroSection.vue';

/**
 * Home (order in docs/design/home-interacciones.md): brand intro → hero → …
 * The sections after the hero arrive in prompt 5b; a white placeholder shows
 * the change from black to white meanwhile.
 */
const props = defineProps({
    /** Local only: ?intro=1 plays the intro again in the same session. */
    forceIntro: {
        type: Boolean,
        default: false,
    },
});

const { t } = useTranslations();
const isIntroPlaying = ref(shouldPlayBrandIntro(props.forceIntro));
</script>

<template>
    <Head :title="t('home.meta_title')" />

    <BrandIntro v-if="isIntroPlaying" @finish="isIntroPlaying = false" />

    <HeroSection :intro-playing="isIntroPlaying" />

    <section class="flex min-h-svh items-center bg-gw-white px-4 py-24 sm:px-8" aria-labelledby="next-title">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-4">
            <h2 id="next-title" class="max-w-3xl font-display text-5xl leading-none font-extrabold uppercase sm:text-7xl">{{ t('home.next.title') }}</h2>
            <p class="max-w-prose text-lg text-gw-black/75">{{ t('home.next.body') }}</p>
        </div>
    </section>
</template>
