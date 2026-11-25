/**
 * `v-reveal`: headings and cards fade in and rise 40 px the first time they
 * enter the viewport (IntersectionObserver, bottom margin -10 %). The value is an
 * optional step (1, 2, 3…) that delays the item 0.12 s per step, for staggering
 * siblings. Styles: `.gw-reveal` in resources/css/app.css.
 *
 * Nothing is hidden with reduced motion or without IntersectionObserver, so the
 * content is never left invisible.
 *
 * Usage in <script setup>: `import { vReveal } from '../Composables/useReveal';`
 * then `<h2 v-reveal>` or `<article v-reveal="2">`.
 */
const REDUCED_MOTION = '(prefers-reduced-motion: reduce)';

let observer = null;

function sharedObserver() {
    observer ??= new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('gw-reveal--in');
                    observer.unobserve(entry.target);
                }
            }
        },
        { rootMargin: '0px 0px -10% 0px' },
    );

    return observer;
}

export const vReveal = {
    mounted(element, binding) {
        if (window.matchMedia(REDUCED_MOTION).matches || !('IntersectionObserver' in window)) {
            return;
        }

        element.classList.add('gw-reveal');

        if (binding.value) {
            element.style.setProperty('--gw-reveal-step', String(binding.value));
        }

        sharedObserver().observe(element);
    },
    beforeUnmount(element) {
        observer?.unobserve(element);
    },
};
