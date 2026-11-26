<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useFocusTrap } from '../Composables/useFocusTrap';
import { useTranslations } from '../Composables/useTranslations';
import BrandLogo from './BrandLogo.vue';
import CtaButton from './CtaButton.vue';
import LanguageMenu from './LanguageMenu.vue';
import NavLink from './NavLink.vue';

/**
 * Fixed site header (docs/design/sitemap.md, "Navegación"; prototype home.html):
 * it sits inside the home hero's frame, --gw-edge from the window like the frame,
 * with 16 px above the crest and the hero's gutter at the sides, so the crest never
 * touches the frame's rounded corner. Transparent over the black hero; once the page
 * scrolls it becomes its own floating dark pill, still --gw-edge from the edges.
 * Solid also means compact: the wordmark folds away and the same crest shrinks from
 * 72 to 48 px, in 0.35 s.
 * The brand link keeps its accessible name in both states. Crest (72 px) and wordmark, the five
 * main links, the "Register" CTA and, in the right corner, the language dropdown.
 *
 * Below 1024 px: a hamburger opens a full-screen black panel (a modal dialog) with
 * the longer mobile link list, the CTAs, WhatsApp and the language switcher. Focus
 * stays inside the panel, Esc closes it and focus goes back to the button.
 *
 * The crest is the landing spot of the brand intro (`data-intro-crest`).
 */
const page = usePage();
const { t } = useTranslations();

const mainLinks = ['programs', 'pricing', 'about', 'leagues', 'gallery'];
// Pricing, About, Leagues and FAQ are sections of the home page (NavLink jumps there).
const mobileLinks = ['programs', 'pricing', 'about', 'leagues', 'gallery', 'faq'];

const navigation = computed(() => page.props.navigation ?? {});
const isSolid = ref(false);
const isMenuOpen = ref(false);
const menuPanel = ref(null);

function updateSolid() {
    isSolid.value = window.scrollY > 8;
}

function closeMenu() {
    isMenuOpen.value = false;
}

useFocusTrap(menuPanel, isMenuOpen, { onEscape: closeMenu });

watch(isMenuOpen, (open) => document.documentElement.classList.toggle('gw-scroll-locked', open));
watch(() => page.url, closeMenu);

