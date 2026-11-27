<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CtaButton from '../Components/CtaButton.vue';
import HomePanel from '../Components/HomePanel.vue';
import SectionIntro from '../Components/SectionIntro.vue';
import { useFinePointer } from '../Composables/useFinePointer';
import { usePinnedHorizontal } from '../Composables/usePinnedHorizontal';
import { useReducedMotion } from '../Composables/useReducedMotion';
import { useTranslations } from '../Composables/useTranslations';

/**
 * "Find your category." (docs/design/direccion-de-diseno.md, "Scroll horizontal";
 * prototype home.html, "#cats"): pinned horizontal scroll nº 1 of the site.
 *
 * The section sticks for one viewport while vertical scroll slides the row of
 * panels left (usePinnedHorizontal), with a pressed-in yellow progress bar. Panels: yellow intro "U6–U14", one #161616 panel per
 * category and a black closing panel with the CTA.
 *
 * Parallax (fine pointer, motion allowed): inside a category panel the big number
 * follows the mouse a little and the ghost number behind moves the other way.
 *
 * With reduced motion: a horizontal carousel (overflow-x, scroll-snap), no pinning,
 * no progress bar and no parallax.
 *
 * Keyboard: when a link inside a panel gets the focus while pinned, the page
 * scrolls to where that panel is in view (the track itself never scrolls).
 */
const page = usePage();
const { t, tList } = useTranslations();
const hasFinePointer = useFinePointer();
const prefersReducedMotion = useReducedMotion();
const navigation = computed(() => page.props.navigation ?? {});
const categories = computed(() => tList('home.categories.items'));

const section = ref(null);
const track = ref(null);
const { progress, isEnabled, sectionStyle, trackStyle } = usePinnedHorizontal(section, track);

const hasParallax = computed(() => hasFinePointer.value && !prefersReducedMotion.value);

/**
 * @param {PointerEvent} event
 */
function onPanelPointerMove(event) {
    if (!hasParallax.value || event.pointerType === 'touch') {
        return;
    }

    const panel = event.currentTarget;
    const rect = panel.getBoundingClientRect();
    const x = (event.clientX - rect.left) / rect.width - 0.5;
    const y = (event.clientY - rect.top) / rect.height - 0.5;
    panel.style.setProperty('--gw-number-x', `${(x * 22).toFixed(1)}px`);
    panel.style.setProperty('--gw-number-y', `${(y * 18).toFixed(1)}px`);
    panel.style.setProperty('--gw-ghost-x', `${(-x * 36).toFixed(1)}px`);
    panel.style.setProperty('--gw-ghost-y', `${(-y * 28).toFixed(1)}px`);
}

/**
 * @param {PointerEvent} event
 */
function onPanelPointerLeave(event) {
    for (const name of ['--gw-number-x', '--gw-number-y', '--gw-ghost-x', '--gw-ghost-y']) {
        event.currentTarget.style.removeProperty(name);
    }
}

/**
 * @param {FocusEvent} event
 */
function onTrackFocusIn(event) {
    if (!isEnabled.value || !section.value || !track.value) {
        return;
    }

    const panel = event.target.closest('[data-panel]');
    const distance = track.value.scrollWidth - track.value.clientWidth;

    // The browser may have scrolled the clipped track to show the focus: undo it.
    track.value.parentElement.scrollLeft = 0;

    if (!panel || distance <= 0) {
        return;
    }

    // Bring the panel's right edge in, plus the same page margin the track has on the left.
    const margin = parseFloat(getComputedStyle(track.value).paddingLeft) || 0;
    const wanted = Math.min(1, Math.max(0, (panel.offsetLeft + panel.offsetWidth + margin - track.value.clientWidth) / distance));
    const sectionTop = section.value.getBoundingClientRect().top + window.scrollY;
    const scrollable = section.value.offsetHeight - window.innerHeight;
    window.scrollTo({ top: sectionTop + wanted * scrollable, behavior: 'instant' });
}
</script>

