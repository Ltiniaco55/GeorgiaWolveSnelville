import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useFinePointer } from './useFinePointer';
import { useReducedMotion } from './useReducedMotion';

/**
 * Photo trail under the cursor (home gallery, docs/design/home-interacciones.md):
 * a new item every `spacing` px of pointer travel inside the area, randomly
 * rotated, removed after `lifetime` ms, at most `max` at once (oldest go first).
 *
 * Only with a fine pointer and without reduced motion; the component shows a
 * static grid otherwise. Timers and listeners are cleared on unmount.
 *
 * @param {import('vue').Ref<HTMLElement|null>} areaRef
 * @param {{ itemCount: number, spacing?: number, lifetime?: number, max?: number, maxRotation?: number }} options
 *   `itemCount` is how many different images there are; items cycle through them.
 * @returns {{
 *   trail: import('vue').Ref<Array<{ id: number, x: number, y: number, rotation: number, index: number }>>,
 *   isActive: import('vue').ComputedRef<boolean>,
 * }}
 */
export function useCursorTrail(areaRef, { itemCount, spacing = 110, lifetime = 1600, max = 14, maxRotation = 14 }) {
    const hasFinePointer = useFinePointer();
    const prefersReducedMotion = useReducedMotion();
    const isActive = computed(() => hasFinePointer.value && !prefersReducedMotion.value);
    const trail = ref([]);
    const timers = new Map();
    let lastPoint = null;
    let travelled = 0;
    let nextId = 0;
    let nextIndex = 0;
    let listeningOn = null;

    function remove(id) {
        clearTimeout(timers.get(id));
        timers.delete(id);
        trail.value = trail.value.filter((item) => item.id !== id);
    }

    function spawn(x, y) {
        const id = nextId++;

        trail.value = [...trail.value, { id, x, y, rotation: (Math.random() * 2 - 1) * maxRotation, index: nextIndex }];
        nextIndex = (nextIndex + 1) % Math.max(1, itemCount);
        timers.set(id, setTimeout(() => remove(id), lifetime));

        while (trail.value.length > max) {
            remove(trail.value[0].id);
        }
    }

    function onPointerMove(event) {
        const rect = listeningOn.getBoundingClientRect();
        const point = { x: event.clientX - rect.left, y: event.clientY - rect.top };

        if (lastPoint === null) {
            lastPoint = point;
            spawn(point.x, point.y);

            return;
        }

        travelled += Math.hypot(point.x - lastPoint.x, point.y - lastPoint.y);
        lastPoint = point;

        if (travelled >= spacing) {
            travelled = 0;
            spawn(point.x, point.y);
        }
    }

    function onPointerLeave() {
        lastPoint = null;
        travelled = 0;
    }

    function start() {
        listeningOn = areaRef.value;
        listeningOn?.addEventListener('pointermove', onPointerMove, { passive: true });
        listeningOn?.addEventListener('pointerleave', onPointerLeave);
    }

    function stop() {
        listeningOn?.removeEventListener('pointermove', onPointerMove);
        listeningOn?.removeEventListener('pointerleave', onPointerLeave);
        listeningOn = null;
        timers.forEach((timer) => clearTimeout(timer));
        timers.clear();
        trail.value = [];
        onPointerLeave();
    }

    onMounted(() => {
        if (isActive.value) {
            start();
        }
    });

    watch(isActive, (active) => (active ? start() : stop()));
    onBeforeUnmount(stop);

    return { trail, isActive };
}
