<script setup>
import { useTranslations } from '../Composables/useTranslations';

/**
 * Layout without site chrome (no header, no mobile CTA bar), for pages that
 * stand on their own: /styleguide and the error pages.
 */
const { t } = useTranslations();
</script>

<template>
    <a href="#main" class="gw-skip-link">{{ t('common.skip_to_content') }}</a>
    <main id="main" tabindex="-1">
        <slot />
    </main>
</template>

<style scoped>
.gw-skip-link {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    z-index: 50;
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
