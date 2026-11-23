<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useReducedMotion } from '../Composables/useReducedMotion';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Hero stage: a pressed-in dark neumorphic panel, 4:5, with two badges
 * (docs/design/direccion-de-diseno.md, "Hero").
 *
 * mode "jersey": the spinning 3D jersey (jerseyScene.js, plain Three.js).
 * - A static WebP poster (the jersey from the front) is always there first.
 * - Three.js and the model are imported only once the stage is on screen, the
 *   brand intro is over (`ready`) and the browser is idle; then the canvas fades
 *   in over the poster on its first frame.
 * - With reduced motion, without hardware-accelerated WebGL, or if loading fails,
 *   the poster stays and no canvas is created.
 * - The animation pauses while the stage is off screen or the tab is hidden, and
 *   everything (geometries, textures, renderer) is freed on unmount.
 * mode "photo": the default slot (an <img> with its alt).
 *
 * `data-jersey-state` (poster | loading | running | paused) is for tests.
 */
const props = defineProps({
    mode: {
        type: String,
        default: 'jersey',
        validator: (value) => ['jersey', 'photo'].includes(value),
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
    <figure ref="stage" class="gw-stage relative aspect-[4/5] w-full" data-test="hero-stage" :data-jersey-state="mode === 'jersey' ? status : undefined">
        <div class="gw-stage__well absolute inset-0 overflow-hidden">
            <slot v-if="mode === 'photo'" />
            <template v-else>
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
            </template>
        </div>

        <figcaption class="contents">
            <span class="absolute top-5 left-5 inline-flex items-center gap-2 rounded-full bg-gw-yellow px-4 py-2 text-sm font-semibold text-gw-black shadow-gw-raised-dark-sm">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4zM7 6H4a3 3 0 0 0 3 4M17 6h3a3 3 0 0 1-3 4" />
                </svg>
                {{ t('home.hero.stage.badge_champions') }}
            </span>
            <span class="absolute right-5 bottom-5 max-w-[80%] rounded-full bg-gw-neu-dark px-4 py-2 text-right text-sm font-medium text-gw-white shadow-gw-raised-dark-sm">
                {{ t('home.hero.stage.badge_league') }}
            </span>
        </figcaption>
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

@media (prefers-reduced-motion: reduce) {
    .gw-stage__poster,
    .gw-stage__canvas {
        transition: none;
    }
}
</style>
