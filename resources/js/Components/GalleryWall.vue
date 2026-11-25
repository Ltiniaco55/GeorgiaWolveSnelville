<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Masonry wall of /gallery (docs/design/home-interacciones.md, "Página /gallery"),
 * laid out by JS: 4 columns from 1024 px of wall width, 3 from 640 px, 2 below.
 * Each photo goes to the shortest column, in display order.
 *
 * Filtering (`filter`): the photos that leave shrink and fade where they are; the
 * ones that stay move to their new place with a 0.7 s transform; the wall's height
 * animates too. Hidden photos are inert (out of the tab order and of the
 * accessibility tree). Everything moves with transform and opacity; with reduced
 * motion it changes at once.
 *
 * Hover (fine pointer) or keyboard focus: slow zoom and the caption rises from the
 * bottom (yellow category label, title, subtitle). On touch it is always visible.
 * A click opens the viewer (`open` with the photo id).
 *
 * @typedef {import('../Sections/GalleryTrail.vue').GalleryPhoto & { category: string, subtitle: string }} WallPhoto
 */
const props = defineProps({
    /** @type {import('vue').PropType<WallPhoto[]>} */
    photos: {
        type: Array,
        required: true,
    },
    /** "all" or a category. */
    filter: {
        type: String,
        default: 'all',
    },
    /** Category key => label. */
    labels: {
        type: Object,
        required: true,
    },
    panelId: {
        type: String,
        required: true,
    },
    labelledBy: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['open']);

const GAP = 16;

const { t } = useTranslations();
const wall = ref(null);
const width = ref(0);
const isReady = ref(false);
const lastPositions = new Map();
let resizeObserver = null;
let resizeFrame = null;

const columnCount = computed(() => {
    if (width.value >= 1024) {
        return 4;
    }

    return width.value >= 640 ? 3 : 2;
});

const isVisible = (photo) => props.filter === 'all' || photo.category === props.filter;

const layout = computed(() => {
    const count = columnCount.value;
    const columnWidth = Math.max(0, (width.value - GAP * (count - 1)) / count);
    const heights = Array(count).fill(0);
    const positions = new Map();

    for (const photo of props.photos) {
        if (!isVisible(photo)) {
            continue;
        }

        const column = heights.indexOf(Math.min(...heights));
        const height = (columnWidth * photo.height) / photo.width;
        positions.set(photo.id, { x: column * (columnWidth + GAP), y: heights[column], width: columnWidth, height });
        heights[column] += height + GAP;
    }

    return { positions, height: Math.max(0, Math.max(...heights) - GAP) };
});

/**
 * Where a photo is drawn: its slot if visible, its last slot (shrunk) if not.
 *
 * @param {WallPhoto} photo
 */
function itemStyle(photo) {
    const visible = isVisible(photo);
    const position = layout.value.positions.get(photo.id) ?? lastPositions.get(photo.id);

    if (!position) {
        return { opacity: 0 };
    }

    return {
        width: `${position.width}px`,
        height: `${position.height}px`,
        transform: `translate3d(${position.x.toFixed(1)}px, ${position.y.toFixed(1)}px, 0) scale(${visible ? 1 : 0.8})`,
        opacity: visible ? 1 : 0,
    };
}

watch(
    () => layout.value.positions,
    (positions) => positions.forEach((position, id) => lastPositions.set(id, position)),
    { immediate: true },
);

onMounted(() => {
    width.value = wall.value.clientWidth;
    // Applied on the next frame: the new height may toggle the page scrollbar and
    // change the width again, which would otherwise loop inside the observer.
    resizeObserver = new ResizeObserver(([entry]) => {
        cancelAnimationFrame(resizeFrame);
        resizeFrame = requestAnimationFrame(() => (width.value = entry.contentRect.width));
    });
    resizeObserver.observe(wall.value);

    // Transitions only after the first layout, so nothing flies in from the corner.
    requestAnimationFrame(() => requestAnimationFrame(() => (isReady.value = true)));
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    cancelAnimationFrame(resizeFrame);
});
</script>

<template>
    <div
        :id="panelId"
        ref="wall"
        class="gw-wall"
        :class="{ 'gw-wall--ready': isReady }"
        role="tabpanel"
        :aria-labelledby="labelledBy"
        :style="{ height: `${layout.height}px` }"
        data-test="gallery-wall"
        :data-columns="columnCount"
    >
        <ul class="absolute inset-0">
            <li
                v-for="photo in photos"
                :key="photo.id"
                class="gw-wall__item"
                :class="{ 'gw-wall__item--hidden': !isVisible(photo) }"
                :style="itemStyle(photo)"
                :inert="!isVisible(photo)"
                :aria-hidden="isVisible(photo) ? undefined : 'true'"
                :data-category="photo.category"
                data-test="wall-item"
            >
                <button type="button" class="gw-wall__card" :aria-label="t('gallery.open', { title: photo.title })" @click="emit('open', photo.id)">
                    <picture>
                        <source type="image/avif" :srcset="photo.avif" />
                        <img :src="photo.webp" alt="" :width="photo.width" :height="photo.height" loading="lazy" decoding="async" class="gw-wall__image" />
                    </picture>
                    <span class="gw-wall__caption" aria-hidden="true">
                        <span class="gw-wall__tag">{{ labels[photo.category] }}</span>
                        <span class="gw-wall__title">{{ photo.title }}</span>
                        <span v-if="photo.subtitle" class="gw-wall__subtitle">{{ photo.subtitle }}</span>
                    </span>
                </button>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.gw-wall {
    position: relative;
}

.gw-wall--ready {
    transition: height 0.7s cubic-bezier(0.22, 1, 0.36, 1);
}

.gw-wall__item {
    position: absolute;
    top: 0;
    left: 0;
    will-change: transform, opacity;
}

.gw-wall--ready .gw-wall__item {
    transition:
        transform 0.7s cubic-bezier(0.22, 1, 0.36, 1),
        opacity 0.5s ease;
}

.gw-wall__item--hidden {
    pointer-events: none;
}

.gw-wall__card {
    position: relative;
    display: block;
    width: 100%;
    height: 100%;
    padding: 0;
    overflow: hidden;
    border: 0;
    border-radius: 20px;
    background: var(--color-gw-neu-dark);
    box-shadow:
        10px 10px 22px var(--color-gw-neu-dark-shade),
        -6px -6px 16px var(--color-gw-neu-dark-highlight);
    cursor: zoom-in;
}

.gw-wall__card:focus-visible {
    outline-offset: 3px;
}

.gw-wall__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 1.2s cubic-bezier(0.22, 1, 0.36, 1);
}

