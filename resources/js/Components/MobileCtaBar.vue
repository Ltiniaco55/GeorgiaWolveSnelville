<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Fixed bottom bar on mobile (docs/design/sitemap.md, "Barra fija inferior en
 * móvil"): Register, Free training and WhatsApp. It slides in once the page hero
 * (`[data-site-hero]`) has scrolled out of view, and is always there on pages
 * without a hero. Respects the iOS safe area. While hidden it is inert, so it is
 * out of the tab order and of the accessibility tree.
 * The layout reserves space below the content so the bar never covers it.
 */
const page = usePage();
const { t } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});

const isVisible = ref(false);
let observer = null;

function observeHero() {
    observer?.disconnect();
    observer = null;

    const hero = document.querySelector('[data-site-hero]');

    if (!hero) {
        isVisible.value = true;

        return;
    }

    observer = new IntersectionObserver(([entry]) => {
        // Visible once the hero is above the viewport, not while it is still below.
        isVisible.value = !entry.isIntersecting && entry.boundingClientRect.bottom <= 0;
    });
    observer.observe(hero);
}

onMounted(observeHero);
watch(
    () => page.url,
    async () => {
        await nextTick();
        observeHero();
    },
);
onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <nav class="gw-bar" :class="{ 'gw-bar--visible': isVisible }" :inert="!isVisible" :aria-label="t('common.mobile_bar.label')" data-test="mobile-bar">
        <Link :href="navigation.register" class="gw-bar__button gw-bar__button--primary">{{ t('common.mobile_bar.register') }}</Link>
        <Link :href="navigation.free_training" class="gw-bar__button gw-bar__button--secondary">{{ t('common.mobile_bar.free_training') }}</Link>
        <a v-if="page.props.whatsappUrl" :href="page.props.whatsappUrl" class="gw-bar__whatsapp" target="_blank" rel="noopener" :aria-label="t('common.mobile_bar.whatsapp')">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                <path
                    d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.2c0-.1-.2-.2-.5-.3z"
                />
            </svg>
        </a>
    </nav>
</template>

<style scoped>
.gw-bar {
    --gw-focus-ring: var(--color-gw-yellow);

    position: fixed;
    inset: auto 0 0;
    z-index: 30;
    display: flex;
    gap: 0.5rem;
    align-items: center;
    padding: 0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom));
    border-top: 1px solid rgb(255 255 255 / 0.08);
    background: rgb(10 10 10 / 0.97);
    transform: translateY(110%);
    visibility: hidden;
    transition:
        transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
        visibility 0s 0.35s;
}

.gw-bar--visible {
    transform: none;
    visibility: visible;
    transition:
        transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
        visibility 0s;
}

/* Sized by their text, sharing the spare room: "Entrenamiento gratis" needs more than half. */
.gw-bar__button {
    display: inline-grid;
    flex: 1 1 auto;
    place-items: center;
    min-width: 0;
    height: 48px;
    padding-inline: 0.75rem;
    border-radius: 999px;
    font-size: 0.9375rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}

@media (max-width: 379px) {
    .gw-bar__button {
        padding-inline: 0.625rem;
        font-size: 0.8125rem;
    }
}

.gw-bar__button--primary {
    background: var(--color-gw-yellow);
    color: var(--color-gw-black);
    box-shadow:
        inset 0 3px 6px rgb(0 0 0 / 0.18),
        inset 0 -2px 4px rgb(255 255 255 / 0.35);
}

.gw-bar__button--secondary {
    border: 1px solid rgb(255 255 255 / 0.85);
    color: var(--color-gw-white);
}

.gw-bar__whatsapp {
    display: grid;
    flex: none;
    place-items: center;
    width: 48px;
    height: 48px;
    border: 1px solid rgb(255 255 255 / 0.25);
    border-radius: 999px;
    color: var(--color-gw-white);
}

.gw-bar__whatsapp svg {
    width: 22px;
    height: 22px;
}

/* Desktop has the header CTA instead (and, later, the floating WhatsApp button). */
@media (min-width: 1024px) {
    .gw-bar {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-bar,
    .gw-bar--visible {
        transition: none;
    }
}
</style>
