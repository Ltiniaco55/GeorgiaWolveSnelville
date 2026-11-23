<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTranslations } from '../Composables/useTranslations';
import mxFlagSvg from '../../svg/flags/mx.svg?raw';
import usFlagSvg from '../../svg/flags/us.svg?raw';

/**
 * Language dropdown (docs/design/direccion-de-diseno.md, "Selector de idioma").
 *
 * A raised neumorphic button on the #161616 surface with the current language's
 * flag or icon and a chevron; open, it is pressed in and the chevron turns. The
 * panel lists both languages, each linking to the same page in that language
 * (shared `localeUrls`), with a yellow check on the active one. Plain links, not
 * Inertia visits: the whole document, including <html lang>, has to change.
 *
 * Keyboard: Enter, Space or ↓ open it on the active option, ↑ on the last one;
 * ↑ ↓ Home End move; Esc closes and gives the focus back to the button; Tab or a
 * click outside closes it. Esc does not reach the mobile menu's focus trap.
 * Flags are local SVGs (emoji flags do not render on Windows): United States for
 * English, Mexico for Spanish.
 */
defineProps({
    /** Where the panel opens: below the button (header) or above it (mobile menu). */
    placement: {
        type: String,
        default: 'bottom',
        validator: (value) => ['bottom', 'top'].includes(value),
    },
});

/** @type {Record<string, string>} */
const ICONS = {
    en: usFlagSvg.replace('<svg ', '<svg aria-hidden="true" focusable="false" '),
    es: mxFlagSvg.replace('<svg ', '<svg aria-hidden="true" focusable="false" '),
};

const page = usePage();
const { t } = useTranslations();
const uid = `gw-lang-${Math.random().toString(36).slice(2, 8)}`;

const root = ref(null);
const button = ref(null);
const items = ref([]);
const isOpen = ref(false);

const options = computed(() =>
    Object.entries(page.props.localeUrls ?? {}).map(([locale, url]) => ({
        locale,
        url,
        name: t(`common.language.${locale}`),
        isCurrent: locale === page.props.locale,
    })),
);
const current = computed(() => options.value.find((option) => option.isCurrent) ?? options.value[0]);

/**
 * @param {number} index
 */
function focusItem(index) {
    const count = items.value.length;

    if (count > 0) {
        items.value[((index % count) + count) % count]?.focus();
    }
}

/**
 * @param {'current'|'first'|'last'} target
 */
async function open(target = 'current') {
    isOpen.value = true;
    document.addEventListener('pointerdown', onPointerDownOutside, true);
    await nextTick();

    const currentIndex = Math.max(0, options.value.findIndex((option) => option.isCurrent));
    focusItem({ current: currentIndex, first: 0, last: -1 }[target]);
}

/**
 * @param {{ returnFocus?: boolean }} [options]
 */
function close({ returnFocus = false } = {}) {
    if (!isOpen.value) {
        return;
    }

    isOpen.value = false;
    document.removeEventListener('pointerdown', onPointerDownOutside, true);

    if (returnFocus) {
        button.value?.focus();
    }
}

function toggle() {
    isOpen.value ? close() : open();
}

/**
 * @param {PointerEvent} event
 */
function onPointerDownOutside(event) {
    if (!root.value?.contains(event.target)) {
        close();
    }
}

/**
 * @param {KeyboardEvent} event
 */
function onButtonKeydown(event) {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        open('current');
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        open('last');
    } else if (event.key === 'Escape' && isOpen.value) {
        event.stopPropagation();
        close({ returnFocus: true });
    }
}

/**
 * @param {KeyboardEvent} event
 */
function onMenuKeydown(event) {
    const index = items.value.indexOf(document.activeElement);

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            focusItem(index + 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            focusItem(index - 1);
            break;
        case 'Home':
            event.preventDefault();
            focusItem(0);
            break;
        case 'End':
            event.preventDefault();
            focusItem(-1);
            break;
        case 'Escape':
            event.preventDefault();
            event.stopPropagation();
            close({ returnFocus: true });
            break;
        case 'Tab':
            close();
            break;
        case ' ':
            // Space follows the link, like Enter, as in a native menu.
            event.preventDefault();
            document.activeElement?.click();
            break;
    }
}

onBeforeUnmount(() => document.removeEventListener('pointerdown', onPointerDownOutside, true));
</script>

