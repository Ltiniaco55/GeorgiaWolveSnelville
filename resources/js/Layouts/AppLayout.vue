<script setup>
import MobileCtaBar from '../Components/MobileCtaBar.vue';
import SiteFooter from '../Components/SiteFooter.vue';
import SiteHeader from '../Components/SiteHeader.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Site layout: skip link, fixed header, page content, footer and, on mobile, the
 * fixed bottom CTA bar (the footer keeps room for it below 1024 px).
 * `gw-intro-clip` marks what the brand intro reveals through its circle.
 */
const { t } = useTranslations();
</script>

<template>
    <a href="#main" class="gw-skip-link">{{ t('common.skip_to_content') }}</a>
    <SiteHeader />
    <main id="main" tabindex="-1" class="gw-main gw-intro-clip">
        <slot />
    </main>
    <SiteFooter />
    <MobileCtaBar />
</template>

<style scoped>
.gw-skip-link {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    z-index: 70;
    padding: 0.75rem 1.25rem;
    border-radius: 999px;
    background: var(--color-gw-yellow);
    color: var(--color-gw-black);
    font-weight: 600;
    transform: translateY(-200%);
}

.gw-skip-link:focus-visible {
    transform: none;
}

main:focus {
    outline: none;
}
</style>
