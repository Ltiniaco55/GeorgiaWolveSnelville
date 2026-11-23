import { computed, onBeforeUnmount, onMounted, watch } from 'vue';
import { useFinePointer } from './useFinePointer';
import { useReducedMotion } from './useReducedMotion';
import { useSpring } from './useSpring';

/**
 * Magnetic element (final CTA "Join the pack."): when the pointer comes within
 * `radius` px of the element's centre, the element moves `strength` of the way
 * towards it, and springs back with a small bounce when the pointer leaves.
 *
 * Only with a fine pointer and without reduced motion; otherwise the element stays put.
 *
 * @param {import('vue').Ref<HTMLElement|null>} elementRef
 * @param {{ radius?: number, strength?: number, stiffness?: number, damping?: number }} [options]
 * @returns {{ style: import('vue').ComputedRef<Record<string, string>>, isActive: import('vue').ComputedRef<boolean> }}
 */
export function useMagnetic(elementRef, { radius = 220, strength = 0.35, stiffness = 0.12, damping = 0.75 } = {}) {
    const hasFinePointer = useFinePointer();
    const prefersReducedMotion = useReducedMotion();
    const isActive = computed(() => hasFinePointer.value && !prefersReducedMotion.value);
    const { values: offset, setTarget, reset } = useSpring({ x: 0, y: 0 }, { stiffness, damping });

    function onPointerMove(event) {
        const element = elementRef.value;

        if (!element) {
            return;
        }

        // The rect includes the current offset: remove it to get the resting centre.
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2 - offset.x;
        const centerY = rect.top + rect.height / 2 - offset.y;
        const deltaX = event.clientX - centerX;
        const deltaY = event.clientY - centerY;

        if (Math.hypot(deltaX, deltaY) < radius) {
            setTarget({ x: deltaX * strength, y: deltaY * strength });
        } else {
            setTarget({ x: 0, y: 0 });
        }
    }

    function onPointerLeaveWindow() {
        setTarget({ x: 0, y: 0 });
    }

    function start() {
        window.addEventListener('pointermove', onPointerMove, { passive: true });
        document.documentElement.addEventListener('pointerleave', onPointerLeaveWindow);
    }

    function stop() {
        window.removeEventListener('pointermove', onPointerMove);
        document.documentElement.removeEventListener('pointerleave', onPointerLeaveWindow);
        reset({ x: 0, y: 0 });
    }

    onMounted(() => {
        if (isActive.value) {
            start();
        }
    });

    watch(isActive, (active) => (active ? start() : stop()));
    onBeforeUnmount(stop);

    const style = computed(() => {
        if (!isActive.value || (offset.x === 0 && offset.y === 0)) {
            return {};
        }

        return { transform: `translate3d(${offset.x.toFixed(2)}px, ${offset.y.toFixed(2)}px, 0)` };
    });

    return { style, isActive };
}
