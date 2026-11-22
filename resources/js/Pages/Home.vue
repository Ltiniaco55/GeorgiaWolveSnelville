<script setup>
import { Head } from '@inertiajs/vue3';
import BrandLogo from '../Components/BrandLogo.vue';
import { useTranslations } from '../Composables/useTranslations';

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('home.meta_title')" />

    <section
        class="flex min-h-svh flex-col items-center justify-center gap-8 bg-gw-black px-4 text-center text-gw-white [--gw-focus-ring:var(--color-gw-yellow)]"
    >
        <BrandLogo variant="crest" class="h-40 sm:h-56" />
        <h1 class="font-display text-5xl font-extrabold uppercase sm:text-7xl">
            {{ t('home.placeholder_title') }}
        </h1>
        <p class="max-w-prose text-lg text-gw-white/80">{{ t('home.placeholder_body') }}</p>
    </section>
</template>