onMounted(() => {
    updateSolid();
    window.addEventListener('scroll', updateSolid, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', updateSolid);
    document.documentElement.classList.remove('gw-scroll-locked');
});

/**
 * @param {string} name
 */
function isCurrent(name) {
    return page.props.currentPage === name ? 'page' : undefined;
}
</script>

<template>
    <header class="gw-header gw-intro-clip" :class="{ 'gw-header--solid': isSolid }" data-test="site-header">
        <div class="gw-header__bar flex items-center gap-4 lg:gap-8">
            <Link :href="navigation.home" class="gw-brand flex min-w-0 items-center" :aria-label="t('common.nav.home')" data-test="brand-link">
                <span class="gw-brand__mark">
                    <BrandLogo variant="crest" class="gw-brand__crest" decorative data-intro-crest data-test="brand-crest" />
                </span>
                <span class="gw-brand__word" data-test="brand-wordmark">
                    <BrandLogo variant="wordmark-white" class="h-5" decorative />
                </span>
            </Link>

            <nav class="ml-auto hidden lg:block" :aria-label="t('common.nav.label')">
                <ul class="flex items-center gap-7">
                    <li v-for="name in mainLinks" :key="name">
                        <NavLink :href="navigation[name]" class="gw-header__link" :aria-current="isCurrent(name)">{{ t(`common.nav.${name}`) }}</NavLink>
                    </li>
                </ul>
            </nav>

            <div class="hidden lg:block">
                <CtaButton variant="primary" surface="dark" :href="navigation.register" align="end" always-open data-test="header-register">{{ t('common.nav.register') }}</CtaButton>
            </div>

            <div class="hidden lg:block">
                <LanguageMenu />
            </div>

            <button
                type="button"
                class="gw-header__burger ml-auto lg:hidden"
                :aria-expanded="isMenuOpen ? 'true' : 'false'"
                aria-controls="gw-mobile-menu"
                data-test="menu-button"
                @click="isMenuOpen = true"
            >
                <span class="sr-only">{{ t('common.menu.open') }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false">
                    <path d="M4 7h16M4 12h16M4 17h10" />
                </svg>
            </button>
        </div>
    </header>

    <Teleport to="body">
        <Transition name="gw-menu">
            <div
                v-if="isMenuOpen"
                id="gw-mobile-menu"
                ref="menuPanel"
                class="gw-menu"
                role="dialog"
                aria-modal="true"
                :aria-label="t('common.menu.label')"
                data-test="mobile-menu"
            >
                <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-8">
                    <BrandLogo variant="crest" class="h-14" decorative />
                    <button type="button" class="gw-header__burger" data-test="menu-close" @click="closeMenu">
                        <span class="sr-only">{{ t('common.menu.close') }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-4 pt-4 pb-8 sm:px-8" :aria-label="t('common.nav.label')">
                    <ul class="flex flex-col">
                        <li v-for="name in mobileLinks" :key="name">
                            <NavLink :href="navigation[name]" class="gw-menu__link" :aria-current="isCurrent(name)" @click="closeMenu">{{ t(`common.nav.${name}`) }}</NavLink>
                        </li>
                    </ul>

                    <div class="mt-8 flex flex-col items-start gap-4">
                        <CtaButton variant="primary" surface="dark" :href="navigation.register">{{ t('common.nav.register') }}</CtaButton>
                        <CtaButton variant="secondary" surface="dark" :href="navigation.free_training">{{ t('common.nav.free_training') }}</CtaButton>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-gw-charcoal pt-6">
                        <a v-if="page.props.whatsappUrl" :href="page.props.whatsappUrl" class="gw-menu__whatsapp" target="_blank" rel="noopener">
                            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                                <path
                                    d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.2c0-.1-.2-.2-.5-.3z"
                                />
                            </svg>
                            {{ t('common.menu.whatsapp') }}
                        </a>
                        <LanguageMenu placement="top" />
                    </div>
                </nav>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.gw-header {
    --gw-focus-ring: var(--color-gw-yellow);

    position: fixed;
    top: var(--gw-edge);
    right: var(--gw-edge);
    left: var(--gw-edge);
    z-index: 40;
    border-radius: 999px;
    color: var(--color-gw-white);
    transition:
        background-color 0.3s,
        box-shadow 0.3s;
}

/* Floating pill: near-opaque dark (no blur: glassmorphism is ruled out). */
.gw-header--solid {
    background: rgb(14 14 14 / 0.94);
    box-shadow:
        8px 8px 22px rgb(0 0 0 / 0.55),
        -4px -4px 14px rgb(40 40 40 / 0.25),
        inset 0 0 0 1px rgb(255 255 255 / 0.05);
}

/*
 * Brand: crest (72 px, the brand intro lands on it) + one-line wordmark at the top;
 * once solid, the same crest at 48 px and no wordmark. Only the brand box changes
 * size: the links on the right are pushed by margin-left: auto, so they never move
 * sideways.
 */
/* Inside the hero frame and lined up with its content: the gutter (app.css). */
.gw-header__bar {
    padding-block: 16px;
    padding-inline: var(--gw-gutter);
    transition: padding-block 0.35s ease;
}

.gw-brand__mark {
    position: relative;
    display: block;
    flex: none;
    width: calc(72px * 437 / 512);
    height: 72px;
    transition:
        width 0.35s ease,
        height 0.35s ease;
}

.gw-brand__crest {
    position: absolute;
    inset: 0;
    height: 100%;
}

.gw-brand__word {
    display: block;
    max-width: 10rem;
    margin-left: 0.75rem;
    overflow: hidden;
    transition:
        max-width 0.35s ease,
        margin-left 0.35s ease,
        opacity 0.2s ease;
}

.gw-header--solid .gw-header__bar {
    padding-block: 8px;
}

.gw-header--solid .gw-brand__mark {
    width: calc(48px * 437 / 512);
    height: 48px;
}

.gw-header--solid .gw-brand__word {
    max-width: 0;
    margin-left: 0;
    opacity: 0;
}

.gw-header__link {
    position: relative;
    padding-block: 0.5rem;
    color: rgb(255 255 255 / 0.85);
    font-size: 0.9375rem;
    font-weight: 500;
    text-decoration: none;
}

.gw-header__link::after {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    height: 2px;
    border-radius: 2px;
    background: var(--color-gw-yellow);
    content: '';
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s;
}

.gw-header__link[aria-current='page'] {
    color: var(--color-gw-white);
}

.gw-header__link[aria-current='page']::after {
    transform: scaleX(1);
}

@media (hover: hover) and (pointer: fine) {
    .gw-header__link:hover {
        color: var(--color-gw-white);
    }

    .gw-header__link:hover::after {
        transform: scaleX(1);
    }
}

.gw-header__burger {
    display: grid;
    flex: none;
    place-items: center;
    width: 48px;
    height: 48px;
    border: 1px solid rgb(255 255 255 / 0.25);
    border-radius: 999px;
    background: transparent;
    color: var(--color-gw-white);
    cursor: pointer;
}

@media (min-width: 1024px) {
    .gw-header .gw-header__burger {
        display: none;
    }
}

.gw-header__burger svg {
    width: 22px;
    height: 22px;
}

.gw-menu {
    --gw-focus-ring: var(--color-gw-yellow);

    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    flex-direction: column;
    padding-bottom: env(safe-area-inset-bottom);
    background: var(--color-gw-black);
    color: var(--color-gw-white);
}

.gw-menu__link {
    display: block;
    padding-block: 0.375rem;
    font-family: var(--gw-font-barlow);
    font-size: clamp(2.25rem, 10vw, 3.25rem);
    font-weight: 800;
    line-height: 1.05;
    text-decoration: none;
    text-transform: uppercase;
}

.gw-menu__link[aria-current='page'] {
    color: var(--color-gw-yellow);
}

.gw-menu__whatsapp {
    display: inline-flex;
    gap: 0.5rem;
    align-items: center;
    min-height: 3rem;
    color: var(--color-gw-white);
    font-weight: 600;
    text-decoration: none;
}

.gw-menu-enter-active,
.gw-menu-leave-active {
    transition:
        opacity 0.25s,
        transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.gw-menu-enter-from,
.gw-menu-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}

@media (prefers-reduced-motion: reduce) {
    .gw-header,
    .gw-header__bar,
    .gw-brand__mark,
    .gw-brand__word,
    .gw-header__link::after,
    .gw-menu-enter-active,
    .gw-menu-leave-active {
        transition: none;
    }
}
</style>
