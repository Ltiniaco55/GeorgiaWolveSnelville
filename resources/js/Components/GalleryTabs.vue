<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Filter tabs of /gallery (docs/design/home-interacciones.md, "Página /gallery"):
 * a pressed-in pill with one tab per category and its photo count, and a yellow
 * indicator that slides to the active tab. Sticky below the header pill.
 *
 * ARIA tabs with automatic activation: ← → move and select, Home / End jump to
 * the ends; only the selected tab is in the tab order. The tabs control the wall
 * (`panelId`). With reduced motion the indicator does not slide.
 *
 * @typedef {{ key: string, label: string, count: number }} GalleryTab
 */
const props = defineProps({
    /** @type {import('vue').PropType<GalleryTab[]>} */
    tabs: {
        type: Array,
        required: true,
    },
    panelId: {
        type: String,
        required: true,
    },
});

const active = defineModel({ type: String, required: true });

const { t } = useTranslations();
const list = ref(null);
const indicator = ref({ left: 0, width: 0, ready: false });
let resizeObserver = null;

function measure() {
    const tab = list.value?.querySelector(`[data-tab="${active.value}"]`);

    if (!tab) {
        return;
    }

    indicator.value = { left: tab.offsetLeft, width: tab.offsetWidth, ready: true };
}

/**
 * @param {string} key
 */
async function select(key, { focus = false } = {}) {
    active.value = key;
    await nextTick();
    const tab = list.value?.querySelector(`[data-tab="${key}"]`);

    if (focus) {
        tab?.focus();
    }

    tab?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
}

/**
 * @param {KeyboardEvent} event
 */
function onKeydown(event) {
    const index = props.tabs.findIndex((tab) => tab.key === active.value);
    const last = props.tabs.length - 1;
    const targets = { ArrowRight: Math.min(last, index + 1), ArrowLeft: Math.max(0, index - 1), Home: 0, End: last };

    if (!(event.key in targets)) {
        return;
    }

    event.preventDefault();
    select(props.tabs[targets[event.key]].key, { focus: true });
}

watch(active, () => nextTick(measure));

onMounted(() => {
    measure();
    resizeObserver = new ResizeObserver(() => requestAnimationFrame(measure));
    resizeObserver.observe(list.value);
    document.fonts?.ready.then(measure);
});

onBeforeUnmount(() => resizeObserver?.disconnect());
</script>

<template>
    <div class="gw-tabs" data-test="gallery-tabs">
        <div ref="list" class="gw-tabs__list" role="tablist" :aria-label="t('gallery.filters_label')" @keydown="onKeydown">
            <span
                class="gw-tabs__indicator"
                :class="{ 'gw-tabs__indicator--ready': indicator.ready }"
                :style="{ transform: `translateX(${indicator.left}px)`, width: `${indicator.width}px` }"
                aria-hidden="true"
                data-test="tab-indicator"
            />
            <button
                v-for="tab in tabs"
                :id="`gallery-tab-${tab.key}`"
                :key="tab.key"
                type="button"
                role="tab"
                class="gw-tabs__tab"
                :class="{ 'gw-tabs__tab--active': tab.key === active }"
                :aria-selected="tab.key === active ? 'true' : 'false'"
                :aria-controls="panelId"
                :tabindex="tab.key === active ? 0 : -1"
                :data-tab="tab.key"
                @click="select(tab.key)"
            >
                {{ tab.label }}
                <span class="gw-tabs__count" aria-hidden="true">{{ tab.count }}</span>
                <span class="sr-only">, {{ t('gallery.count', { count: tab.count }) }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.gw-tabs {
    max-width: 100%;
    overflow-x: auto;
    padding: 6px;
    border-radius: 999px;
    background: var(--color-gw-neu-dark);
    box-shadow: var(--shadow-gw-inset-dark);
    scrollbar-width: none;
}

.gw-tabs::-webkit-scrollbar {
    display: none;
}

.gw-tabs__list {
    position: relative;
    display: flex;
    gap: 4px;
    width: max-content;
}

.gw-tabs__indicator {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    border-radius: 999px;
    background: var(--color-gw-yellow);
    box-shadow: 3px 3px 8px var(--color-gw-neu-dark-shade);
    opacity: 0;
}

.gw-tabs__indicator--ready {
    opacity: 1;
    transition:
        transform 0.45s cubic-bezier(0.34, 1.3, 0.64, 1),
        width 0.45s cubic-bezier(0.34, 1.3, 0.64, 1);
}

.gw-tabs__tab {
    position: relative;
    display: inline-flex;
    gap: 8px;
    align-items: center;
    min-height: 44px;
    padding: 0 18px;
    border: 0;
    border-radius: 999px;
    background: none;
    color: #cfcfcf;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    transition: color 0.3s;
}

.gw-tabs__tab:focus-visible {
    outline-offset: -3px;
}

.gw-tabs__tab--active {
    color: var(--color-gw-black);
}

@media (hover: hover) and (pointer: fine) {
    .gw-tabs__tab:not(.gw-tabs__tab--active):hover {
        color: var(--color-gw-white);
    }
}

.gw-tabs__count {
    min-width: 24px;
    padding: 2px 7px;
    border-radius: 999px;
    background: rgb(255 255 255 / 0.08);
    font-size: 12px;
    text-align: center;
}

.gw-tabs__tab--active .gw-tabs__count {
    background: rgb(0 0 0 / 0.12);
}

@media (prefers-reduced-motion: reduce) {
    .gw-tabs__indicator--ready,
    .gw-tabs__tab {
        transition: none;
    }
}
</style>