/* Caption: always visible on touch; rises on hover or focus with a mouse. */
.gw-wall__caption {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    padding: 44px 16px 14px;
    background: linear-gradient(0deg, rgb(10 10 10 / 0.92), rgb(10 10 10 / 0));
    color: var(--color-gw-white);
    text-align: left;
    transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}

.gw-wall__tag {
    padding: 3px 9px;
    border-radius: 999px;
    background: var(--color-gw-yellow);
    color: var(--color-gw-black);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.gw-wall__title {
    font-family: var(--gw-font-barlow);
    font-size: 22px;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
}

.gw-wall__subtitle {
    color: #d0d0d0;
    font-size: 13px;
}

@media (hover: hover) and (pointer: fine) {
    .gw-wall__caption {
        transform: translateY(101%);
    }

    .gw-wall__card:hover .gw-wall__caption,
    .gw-wall__card:focus-visible .gw-wall__caption {
        transform: none;
    }

    .gw-wall__card:hover .gw-wall__image {
        transform: scale(1.06);
    }
}

/* Two narrow columns: a smaller caption so it does not hide the photo. */
@media (max-width: 640px) {
    .gw-wall__caption {
        gap: 3px;
        padding: 28px 10px 10px;
    }

    .gw-wall__tag {
        padding: 2px 7px;
        font-size: 10px;
    }

    .gw-wall__title {
        font-size: 17px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-wall--ready,
    .gw-wall--ready .gw-wall__item,
    .gw-wall__image,
    .gw-wall__caption {
        transition: none;
    }

    .gw-wall__card:hover .gw-wall__image {
        transform: none;
    }
}
</style>
