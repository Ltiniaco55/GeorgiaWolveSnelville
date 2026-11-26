<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import HomePanel from '../Components/HomePanel.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Shared template of the legal pages (docs/design/sitemap.md, "Legales"): title,
 * last update, the "DRAFT – pending legal review" notice, contents with anchors,
 * the sections and the club's contact details. Texts: lang/{locale}/legal.php.
 * No CTA, only the contact.
 */
const props = defineProps({
    /** privacy | terms | refund_policy | liability_waiver | media_release | accessibility */
    document: {
        type: String,
        required: true,
    },
});

const page = usePage();
const { t, tList } = useTranslations();
const club = computed(() => page.props.club ?? {});
const key = computed(() => `legal.documents.${props.document}`);
const sections = computed(() => tList(`${key.value}.sections`));
</script>

<template>
    <Head :title="t('legal.meta_title', { title: t(`${key}.title`) })" />

    <section class="gw-legal-head" aria-labelledby="legal-title" data-site-hero>
        <div class="gw-wrap">
            <h1 id="legal-title" class="gw-legal-head__title">{{ t(`${key}.title`) }}</h1>
            <p class="gw-legal-head__intro">{{ t(`${key}.intro`) }}</p>
            <p class="gw-legal-head__updated">{{ t('legal.updated', { date: t('legal.updated_date') }) }}</p>
        </div>
    </section>

    <HomePanel tone="light" class="gw-legal-body" aria-labelledby="legal-title" data-test="legal-page">
        <div class="gw-legal">
            <p class="gw-legal__draft" role="note" data-test="legal-draft">{{ t('legal.draft') }}</p>

            <nav class="gw-legal__toc" :aria-label="t('legal.contents')">
                <h2 class="gw-legal__toc-title">{{ t('legal.contents') }}</h2>
                <ol>
                    <li v-for="section in sections" :key="section.id">
                        <a :href="`#${section.id}`">{{ section.title }}</a>
                    </li>
                </ol>
            </nav>

            <article class="gw-legal__article">
                <section v-for="section in sections" :id="section.id" :key="section.id" class="gw-legal__section" :aria-labelledby="`${section.id}-title`">
                    <h2 :id="`${section.id}-title`">{{ section.title }}</h2>
                    <p v-for="(paragraph, index) in section.body" :key="index">{{ paragraph }}</p>
                </section>

                <section class="gw-legal__section gw-legal__contact" aria-labelledby="legal-contact-title">
                    <h2 id="legal-contact-title">{{ t('legal.contact_title') }}</h2>
                    <p>{{ t('legal.contact_body') }}</p>
                    <address>
                        {{ club.name }}<br />
                        {{ club.address?.venue }}, {{ club.address?.street }}, {{ club.address?.locality }}, {{ club.address?.region }} {{ club.address?.postal_code }}
                        <template v-if="club.email"><br /><a :href="`mailto:${club.email}`">{{ club.email }}</a></template>
                        <template v-if="club.phone"><br /><a :href="`tel:${club.phone.replace(/[^+\d]/g, '')}`">{{ club.phone }}</a></template>
                    </address>
                </section>
            </article>
        </div>
    </HomePanel>
</template>

<style scoped>
.gw-legal-head {
    padding: calc(var(--gw-edge) + var(--gw-header-h) + 56px) 0 110px;
    background: var(--color-gw-black);
    color: var(--color-gw-white);
}

.gw-legal-head__title {
    font-family: var(--gw-font-barlow);
    font-size: clamp(48px, min(8vw, 13vh), 120px);
    font-weight: 800;
    line-height: 0.9;
    text-transform: uppercase;
}

.gw-legal-head__intro {
    max-width: 680px;
    margin-top: 22px;
    color: #d4d4d4;
    font-size: clamp(16px, 1.3vw, 19px);
    line-height: 1.6;
}

.gw-legal-head__updated {
    margin-top: 16px;
    color: var(--color-gw-yellow);
    font-size: 14px;
    font-weight: 600;
}

.gw-legal-body {
    padding: 64px calc(var(--gw-edge) + var(--gw-gutter)) 160px;
}

.gw-legal {
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr);
    gap: 24px 56px;
    max-width: 1120px;
    margin-inline: auto;
}

.gw-legal__draft {
    grid-column: 1 / -1;
    padding: 16px 20px;
    border-radius: 16px;
    background: var(--color-gw-yellow);
    color: var(--color-gw-black);
    font-weight: 600;
}

.gw-legal__toc {
    position: sticky;
    top: calc(var(--gw-edge) + var(--gw-header-solid-h) + 24px);
    align-self: start;
    padding: 22px;
    border-radius: 22px;
    background: var(--color-gw-neu-light);
    box-shadow: var(--shadow-gw-raised-light-sm);
}

.gw-legal__toc-title {
    margin-bottom: 10px;
    font-family: var(--gw-font-barlow);
    font-size: 20px;
    font-weight: 800;
    text-transform: uppercase;
}

.gw-legal__toc ol {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-left: 20px;
    list-style: decimal;
    font-size: 14px;
}

.gw-legal__toc a,
.gw-legal__contact a {
    color: var(--color-gw-black);
    text-decoration: underline;
    text-underline-offset: 3px;
}

.gw-legal__article {
    display: flex;
    flex-direction: column;
    gap: 36px;
    max-width: 720px;
}

.gw-legal__section h2 {
    margin-bottom: 12px;
    font-family: var(--gw-font-barlow);
    font-size: 30px;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
}

.gw-legal__section p,
.gw-legal__section address {
    color: #333;
    font-style: normal;
    line-height: 1.7;
}

.gw-legal__section p + p {
    margin-top: 12px;
}

@media (max-width: 900px) {
    .gw-legal {
        grid-template-columns: 1fr;
    }

    .gw-legal__toc {
        position: static;
    }
}

@media (max-width: 760px) {
    .gw-legal-body {
        padding-block: 40px 120px;
    }
}
</style>
