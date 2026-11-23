<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useFinePointer } from '../Composables/useFinePointer';
import { useReducedMotion } from '../Composables/useReducedMotion';

/**
 * "Flashlight over the pitch" for the hero (docs/design/home-interacciones.md):
 * a circle of light follows the mouse and reveals the lines of a football pitch
 * drawn in yellow, with a soft yellow glow. The lines are an SVG masked with a
 * radial gradient centred on the pointer.
 *
 * Only with a fine pointer. On touch screens, and with reduced motion, the pitch
 * is shown faint and static. It fills its positioned parent and is decorative.
 */
const LAMP_RADIUS = 240;

const hasFinePointer = useFinePointer();
const prefersReducedMotion = useReducedMotion();
const isActive = computed(() => hasFinePointer.value && !prefersReducedMotion.value);

const root = ref(null);
const isLit = ref(false);
let frame = null;
let pointer = null;

function paint() {
    frame = null;

    if (!root.value || !pointer) {
        return;
    }

    const rect = root.value.getBoundingClientRect();
    const x = pointer.x - rect.left;
    const y = pointer.y - rect.top;
    const isInside = x >= 0 && y >= 0 && x <= rect.width && y <= rect.height;

    isLit.value = isInside;

    if (isInside) {
        root.value.style.setProperty('--gw-lamp-x', `${x.toFixed(1)}px`);
        root.value.style.setProperty('--gw-lamp-y', `${y.toFixed(1)}px`);
    }
}

/**
 * @param {PointerEvent} event
 */
function onPointerMove(event) {
    if (event.pointerType === 'touch') {
        return;
    }

    pointer = { x: event.clientX, y: event.clientY };
    frame ??= requestAnimationFrame(paint);
}

function onPointerLeave() {
    isLit.value = false;
}

function start() {
    window.addEventListener('pointermove', onPointerMove, { passive: true });
    document.documentElement.addEventListener('pointerleave', onPointerLeave);
}

function stop() {
    window.removeEventListener('pointermove', onPointerMove);
    document.documentElement.removeEventListener('pointerleave', onPointerLeave);

    if (frame !== null) {
        cancelAnimationFrame(frame);
        frame = null;
    }

    isLit.value = false;
}

onMounted(() => {
    if (isActive.value) {
        start();
    }
});

watch(isActive, (active) => (active ? start() : stop()));
onBeforeUnmount(stop);
</script>

<template>
    <div
        ref="root"
        class="gw-lamp"
        :class="{ 'gw-lamp--live': isActive, 'gw-lamp--lit': isLit }"
        :style="{ '--gw-lamp-radius': `${LAMP_RADIUS}px` }"
        aria-hidden="true"
        data-test="pitch-lamp"
    >
        <svg class="gw-lamp__pitch" viewBox="0 0 1200 760" preserveAspectRatio="xMidYMid slice" focusable="false">
            <g fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke">
                <rect x="40" y="40" width="1120" height="680" />
                <line x1="600" y1="40" x2="600" y2="720" />
                <circle cx="600" cy="380" r="92" />
                <circle cx="600" cy="380" r="4" fill="currentColor" />
                <rect x="40" y="200" width="168" height="360" />
                <rect x="40" y="288" width="56" height="184" />
                <path d="M208 318 A 92 92 0 0 1 208 442" />
                <circle cx="152" cy="380" r="4" fill="currentColor" />
                <rect x="992" y="200" width="168" height="360" />
                <rect x="1104" y="288" width="56" height="184" />
                <path d="M992 318 A 92 92 0 0 0 992 442" />
                <circle cx="1048" cy="380" r="4" fill="currentColor" />
                <path d="M40 58 A 18 18 0 0 0 58 40" />
                <path d="M1142 40 A 18 18 0 0 0 1160 58" />
                <path d="M58 720 A 18 18 0 0 0 40 702" />
                <path d="M1160 702 A 18 18 0 0 0 1142 720" />
            </g>
        </svg>
        <div class="gw-lamp__glow" />
    </div>
</template>

<style scoped>
.gw-lamp {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    color: var(--color-gw-yellow);
}

.gw-lamp__pitch {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;

    /* Touch, reduced motion: a faint, static pitch. */
    opacity: 0.12;
}

.gw-lamp__glow {
    position: absolute;
    inset: 0;
    opacity: 0;
}

.gw-lamp--live .gw-lamp__pitch {
    opacity: 0;
    mask-image: radial-gradient(
        circle var(--gw-lamp-radius) at var(--gw-lamp-x, 50%) var(--gw-lamp-y, 50%),
        #000 0%,
        rgb(0 0 0 / 0.55) 55%,
        transparent 100%
    );
    transition: opacity 0.35s ease-out;
}

.gw-lamp--live .gw-lamp__glow {
    background: radial-gradient(
        circle calc(var(--gw-lamp-radius) * 1.25) at var(--gw-lamp-x, 50%) var(--gw-lamp-y, 50%),
        rgb(255 212 0 / 0.12),
        transparent 70%
    );
    transition: opacity 0.35s ease-out;
}

.gw-lamp--lit .gw-lamp__pitch {
    opacity: 0.9;
}

.gw-lamp--lit .gw-lamp__glow {
    opacity: 1;
}
</style>
