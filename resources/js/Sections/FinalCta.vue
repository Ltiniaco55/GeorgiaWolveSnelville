<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CtaButton from '../Components/CtaButton.vue';
import HomePanel from '../Components/HomePanel.vue';
import { useMagnetic } from '../Composables/useMagnetic';
import { vReveal } from '../Composables/useReveal';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Final call "Join the pack." (docs/design/home-interacciones.md; prototype
 * home.html, ".final"): huge title and a large magnetic CTA. Within 220 px of
 * the button the pointer pulls it 35 % of the way and it springs back with a
 * bounce (useMagnetic; fine pointer, motion allowed).
 */
const page = usePage();
const { t } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});

const magnet = ref(null);
const { style: magnetStyle, isActive: isMagnetic } = useMagnetic(magnet, { radius: 220, strength: 0.35 });
</script>

<template>
    <HomePanel tone="black" id="join" class="gw-final" aria-labelledby="final-title" data-test="final-cta">
        <div class="gw-wrap text-center">
            <h2 id="final-title" v-reveal class="gw-final__title">
                {{ t('home.final.title_lead') }}
                <span class="block text-gw-yellow">{{ t('home.final.title_accent') }}</span>
            </h2>

            <p v-if="isMagnetic" class="gw-final__hint" data-test="section-hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M4 3l7 17 2.5-7.5L21 10z" />
                </svg>
                {{ t('home.final.hint') }}
            </p>

            <div ref="magnet" class="gw-final__magnet" :style="magnetStyle" data-test="magnet">
                <CtaButton variant="primary" surface="dark" size="large" :href="navigation.register" data-test="final-cta-button">{{ t('home.final.cta') }}</CtaButton>
            </div>
        </div>
    </HomePanel>
</template>

<style scoped>
.gw-final {
    overflow: hidden;
    padding-block: 170px 190px;
}

.gw-final__title {
    font-family: var(--gw-font-barlow);
    font-size: clamp(80px, 13vw, 220px);
    font-weight: 800;
    line-height: 0.84;
    text-transform: uppercase;
}

.gw-final__hint {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
    margin-top: 24px;
    color: #a3a3a3;
    font-size: 13px;
}

.gw-final__hint svg {
    width: 15px;
    height: 15px;
}

.gw-final__magnet {
    display: inline-block;
    margin-top: 40px;
    will-change: transform;
}

@media (max-width: 760px) {
    .gw-final {
        padding-block: 120px 140px;
    }
}
</style>
