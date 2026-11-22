import { onBeforeUnmount, ref } from 'vue';

/**
 * Reactive matchMedia. The value is correct from the first render and the
 * listener is removed when the component unmounts.
 *
 * @param {string} query
 * @returns {import('vue').Ref<boolean>}
 */
export function useMediaQuery(query) {
    const mediaQueryList = window.matchMedia(query);
    const matches = ref(mediaQueryList.matches);

    const update = (event) => {
        matches.value = event.matches;
    };

    mediaQueryList.addEventListener('change', update);
    onBeforeUnmount(() => mediaQueryList.removeEventListener('change', update));

    return matches;
}
