<script setup>
import { vReveal } from '../Composables/useReveal';

/**
 * Header of a home section (prototype home.html): a small eyebrow with a dot, a
 * big condensed title whose last words are the accent.
 *
 * The accent is yellow on dark sections and a yellow highlighter stroke under
 * black text on light ones (yellow text on light grey fails contrast).
 * The title fades in with v-reveal; `titleId` is for aria-labelledby.
 */
defineProps({
    eyebrow: {
        type: String,
        required: true,
    },
    titleLead: {
        type: String,
        required: true,
    },
    titleAccent: {
        type: String,
        default: '',
    },
    titleId: {
        type: String,
        required: true,
    },
    tone: {
        type: String,
        default: 'dark',
        validator: (value) => ['dark', 'light'].includes(value),
    },
    align: {
        type: String,
        default: 'start',
        validator: (value) => ['start', 'center'].includes(value),
    },
});
</script>

<template>
    <div class="gw-intro" :class="[`gw-intro--${tone}`, `gw-intro--${align}`]">
        <p class="gw-intro__eyebrow"><span class="gw-intro__dot" aria-hidden="true" />{{ eyebrow }}</p>
        <h2 :id="titleId" v-reveal class="gw-intro__title">
            {{ titleLead }}
            <span v-if="titleAccent" class="gw-intro__accent">{{ titleAccent }}</span>
        </h2>
        <slot />
    </div>
</template>

<style scoped>
.gw-intro--center {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.gw-intro__eyebrow {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    color: var(--color-gw-yellow);
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.gw-intro__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.gw-intro--light .gw-intro__eyebrow {
    color: var(--color-gw-black);
}

.gw-intro--light .gw-intro__dot {
    background: var(--color-gw-yellow);
    box-shadow: 0 0 0 1px var(--color-gw-black);
}

.gw-intro__title {
    margin-top: 12px;
    font-family: var(--gw-font-barlow);
    font-size: clamp(44px, min(6.4vw, 11vh), 104px);
    font-weight: 800;
    line-height: 0.88;
    text-transform: uppercase;
}

.gw-intro--dark .gw-intro__accent {
    color: var(--color-gw-yellow);
}

.gw-intro--light .gw-intro__accent {
    background: linear-gradient(transparent 62%, var(--color-gw-yellow) 62% 92%, transparent 92%);
    box-decoration-break: clone;
}
</style>
