import { computed, onBeforeUnmount, onMounted, ref, toValue, watch } from 'vue';

/**
 * Pinned "deck of cards" scroll (mobile pricing, docs/design/direccion-de-diseno.md).
 *
 * The section is one viewport tall plus `stepHeight` (svh) per card change; its
 * inner wrapper is `position: sticky; height: 100svh` (component CSS). While it is
 * pinned, vertical scroll turns the deck: `position` goes from 0 (first card on top)
 * to count - 1 (last card on top). Each step has a rest zone at both ends so a card
 * settles on top before the next one starts to leave. After the last card the page
 * scrolls on normally.
 *
 * Scroll is the only source of truth: swipes, dots and keyboard focus call `goTo()`,
 * which scrolls the page to that card's spot.
 *
 * @param {import('vue').Ref<HTMLElement|null>} sectionRef Tall outer section.
 * @param {{
 *   count: number,
 *   enabled: import('vue').MaybeRefOrGetter<boolean>,
 *   stepHeight?: number,
 *   restZone?: number,
 *   swipeThreshold?: number,
 * }} options
 * @returns {{
 *   position: import('vue').Ref<number>,
 *   activeIndex: import('vue').ComputedRef<number>,
 *   sectionStyle: import('vue').ComputedRef<Record<string, string>>,
 *   goTo: (index: number, behavior?: ScrollBehavior) => void,
 *   swipeHandlers: Record<string, (event: PointerEvent) => void>,
 * }}
 */
export function useDeckScroll(sectionRef, { count, enabled, stepHeight = 70, restZone = 0.2, swipeThreshold = 50 }) {
    const steps = Math.max(1, count - 1);
    const position = ref(0);
    const activeIndex = computed(() => Math.round(position.value));
    let frame = null;
    let swipeStart = null;

    /**
     * Pixels of scroll for one card change.
     */
    function stepPixels() {
        const section = sectionRef.value;

        return section ? Math.max(0, section.offsetHeight - window.innerHeight) / steps : 0;
    }

    /**
     * Scroll progress inside one step (0–1) → card movement (0–1), still at both ends.
     *
     * @param {number} progress
     */
    function ease(progress) {
        const moving = Math.min(1, Math.max(0, (progress - restZone) / (1 - 2 * restZone)));

        return moving * moving * (3 - 2 * moving);
    }

    function update() {
        frame = null;
        const section = sectionRef.value;
        const step = stepPixels();

        if (!section || step === 0) {
            position.value = 0;

            return;
        }

        const scrolled = Math.min(steps, Math.max(0, -section.getBoundingClientRect().top / step));
        const completedSteps = Math.min(steps - 1, Math.floor(scrolled));

        position.value = completedSteps + ease(scrolled - completedSteps);
    }

    function onScroll() {
        frame ??= requestAnimationFrame(update);
    }

    /**
     * Scrolls the page so that card `index` is on top.
     *
     * @param {number} index
     * @param {ScrollBehavior} [behavior]
     */
    function goTo(index, behavior = 'smooth') {
        const section = sectionRef.value;

        if (!section || !toValue(enabled)) {
            return;
        }

        const target = Math.min(count - 1, Math.max(0, index));
        const sectionTop = window.scrollY + section.getBoundingClientRect().top;

        window.scrollTo({ top: Math.round(sectionTop + target * stepPixels()), behavior });
    }

    /** Horizontal swipe on the deck: left shows the next card, right the previous one. */
    const swipeHandlers = {
        pointerdown(event) {
            swipeStart = { x: event.clientX, y: event.clientY };
        },
        pointerup(event) {
            if (!swipeStart) {
                return;
            }

            const deltaX = event.clientX - swipeStart.x;
            const deltaY = event.clientY - swipeStart.y;
            swipeStart = null;

            if (Math.abs(deltaX) < swipeThreshold || Math.abs(deltaX) < Math.abs(deltaY)) {
                return;
            }

            goTo(activeIndex.value + (deltaX < 0 ? 1 : -1));
        },
        pointercancel() {
            swipeStart = null;
        },
    };

    function start() {
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        onScroll();
    }

    function stop() {
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', onScroll);

        if (frame !== null) {
            cancelAnimationFrame(frame);
            frame = null;
        }

        position.value = 0;
    }

    onMounted(() => {
        if (toValue(enabled)) {
            start();
        }
    });

    watch(
        () => toValue(enabled),
        (isEnabled) => (isEnabled ? start() : stop()),
    );
    onBeforeUnmount(stop);

    const sectionStyle = computed(() => (toValue(enabled) ? { height: `calc(100svh + ${steps * stepHeight}svh)` } : {}));

    return { position, activeIndex, sectionStyle, goTo, swipeHandlers };
}
