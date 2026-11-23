/**
 * Brand intro (docs/design/direccion-de-diseno.md, "Intro de marca y home").
 * The intro plays on the first visit of the session only, never with reduced motion.
 */
const SEEN_KEY = 'gw-intro-seen';

/**
 * Whether the brand intro should play now. `force` (local ?intro=1) ignores the
 * session flag but never reduced motion.
 *
 * @param {boolean} [force]
 * @returns {boolean}
 */
export function shouldPlayBrandIntro(force = false) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return false;
    }

    if (force) {
        return true;
    }

    try {
        return window.sessionStorage.getItem(SEEN_KEY) === null;
    } catch {
        // Storage blocked (private mode, policies): better no intro than one on every visit.
        return false;
    }
}

/**
 * Remembers for this session (browser tab) that the intro was seen.
 */
export function markBrandIntroSeen() {
    try {
        window.sessionStorage.setItem(SEEN_KEY, '1');
    } catch {
        // Nothing to do: the intro just will not be remembered.
    }
}
