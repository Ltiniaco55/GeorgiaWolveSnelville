<script setup>
import { computed, onBeforeUnmount, onMounted, ref, useAttrs } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useReducedMotion } from '../Composables/useReducedMotion';

/**
 * Brand CTA (docs/design/direccion-de-diseno.md, "Botón CTA — versión final aprobada").
 *
 * A circle with an arrow that rolls open into a pill when it enters the viewport
 * and folds back when it leaves. Inset 3D look, charcoal on hover.
 * Renders an Inertia <Link> for internal URLs, an <a> for external/anchor URLs
 * and a <button> without href. The label is always in the DOM, so the accessible
 * name exists even while the button is a circle.
 *
 * The button sits in a slot that always reserves the open pill's width, so opening
 * and folding never moves the layout (no wrapping rows, no CLS). The slot, not the
 * button, is observed, with hysteresis: it opens once fully inside the viewport
 * minus a 10 % margin and folds only after leaving the viewport completely.
 * `class` and `style` go to the slot (layout); every other attribute to the button.
 */
defineOptions({ inheritAttrs: false });

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary'].includes(value),
    },
    surface: {
        type: String,
        default: 'light',
        validator: (value) => ['light', 'dark'].includes(value),
    },
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
        validator: (value) => ['button', 'submit', 'reset'].includes(value),
    },
    align: {
        type: String,
        default: 'start',
        validator: (value) => ['start', 'end'].includes(value),
    },
    /**
     * Always a pill, never folded: for buttons that live in the fixed header, at the
     * top edge the scroll-in observer never counts as inside (rootMargin -10 %).
     */
    alwaysOpen: {
        type: Boolean,
        default: false,
    },
    /** "large": 72 px and 19 px text (final CTA "Join the pack."); 60 px and 17 px on mobile. */
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'large'].includes(value),
    },
    /** Keeps the button folded (e.g. during the brand intro); it opens once released. */
    hold: {
        type: Boolean,
        default: false,
    },
});

const attrs = useAttrs();
const slotAttrs = computed(() => ({ class: attrs.class, style: attrs.style }));
const buttonAttrs = computed(() => {
    const { class: _class, style: _style, ...rest } = attrs;

    return rest;
});

const prefersReducedMotion = useReducedMotion();
const slotElement = ref(null);
const label = ref(null);
const isInView = ref(false);
const openWidth = ref(null);

const isInternalLink = computed(() => {
    if (!props.href || props.href.startsWith('#')) {
        return false;
    }

    const url = new URL(props.href, window.location.href);

    return url.origin === window.location.origin && ['http:', 'https:'].includes(url.protocol);
});

const tag = computed(() => {
    if (!props.href) {
        return 'button';
    }

    return isInternalLink.value ? Link : 'a';
});

const isOpen = computed(() => prefersReducedMotion.value || props.alwaysOpen || (!props.hold && isInView.value));

let openObserver = null;
let closeObserver = null;
let resizeObserver = null;

function measure() {
    if (label.value) {
        openWidth.value = `${label.value.scrollWidth}px`;
    }
}

onMounted(() => {
    measure();

    resizeObserver = new ResizeObserver(measure);
    resizeObserver.observe(label.value);

    openObserver = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting && entry.intersectionRatio >= 1) {
                isInView.value = true;
            }
        },
        { rootMargin: '-10% 0px -10% 0px', threshold: 1 },
    );
    openObserver.observe(slotElement.value);

    closeObserver = new IntersectionObserver(
        ([entry]) => {
            if (!entry.isIntersecting) {
                isInView.value = false;
            }
        },
        { threshold: 0 },
    );
    closeObserver.observe(slotElement.value);
});

onBeforeUnmount(() => {
    openObserver?.disconnect();
    closeObserver?.disconnect();
    resizeObserver?.disconnect();
});
</script>

