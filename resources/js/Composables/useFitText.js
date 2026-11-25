import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Sizes a one-line text so it spans `ratio` of its container's width, on any
 * screen: it is measured at a reference size and scaled in proportion (letter
 * spacing in em scales with it). Recalculated when the container resizes
 * (ResizeObserver) and when web fonts finish loading, since the fallback font has
 * different widths.
 *
 * `text` must be a shrink-to-fit box on one line (white-space: nowrap), e.g. a
 * flex item or an inline-block; its font size is set on `text` itself.
 *
 * @param {import('vue').Ref<HTMLElement|null>} container
 * @param {import('vue').Ref<HTMLElement|null>} text
 * @param {{ ratio?: number }} [options]
 */
export function useFitText(container, text, { ratio = 1 } = {}) {
    const REFERENCE_SIZE = 100;
    let resizeObserver = null;
    let lastWidth = null;

    function fit(force = false) {
        const box = container.value;
        const line = text.value;

        if (!box || !line) {
            return;
        }

        const width = box.clientWidth;

        if (!force && width === lastWidth) {
            return;
        }

        lastWidth = width;
        line.style.fontSize = `${REFERENCE_SIZE}px`;
        // Layout width: unlike getBoundingClientRect, not affected by transforms.
        const measured = line.offsetWidth;

        if (measured > 0) {
            line.style.fontSize = `${((REFERENCE_SIZE * width * ratio) / measured).toFixed(2)}px`;
        }
    }

    const refit = () => fit(true);

    onMounted(() => {
        fit(true);
        resizeObserver = new ResizeObserver(() => fit());
        resizeObserver.observe(container.value);
        document.fonts?.ready.then(refit);
        document.fonts?.addEventListener('loadingdone', refit);
    });

    onBeforeUnmount(() => {
        resizeObserver?.disconnect();
        document.fonts?.removeEventListener('loadingdone', refit);
    });

    return { fit: refit };
}