<template>
    <div ref="root" class="gw-lang" :class="[`gw-lang--${placement}`, { 'gw-lang--open': isOpen }]" data-test="language-menu">
        <button
            :id="`${uid}-button`"
            ref="button"
            type="button"
            class="gw-lang__button"
            aria-haspopup="true"
            :aria-expanded="isOpen ? 'true' : 'false'"
            :aria-controls="`${uid}-menu`"
            data-test="language-button"
            @click="toggle"
            @keydown="onButtonKeydown"
        >
            <span class="gw-lang__icon" :class="`gw-lang__icon--${current?.locale}`" v-html="ICONS[current?.locale]" />
            <span class="sr-only">{{ t('common.language.current', { language: current?.name ?? '' }) }}</span>
            <svg class="gw-lang__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M6 9l6 6 6-6" />
            </svg>
        </button>

        <Transition name="gw-lang">
            <ul
                v-show="isOpen"
                :id="`${uid}-menu`"
                class="gw-lang__menu"
                role="menu"
                :aria-labelledby="`${uid}-button`"
                data-test="language-list"
                @keydown="onMenuKeydown"
            >
                <li v-for="option in options" :key="option.locale" role="none">
                    <a
                        ref="items"
                        :href="option.url"
                        :hreflang="option.locale"
                        :lang="option.locale"
                        role="menuitemradio"
                        :aria-checked="option.isCurrent ? 'true' : 'false'"
                        tabindex="-1"
                        class="gw-lang__option"
                        :data-test="`language-option-${option.locale}`"
                    >
                        <span class="gw-lang__icon" :class="`gw-lang__icon--${option.locale}`" v-html="ICONS[option.locale]" />
                        <span class="gw-lang__name">{{ option.name }}</span>
                        <svg v-if="option.isCurrent" class="gw-lang__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M5 12.5l4.5 4.5L19 7.5" />
                        </svg>
                    </a>
                </li>
            </ul>
        </Transition>
    </div>
</template>

<style scoped>
.gw-lang {
    --gw-focus-ring: var(--color-gw-yellow);

    position: relative;
    display: inline-block;
}

/* Raised on the #161616 surface; pressed in while open. */
.gw-lang__button {
    display: inline-flex;
    gap: 0.5rem;
    align-items: center;
    height: 48px;
    padding: 0 0.875rem 0 0.75rem;
    border: 0;
    border-radius: 999px;
    background: var(--color-gw-neu-dark);
    color: var(--color-gw-white);
    box-shadow: var(--shadow-gw-raised-dark-sm);
    cursor: pointer;
    transition:
        box-shadow 0.25s,
        transform 0.25s;
}

.gw-lang--open .gw-lang__button {
    box-shadow: var(--shadow-gw-inset-dark-sm);
    transform: scale(0.98);
}

.gw-lang__icon {
    display: inline-block;
    flex: none;
    width: 24px;
    height: 16px;
    overflow: hidden;
    border-radius: 3px;
    line-height: 0;
}

.gw-lang__icon :deep(svg) {
    width: 100%;
    height: 100%;
}

.gw-lang__chevron {
    width: 16px;
    height: 16px;
    color: rgb(255 255 255 / 0.8);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.gw-lang--open .gw-lang__chevron {
    transform: rotate(180deg);
}

.gw-lang__menu {
    position: absolute;
    right: 0;
    z-index: 10;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 13rem;
    margin: 0;
    padding: 0.5rem;
    border-radius: 20px;
    background: var(--color-gw-neu-dark);
    box-shadow:
        10px 10px 24px var(--color-gw-neu-dark-shade),
        -6px -6px 18px var(--color-gw-neu-dark-highlight);
    list-style: none;
}

.gw-lang--bottom .gw-lang__menu {
    top: calc(100% + 0.75rem);
    transform-origin: top right;
}

.gw-lang--top .gw-lang__menu {
    bottom: calc(100% + 0.75rem);
    transform-origin: bottom right;
}

.gw-lang__option {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    min-height: 48px;
    padding: 0 0.875rem;
    border-radius: 14px;
    color: var(--color-gw-white);
    font-family: var(--gw-font-inter);
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-decoration: none;
    text-transform: uppercase;
}

.gw-lang__option:focus-visible {
    outline-offset: -3px;
}

.gw-lang__option:focus,
.gw-lang__option[aria-checked='true'] {
    box-shadow: var(--shadow-gw-inset-dark-sm);
}

.gw-lang__name {
    flex: 1;
}

.gw-lang__check {
    width: 18px;
    height: 18px;
    color: var(--color-gw-yellow);
}

@media (hover: hover) and (pointer: fine) {
    .gw-lang__button:hover .gw-lang__chevron {
        color: var(--color-gw-yellow);
    }

    .gw-lang__option:hover {
        box-shadow: var(--shadow-gw-inset-dark-sm);
    }
}

.gw-lang-enter-active,
.gw-lang-leave-active {
    transition:
        opacity 0.2s,
        transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.gw-lang-enter-from,
.gw-lang-leave-to {
    opacity: 0;
    transform: scale(0.92);
}

@media (prefers-reduced-motion: reduce) {
    .gw-lang__button,
    .gw-lang__chevron,
    .gw-lang-enter-active,
    .gw-lang-leave-active {
        transition: none;
    }

    .gw-lang--open .gw-lang__button {
        transform: none;
    }
}
</style>
