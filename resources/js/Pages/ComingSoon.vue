<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import BrandLogo from '../Components/BrandLogo.vue';
import CtaButton from '../Components/CtaButton.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Placeholder for a page that is not built yet (e.g. /programs): black, with the
 * emblem, "Coming soon" and the way to the free training and back home. Not
 * indexed by search engines.
 */
const props = defineProps({
    /** Key under lang/{locale}/coming_soon.php "pages" (e.g. "programs"). */
    section: {
        type: String,
        required: true,
    },
});

const page = usePage();
const { t } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});
const title = computed(() => t(`coming_soon.pages.${props.section}`));
</script>

<template>
    <Head :title="t('coming_soon.meta_title', { page: title })">
        <meta name="robots" content="noindex" />
    </Head>

    <section class="gw-soon" aria-labelledby="soon-title" data-site-hero data-test="coming-soon">
        <BrandLogo variant="wolf-yellow" class="h-28 sm:h-36" decorative />
        <p class="gw-soon__eyebrow">{{ title }}</p>
        <h1 id="soon-title" class="gw-soon__title">
            {{ t('coming_soon.title_lead') }}
            <span class="block text-gw-yellow">{{ t('coming_soon.title_accent') }}</span>
        </h1>
        <p class="max-w-prose text-lg text-gw-white/80">{{ t('coming_soon.body') }}</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <CtaButton variant="primary" surface="dark" :href="navigation.free_training">{{ t('coming_soon.cta_primary') }}</CtaButton>
            <CtaButton variant="secondary" surface="dark" :href="navigation.home">{{ t('coming_soon.cta_secondary') }}</CtaButton>
        </div>
    </section>
</template>

<style scoped>
.gw-soon {
    --gw-focus-ring: var(--color-gw-yellow);

    display: flex;
    flex-direction: column;
    gap: 24px;
    align-items: center;
    justify-content: center;
    min-height: 100svh;
    padding: calc(var(--gw-edge) + var(--gw-header-h) + 40px) calc(var(--gw-edge) + var(--gw-gutter)) 140px;
    background: var(--color-gw-black);
    color: var(--color-gw-white);
    text-align: center;
}

.gw-soon__eyebrow {
    color: var(--color-gw-yellow);
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.gw-soon__title {
    font-family: var(--gw-font-barlow);
    font-size: clamp(56px, 10vw, 140px);
    font-weight: 800;
    line-height: 0.86;
    text-transform: uppercase;
}
</style>
