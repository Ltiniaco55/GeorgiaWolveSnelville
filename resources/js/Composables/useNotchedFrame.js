import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Outline of a rounded frame with a notch cut out of its bottom-left corner, as an
 * SVG path in the frame's own pixels (origin top left).
 *
 * Going clockwise: down the left side, a rounded turn (radius `notchRadius`) onto
 * the notch's top edge, a rounded concave corner down into its right side, another
 * rounded turn onto the frame's bottom edge, and on to the bottom-right corner. The
 * frame's own bottom-left corner is gone. Without a notch (`notchWidth` or
 * `notchHeight` 0) it is a plain rounded rectangle.
 *
 * `inset` shrinks the shape by that many pixels on every side (convex radii shrink,
 * the concave one grows), so a 1 px stroke drawn on `inset: 0.5` sits just inside
 * the clipped area, like a CSS border.
 *
 * @param {{ width: number, height: number, radius: number, notchWidth?: number, notchHeight?: number, notchRadius?: number, inset?: number }} shape
 * @returns {string}
 */
export function notchedFramePath({ width, height, radius, notchWidth = 0, notchHeight = 0, notchRadius = 0, inset = 0 }) {
    const round = (value) => Math.round(value * 100) / 100;
    const left = inset;
    const top = inset;
    const right = width - inset;
    const bottom = height - inset;
    const outer = Math.max(0, Math.min(radius - inset, (right - left) / 2, (bottom - top) / 2));
    const arc = (r, sweep, x, y) => `A${round(r)} ${round(r)} 0 0 ${sweep} ${round(x)} ${round(y)}`;
    const start = [`M${round(left)} ${round(top + outer)}`, arc(outer, 1, left + outer, top), `H${round(right - outer)}`, arc(outer, 1, right, top + outer), `V${round(bottom - outer)}`, arc(outer, 1, right - outer, bottom)];

    if (notchWidth <= 0 || notchHeight <= 0) {
        return [...start, `H${round(left + outer)}`, arc(outer, 1, left, bottom - outer), 'Z'].join(' ');
    }

    const notchRight = notchWidth + inset;
    const notchTop = height - notchHeight - inset;
    const turn = Math.max(0, Math.min(notchRadius - inset, notchTop - top - outer, right - notchRight - outer, (bottom - notchTop) / 2, (notchRight - left) / 2));
    const concave = Math.max(0, Math.min(notchRadius + inset, (bottom - notchTop) / 2, (notchRight - left) / 2));

    return [
        ...start,
        `H${round(notchRight + turn)}`,
        arc(turn, 1, notchRight, bottom - turn),
        `V${round(notchTop + concave)}`,
        arc(concave, 0, notchRight - concave, notchTop),
        `H${round(left + turn)}`,
        arc(turn, 1, left, notchTop - turn),
        'Z',
    ].join(' ');
}

/**
 * Keeps `clipPath` (the frame's outline, for `clip-path: path()`) and `strokePath`
 * (the same outline 0.5 px inside, for a 1 px border) in step with the frame's size
 * and the notch element's size, through a ResizeObserver. `setRadius` changes the
 * frame's corner radius (the hero's recede animation).
 *
 * @param {import('vue').Ref<HTMLElement|null>} frame
 * @param {import('vue').Ref<HTMLElement|null>} notch
 * @param {{ radius: () => number, notchRadius: () => number }} radii
 */
export function useNotchedFrame(frame, notch, radii) {
    const clipPath = ref('');
    const strokePath = ref('');
    let radiusOverride = null;
    let resizeObserver = null;

    function update() {
        const box = frame.value;

        if (!box || !box.offsetWidth) {
            return;
        }

        const shape = {
            width: box.offsetWidth,
            height: box.offsetHeight,
            radius: radiusOverride ?? radii.radius(),
            notchWidth: notch.value?.offsetWidth ?? 0,
            notchHeight: notch.value?.offsetHeight ?? 0,
            notchRadius: radii.notchRadius(),
        };

        clipPath.value = notchedFramePath(shape);
        strokePath.value = notchedFramePath({ ...shape, inset: 0.5 });
    }

    /** @param {number|null} radius null goes back to the default radius. */
    function setRadius(radius) {
        radiusOverride = radius;
        update();
    }

    onMounted(() => {
        update();
        resizeObserver = new ResizeObserver(update);
        resizeObserver.observe(frame.value);

        if (notch.value) {
            resizeObserver.observe(notch.value);
        }
    });

    onBeforeUnmount(() => resizeObserver?.disconnect());

    return { clipPath, strokePath, setRadius, update };
}
