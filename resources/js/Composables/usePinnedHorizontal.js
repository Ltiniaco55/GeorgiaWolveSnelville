import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useReducedMotion } from './useReducedMotion';

/**
 * Pinned horizontal scroll (max. 2–3 on the whole site, see CLAUDE.md).
 *
 * The section gets the height of one viewport plus the horizontal distance the
 * track has to travel; its inner wrapper is `position: sticky; height: 100vh`
 * (component CSS). While the section is pinned, vertical scroll moves the track
 * with translate3d inside requestAnimationFrame.
 *
 * With reduced motion it does nothing: the component falls back to an
 * overflow-x carousel with scroll-snap.
 *
 * @param {import('vue').Ref<HTMLElement|null>} sectionRef Tall outer section.
 * @param {import('vue').Ref<HTMLElement|null>} trackRef Row of panels that slides left.
 * @returns {{
 *   progress: import('vue').Ref<number>,
 *   isEnabled: import('vue').ComputedRef<boolean>,
 *   sectionStyle: import('vue').ComputedRef<Record<string, string>>,
 *   trackStyle: import('vue').ComputedRef<Record<string, string>>,
 * }}
 */
export function usePinnedHorizontal(sectionRef, trackRef) {
    const prefersReducedMotion = useReducedMotion();
    const isEnabled = computed(() => !prefersReducedMotion.value);
    const progress = ref(0);
    const distance = ref(0);
    const viewportHeight = ref(0);
    let frame = null;
    let resizeObserver = null;

    function measure() {
        const track = trackRef.value;

        if (!track) {
            return;
        }

        viewportHeight.value = window.innerHeight;
        distance.value = Math.max(0, track.scrollWidth - track.clientWidth);
        update();
    }

    function update() {
        frame = null;
        const section = sectionRef.value;

        if (!section) {
            return;
        }

        const scrollable = section.offsetHeight - viewportHeight.value;
        const scrolled = -section.getBoundingClientRect().top;

        progress.value = scrollable > 0 ? Math.min(1, Math.max(0, scrolled / scrollable)) : 0;
    }

    function onScroll() {
        frame ??= requestAnimationFrame(update);
    }

    function start() {
        measure();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', measure);
        resizeObserver = new ResizeObserver(measure);

        if (trackRef.value) {
            resizeObserver.observe(trackRef.value);
        }
    }

    function stop() {
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', measure);
        resizeObserver?.disconnect();
        resizeObserver = null;

        if (frame !== null) {
            cancelAnimationFrame(frame);
            frame = null;
        }

        progress.value = 0;
    }

    onMounted(() => {
        if (isEnabled.value) {
            start();
        }
    });

    watch(isEnabled, (enabled) => (enabled ? start() : stop()));
    onBeforeUnmount(stop);

    const sectionStyle = computed(() =>
        isEnabled.value && viewportHeight.value ? { height: `${viewportHeight.value + distance.value}px` } : {},
    );

    const trackStyle = computed(() =>
        isEnabled.value ? { transform: `translate3d(${(-progress.value * distance.value).toFixed(2)}px, 0, 0)` } : {},
    );

    return { progress, isEnabled, sectionStyle, trackStyle };
}