<template>
    <span
        ref="slotElement"
        class="gw-cta-slot"
        :class="[`gw-cta-slot--${align}`, `gw-cta-slot--${size}`, slotAttrs.class]"
        :style="[openWidth ? { '--gw-cta-open': openWidth } : undefined, slotAttrs.style]"
    >
        <component
            :is="tag"
            v-bind="buttonAttrs"
            :href="href ?? undefined"
            :type="href ? undefined : type"
            class="gw-cta"
            :class="[`gw-cta--${variant}`, `gw-cta--${surface}`, { 'gw-cta--open': isOpen }]"
        >
            <span ref="label" class="gw-cta__label"><slot /></span>
            <span class="gw-cta__arrow" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M5 12h14" />
                    <path d="M13 6l6 6-6 6" />
                </svg>
            </span>
        </component>
    </span>
</template>

<style scoped>
/* Reserves the open pill's width so the layout never changes with the state. */
.gw-cta-slot {
    --gw-cta-size: 56px;
    --gw-cta-open: var(--gw-cta-size);

    display: inline-flex;
    flex: none;
    justify-content: flex-start;
    width: var(--gw-cta-open);
    max-width: 100%;
    vertical-align: middle;
}

.gw-cta-slot--end {
    justify-content: flex-end;
}

.gw-cta {
    --gw-cta-ease-open: cubic-bezier(0.65, 0, 0.3, 1);
    --gw-cta-ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);

    position: relative;
    display: inline-flex;
    flex: none;
    align-items: center;
    width: var(--gw-cta-size);
    height: var(--gw-cta-size);
    padding: 0;
    overflow: hidden;
    border: 0;
    border-radius: 999px;
    font-family: var(--gw-font-inter);
    font-size: 16px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    transition:
        width 1.4s var(--gw-cta-ease-open),
        background-color 0.25s,
        color 0.25s,
        box-shadow 0.25s,
        filter 0.1s,
        transform 0.35s var(--gw-cta-ease-bounce);
}

/* Keyboard users always see the label, even while the button is a circle. */
.gw-cta--open,
.gw-cta:focus-visible {
    width: var(--gw-cta-open);
}

.gw-cta__label {
    flex: none;
    padding: 0 calc(var(--gw-cta-size) + 2px) 0 26px;
    opacity: 0;
    transform: translateX(-8px);
    transition:
        opacity 0.4s,
        transform 0.4s;
}

.gw-cta--open .gw-cta__label {
    opacity: 1;
    transform: none;
    transition:
        opacity 0.6s 0.75s,
        transform 0.6s 0.75s;
}

.gw-cta:focus-visible .gw-cta__label {
    opacity: 1;
    transform: none;
}

/*
 * The arrow rides the right edge with a transform (same duration and easing as
 * the width), not with `right: 0`: moving it through layout counts as a layout shift.
 */
.gw-cta__arrow {
    position: absolute;
    top: 0;
    left: 0;
    display: grid;
    place-items: center;
    width: var(--gw-cta-size);
    height: var(--gw-cta-size);
    transition: transform 1.4s var(--gw-cta-ease-open);
}

.gw-cta--open .gw-cta__arrow,
.gw-cta:focus-visible .gw-cta__arrow {
    transform: translateX(calc(var(--gw-cta-open) - var(--gw-cta-size)));
}

.gw-cta__arrow svg {
    width: 22px;
    height: 22px;
    transition: transform 0.3s;
}

/* Surfaces: focus ring has to contrast with the section behind the button. */
.gw-cta--light {
    --gw-focus-ring: var(--color-gw-black);
}

.gw-cta--dark {
    --gw-focus-ring: var(--color-gw-yellow);
}

/* Inset (sunken) look, per variant and surface. */
.gw-cta--primary {
    background-color: var(--color-gw-yellow);
    color: var(--color-gw-black);
}

.gw-cta--primary .gw-cta__label,
.gw-cta--secondary.gw-cta--light .gw-cta__label {
    text-shadow: 0 1px 0 rgb(255 255 255 / 0.55);
}

.gw-cta--primary.gw-cta--light {
    box-shadow:
        inset 0 4px 9px rgb(0 0 0 / 0.38),
        inset 0 -2px 4px rgb(255 255 255 / 0.6),
        0 1px 0 rgb(255 255 255 / 0.9),
        0 -1px 0 rgb(0 0 0 / 0.1);
}

.gw-cta--primary.gw-cta--dark {
    box-shadow:
        inset 0 4px 9px rgb(0 0 0 / 0.45),
        inset 0 -2px 4px rgb(255 255 255 / 0.55),
        0 1px 0 rgb(255 255 255 / 0.14),
        0 -1px 0 rgb(0 0 0 / 0.6);
}

