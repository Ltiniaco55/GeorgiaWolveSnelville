<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePointerLamp } from '../Composables/usePointerLamp';
import { useReducedMotion } from '../Composables/useReducedMotion';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Hero stage (docs/design/direccion-de-diseno.md, "Hero").
 *
 * mode "photo" (the home hero): fills its positioned parent (the hero frame).
 * - The photo sits on the right (58 % of the width, at most 860 px), darkened and
 *   faded into the black towards the left and the bottom. Below 760 px it goes on
 *   top, full width, 46 % of the height, faded towards the bottom.
 * - Flashlight: with a fine pointer and motion allowed, while the pointer is over
 *   the stage, an undimmed copy of the photo shows through a 230 px circle around
 *   it (a radial mask). The copy is decorative (aria-hidden); the photo has the alt.
 * - `image`: { src, width, height, sizes, srcset: { avif, webp } }.
 *
 * mode "jersey": a pressed-in dark neumorphic panel, 4:5, with the spinning 3D
 * jersey (jerseyScene.js, plain Three.js). Not used in the home for now; kept for
 * later (and shown in /styleguide).
 * - A static WebP poster (the jersey from the front) is always there first.
 * - Three.js and the model are imported only once the stage is on screen, the
 *   brand intro is over (`ready`) and the browser is idle; then the canvas fades
 *   in over the poster on its first frame.
 * - With reduced motion, without hardware-accelerated WebGL, or if loading fails,
 *   the poster stays and no canvas is created.
 * - The animation pauses while the stage is off screen or the tab is hidden, and
 *   everything (geometries, textures, renderer) is freed on unmount.
 * `data-jersey-state` (poster | loading | running | paused) is for tests.
 */
const props = defineProps({
    mode: {
        type: String,
        default: 'jersey',
        validator: (value) => ['jersey', 'photo'].includes(value),
    },
    /** Photo mode: the responsive image (see above). */
    image: {
        type: Object,
        default: null,
    },
    /** Photo mode: the photo's alternative text. */
    alt: {
        type: String,
        default: '',
    },
    /** False while the brand intro plays: the 3D jersey waits for it. */
    ready: {
        type: Boolean,
        default: true,
    },
});

const MODEL_URL = '/models/camiseta-georgia-wolves.glb';
const POSTER_URL = '/images/jersey-poster.webp';

const { t } = useTranslations();
const prefersReducedMotion = useReducedMotion();

const stage = ref(null);
const canvas = ref(null);
const photoStage = ref(null);
const photoBox = ref(null);
const { isLit: isPhotoLit } = usePointerLamp(photoBox, { area: photoStage });
const isOnScreen = ref(false);
const isPageVisible = ref(!document.hidden);
const hasWebGL = ref(null);
const status = ref('poster');
const hasFirstFrame = ref(false);

const wants3d = computed(() => props.mode === 'jersey' && !prefersReducedMotion.value && hasWebGL.value !== false);
const showCanvas = computed(() => wants3d.value && status.value !== 'poster');

let scene = null;
let isUnmounted = false;
let intersectionObserver = null;
let resizeObserver = null;
let idleHandle = null;

/** Renderers that draw WebGL on the CPU. */
const SOFTWARE_RENDERER = /swiftshader|llvmpipe|softpipe|software|basic render/i;

/**
 * Whether this browser has hardware-accelerated WebGL (checked once, then released).
 * Software rendering is ruled out twice: failIfMajorPerformanceCaveat, and the
 * renderer name (Chrome does not flag SwiftShader as a caveat). Spinning a
 * 160k-triangle model on the CPU makes the whole page sluggish, so those devices
 * keep the poster too.
 */
function detectWebGL() {
    try {
        const probe = document.createElement('canvas');
        const options = { failIfMajorPerformanceCaveat: true };
        const context = probe.getContext('webgl2', options) ?? probe.getContext('webgl', options);

        if (!context) {
            return false;
        }

        const debugInfo = context.getExtension('WEBGL_debug_renderer_info');
        const renderer = debugInfo ? String(context.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL)) : '';
        context.getExtension('WEBGL_lose_context')?.loseContext();

        return !SOFTWARE_RENDERER.test(renderer);
    } catch {
        return false;
    }
}

