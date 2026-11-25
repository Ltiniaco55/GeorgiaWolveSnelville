<script setup>
import { ref } from 'vue';
import BrandLogo from '../Components/BrandLogo.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Achievements ticker (docs/design/home-interacciones.md): a yellow band right
 * below the hero, with rounded top corners, that slides over the sticky hero as
 * the page scrolls. The row moves on a loop (two copies, the second hidden from
 * assistive technology) and pauses on hover.
 *
 * A pause button appears on keyboard focus (WCAG 2.2.2: moving content can be
 * stopped without a mouse). With reduced motion nothing moves and the items wrap.
 */
const { t, tList } = useTranslations();
const isPaused = ref(false);
</script>

<template>
    <section class="gw-ticker" :class="{ 'gw-ticker--paused': isPaused }" :aria-label="t('home.ticker.label')" data-test="ticker">
        <button type="button" class="gw-ticker__toggle" :aria-pressed="isPaused ? 'true' : 'false'" @click="isPaused = !isPaused">
            {{ isPaused ? t('home.ticker.play') : t('home.ticker.pause') }}
        </button>

        <div class="gw-ticker__row">
            <ul v-for="copy in 2" :key="copy" class="gw-ticker__list" :aria-hidden="copy === 2 ? 'true' : undefined">
                <li v-for="item in tList('home.ticker.items')" :key="item" class="gw-ticker__item">
                    <BrandLogo variant="wolf-yellow-black" class="size-[30px]" decorative />
                    {{ item }}
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.gw-ticker {
    --gw-focus-ring: var(--color-gw-black);

    position: relative;
    z-index: 2;
    overflow: hidden;
    padding: 22px 0 66px;
    border-radius: 44px 44px 0 0;
    background: var(--color-gw-yellow);
    color: var(--color-gw-black);
}

.gw-ticker__row {
    display: flex;
    width: max-content;
    animation: gw-ticker 38s linear infinite;
}

.gw-ticker__list {
    display: flex;
}

.gw-ticker__item {
    display: flex;
    gap: 26px;
    align-items: center;
    padding-right: 26px;
    font-family: var(--gw-font-barlow);
    font-size: 34px;
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
    text-transform: uppercase;
}

.gw-ticker:hover .gw-ticker__row,
.gw-ticker--paused .gw-ticker__row {
    animation-play-state: paused;
}

/* Visible only while focused with the keyboard. */
.gw-ticker__toggle {
    position: absolute;
    top: 8px;
    right: 24px;
    z-index: 1;
    padding: 6px 14px;
    border-radius: 999px;
    background: var(--color-gw-black);
    color: var(--color-gw-white);
    font-size: 13px;
    font-weight: 600;
    opacity: 0;
    pointer-events: none;
}

.gw-ticker__toggle:focus-visible {
    opacity: 1;
    pointer-events: auto;
}

@keyframes gw-ticker {
    to {
        transform: translate3d(-50%, 0, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-ticker__row {
        width: auto;
        justify-content: center;
        padding-inline: var(--gw-gutter);
        animation: none;
    }

    .gw-ticker__list {
        flex-wrap: wrap;
        row-gap: 14px;
        justify-content: center;
    }

    .gw-ticker__list[aria-hidden='true'],
    .gw-ticker__toggle {
        display: none;
    }
}
</style>