<template>
    <HomePanel tone="dark" id="categories" aria-labelledby="categories-title" data-test="categories">
        <div ref="section" class="gw-cats" :class="{ 'gw-cats--pinned': isEnabled }" :style="sectionStyle">
            <div class="gw-cats__stage">
                <div class="gw-cats__head">
                    <SectionIntro
                        :eyebrow="t('home.categories.eyebrow')"
                        :title-lead="t('home.categories.title_lead')"
                        :title-accent="t('home.categories.title_accent')"
                        title-id="categories-title"
                    />
                </div>

                <div class="gw-cats__viewport">
                    <ul ref="track" class="gw-cats__track" :style="trackStyle" data-test="categories-track" @focusin="onTrackFocusIn">
                        <li class="gw-pane gw-pane--yellow" data-panel>
                            <p class="gw-pane__range">{{ t('home.categories.intro.range') }}</p>
                            <h3 class="gw-pane__title">{{ t('home.categories.intro.title') }}</h3>
                            <p class="gw-pane__body">{{ t('home.categories.intro.body') }}</p>
                        </li>

                        <li
                            v-for="category in categories"
                            :key="category.name"
                            class="gw-pane"
                            data-panel
                            data-test="category-panel"
                            @pointermove="onPanelPointerMove"
                            @pointerleave="onPanelPointerLeave"
                        >
                            <span class="gw-pane__ghost" aria-hidden="true">{{ category.ghost }}</span>
                            <p class="gw-pane__number" data-test="category-number">{{ category.name }}</p>
                            <h3 class="gw-pane__title">{{ category.title }}</h3>
                            <p class="gw-pane__body">{{ category.body }}</p>
                            <ul class="gw-pane__tags">
                                <li>{{ category.ages }}</li>
                                <li>{{ t('home.categories.days') }}</li>
                            </ul>
                        </li>

                        <li class="gw-pane gw-pane--black" data-panel>
                            <h3 class="gw-pane__question">
                                {{ t('home.categories.outro.title') }}<span class="text-gw-yellow">{{ t('home.categories.outro.mark') }}</span>
                            </h3>
                            <p class="gw-pane__body">{{ t('home.categories.outro.body') }}</p>
                            <div class="mt-auto pt-6">
                                <CtaButton variant="primary" surface="dark" :href="navigation.free_training" data-test="categories-cta">
                                    {{ t('home.categories.outro.cta') }}
                                </CtaButton>
                            </div>
                        </li>
                    </ul>
                </div>

                <div
                    v-if="isEnabled"
                    class="gw-cats__progress"
                    role="progressbar"
                    :aria-label="t('home.categories.progress_label')"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-valuenow="Math.round(progress * 100)"
                    data-test="categories-progress"
                >
                    <i :style="{ transform: `scaleX(${progress.toFixed(4)})` }" />
                </div>
            </div>
        </div>
    </HomePanel>
</template>

<style scoped>
.gw-cats__stage {
    display: flex;
    flex-direction: column;
    padding: 96px 0 72px;
}

/* Pinned: one viewport tall; room above for the floating header pill. */
.gw-cats--pinned .gw-cats__stage {
    position: sticky;
    top: 0;
    height: 100svh;
    overflow: hidden;
    justify-content: center;
    padding: calc(var(--gw-edge) + 80px) 0 28px;
}

.gw-cats__head {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 34px;
    padding-inline: calc(var(--gw-edge) + var(--gw-gutter));
}

.gw-cats__viewport {
    min-height: 0;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-padding-inline: calc(var(--gw-edge) + var(--gw-gutter));
}

/* 30 px above and below so the panels' shadows are not clipped. */
.gw-cats--pinned .gw-cats__viewport {
    flex: 1 1 auto;
    max-height: 620px;
    padding-block: 30px;
    overflow: hidden;
    scroll-snap-type: none;
}

.gw-cats--pinned .gw-cats__head {
    margin-bottom: 4px;
}

.gw-cats--pinned .gw-cats__progress {
    margin-top: 4px;
}

.gw-cats__track {
    display: flex;
    gap: 28px;
    width: max-content;
    height: 100%;
    padding-left: calc(var(--gw-edge) + var(--gw-gutter));
    will-change: transform;
}

/*
 * Pinned: the track is as wide as the window and its panels overflow it;
 * usePinnedHorizontal slides it by scrollWidth - clientWidth.
 */
