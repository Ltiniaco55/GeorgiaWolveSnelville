<script setup>
import CtaButton from './CtaButton.vue';
import NeuCard from './NeuCard.vue';

/**
 * Coach card for the #161616 section (docs/design/direccion-de-diseno.md,
 * "Card de entrenador"). Horizontal raised card with 32 px corners: photo 5/12
 * in a pressed-in hollow, content 7/12; stacked on mobile. Tilts towards the
 * pointer and slowly zooms the photo on hover (fine pointer, no reduced motion).
 *
 * Until the real photo arrives, the hollow shows the coach's initials.
 *
 * @typedef {Object} Coach
 * @property {string} role
 * @property {string} name
 * @property {string} initials
 * @property {string} licence Yellow pill over the photo.
 * @property {string} bio
 * @property {{ value: string, label: string }[]} stats
 * @property {string} coachingTitle
 * @property {{ period: string, title: string, place: string }[]} coachingCareer
 * @property {string} playingTitle
 * @property {string[]} playingCareer
 * @property {{ label: string, href: string }} primaryCta
 * @property {{ label: string, href: string }} secondaryCta
 * @property {string|null} [photo] Image URL; initials are shown without it.
 * @property {string} [photoAlt]
 */
defineProps({
    /** @type {import('vue').PropType<Coach>} */
    coach: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <NeuCard as="article" surface="dark" hover="tilt" class="gw-coach grid grid-cols-1 gap-2 p-4 lg:grid-cols-12 [--gw-neu-radius:32px]" aria-labelledby="coach-name" data-test="coach-card">
        <div class="gw-coach__hollow relative aspect-[4/5] overflow-hidden lg:col-span-5 lg:aspect-auto lg:min-h-[32rem]">
            <img v-if="coach.photo" :src="coach.photo" :alt="coach.photoAlt ?? ''" class="gw-coach__photo h-full w-full object-cover" />
            <div v-else class="gw-coach__photo grid h-full w-full place-items-center" role="img" :aria-label="coach.photoAlt ?? coach.name">
                <span class="font-display text-[7rem] leading-none font-extrabold text-gw-white/15" aria-hidden="true">{{ coach.initials }}</span>
            </div>
            <span class="absolute top-4 left-4 z-[1] rounded-full bg-gw-yellow px-3 py-1 text-xs font-semibold tracking-wide text-gw-black uppercase">
                {{ coach.licence }}
            </span>
        </div>

        <div class="flex min-w-0 flex-col gap-6 p-4 sm:p-6 lg:col-span-7 lg:p-10">
            <div class="flex flex-col gap-2">
                <p class="text-sm font-semibold tracking-widest text-gw-yellow uppercase">{{ coach.role }}</p>
                <h3 id="coach-name" class="font-display text-5xl leading-[0.9] font-extrabold break-words uppercase sm:text-7xl">{{ coach.name }}</h3>
            </div>

            <p class="max-w-prose text-gw-white/80">{{ coach.bio }}</p>

            <dl class="grid grid-cols-3 gap-3">
                <div v-for="stat in coach.stats" :key="stat.label" class="flex min-w-0 flex-col items-center gap-1 rounded-2xl p-3 text-center shadow-gw-inset-dark-sm">
                    <dt class="order-2 text-xs text-gw-white/70">{{ stat.label }}</dt>
                    <dd class="order-1 font-display text-2xl font-bold text-gw-yellow sm:text-3xl">{{ stat.value }}</dd>
                </div>
            </dl>

            <div class="flex flex-col gap-3">
                <h4 class="text-sm font-semibold tracking-widest text-gw-white/70 uppercase">{{ coach.coachingTitle }}</h4>
                <ol class="flex flex-col">
                    <li v-for="step in coach.coachingCareer" :key="step.period + step.title" class="gw-coach__step flex flex-wrap items-baseline gap-x-4 gap-y-1 py-3">
                        <span class="w-24 flex-none text-sm font-semibold text-gw-yellow">{{ step.period }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="font-semibold">{{ step.title }}</span>
                            <span class="text-gw-white/70"> · {{ step.place }}</span>
                        </span>
                    </li>
                </ol>
            </div>

            <div class="flex flex-col gap-3">
                <h4 class="text-sm font-semibold tracking-widest text-gw-white/70 uppercase">{{ coach.playingTitle }}</h4>
                <ul class="flex flex-wrap gap-3">
                    <li v-for="club in coach.playingCareer" :key="club" class="rounded-full px-4 py-2 text-sm font-medium shadow-gw-raised-dark-sm">{{ club }}</li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-4 pt-2">
                <CtaButton variant="primary" surface="dark" :href="coach.primaryCta.href">{{ coach.primaryCta.label }}</CtaButton>
                <CtaButton variant="secondary" surface="dark" :href="coach.secondaryCta.href">{{ coach.secondaryCta.label }}</CtaButton>
            </div>
        </div>
    </NeuCard>
</template>

<style scoped>
/*
 * Photo sits in a pressed-in hollow: 16 px margin (card padding) and 24 px corners.
 * The inset shadow is an overlay so it also falls over the photo.
 */
.gw-coach__hollow {
    border-radius: 24px;
    background: var(--color-gw-neu-dark);
}

.gw-coach__hollow::after {
    position: absolute;
    inset: 0;
    border-radius: inherit;
    box-shadow: var(--shadow-gw-inset-dark);
    content: '';
    pointer-events: none;
}

/* Engraved separators between timeline steps. */
.gw-coach__step + .gw-coach__step {
    border-top: 1px solid var(--color-gw-neu-dark-shade);
    box-shadow: inset 0 1px 0 var(--color-gw-neu-dark-highlight);
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
    .gw-coach__photo {
        transition: transform 1.6s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .gw-coach:hover .gw-coach__photo {
        transform: scale(1.08);
    }
}
</style>
