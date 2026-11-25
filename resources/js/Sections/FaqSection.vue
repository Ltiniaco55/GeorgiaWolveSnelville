<script setup>
import FaqAccordion from '../Components/FaqAccordion.vue';
import HomePanel from '../Components/HomePanel.vue';
import SectionIntro from '../Components/SectionIntro.vue';
import { vReveal } from '../Composables/useReveal';
import { useTranslations } from '../Composables/useTranslations';

/**
 * FAQ (#ECECEC; prototype home.html, "#faq"): the neumorphic accordion next to a
 * photo that stays in place while the list scrolls (desktop). The questions come
 * from the database (Faq model, the same rows the WhatsApp bot will use); without
 * questions the section is not shown.
 */
defineProps({
    /** @type {import('vue').PropType<import('../Components/FaqAccordion.vue').FaqItem[]>} */
    faqs: {
        type: Array,
        default: () => [],
    },
});

const { t } = useTranslations();
</script>

<template>
    <HomePanel v-if="faqs.length" tone="light" id="faq" class="gw-section-pad" aria-labelledby="faq-title" data-test="faq">
        <div class="gw-wrap">
            <SectionIntro
                tone="light"
                :eyebrow="t('home.faq.eyebrow')"
                :title-lead="t('home.faq.title_lead')"
                :title-accent="t('home.faq.title_accent')"
                :hint="t('home.faq.hint')"
                title-id="faq-title"
            />

            <div class="gw-faq-grid">
                <div v-reveal class="gw-faq-grid__photo">
                    <picture>
                        <source type="image/avif" srcset="/images/gallery/gysl-champions.avif" />
                        <img src="/images/gallery/gysl-champions.webp" :alt="t('home.faq.photo_alt')" width="420" height="385" loading="lazy" decoding="async" />
                    </picture>
                </div>

                <FaqAccordion :items="faqs" />
            </div>
        </div>
    </HomePanel>
</template>

<style scoped>
.gw-faq-grid {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: 56px;
    align-items: start;
    margin-top: 56px;
}

.gw-faq-grid__photo {
    position: sticky;
    top: 120px;
    aspect-ratio: 4 / 5;
    overflow: hidden;
    border-radius: 28px;
    box-shadow: var(--shadow-gw-raised-light);
}

.gw-faq-grid__photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 900px) {
    .gw-faq-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }

    .gw-faq-grid__photo {
        position: relative;
        top: 0;
        aspect-ratio: 16 / 10;
    }
}
</style>