.gw-cats--pinned .gw-cats__track {
    width: 100%;
}

/* End margin as a flex item: the track's end padding does not count in scrollWidth. */
.gw-cats__track::after {
    flex: none;
    width: calc(var(--gw-edge) + var(--gw-gutter) - 28px);
    content: '';
}

/* Reduced motion carousel: room for the panels' shadows. */
.gw-cats:not(.gw-cats--pinned) .gw-cats__track {
    padding-block: 10px 40px;
}

.gw-pane {
    position: relative;
    display: flex;
    flex: none;
    flex-direction: column;
    width: min(440px, 78vw);
    height: min(560px, 62vh);
    padding: 34px;
    overflow: hidden;
    border-radius: 32px;
    background: var(--color-gw-neu-dark);
    box-shadow: 14px 14px 30px var(--color-gw-neu-dark-shade), -10px -10px 26px var(--color-gw-neu-dark-highlight);
    scroll-snap-align: start;
}

.gw-cats--pinned .gw-pane {
    height: 100%;
    max-height: 560px;
}

.gw-pane--yellow {
    background: var(--color-gw-yellow);
    color: var(--color-gw-black);
}

.gw-pane--black {
    background: var(--color-gw-black);
}

.gw-pane__range,
.gw-pane__number {
    font-family: var(--gw-font-barlow);
    font-weight: 800;
    line-height: 0.8;
}

.gw-pane__range {
    font-size: clamp(64px, 12vh, 120px);
}

.gw-pane__number {
    color: var(--color-gw-yellow);
    font-size: clamp(96px, 20vh, 190px);
    transform: translate3d(var(--gw-number-x, 0), var(--gw-number-y, 0), 0);
    transition: transform 0.2s linear;
}

.gw-pane__ghost {
    position: absolute;
    right: -30px;
    bottom: -60px;
    color: rgb(255 255 255 / 0.035);
    font-family: var(--gw-font-barlow);
    font-size: 360px;
    font-weight: 800;
    line-height: 0.8;
    pointer-events: none;
    transform: translate3d(var(--gw-ghost-x, 0), var(--gw-ghost-y, 0), 0);
    transition: transform 0.2s linear;
}

.gw-pane__title {
    position: relative;
    margin-top: auto;
    font-family: var(--gw-font-barlow);
    font-size: 40px;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
}

.gw-pane__question {
    font-family: var(--gw-font-barlow);
    font-size: clamp(48px, 9vh, 64px);
    font-weight: 800;
    line-height: 0.9;
    text-transform: uppercase;
}

.gw-pane__body {
    position: relative;
    margin-top: 10px;
    color: #bbb;
    font-size: 15px;
    line-height: 1.55;
}

.gw-pane--yellow .gw-pane__body {
    color: #222;
}

.gw-pane__tags {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 16px;
}

.gw-pane__tags li {
    padding: 8px 13px;
    border-radius: 999px;
    background: var(--color-gw-neu-dark);
    box-shadow: var(--shadow-gw-inset-dark-sm);
    color: #ddd;
    font-size: 13px;
    font-weight: 600;
}

.gw-cats__progress {
    flex: none;
    height: 8px;
    margin: 34px calc(var(--gw-edge) + var(--gw-gutter)) 0;
    overflow: hidden;
    border-radius: 99px;
    background: var(--color-gw-neu-dark);
    box-shadow: var(--shadow-gw-inset-dark-sm);
}

.gw-cats__progress i {
    display: block;
    height: 100%;
    border-radius: 99px;
    background: var(--color-gw-yellow);
    transform-origin: left;
}

/* Below 1024 px the fixed mobile CTA bar (72 px + safe area) covers the bottom. */
@media (max-width: 1023px) {
    .gw-cats--pinned .gw-cats__stage {
        padding-bottom: calc(84px + env(safe-area-inset-bottom));
    }
}

/* Short screens: smaller panels inside. */
@media (max-height: 760px) {
    .gw-pane {
        padding: 26px;
    }

    .gw-pane__title {
        font-size: 32px;
    }

    .gw-pane__body {
        font-size: 14px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-pane__number,
    .gw-pane__ghost {
        transition: none;
    }
}
</style>
