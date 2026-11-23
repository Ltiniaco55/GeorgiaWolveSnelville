<script setup>
import { computed, watch } from 'vue';
import { useFinePointer } from '../Composables/useFinePointer';
import { useReducedMotion } from '../Composables/useReducedMotion';
import { useSpring } from '../Composables/useSpring';

/**
 * Neumorphic card (docs/design/direccion-de-diseno.md, "Cards — estilo neumorphism").
 *
 * The card has the colour of its section and only stands out through two outer
 * shadows (light top-left, dark bottom-right). `surface` must match the section
 * behind it: light = #ECECEC, dark = #161616.
 *
 * - variant raised: same colour as the section, raised.
 * - variant inset: same colour as the section, pressed in.
 * - variant featured: near-black panel (#0B0B0B) with a subtle inset that keeps
 *   the section's outer shadows, so it still belongs to it (Annual plan).
 *
 * - hover expand: scale(1.035) with a bounce in 0.4 s, the shadow grows with it.
 * - hover tilt: 3D tilt towards the pointer; the corner under the pointer sinks.
 *   Spring (stiffness 0.055, damping 0.86) in requestAnimationFrame, so it sways
 *   back to rest when the pointer leaves. Perspective 1000 px, 4° max.
 * - Both only with (hover: hover) and (pointer: fine); none with reduced motion.
 *
 * Corner radius: 28 px by default; override with the `--gw-neu-radius` custom property.
 */
const props = defineProps({
    as: {
        type: String,
        default: 'div',
    },
    surface: {
        type: String,
        default: 'light',
        validator: (value) => ['light', 'dark'].includes(value),
    },
    variant: {
        type: String,
        default: 'raised',
        validator: (value) => ['raised', 'inset', 'featured'].includes(value),
    },
    hover: {
        type: String,
        default: 'none',
        validator: (value) => ['expand', 'tilt', 'none'].includes(value),
    },
});

const TILT_MAX_DEGREES = 4;
const TILT_PERSPECTIVE = 1000;

const hasFinePointer = useFinePointer();
const prefersReducedMotion = useReducedMotion();
const isTiltActive = computed(() => props.hover === 'tilt' && hasFinePointer.value && !prefersReducedMotion.value);

const { values: tilt, setTarget, reset } = useSpring({ rotateX: 0, rotateY: 0 }, { stiffness: 0.055, damping: 0.86 });

watch(isTiltActive, (active) => {
    if (!active) {
        reset();
    }
});

/**
 * @param {PointerEvent} event
 */
function onPointerMove(event) {
    if (!isTiltActive.value || event.pointerType === 'touch') {
        return;
    }

    const rect = event.currentTarget.getBoundingClientRect();
    const pointerX = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    const pointerY = ((event.clientY - rect.top) / rect.height) * 2 - 1;
    const clamp = (value) => Math.min(1, Math.max(-1, value));

    // rotateY > 0 pushes the right edge away; rotateX > 0 pushes the top edge away.
    setTarget({
        rotateX: -clamp(pointerY) * TILT_MAX_DEGREES,
        rotateY: clamp(pointerX) * TILT_MAX_DEGREES,
    });
}

function onPointerLeave() {
    if (isTiltActive.value) {
        setTarget({ rotateX: 0, rotateY: 0 });
    }
}

const tiltStyle = computed(() => {
    if (!isTiltActive.value || (tilt.rotateX === 0 && tilt.rotateY === 0)) {
        return {};
    }

    // The spring overshoots its target; the drawn angle never goes past the maximum.
    const limit = (degrees) => Math.min(TILT_MAX_DEGREES, Math.max(-TILT_MAX_DEGREES, degrees)).toFixed(3);

    return {
        transform: `perspective(${TILT_PERSPECTIVE}px) rotateX(${limit(tilt.rotateX)}deg) rotateY(${limit(tilt.rotateY)}deg)`,
    };
});
</script>

<template>
    <component
        :is="as"
        class="gw-neu"
        :class="[`gw-neu--${surface}`, `gw-neu--${variant}`, `gw-neu--hover-${hover}`]"
        :style="tiltStyle"
        @pointermove="onPointerMove"
        @pointerleave="onPointerLeave"
    >
        <slot />
    </component>
</template>

<style scoped>
.gw-neu {
    --gw-neu-radius: 28px;

    position: relative;
    border-radius: var(--gw-neu-radius);
    background: var(--gw-neu-bg);
    box-shadow: var(--gw-neu-shadow);
}

.gw-neu--light {
    --gw-neu-bg: var(--color-gw-neu-light);
    --gw-neu-raised: var(--shadow-gw-raised-light);
    --gw-neu-raised-hover: var(--shadow-gw-raised-light-hover);
    --gw-neu-inset: var(--shadow-gw-inset-light);

    color: var(--color-gw-black);
}

.gw-neu--dark {
    --gw-neu-bg: var(--color-gw-neu-dark);
    --gw-neu-raised: var(--shadow-gw-raised-dark);
    --gw-neu-raised-hover: var(--shadow-gw-raised-dark-hover);
    --gw-neu-inset: var(--shadow-gw-inset-dark);
    --gw-focus-ring: var(--color-gw-yellow);

    color: var(--color-gw-white);
}

.gw-neu--raised {
    --gw-neu-shadow: var(--gw-neu-raised);
    --gw-neu-shadow-hover: var(--gw-neu-raised-hover);
}

.gw-neu--inset {
    --gw-neu-shadow: var(--gw-neu-inset);
    --gw-neu-shadow-hover: var(--gw-neu-inset);
}

.gw-neu--featured {
    --gw-neu-bg: var(--color-gw-featured);
    --gw-neu-shadow: var(--gw-neu-raised), var(--shadow-gw-featured-inset);
    --gw-neu-shadow-hover: var(--gw-neu-raised-hover), var(--shadow-gw-featured-inset);
    --gw-focus-ring: var(--color-gw-yellow);

    color: var(--color-gw-white);
}

.gw-neu--hover-tilt {
    transform-style: preserve-3d;
}

@media (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
    .gw-neu--hover-expand {
        transition:
            transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .gw-neu--hover-expand:hover {
        z-index: 1;
        transform: scale(1.035);
        box-shadow: var(--gw-neu-shadow-hover);
    }

    .gw-neu--hover-tilt {
        will-change: transform;
    }
}
</style>