function whenIdle(callback) {
    if ('requestIdleCallback' in window) {
        idleHandle = window.requestIdleCallback(callback, { timeout: 1500 });
    } else {
        idleHandle = window.setTimeout(callback, 200);
    }
}

function cancelIdle() {
    if (idleHandle === null) {
        return;
    }

    if ('cancelIdleCallback' in window) {
        window.cancelIdleCallback(idleHandle);
    } else {
        window.clearTimeout(idleHandle);
    }

    idleHandle = null;
}

function syncPlayback() {
    if (!scene) {
        return;
    }

    if (isOnScreen.value && isPageVisible.value) {
        scene.start();
        status.value = 'running';
    } else {
        scene.stop();
        status.value = 'paused';
    }
}

async function load() {
    idleHandle = null;
    hasWebGL.value ??= detectWebGL();

    if (!wants3d.value || status.value !== 'poster' || isUnmounted) {
        return;
    }

    status.value = 'loading';

    try {
        // Wait for Vue to render the canvas before handing it to Three.js.
        await new Promise((resolve) => requestAnimationFrame(resolve));

        const { createJerseyScene } = await import('./jerseyScene.js');
        const created = await createJerseyScene(canvas.value, {
            modelUrl: MODEL_URL,
            onFirstFrame: () => {
                hasFirstFrame.value = true;
            },
        });

        if (isUnmounted || !wants3d.value) {
            created.dispose();

            return;
        }

        scene = created;
        const { width, height } = stage.value.getBoundingClientRect();
        scene.resize(width, height);
        resizeObserver = new ResizeObserver(([entry]) => scene?.resize(entry.contentRect.width, entry.contentRect.height));
        resizeObserver.observe(stage.value);
        syncPlayback();
    } catch {
        // No WebGL context or the model failed: keep the poster.
        hasWebGL.value = false;
        status.value = 'poster';
    }
}

function maybeLoad() {
    if (status.value === 'poster' && idleHandle === null && props.ready && isOnScreen.value && wants3d.value) {
        whenIdle(load);
    }
}

function onVisibilityChange() {
    isPageVisible.value = !document.hidden;
}

function teardown() {
    cancelIdle();
    resizeObserver?.disconnect();
    resizeObserver = null;
    scene?.dispose();
    scene = null;
    hasFirstFrame.value = false;
    status.value = 'poster';
}

onMounted(() => {
    if (props.mode !== 'jersey') {
        return;
    }

    intersectionObserver = new IntersectionObserver(([entry]) => {
        isOnScreen.value = entry.isIntersecting;
    });
    intersectionObserver.observe(stage.value);
    document.addEventListener('visibilitychange', onVisibilityChange);
});

watch([isOnScreen, isPageVisible], () => {
    syncPlayback();
    maybeLoad();
});
watch(() => props.ready, maybeLoad);
watch(wants3d, (allowed) => (allowed ? maybeLoad() : teardown()));

onBeforeUnmount(() => {
    isUnmounted = true;
    intersectionObserver?.disconnect();
    document.removeEventListener('visibilitychange', onVisibilityChange);
    teardown();
});
</script>

