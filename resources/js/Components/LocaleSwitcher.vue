<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTranslations } from '../Composables/useTranslations';

/**
 * EN/ES switcher. Each link goes to the same page in the other language
 * (shared `localeUrls`). Plain links, not Inertia visits: the whole document,
 * including <html lang>, has to change language.
 */
defineProps({
    size: {
        type: String,
        default: 'sm',
        validator: (value) => ['sm', 'lg'].includes(value),
    },
});

const page = usePage();
const { t } = useTranslations();
const locales = computed(() => Object.entries(page.props.localeUrls ?? {}));
</script>

<template>
    <div class="flex items-center gap-1" role="group" :aria-label="t('common.language.label')">
        <a
            v-for="[locale, url] in locales"
            :key="locale"
            :href="url"
            :hreflang="locale"
            :lang="locale"
            :aria-current="locale === page.props.locale ? 'true' : undefined"
            class="gw-locale"
            :class="`gw-locale--${size}`"
        >
            <span aria-hidden="true">{{ locale.toUpperCase() }}</span>
            <span class="sr-only">{{ t(`common.language.${locale}`) }}</span>
        </a>
    </div>
</template>

<style scoped>
.gw-locale {
    display: inline-grid;
    place-items: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding-inline: 0.5rem;
    border-radius: 999px;
    color: rgb(255 255 255 / 0.75);
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-decoration: none;
}

.gw-locale--lg {
    min-width: 3rem;
    height: 3rem;
    font-size: 1rem;
}

.gw-locale[aria-current='true'] {
    background: var(--color-gw-white);
    color: var(--color-gw-black);
}

@media (hover: hover) and (pointer: fine) {
    .gw-locale:not([aria-current='true']):hover {
        color: var(--color-gw-yellow);
    }
}
</style>
