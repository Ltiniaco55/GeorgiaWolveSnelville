<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import GalleryTabs from '../Components/GalleryTabs.vue';
import GalleryWall from '../Components/GalleryWall.vue';
import HomePanel from '../Components/HomePanel.vue';
import PhotoViewer from '../Components/PhotoViewer.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * /gallery "Life in the pack." (docs/design/home-interacciones.md, "Página
 * /gallery"; behaviour inspired by React Bits Pro "Gallery 8", own code):
 * a black header with "← Back to home", then on #161616 the sticky filter tabs
 * (GalleryTabs), the masonry wall (GalleryWall) and the full-screen viewer
 * (PhotoViewer), which walks only through the active filter.
 *
 * Photos: only those with the parents' media release (GalleryController).
 */
const props = defineProps({
    /** @type {import('vue').PropType<import('../Components/GalleryWall.vue').WallPhoto[]>} */
    photos: {
        type: Array,
        default: () => [],
    },
    /** Categories that have photos, in tab order. */
    categories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});

const filter = ref('all');
const viewerIndex = ref(null);

const labels = computed(() => Object.fromEntries(props.categories.map((key) => [key, t(`gallery.categories.${key}`)])));

const tabs = computed(() => [
    { key: 'all', label: t('gallery.all'), count: props.photos.length },
    ...props.categories.map((key) => ({ key, label: labels.value[key], count: props.photos.filter((photo) => photo.category === key).length })),
]);

const filteredPhotos = computed(() => (filter.value === 'all' ? props.photos : props.photos.filter((photo) => photo.category === filter.value)));

/**
 * @param {number} id
 */
function openViewer(id) {
    viewerIndex.value = filteredPhotos.value.findIndex((photo) => photo.id === id);
}
</script>

<template>
    <Head :title="t('gallery.meta_title')" />

    <section class="gw-gallery-head" aria-labelledby="gallery-title" data-site-hero>
        <div class="gw-wrap">
            <Link :href="navigation.home" class="gw-gallery-head__back" data-test="back-home">
                <span aria-hidden="true">←</span>
                {{ t('gallery.back_home') }}
            </Link>
            <p class="gw-gallery-head__eyebrow"><span class="gw-gallery-head__dot" aria-hidden="true" />{{ t('gallery.eyebrow') }}</p>
            <h1 id="gallery-title" class="gw-gallery-head__title">
                {{ t('gallery.title_lead') }}
                <span class="text-gw-yellow">{{ t('gallery.title_accent') }}</span>
            </h1>
            <p class="gw-gallery-head__intro">{{ t('gallery.intro') }}</p>
        </div>
    </section>

    <HomePanel tone="dark" class="gw-gallery-body" aria-labelledby="gallery-title">
        <div class="gw-wrap">
            <template v-if="photos.length">
                <div class="gw-gallery-body__bar">
                    <GalleryTabs v-model="filter" :tabs="tabs" panel-id="gallery-wall" />
                </div>
                <p class="gw-gallery-body__hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M4 3l7 17 2.5-7.5L21 10z" />
                    </svg>
                    {{ t('gallery.hint') }}
                </p>

                <GalleryWall :photos="photos" :filter="filter" :labels="labels" panel-id="gallery-wall" :labelled-by="`gallery-tab-${filter}`" @open="openViewer" />
            </template>

            <p v-else class="py-24 text-center text-lg text-gw-white/75" data-test="gallery-empty">{{ t('gallery.empty') }}</p>
        </div>
    </HomePanel>

    <PhotoViewer v-model="viewerIndex" :photos="filteredPhotos" :labels="labels" />
</template>

<style scoped>
.gw-gallery-head {
    --gw-focus-ring: var(--color-gw-yellow);

    padding: calc(var(--gw-edge) + var(--gw-header-h) + 48px) 0 120px;
    background: var(--color-gw-black);
    color: var(--color-gw-white);
}

.gw-gallery-head__back {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    color: #cfcfcf;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

@media (hover: hover) and (pointer: fine) {
    .gw-gallery-head__back:hover {
        color: var(--color-gw-yellow);
    }
}

.gw-gallery-head__eyebrow {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 40px;
    color: var(--color-gw-yellow);
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.gw-gallery-head__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.gw-gallery-head__title {
    margin-top: 12px;
    font-family: var(--gw-font-barlow);
    font-size: clamp(64px, min(11vw, 18vh), 180px);
    font-weight: 800;
    line-height: 0.84;
    text-transform: uppercase;
}

.gw-gallery-head__intro {
    max-width: 620px;
    margin-top: 24px;
    color: #d4d4d4;
    font-size: clamp(16px, 1.3vw, 19px);
    line-height: 1.6;
}

.gw-gallery-body {
    padding-block: 56px 160px;
}

/* Tabs stick below the header pill. */
.gw-gallery-body__bar {
    position: sticky;
    top: calc(var(--gw-edge) + var(--gw-header-solid-h) + 12px);
    z-index: 30;
    display: flex;
    justify-content: center;
}

.gw-gallery-body__hint {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
    margin: 18px 0 40px;
    color: #a3a3a3;
    font-size: 13px;
}

.gw-gallery-body__hint svg {
    width: 15px;
    height: 15px;
}

@media (max-width: 760px) {
    .gw-gallery-head {
        padding-bottom: 90px;
    }

    .gw-gallery-body {
        padding-block: 40px 120px;
    }
}
</style>
