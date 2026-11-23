<script setup>
import MobileCtaBar from '../Components/MobileCtaBar.vue';
import SiteHeader from '../Components/SiteHeader.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Site layout: skip link, fixed header, page content and, on mobile, the fixed
 * bottom CTA bar (the content keeps room for it below 1024 px).
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

/* Room for the mobile CTA bar (48 px button + 2 × 12 px padding + safe area). */
.gw-main {
    padding-bottom: calc(72px + env(safe-area-inset-bottom));
}

@media (min-width: 1024px) {
    .gw-main {
        padding-bottom: 0;
    }
}
</style>
