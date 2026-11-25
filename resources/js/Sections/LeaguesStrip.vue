<script setup>
import { Link } from '@inertiajs/vue3';
import BrandLogo from '../Components/BrandLogo.vue';
import HomePanel from '../Components/HomePanel.vue';
import { vReveal } from '../Composables/useReveal';
import { useTranslations } from '../Composables/useTranslations';

/**
 * "Associated leagues and partners" (docs/design/home-interacciones.md; prototype
 * home.html, "#leagues"): the leagues have no section of their own in the home,
 * only their crests in raised neumorphic pills, grey and faded. On hover or focus
 * a pill gets its colour back, grows a little and sinks (inset shadow). Each crest
 * links to its league page (/leagues/{slug}).
 *
 * Partners (config/club.php, "partners"): Joma and CER, ready for their logos.
 * Without a logo the pill shows the name in a pressed-in circle; without a URL it
 * is not a link (and has no hover).
 */
defineProps({
    /** @type {import('vue').PropType<{ slug: string, url: string }[]>} */
    leagues: {
        type: Array,
        required: true,
    },
    /** @type {import('vue').PropType<{ name: string, initials: string, role: string, logo: string|null, url: string|null }[]>} */
    partners: {
        type: Array,
        default: () => [],
    },
});

const { t } = useTranslations();
</script>

<template>
    <HomePanel tone="light" id="leagues" class="gw-leagues" aria-labelledby="leagues-title" data-test="leagues">
        <div class="gw-wrap">
            <h2 id="leagues-title" class="gw-leagues__eyebrow"><span class="gw-leagues__dot" aria-hidden="true" />{{ t('home.leagues.eyebrow') }}</h2>
            <p class="gw-leagues__hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M4 3l7 17 2.5-7.5L21 10z" />
                </svg>
                {{ t('home.leagues.hint') }}
            </p>

            <ul v-reveal class="gw-leagues__row">
                <li v-for="league in leagues" :key="league.slug">
                    <Link :href="league.url" class="gw-pill gw-pill--link" :data-test="`league-${league.slug}`">
                        <BrandLogo :variant="league.slug" class="gw-pill__logo" decorative />
                        <span class="gw-pill__name">{{ t(`home.leagues.names.${league.slug}`) }}</span>
                    </Link>
                </li>

                <li v-for="partner in partners" :key="partner.name">
                    <component
                        :is="partner.url ? 'a' : 'div'"
                        :href="partner.url ?? undefined"
                        :target="partner.url ? '_blank' : undefined"
                        :rel="partner.url ? 'noopener' : undefined"
                        class="gw-pill"
                        :class="partner.url ? 'gw-pill--link' : 'gw-pill--placeholder'"
                        :data-test="`partner-${partner.initials.toLowerCase()}`"
                    >
                        <img v-if="partner.logo" :src="partner.logo" :alt="partner.name" class="gw-pill__logo" />
                        <span v-else class="gw-pill__initials" role="img" :aria-label="t('home.leagues.logo_soon', { name: partner.name })">
                            <span aria-hidden="true">{{ partner.initials }}</span>
                        </span>
                        <span class="gw-pill__name">{{ t(`home.leagues.roles.${partner.role}`) }}</span>
                    </component>
                </li>
            </ul>
        </div>
    </HomePanel>
</template>

<style scoped>
.gw-leagues {
    padding-block: 90px 130px;
}

.gw-leagues__eyebrow {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.gw-leagues__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color-gw-yellow);
    box-shadow: 0 0 0 1px var(--color-gw-black);
}

.gw-leagues__hint {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 14px;
    color: #595959;
    font-size: 13px;
}

.gw-leagues__hint svg {
    width: 15px;
    height: 15px;
}

.gw-leagues__row {
    display: flex;
    flex-wrap: wrap;
    gap: 28px;
    align-items: center;
    margin-top: 40px;
}

.gw-pill {
    display: flex;
    gap: 16px;
    align-items: center;
    padding: 14px 26px 14px 14px;
    border-radius: 999px;
    background: var(--color-gw-neu-light);
    box-shadow:
        8px 8px 18px var(--color-gw-neu-light-shade),
        -8px -8px 18px var(--color-gw-neu-light-highlight);
    color: inherit;
    text-decoration: none;
    transition:
        transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.35s;
}

.gw-pill__logo {
    width: 64px;
    height: 64px;
    object-fit: contain;
    filter: grayscale(1);
    opacity: 0.45;
    transition:
        filter 0.35s,
        opacity 0.35s;
}

.gw-pill__name {
    max-width: 16ch;
    color: #6b6b6b;
    font-family: var(--gw-font-barlow);
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
    transition: color 0.35s;
}

/* Hover (fine pointer) or keyboard focus: colour back, grows and sinks. */
.gw-pill--link:focus-visible {
    box-shadow: var(--shadow-gw-inset-light);
    transform: scale(1.05);
}

.gw-pill--link:focus-visible .gw-pill__logo {
    filter: none;
    opacity: 1;
}

.gw-pill--link:focus-visible .gw-pill__name {
    color: var(--color-gw-black);
}

@media (hover: hover) and (pointer: fine) {
    .gw-pill--link:hover {
        box-shadow: var(--shadow-gw-inset-light);
        transform: scale(1.05);
    }

    .gw-pill--link:hover .gw-pill__logo {
        filter: none;
        opacity: 1;
    }

    .gw-pill--link:hover .gw-pill__name {
        color: var(--color-gw-black);
    }
}

/* Partner without its logo yet: only the placeholder circle is faded (text keeps 3:1). */
.gw-pill--placeholder .gw-pill__initials {
    opacity: 0.6;
}

.gw-pill__initials {
    display: grid;
    flex: none;
    place-items: center;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    box-shadow: var(--shadow-gw-inset-light-sm);
    color: #6b6b6b;
    font-family: var(--gw-font-barlow);
    font-size: 18px;
    font-weight: 800;
}

@media (max-width: 760px) {
    .gw-leagues__row {
        gap: 18px;
    }

    .gw-pill {
        padding: 10px 20px 10px 10px;
    }

    .gw-pill__logo,
    .gw-pill__initials {
        width: 52px;
        height: 52px;
    }

    .gw-pill__name {
        font-size: 20px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-pill,
    .gw-pill__logo,
    .gw-pill__name {
        transition: none;
    }

    .gw-pill--link:focus-visible,
    .gw-pill--link:hover {
        transform: none;
    }
}
</style>
