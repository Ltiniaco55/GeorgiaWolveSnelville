import { useMediaQuery } from './useMediaQuery';

/**
 * True when the user asked the OS to reduce motion: no intro, CTAs open,
 * no parallax, no tilt (see CLAUDE.md).
 *
 * @returns {import('vue').Ref<boolean>}
 */
export function useReducedMotion() {
    return useMediaQuery('(prefers-reduced-motion: reduce)');
}
