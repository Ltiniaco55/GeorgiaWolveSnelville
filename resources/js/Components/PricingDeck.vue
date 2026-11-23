<script setup>
import { computed, ref } from 'vue';
import { useDeckScroll } from '../Composables/useDeckScroll';
import { useMediaQuery } from '../Composables/useMediaQuery';
import { useReducedMotion } from '../Composables/useReducedMotion';
import { useTranslations } from '../Composables/useTranslations';
import PricingCard from './PricingCard.vue';

/**
 * Pricing cards (docs/design/direccion-de-diseno.md, "Card de tarifas").
 *
 * From 640 px: the cards in their normal order (a row from 1024 px).
 * Below 640 px: a "deck". The cards are stacked with the featured plan on top and
 * the others peeking out below, shifted and slightly rotated. The section pins to
 * the scroll; each step sends the top card up, spinning, to the back of the deck.
 * A horizontal swipe, the dots or keyboard focus also bring a card to the top.
 * All cards stay in the DOM in their mobile order, so Tab reaches all of them.
 * With reduced motion the mobile cards are simply stacked vertically.
 */
const props = defineProps({
    /** @type {import('vue').PropType<import('./PricingCard.vue').PricingPlan[]>} */
    plans: {
        type: Array,
        required: true,
    },
    featuredId: {
        type: String,
        required: true,
    },
    /** Plan ids from the top of the deck down. Defaults to the reverse of `plans`. */
    mobileOrder: {
        type: Array,
        default: null,
    },
});

/** Rotation (deg) of each deck position when it is not on top: Annual, Monthly, Free trial. */
const PEEK_ROTATIONS = [2.5, -3, 3.5];
/** Vertical offset (px) and scale lost per card of depth. */
const PEEK_OFFSET = 20;
const PEEK_SCALE = 0.03;
/** How far up (% of its height) and how much (deg) the leaving card flies and spins. */
const LEAVE_LIFT = 70;
const LEAVE_ROTATION = -12;

const { t } = useTranslations();
const isMobile = useMediaQuery('(max-width: 639.98px)');
const prefersReducedMotion = useReducedMotion();
const isDeck = computed(() => isMobile.value && !prefersReducedMotion.value);

const orderedPlans = computed(() => {
    if (!isMobile.value) {
        return props.plans;
    }

    const order = props.mobileOrder ?? props.plans.map((plan) => plan.id).reverse();

    return order.map((id) => props.plans.find((plan) => plan.id === id)).filter(Boolean);
});

const section = ref(null);
const { position, activeIndex, sectionStyle, goTo, swipeHandlers } = useDeckScroll(section, {
    count: props.plans.length,
    enabled: isDeck,
});

/**
 * Resting transform of the card at deck position `index` when it sits `depth` cards deep.
 *
 * @param {number} index
 * @param {number} depth
 */
function restingPose(index, depth) {
    return {
        lift: 0,
        offset: depth * PEEK_OFFSET,
        rotation: PEEK_ROTATIONS[index % PEEK_ROTATIONS.length] * Math.min(depth, 1),
        scale: 1 - depth * PEEK_SCALE,
    };
}

/**
 * Transform and stacking of each card for the current deck position. Opacity stays at 1:
 * a see-through card flying over the next one mixes both texts.
 *
 * @param {number} index
 * @returns {Record<string, string|number>}
 */
