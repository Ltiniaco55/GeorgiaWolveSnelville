<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CtaButton from '../Components/CtaButton.vue';
import HomePanel from '../Components/HomePanel.vue';
import SectionIntro from '../Components/SectionIntro.vue';
import { useCursorTrail } from '../Composables/useCursorTrail';
import { useTranslations } from '../Composables/useTranslations';

/**
 * "Life in the pack." (docs/design/home-interacciones.md; prototype home.html,
 * "#gallery"): black section where moving the mouse leaves a trail of photos
 * under the cursor (useCursorTrail): one every 110 px of travel, randomly rotated,
 * 1.6 s each, at most 14 at once. The trail is decorative (aria-hidden).
 *
 * Touch screens and reduced motion: a static grid of the first 6 photos, with
 * their alternative texts. CTA "View full gallery" → /gallery.
 *
 * Photos come from the Photo model; the server only sends the ones with the
 * parents' media release. Without photos there is neither trail nor grid.
 *
 * @typedef {Object} GalleryPhoto
 * @property {number} id
 * @property {string} title
 * @property {string} alt
 * @property {string} avif
 * @property {string} webp
 * @property {number} width
 * @property {number} height
 */
const props = defineProps({
    /** @type {import('vue').PropType<GalleryPhoto[]>} */
    photos: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});

const area = ref(null);
const { trail, isActive } = useCursorTrail(area, { itemCount: Math.max(1, props.photos.length), spacing: 110, lifetime: 1600, max: 14, maxRotation: 12 });

const hasPhotos = computed(() => props.photos.length > 0);
const showsTrail = computed(() => isActive.value && hasPhotos.value);
const gridPhotos = computed(() => props.photos.slice(0, 6));
</script>

<template>
    <HomePanel tone="black" id="gallery" class="gw-gallery" :class="{ 'gw-gallery--trail': showsTrail }" aria-labelledby="gallery-title" data-test="gallery">
        <div ref="area" class="gw-gallery__area" :data-trail="showsTrail ? 'on' : 'off'">
            <div class="gw-gallery__center gw-wrap">
                <SectionIntro
                    align="center"
                    :eyebrow="t('home.gallery.eyebrow')"
                    :title-lead="t('home.gallery.title_lead')"
                    :title-accent="t('home.gallery.title_accent')"
                    title-id="gallery-title"
                />
                <div class="gw-gallery__cta">
                    <CtaButton variant="primary" surface="dark" :href="navigation.gallery" data-test="gallery-cta">{{ t('home.gallery.cta') }}</CtaButton>
                </div>
            </div>

            <div v-if="showsTrail" class="gw-gallery__trail" aria-hidden="true">
                <picture
                    v-for="item in trail"
                    :key="item.id"
                    class="gw-gallery__trail-item"
                    :style="{ left: `${item.x}px`, top: `${item.y}px`, '--gw-rotation': `${item.rotation.toFixed(1)}deg` }"
                    data-test="trail-photo"
                >
                    <source type="image/avif" :srcset="photos[item.index].avif" />
                    <img :src="photos[item.index].webp" alt="" :width="photos[item.index].width" :height="photos[item.index].height" decoding="async" />
                </picture>
            </div>

            <ul v-else-if="hasPhotos" class="gw-gallery__grid gw-wrap" data-test="gallery-grid">
                <li v-for="photo in gridPhotos" :key="photo.id">
                    <picture>
                        <source type="image/avif" :srcset="photo.avif" />
                        <img :src="photo.webp" :alt="photo.alt" :width="photo.width" :height="photo.height" loading="lazy" decoding="async" />
                    </picture>
                </li>
            </ul>
        </div>
    </HomePanel>
</template>

<style scoped>
.gw-gallery {
    overflow: hidden;
}

.gw-gallery__area {
    position: relative;
    min-height: 100svh;
}

.gw-gallery--trail .gw-gallery__area {
    cursor: crosshair;
}

/* Text above the trail; only the CTA takes the pointer. */
.gw-gallery__center {
    position: relative;
    z-index: 3;
    padding-block: 22vh;
    pointer-events: none;
}

.gw-gallery__cta {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    pointer-events: auto;
}

.gw-gallery__trail {
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
}

.gw-gallery__trail-item {
    position: absolute;
    width: 190px;
    height: 240px;
    animation: gw-trail 1.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.gw-gallery__trail-item img {
    width: 100%;
    height: 100%;
    border-radius: 18px;
    object-fit: cover;
    box-shadow: 0 20px 40px rgb(0 0 0 / 0.6);
}

@keyframes gw-trail {
    0% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.6) rotate(var(--gw-rotation));
    }

    12% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1) rotate(var(--gw-rotation));
    }

    70% {
        opacity: 1;
    }

    100% {
        opacity: 0;
        transform: translate(-50%, -40%) scale(0.85) rotate(var(--gw-rotation));
    }
}

/* Touch and reduced motion: static grid, slightly rotated cards. */
.gw-gallery__center:has(+ .gw-gallery__grid) {
    padding-block: 120px 40px;
}

.gw-gallery__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    padding-bottom: 120px;
}

.gw-gallery__grid img {
    width: 100%;
    aspect-ratio: 4 / 5;
    border-radius: 16px;
    object-fit: cover;
}

.gw-gallery__grid li:nth-child(odd) {
    transform: rotate(-2deg);
}

.gw-gallery__grid li:nth-child(even) {
    transform: rotate(2deg) translateY(16px);
}

@media (max-width: 760px) {
    .gw-gallery__area {
        min-height: 0;
    }

    .gw-gallery__center:has(+ .gw-gallery__grid) {
        padding-block: 90px 30px;
    }

    .gw-gallery__grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
}
</style>
