<script setup>
import { vReveal } from '../Composables/useReveal';

/**
 * Header of a home section (prototype home.html): a small eyebrow with a dot, a
 * big condensed title whose last words are the accent, and an optional hint with
 * a cursor icon that invites to interact ("Move over a card"…).
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
    hint: {
        type: String,
        default: '',
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
        <p v-if="hint" v-reveal="2" class="gw-intro__hint" data-test="section-hint">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M4 3l7 17 2.5-7.5L21 10z" />
            </svg>
            {{ hint }}
        </p>
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

.gw-intro__hint {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    margin-top: 14px;
    color: #a3a3a3;
    font-size: 13px;
}

.gw-intro--light .gw-intro__hint {
    color: #595959;
}

.gw-intro__hint svg {
    width: 15px;
    height: 15px;
}
</style>
