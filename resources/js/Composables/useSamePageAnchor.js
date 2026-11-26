/**
 * Whether `href` points to an anchor of the page being shown (e.g. "/#pricing"
 * while on the home page). Those links must jump natively: an Inertia visit would
 * reload the page instead of scrolling.
 *
 * @param {string|null|undefined} href
 * @returns {boolean}
 */
export function isSamePageAnchor(href) {
    if (!href) {
        return false;
    }

    if (href.startsWith('#')) {
        return true;
    }

    const url = new URL(href, window.location.href);
    const trim = (path) => path.replace(/\/+$/, '');

    return url.hash !== '' && url.origin === window.location.origin && trim(url.pathname) === trim(window.location.pathname);
}