<template>
    <figure
        v-if="mode === 'photo'"
        ref="photoStage"
        class="gw-photo-stage"
        :class="{ 'gw-photo-stage--lit': isPhotoLit }"
        data-test="hero-stage"
        data-mode="photo"
    >
        <div ref="photoBox" class="gw-photo-stage__box" data-test="hero-photo">
            <picture>
                <source type="image/avif" :srcset="image.srcset.avif" :sizes="image.sizes" />
                <source type="image/webp" :srcset="image.srcset.webp" :sizes="image.sizes" />
                <img :src="image.src" :alt="alt" :width="image.width" :height="image.height" fetchpriority="high" decoding="async" class="gw-photo-stage__img" />
            </picture>
            <picture aria-hidden="true">
                <source type="image/avif" :srcset="image.srcset.avif" :sizes="image.sizes" />
                <source type="image/webp" :srcset="image.srcset.webp" :sizes="image.sizes" />
                <img :src="image.src" alt="" :width="image.width" :height="image.height" decoding="async" class="gw-photo-stage__img gw-photo-stage__lit" data-test="hero-photo-lit" />
            </picture>
        </div>
        <div class="gw-photo-stage__fade" aria-hidden="true" />
    </figure>

    <figure v-else ref="stage" class="gw-stage relative aspect-[4/5] w-full" data-test="hero-stage" :data-jersey-state="status">
        <div class="gw-stage__well absolute inset-0 overflow-hidden">
            <img
                :src="POSTER_URL"
                :alt="t('home.hero.stage.poster_alt')"
                width="800"
                height="1000"
                fetchpriority="high"
                decoding="async"
                class="gw-stage__poster"
                :class="{ 'gw-stage__poster--hidden': hasFirstFrame }"
                data-test="jersey-poster"
            />
            <canvas
                v-if="showCanvas"
                ref="canvas"
                class="gw-stage__canvas"
                :class="{ 'gw-stage__canvas--visible': hasFirstFrame }"
                aria-hidden="true"
                data-test="jersey-canvas"
            />
        </div>
    </figure>
</template>

<style scoped>
.gw-stage__well {
    border-radius: 32px;
    background: var(--color-gw-neu-dark);
    box-shadow:
        var(--shadow-gw-inset-dark),
        0 0 0 1px rgb(255 255 255 / 0.04);
}

/* Poster and canvas fill the well; the canvas fades in over the poster. */
.gw-stage__poster,
.gw-stage__canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.gw-stage__poster {
    object-fit: contain;
    transition: opacity 0.6s ease;
}

.gw-stage__poster--hidden {
    opacity: 0;
}

.gw-stage__canvas {
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.6s ease;
}

.gw-stage__canvas--visible {
    opacity: 1;
}

/* Photo mode: fills the hero frame; the photo box sits on the right. */
.gw-photo-stage {
    position: absolute;
    inset: 0;
    margin: 0;
}

.gw-photo-stage__box {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: min(58%, 860px);
    overflow: hidden;

    /* On wide screens the photo stops at 860 px: no hard edge on its left side. */
    mask-image: linear-gradient(90deg, transparent 0, #000 22%);
}

.gw-photo-stage__img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 62% 30%;
    filter: brightness(0.6) saturate(0.85) contrast(1.05);
}

.gw-photo-stage__lit {
    filter: none;
    opacity: 0;
    mask-image: radial-gradient(circle 230px at var(--gw-lamp-x, 70%) var(--gw-lamp-y, 45%), #000 40%, transparent 100%);
    transition: opacity 0.4s;
}

.gw-photo-stage--lit .gw-photo-stage__lit {
    opacity: 1;
}

.gw-photo-stage__fade {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        linear-gradient(90deg, var(--color-gw-black) 0%, var(--color-gw-black) 38%, rgb(10 10 10 / 0.85) 48%, rgb(10 10 10 / 0.25) 66%, rgb(10 10 10 / 0) 80%),
        linear-gradient(0deg, rgb(10 10 10 / 0.95) 0%, rgb(10 10 10 / 0.5) 22%, rgb(10 10 10 / 0) 42%);
}

@media (max-width: 760px) {
    .gw-photo-stage__box {
        bottom: auto;
        width: 100%;
        height: 46%;
        mask-image: none;
    }

    .gw-photo-stage__img {
        object-position: 60% 25%;
    }

    .gw-photo-stage__fade {
        background: linear-gradient(0deg, var(--color-gw-black) 0%, var(--color-gw-black) 50%, rgb(10 10 10 / 0.3) 62%, rgb(10 10 10 / 0) 76%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-photo-stage__lit,
    .gw-stage__poster,
    .gw-stage__canvas {
        transition: none;
    }
}
</style>
