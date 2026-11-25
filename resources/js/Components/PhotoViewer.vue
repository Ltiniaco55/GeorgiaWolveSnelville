<script setup>
import { computed, ref, watch } from 'vue';
import { useFocusTrap } from '../Composables/useFocusTrap';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Full-screen photo viewer of /gallery (docs/design/home-interacciones.md): the
 * photo with its alt text and caption, previous / next (wrapping round), a counter
 * and a close button. Esc closes; ← and → move. It only walks through `photos`
 * (the active filter).
 *
 * A modal dialog: focus stays inside and goes back to the photo that opened it;
 * the page behind does not scroll. `index` (v-model) is null while closed.
 */
const props = defineProps({
    /** @type {import('vue').PropType<import('./GalleryWall.vue').WallPhoto[]>} */
    photos: {
        type: Array,
        required: true,
    },
    /** Category key => label. */
    labels: {
        type: Object,
        required: true,
    },
});

const index = defineModel({ type: [Number, null], default: null });

const { t } = useTranslations();
const dialog = ref(null);
const isOpen = computed(() => index.value !== null && props.photos.length > 0);
const photo = computed(() => (isOpen.value ? props.photos[index.value] : null));

function close() {
    index.value = null;
}

/**
 * @param {number} step
 */
function move(step) {
    const total = props.photos.length;
    index.value = (index.value + step + total) % total;
}

/**
 * @param {KeyboardEvent} event
 */
function onKeydown(event) {
    if (event.key === 'ArrowRight') {
        event.preventDefault();
        move(1);
    } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        move(-1);
    }
}

useFocusTrap(dialog, isOpen, { onEscape: close });
watch(isOpen, (open) => document.documentElement.classList.toggle('gw-scroll-locked', open));
</script>

<template>
    <Teleport to="body">
        <Transition name="gw-viewer">
            <div
                v-if="isOpen"
                ref="dialog"
                class="gw-viewer"
                role="dialog"
                aria-modal="true"
                :aria-label="t('gallery.viewer.label')"
                data-test="photo-viewer"
                @keydown="onKeydown"
                @click.self="close"
            >
                <button type="button" class="gw-viewer__button gw-viewer__close" data-test="viewer-close" @click="close">
                    <span class="sr-only">{{ t('gallery.viewer.close') }}</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>

                <figure class="gw-viewer__figure">
                    <picture>
                        <source type="image/avif" :srcset="photo.avif" />
                        <img :src="photo.webp" :alt="photo.alt" :width="photo.width" :height="photo.height" class="gw-viewer__image" data-test="viewer-image" />
                    </picture>
                    <figcaption class="gw-viewer__caption">
                        <span class="gw-viewer__tag">{{ labels[photo.category] }}</span>
                        <span class="gw-viewer__title">{{ photo.title }}</span>
                        <span v-if="photo.subtitle" class="text-sm text-gw-white/75">{{ photo.subtitle }}</span>
                    </figcaption>
                </figure>

                <div class="gw-viewer__nav">
                    <button type="button" class="gw-viewer__button" data-test="viewer-previous" @click="move(-1)">
                        <span class="sr-only">{{ t('gallery.viewer.previous') }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 6l-6 6 6 6" /></svg>
                    </button>
                    <p class="gw-viewer__counter" aria-live="polite" data-test="viewer-counter">
                        {{ t('gallery.viewer.counter', { current: index + 1, total: photos.length }) }}
                    </p>
                    <button type="button" class="gw-viewer__button" data-test="viewer-next" @click="move(1)">
                        <span class="sr-only">{{ t('gallery.viewer.next') }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6" /></svg>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.gw-viewer {
    --gw-focus-ring: var(--color-gw-yellow);

    position: fixed;
    inset: 0;
    z-index: 60;
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
    justify-content: center;
    padding: 72px 20px calc(24px + env(safe-area-inset-bottom));
    background: rgb(5 5 5 / 0.96);
    color: var(--color-gw-white);
}

.gw-viewer__figure {
    display: flex;
    flex-direction: column;
    gap: 14px;
    align-items: center;
    min-height: 0;
    margin: 0;
}

.gw-viewer__image {
    width: auto;
    max-width: min(92vw, 1100px);
    height: auto;
    max-height: calc(100svh - 280px);
    border-radius: 20px;
    object-fit: contain;
}

.gw-viewer__caption {
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: center;
    text-align: center;
}

.gw-viewer__tag {
    padding: 3px 10px;
    border-radius: 999px;
    background: var(--color-gw-yellow);
    color: var(--color-gw-black);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.gw-viewer__title {
    font-family: var(--gw-font-barlow);
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
}

.gw-viewer__nav {
    display: flex;
    gap: 20px;
    align-items: center;
}

.gw-viewer__counter {
    min-width: 64px;
    color: #cfcfcf;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
}

.gw-viewer__button {
    display: grid;
    place-items: center;
    width: 52px;
    height: 52px;
    border: 0;
    border-radius: 50%;
    background: var(--color-gw-neu-dark);
    box-shadow:
        5px 5px 12px #000,
        -3px -3px 9px var(--color-gw-neu-dark-highlight);
    color: var(--color-gw-white);
    cursor: pointer;
}

.gw-viewer__button svg {
    width: 22px;
    height: 22px;
}

.gw-viewer__close {
    position: absolute;
    top: 16px;
    right: 16px;
}

.gw-viewer-enter-active,
.gw-viewer-leave-active {
    transition: opacity 0.25s ease;
}

.gw-viewer-enter-from,
.gw-viewer-leave-to {
    opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
    .gw-viewer-enter-active,
    .gw-viewer-leave-active {
        transition: none;
    }
}
</style>
