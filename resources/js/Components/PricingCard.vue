<script setup>
import CtaButton from './CtaButton.vue';
import NeuCard from './NeuCard.vue';

/**
 * Pricing plan card for the #ECECEC section (docs/design/direccion-de-diseno.md,
 * "Card de tarifas"). Regular plans are raised; the featured plan (Annual) is a
 * black panel with a yellow price, yellow checks and a "Best value" badge.
 * Expands on hover (the tilt was tried on these cards and rejected).
 *
 * @typedef {Object} PricingPlan
 * @property {string} id Used for ids and data-test.
 * @property {string} name
 * @property {string} price e.g. "$100"
 * @property {string} period e.g. "/ month"
 * @property {string} saving
 * @property {string} description
 * @property {string[]} features
 * @property {string} note Billing rule note (day 15).
 * @property {string} ctaLabel
 * @property {string} ctaHref
 * @property {string|null} [badge] Only on the featured plan.
 */
defineProps({
    /** @type {import('vue').PropType<PricingPlan>} */
    plan: {
        type: Object,
        required: true,
    },
    featured: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <NeuCard
        as="article"
        surface="light"
        :variant="featured ? 'featured' : 'raised'"
        hover="expand"
        class="gw-plan flex min-w-0 flex-col gap-6 p-6 sm:p-8"
        :class="{ 'gw-plan--featured': featured }"
        :aria-labelledby="`plan-${plan.id}-name`"
        :data-test="`plan-${plan.id}`"
    >
        <header class="flex flex-wrap items-center justify-between gap-3">
            <h3 :id="`plan-${plan.id}-name`" class="font-display text-2xl font-bold tracking-wide uppercase">{{ plan.name }}</h3>
            <span v-if="plan.badge" class="rounded-full bg-gw-yellow px-3 py-1 text-xs font-semibold tracking-wide text-gw-black uppercase">
                {{ plan.badge }}
            </span>
        </header>

        <div class="flex flex-col gap-1">
            <p class="flex flex-wrap items-baseline gap-x-2">
                <span class="font-display text-6xl leading-none font-extrabold" :class="featured ? 'text-gw-yellow' : ''">{{ plan.price }}</span>
                <span class="text-sm font-medium" :class="featured ? 'text-gw-white/75' : 'text-gw-black/70'">{{ plan.period }}</span>
            </p>
            <p class="text-sm font-semibold" :class="featured ? 'text-gw-yellow' : 'text-gw-black/75'">{{ plan.saving }}</p>
        </div>

        <p :class="featured ? 'text-gw-white/80' : 'text-gw-black/75'">{{ plan.description }}</p>

        <div class="gw-plan__divider" aria-hidden="true" />

        <ul class="flex flex-col gap-3">
            <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                <span class="gw-plan__check" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M5 12.5l4.5 4.5L19 7.5" />
                    </svg>
                </span>
                <span class="text-sm" :class="featured ? 'text-gw-white/90' : ''">{{ feature }}</span>
            </li>
        </ul>

        <div class="gw-plan__divider mt-auto" aria-hidden="true" />

        <p class="text-xs" :class="featured ? 'text-gw-white/70' : 'text-gw-black/70'">{{ plan.note }}</p>

        <div class="pt-2">
            <CtaButton :variant="featured ? 'primary' : 'secondary'" :surface="featured ? 'dark' : 'light'" :href="plan.ctaHref">
                {{ plan.ctaLabel }}
            </CtaButton>
        </div>
    </NeuCard>
</template>

<style scoped>
/* Engraved divider: a dark line over a light one. */
.gw-plan__divider {
    height: 2px;
    border-top: 1px solid var(--color-gw-neu-light-shade);
    border-bottom: 1px solid var(--color-gw-neu-light-highlight);
}

.gw-plan--featured .gw-plan__divider {
    border-top-color: #000;
    border-bottom-color: var(--color-gw-charcoal);
}

/* Regular plans: pressed-in circle with a black check. Featured: yellow circle. */
.gw-plan__check {
    display: grid;
    flex: none;
    place-items: center;
    width: 24px;
    height: 24px;
    border-radius: 999px;
    color: var(--color-gw-black);
    box-shadow: var(--shadow-gw-inset-light-sm);
}

.gw-plan--featured .gw-plan__check {
    background: var(--color-gw-yellow);
    box-shadow: none;
}

.gw-plan__check svg {
    width: 14px;
    height: 14px;
}
</style>
