import { useMediaQuery } from './useMediaQuery';

/**
 * True with a real mouse or trackpad. Everything that follows the pointer
 * (hover effects, tilt, magnetic buttons, cursor trail) is gated on this;
 * touch screens always get the static alternative (see CLAUDE.md).
 *
 * @returns {import('vue').Ref<boolean>}
 */
export function useFinePointer() {
    return useMediaQuery('(hover: hover) and (pointer: fine)');
}
