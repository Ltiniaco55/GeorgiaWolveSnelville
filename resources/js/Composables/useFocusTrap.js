import { nextTick, onBeforeUnmount, toValue, watch } from 'vue';

const FOCUSABLE = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

/**
 * Keeps keyboard focus inside a container while it is active (dialogs, the mobile
 * menu panel): Tab and Shift+Tab wrap around, Escape calls `onEscape`. When it
 * activates, focus moves to the first focusable element; when it deactivates,
 * focus goes back to the element that had it before.
 *
 * @param {import('vue').Ref<HTMLElement|null>} containerRef
 * @param {import('vue').MaybeRefOrGetter<boolean>} isActive
 * @param {{ onEscape?: () => void }} [options]
 */
export function useFocusTrap(containerRef, isActive, { onEscape } = {}) {
    let previouslyFocused = null;

    function focusableElements() {
        return [...(containerRef.value?.querySelectorAll(FOCUSABLE) ?? [])].filter(
            (element) => element.getClientRects().length > 0 && !element.closest('[inert]'),
        );
    }

    /**
     * @param {KeyboardEvent} event
     */
    function onKeydown(event) {
        if (event.key === 'Escape') {
            onEscape?.();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const elements = focusableElements();

        if (elements.length === 0) {
            event.preventDefault();

            return;
        }

        const first = elements[0];
        const last = elements[elements.length - 1];
        const isInside = containerRef.value?.contains(document.activeElement);

        if (event.shiftKey && (document.activeElement === first || !isInside)) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && (document.activeElement === last || !isInside)) {
            event.preventDefault();
            first.focus();
        }
    }

    async function activate() {
        previouslyFocused = document.activeElement;
        document.addEventListener('keydown', onKeydown);
        await nextTick();
        focusableElements()[0]?.focus();
    }

    function deactivate() {
        document.removeEventListener('keydown', onKeydown);

        if (previouslyFocused instanceof HTMLElement && previouslyFocused.isConnected) {
            previouslyFocused.focus();
        }

        previouslyFocused = null;
    }

    watch(
        () => toValue(isActive),
        (active) => (active ? activate() : deactivate()),
    );

    onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
}