function cardStyle(index) {
    const count = orderedPlans.value.length;
    const relative = index - position.value;
    let pose;
    let zIndex;

    if (relative < 0 && relative > -1) {
        // Leaving: first half flies up and spins, second half tucks in at the back.
        const progress = -relative;
        const back = restingPose(index, count - 1);

        if (progress < 0.5) {
            const k = progress / 0.5;
            pose = { lift: k * LEAVE_LIFT, offset: 0, rotation: k * LEAVE_ROTATION, scale: 1 };
            zIndex = count * 10 + 5;
        } else {
            const k = (progress - 0.5) / 0.5;
            pose = {
                lift: (1 - k) * LEAVE_LIFT,
                offset: k * back.offset,
                rotation: LEAVE_ROTATION + k * (back.rotation - LEAVE_ROTATION),
                scale: 1 + k * (back.scale - 1),
            };
            zIndex = 0;
        }
    } else {
        const depth = ((relative % count) + count) % count;
        pose = restingPose(index, depth);
        zIndex = count * 10 - Math.round(depth * 10);
    }

    return {
        transform: `translate3d(0, calc(${(-pose.lift).toFixed(2)}% + ${pose.offset.toFixed(2)}px), 0) rotate(${pose.rotation.toFixed(2)}deg) scale(${pose.scale.toFixed(4)})`,
        zIndex,
    };
}

/**
 * A card that receives keyboard focus comes to the top.
 *
 * @param {number} index
 */
function onCardFocus(index) {
    if (isDeck.value && index !== activeIndex.value) {
        goTo(index, 'instant');
    }
}
</script>

<template>
    <div ref="section" class="gw-deck" :class="{ 'gw-deck--active': isDeck }" :style="sectionStyle" data-test="pricing-deck" :data-top-plan="isDeck ? orderedPlans[activeIndex]?.id : undefined">
        <div class="gw-deck__stage">
            <div class="gw-deck__cards" :class="isDeck ? '' : 'grid grid-cols-1 gap-8 lg:grid-cols-3'" v-on="isDeck ? swipeHandlers : {}">
                <div
                    v-for="(plan, index) in orderedPlans"
                    :key="plan.id"
                    class="gw-deck__card"
                    :class="{ 'gw-deck__card--behind': isDeck && index !== activeIndex }"
                    :style="isDeck ? cardStyle(index) : undefined"
                    @focusin="onCardFocus(index)"
                >
                    <PricingCard :plan="plan" :featured="plan.id === featuredId" class="h-full" />
                </div>
            </div>

            <div v-if="isDeck" class="flex flex-col items-center gap-3">
                <div class="flex items-center gap-1" role="group" :aria-label="t('common.pricing_deck.plans')">
                    <button
                        v-for="(plan, index) in orderedPlans"
                        :key="plan.id"
                        type="button"
                        class="gw-deck__dot"
                        :aria-label="t('common.pricing_deck.dot', { current: index + 1, total: orderedPlans.length })"
                        :aria-current="index === activeIndex ? 'true' : undefined"
                        :data-test="`deck-dot-${index + 1}`"
                        @click="goTo(index)"
                    />
                </div>
                <p class="text-xs font-medium text-gw-black/75">{{ t('common.pricing_deck.hint') }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.gw-deck--active .gw-deck__stage {
    position: sticky;
    top: 0;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    justify-content: center;
    height: 100svh;
    padding-block: 1.5rem 1rem;

    /* Rotated cards must not create horizontal scroll; clip keeps sticky working. */
    overflow-x: clip;
}

/* All cards share one grid cell: the deck is as tall as the tallest card. */
.gw-deck--active .gw-deck__cards {
    display: grid;
    padding-bottom: 2rem;
    touch-action: pan-y;
}

.gw-deck--active .gw-deck__card {
    grid-area: 1 / 1;
    transform-origin: 50% 85%;
    will-change: transform;
}

.gw-deck__card--behind {
    pointer-events: none;
}

/* 24 px tap target around a small dot; the active dot is a black pill. */
.gw-deck__dot {
    display: grid;
    place-items: center;
    width: 32px;
    height: 24px;
    padding: 0;
    border: 0;
    background: none;
    cursor: pointer;
}

.gw-deck__dot::before {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: rgb(10 10 10 / 0.55);
    content: '';
    transition:
        width 0.3s,
        background-color 0.3s;
}

.gw-deck__dot[aria-current='true']::before {
    width: 24px;
    background: var(--color-gw-black);
}
</style>
