import { usePage } from '@inertiajs/vue3';

/**
 * Translate UI copy from the lang/{locale}/*.php groups shared by HandleInertiaRequests.
 *
 * Keys use the Laravel format "group.key.subkey". Placeholders use ":name".
 * A missing key returns the key itself, like Laravel's __().
 */
export function useTranslations() {
    const page = usePage();

    /**
     * @param {string} key
     * @param {Record<string, string|number>} [replacements]
     * @returns {string}
     */
    function t(key, replacements = {}) {
        const value = key.split('.').reduce((node, segment) => node?.[segment], page.props.translations);

        if (typeof value !== 'string') {
            return key;
        }

        return Object.entries(replacements).reduce(
            (text, [name, replacement]) => text.replaceAll(`:${name}`, String(replacement)),
            value,
        );
    }

    /**
     * A list or group of copy (e.g. the features of a plan), as written in the lang file.
     * Returns an empty array when the key does not exist.
     *
     * @param {string} key
     * @returns {Array<any>|Record<string, any>}
     */
    function tList(key) {
        const value = key.split('.').reduce((node, segment) => node?.[segment], page.props.translations);

        return value !== null && typeof value === 'object' ? value : [];
    }

    return { t, tList };
}
