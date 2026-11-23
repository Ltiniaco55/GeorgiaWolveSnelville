<script setup>
import { Head } from '@inertiajs/vue3';
import BrandLogo from '../Components/BrandLogo.vue';
import CtaButton from '../Components/CtaButton.vue';
import { useTranslations } from '../Composables/useTranslations';

const { t } = useTranslations();

/** Bands further down the page, to check that each CTA opens and folds while scrolling. */
const scrollBands = ['light', 'dark', 'light', 'dark'];

/** Each logo is shown on the surface it is meant for. */
const logos = [
    { variant: 'wolf-yellow', surface: 'bg-gw-black', size: 'h-28' },
    { variant: 'wolf-yellow-black', surface: 'bg-gw-black', size: 'h-28' },
    { variant: 'wolf-white', surface: 'bg-gw-black', size: 'h-28' },
    { variant: 'wolf-black', surface: 'bg-gw-white ring-1 ring-gw-neu-light-shade', size: 'h-28' },
    { variant: 'crest', surface: 'bg-gw-black', size: 'h-32' },
    { variant: 'wordmark-yellow', surface: 'bg-gw-black', size: 'h-8' },
    { variant: 'wordmark-white', surface: 'bg-gw-black', size: 'h-8' },
    { variant: 'wordmark-black', surface: 'bg-gw-white ring-1 ring-gw-neu-light-shade', size: 'h-8' },
    { variant: 'liga-interamericana', surface: 'bg-gw-neu-light', size: 'h-28' },
    { variant: 'gysl', surface: 'bg-gw-neu-light', size: 'h-28' },
];

const colors = [
    { key: 'white', token: 'gw-white', hex: '#FFFFFF', swatch: 'bg-gw-white ring-1 ring-inset ring-gw-neu-light-shade' },
    { key: 'black', token: 'gw-black', hex: '#0A0A0A', swatch: 'bg-gw-black' },
    { key: 'charcoal', token: 'gw-charcoal', hex: '#262626', swatch: 'bg-gw-charcoal' },
    { key: 'yellow', token: 'gw-yellow', hex: '#FFD400', swatch: 'bg-gw-yellow' },
    { key: 'neu-light', token: 'gw-neu-light', hex: '#ECECEC', swatch: 'bg-gw-neu-light' },
    { key: 'neu-light-shade', token: 'gw-neu-light-shade', hex: '#C8C8C8', swatch: 'bg-gw-neu-light-shade' },
    { key: 'neu-light-highlight', token: 'gw-neu-light-highlight', hex: '#FFFFFF', swatch: 'bg-gw-neu-light-highlight ring-1 ring-inset ring-gw-neu-light-shade' },
    { key: 'neu-dark', token: 'gw-neu-dark', hex: '#161616', swatch: 'bg-gw-neu-dark' },
    { key: 'neu-dark-shade', token: 'gw-neu-dark-shade', hex: '#090909', swatch: 'bg-gw-neu-dark-shade' },
    { key: 'neu-dark-highlight', token: 'gw-neu-dark-highlight', hex: '#232323', swatch: 'bg-gw-neu-dark-highlight' },
];

const lightShadows = [
    { label: 'raised', utility: 'shadow-gw-raised-light' },
    { label: 'raised_hover', utility: 'shadow-gw-raised-light-hover' },
    { label: 'raised_sm', utility: 'shadow-gw-raised-light-sm' },
    { label: 'inset', utility: 'shadow-gw-inset-light' },
    { label: 'inset_sm', utility: 'shadow-gw-inset-light-sm' },
];

const darkShadows = [
    { label: 'raised', utility: 'shadow-gw-raised-dark' },
    { label: 'raised_sm', utility: 'shadow-gw-raised-dark-sm' },
    { label: 'inset', utility: 'shadow-gw-inset-dark' },
    { label: 'inset_sm', utility: 'shadow-gw-inset-dark-sm' },
];

