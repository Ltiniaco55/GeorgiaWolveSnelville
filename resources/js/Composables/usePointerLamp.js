import { computed, onBeforeUnmount, onMounted, ref, unref, watch } from 'vue';
import { useFinePointer } from './useFinePointer';
import { useReducedMotion } from './useReducedMotion';

/**
 * "Flashlight" that follows the mouse: writes the pointer position, relative to
 * `target`, into `--gw-lamp-x` / `--gw-lamp-y` on it, and says whether the pointer
 * is inside `area` (defaults to `target`). The CSS draws the light with those
 * variables (a radial mask or gradient).
 *
 * The pointer is read on `window`, so content stacked on top of the lamp does not
 * block it. Only with a fine pointer and motion allowed (see CLAUDE.md); otherwise
 * nothing is listened to and `isLit` stays false. One rAF per frame at most.
 *
 * @param {import('vue').Ref<HTMLElement|null>} target
 * @param {{ area?: import('vue').Ref<HTMLElement|null> }} [options]
 * @returns {{ isActive: import('vue').ComputedRef<boolean>, isLit: import('vue').Ref<boolean> }}
 */
export function usePointerLamp(target, { area = null } = {}) {
    const hasFinePointer = useFinePointer();
    const prefersReducedMotion = useReducedMotion();
    const isActive = computed(() => hasFinePointer.value && !prefersReducedMotion.value);
    const isLit = ref(false);

    let frame = null;
    let pointer = null;

    function paint() {
        frame = null;

        const element = unref(target);
        const areaElement = unref(area) ?? element;

        if (!element || !areaElement || !pointer) {
            return;
        }

        const areaRect = areaElement.getBoundingClientRect();
        const isInside = pointer.x >= areaRect.left && pointer.x <= areaRect.right && pointer.y >= areaRect.top && pointer.y <= areaRect.bottom;

        isLit.value = isInside;

        if (isInside) {
            const rect = element.getBoundingClientRect();
            element.style.setProperty('--gw-lamp-x', `${(pointer.x - rect.left).toFixed(1)}px`);
            element.style.setProperty('--gw-lamp-y', `${(pointer.y - rect.top).toFixed(1)}px`);
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

    return { isActive, isLit };
}
