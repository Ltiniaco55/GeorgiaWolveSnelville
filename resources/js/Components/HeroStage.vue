<script setup>
import { useTranslations } from '../Composables/useTranslations';
import BrandLogo from './BrandLogo.vue';

/**
 * Hero stage: a pressed-in dark neumorphic panel, 4:5, with two badges
 * (docs/design/direccion-de-diseno.md, "Hero"). It will hold the 3D jersey
 * (phase 6) and later a photo of the academy's kids.
 *
 * mode "jersey": placeholder silhouette until the 3D model arrives.
 * mode "photo": the default slot (an <img> with its alt).
 */
defineProps({
    mode: {
        type: String,
        default: 'jersey',
        validator: (value) => ['jersey', 'photo'].includes(value),
    },
});

const { t } = useTranslations();
</script>

<template>
    <figure class="gw-stage relative aspect-[4/5] w-full" data-test="hero-stage">
        <div class="gw-stage__well absolute inset-0 grid place-items-center overflow-hidden">
            <slot v-if="mode === 'photo'" />
            <div v-else class="flex w-3/5 flex-col items-center gap-6" role="img" :aria-label="t('home.hero.stage.label')">
                <div class="relative w-full">
                    <svg class="w-full text-gw-charcoal" viewBox="0 0 240 230" aria-hidden="true" focusable="false">
                        <path
                            d="M86 10 L46 24 L8 64 L34 102 L56 88 L56 222 L184 222 L184 88 L206 102 L232 64 L194 24 L154 10 C146 26 134 34 120 34 C106 34 94 26 86 10 Z"
                            fill="currentColor"
                        />
                        <path d="M86 10 C94 26 106 34 120 34 C134 34 146 26 154 10" fill="none" stroke="var(--color-gw-yellow)" stroke-width="6" stroke-linecap="round" />
                        <path d="M8 64 L34 102 M232 64 L206 102" fill="none" stroke="var(--color-gw-yellow)" stroke-width="5" stroke-linecap="round" />
                    </svg>
                    <BrandLogo variant="wolf-yellow" class="gw-stage__emblem" decorative />
                </div>
                <span class="text-xs font-medium tracking-widest text-gw-white/60 uppercase">{{ t('home.hero.stage.placeholder') }}</span>
            </div>
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

/* The emblem sits on the jersey's chest. */
.gw-stage__emblem {
    position: absolute;
    top: 30%;
    left: 50%;
    width: 24%;
    transform: translateX(-50%);
}
</style>