.gw-cta--secondary.gw-cta--light {
    background-color: rgb(0 0 0 / 0.03);
    color: var(--color-gw-black);
    box-shadow:
        inset 0 0 0 1.5px var(--color-gw-black),
        inset 0 4px 8px rgb(0 0 0 / 0.2),
        inset 0 -2px 3px rgb(255 255 255 / 0.95),
        0 1px 0 var(--color-gw-white);
}

.gw-cta--secondary.gw-cta--dark {
    background-color: rgb(255 255 255 / 0.04);
    color: var(--color-gw-white);
    box-shadow:
        inset 0 0 0 1.5px var(--color-gw-white),
        inset 0 4px 10px rgb(0 0 0 / 0.75),
        inset 0 -2px 4px rgb(255 255 255 / 0.14),
        0 1px 0 rgb(255 255 255 / 0.1);
}

.gw-cta--secondary.gw-cta--dark .gw-cta__label {
    text-shadow: 0 -1px 0 rgb(0 0 0 / 0.6);
}

/* Hover only exists with a real mouse (CLAUDE.md). Charcoal keeps the inset visible. */
@media (hover: hover) and (pointer: fine) {
    .gw-cta:hover {
        transform: scale(1.09);
    }

    .gw-cta:hover .gw-cta__arrow svg {
        transform: translateX(3px);
    }

    .gw-cta--primary.gw-cta--light:hover,
    .gw-cta--secondary.gw-cta--light:hover {
        background-color: var(--color-gw-charcoal);
        color: var(--color-gw-white);
        box-shadow:
            inset 0 0 0 1.5px var(--color-gw-black),
            inset 0 6px 14px rgb(0 0 0 / 0.95),
            inset 0 -3px 6px rgb(255 255 255 / 0.28),
            0 1px 0 var(--color-gw-white);
    }

    .gw-cta--primary.gw-cta--dark:hover {
        background-color: var(--color-gw-charcoal);
        color: var(--color-gw-yellow);
        box-shadow:
            inset 0 0 0 1.5px var(--color-gw-yellow),
            inset 0 6px 14px rgb(0 0 0 / 0.95),
            inset 0 -3px 6px rgb(255 255 255 / 0.22),
            0 1px 0 rgb(255 255 255 / 0.16);
    }

    .gw-cta--primary:hover .gw-cta__label,
    .gw-cta--secondary.gw-cta--light:hover .gw-cta__label {
        text-shadow: 0 -1px 0 rgb(0 0 0 / 0.6);
    }

    .gw-cta--secondary.gw-cta--dark:hover {
        background-color: var(--color-gw-white);
        color: var(--color-gw-black);
        box-shadow:
            inset 0 0 0 1.5px var(--color-gw-white),
            inset 0 4px 9px rgb(0 0 0 / 0.32),
            inset 0 -2px 4px var(--color-gw-white),
            0 1px 0 rgb(255 255 255 / 0.1);
    }

    .gw-cta--secondary.gw-cta--dark:hover .gw-cta__label {
        text-shadow: 0 1px 0 rgb(255 255 255 / 0.8);
    }
}

.gw-cta:active {
    filter: brightness(0.94);
    transform: scale(1.03);
}

@media (max-width: 639px) {
    .gw-cta-slot {
        --gw-cta-size: 52px;
    }

    .gw-cta {
        font-size: 15px;
    }
}

.gw-cta-slot--large {
    --gw-cta-size: 72px;
}

.gw-cta-slot--large .gw-cta {
    font-size: 19px;
}

@media (max-width: 639px) {
    .gw-cta-slot--large {
        --gw-cta-size: 60px;
    }

    .gw-cta-slot--large .gw-cta {
        font-size: 17px;
    }
}

/* Reduced motion: always open (set in the script), no width animation, no scaling. */
@media (prefers-reduced-motion: reduce) {
    .gw-cta,
    .gw-cta__label,
    .gw-cta__arrow,
    .gw-cta__arrow svg {
        transition: none;
    }

    .gw-cta:hover,
    .gw-cta:active,
    .gw-cta:hover .gw-cta__arrow svg {
        transform: none;
    }
}
</style>