const typeSamples = [
    { label: 'display_800', sample: 'sample_headline', classes: 'font-display text-5xl font-extrabold uppercase leading-none sm:text-7xl' },
    { label: 'display_700', sample: 'sample_name', classes: 'font-display text-4xl font-bold uppercase' },
    { label: 'text_400', sample: 'sample_body', classes: 'text-lg font-normal' },
    { label: 'text_500', sample: 'sample_label', classes: 'text-sm font-medium' },
    { label: 'text_600', sample: 'sample_button', classes: 'text-base font-semibold' },
];
</script>

<template>
    <Head :title="t('styleguide.meta_title')" />

    <header class="bg-gw-black px-4 py-16 text-gw-white sm:px-8 [--gw-focus-ring:var(--color-gw-yellow)]">
        <div class="mx-auto flex max-w-6xl flex-col gap-4">
            <BrandLogo variant="wordmark-yellow" class="h-6" decorative />
            <h1 class="font-display text-6xl font-extrabold uppercase">{{ t('styleguide.title') }}</h1>
            <p class="max-w-prose text-gw-white/80">{{ t('styleguide.intro') }}</p>
        </div>
    </header>

    <section aria-labelledby="sg-logos" class="px-4 py-16 sm:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-8">
            <div class="flex flex-col gap-2">
                <h2 id="sg-logos" class="font-display text-4xl font-extrabold uppercase">{{ t('styleguide.logos.title') }}</h2>
                <p class="text-gw-black/70">{{ t('styleguide.logos.intro') }}</p>
            </div>
            <ul class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <li v-for="logo in logos" :key="logo.variant" class="flex flex-col gap-3">
                    <div class="flex h-44 items-center justify-center rounded-3xl p-6" :class="logo.surface">
                        <BrandLogo :variant="logo.variant" :class="logo.size" />
                    </div>
                    <code class="text-sm font-medium">variant="{{ logo.variant }}"</code>
                </li>
            </ul>
        </div>
    </section>

    <section aria-labelledby="sg-palette" class="bg-gw-neu-light px-4 py-16 sm:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-8">
            <div class="flex flex-col gap-2">
                <h2 id="sg-palette" class="font-display text-4xl font-extrabold uppercase">{{ t('styleguide.palette.title') }}</h2>
                <p class="text-gw-black/70">{{ t('styleguide.palette.intro') }}</p>
            </div>
            <ul class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
                <li v-for="color in colors" :key="color.key" class="flex flex-col gap-3 rounded-3xl bg-gw-neu-light p-4 shadow-gw-raised-light-sm">
                    <div class="h-20 rounded-2xl" :class="color.swatch" />
                    <div class="flex flex-col gap-1">
                        <p class="font-semibold">{{ t(`styleguide.palette.colors.${color.key}.name`) }}</p>
                        <p class="text-sm"><code>{{ color.hex }}</code> · <code>{{ color.token }}</code></p>
                        <p class="text-sm text-gw-black/70">{{ t(`styleguide.palette.colors.${color.key}.usage`) }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    <section aria-labelledby="sg-shadows" class="px-4 py-16 sm:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-8">
            <div class="flex flex-col gap-2">
                <h2 id="sg-shadows" class="font-display text-4xl font-extrabold uppercase">{{ t('styleguide.shadows.title') }}</h2>
                <p class="max-w-prose text-gw-black/70">{{ t('styleguide.shadows.intro') }}</p>
            </div>

            <div class="flex flex-col gap-8 rounded-[2rem] bg-gw-neu-light p-6 sm:p-10">
                <h3 class="font-display text-2xl font-bold uppercase">{{ t('styleguide.shadows.light_surface') }}</h3>
                <ul class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-5">
                    <li v-for="shadow in lightShadows" :key="shadow.utility" class="flex flex-col gap-4">
                        <div class="h-32 rounded-[1.75rem] bg-gw-neu-light" :class="shadow.utility" />
                        <p class="font-semibold">{{ t(`styleguide.shadows.${shadow.label}`) }}</p>
                        <code class="text-xs break-all">{{ shadow.utility }}</code>
                    </li>
                </ul>
            </div>

            <div class="flex flex-col gap-8 rounded-[2rem] bg-gw-neu-dark p-6 text-gw-white sm:p-10">
                <h3 class="font-display text-2xl font-bold uppercase">{{ t('styleguide.shadows.dark_surface') }}</h3>
                <ul class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="shadow in darkShadows" :key="shadow.utility" class="flex flex-col gap-4">
                        <div class="h-32 rounded-[2rem] bg-gw-neu-dark" :class="shadow.utility" />
                        <p class="font-semibold">{{ t(`styleguide.shadows.${shadow.label}`) }}</p>
                        <code class="text-xs break-all text-gw-white/80">{{ shadow.utility }}</code>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section aria-labelledby="sg-buttons" class="px-4 py-16 sm:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-8">
            <div class="flex flex-col gap-2">
                <h2 id="sg-buttons" class="font-display text-4xl font-extrabold uppercase">{{ t('styleguide.buttons.title') }}</h2>
                <p class="max-w-prose text-gw-black/70">{{ t('styleguide.buttons.intro') }}</p>
            </div>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="flex flex-col gap-6 rounded-[2rem] bg-gw-white p-8 ring-1 ring-gw-neu-light-shade">
                    <h3 class="font-display text-2xl font-bold uppercase">{{ t('styleguide.buttons.on_light') }}</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <CtaButton variant="primary" surface="light" href="/" data-test="cta-primary-light">{{ t('styleguide.buttons.sample_primary') }}</CtaButton>
                        <CtaButton variant="secondary" surface="light" data-test="cta-secondary-light">{{ t('styleguide.buttons.sample_secondary') }}</CtaButton>
                    </div>
                </div>
                <div class="flex flex-col gap-6 rounded-[2rem] bg-gw-black p-8 text-gw-white">
                    <h3 class="font-display text-2xl font-bold uppercase">{{ t('styleguide.buttons.on_dark') }}</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <CtaButton variant="primary" surface="dark" href="/" data-test="cta-primary-dark">{{ t('styleguide.buttons.sample_primary') }}</CtaButton>
                        <CtaButton variant="secondary" surface="dark" data-test="cta-secondary-dark">{{ t('styleguide.buttons.sample_secondary') }}</CtaButton>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gw-black/70">{{ t('styleguide.buttons.scroll_hint') }}</p>
        </div>
    </section>

    <section
        v-for="(surface, index) in scrollBands"
        :key="index"
        :aria-label="t('styleguide.buttons.scroll_band', { number: index + 1 })"
        class="flex min-h-[80vh] items-center px-4 sm:px-8"
        :class="surface === 'dark' ? 'bg-gw-black text-gw-white' : 'bg-gw-white'"
    >
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
            <p class="font-display text-5xl font-extrabold uppercase">{{ t('styleguide.buttons.scroll_band', { number: index + 1 }) }}</p>
            <div class="flex flex-wrap items-center gap-4">
                <CtaButton variant="primary" :surface="surface" href="#sg-buttons">{{ t('styleguide.buttons.sample_long') }}</CtaButton>
                <CtaButton variant="secondary" :surface="surface" href="#sg-buttons">{{ t('styleguide.buttons.sample_back') }}</CtaButton>
            </div>
        </div>
    </section>

    <section aria-labelledby="sg-type" class="bg-gw-black px-4 py-16 text-gw-white sm:px-8">
        <div class="mx-auto flex max-w-6xl flex-col gap-8">
            <div class="flex flex-col gap-2">
                <h2 id="sg-type" class="font-display text-4xl font-extrabold uppercase">{{ t('styleguide.typography.title') }}</h2>
                <p class="text-gw-white/80">{{ t('styleguide.typography.intro') }}</p>
            </div>
            <dl class="flex flex-col divide-y divide-gw-charcoal">
                <div v-for="sample in typeSamples" :key="sample.label" class="flex flex-col gap-2 py-6">
                    <dt class="text-sm font-medium text-gw-yellow">{{ t(`styleguide.typography.${sample.label}`) }}</dt>
                    <dd :class="sample.classes">{{ t(`styleguide.typography.${sample.sample}`) }}</dd>
                </div>
            </dl>
        </div>
    </section>
</template>
