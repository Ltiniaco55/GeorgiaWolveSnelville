<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTranslations } from '../Composables/useTranslations';
import BrandLogo from './BrandLogo.vue';
import LanguageMenu from './LanguageMenu.vue';
import SocialIcon from './SocialIcon.vue';

/**
 * Site footer (docs/design/sitemap.md, "Footer"; prototype home.html): near black
 * (#050505) with rounded top corners, sliding up 44 px over the page's last
 * section like the home panels.
 *
 * - Club details from config/club.php (shared as `club`): address, email and
 *   phone (only if set), a link to Google Maps and the social profiles.
 * - Link columns of the sitemap: Club, Train, Leagues and Legal.
 * - Bottom line: © legal name, small crest and the language dropdown.
 *
 * Below 1024 px it keeps room at the bottom for the fixed mobile CTA bar.
 */
const page = usePage();
const { t } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});
const club = computed(() => page.props.club ?? {});
const socialLinks = computed(() => page.props.socialLinks ?? []);
const year = new Date().getFullYear();

const columns = [
    { key: 'club', links: ['about', 'teams', 'gallery', 'faq'] },
    { key: 'train', links: ['programs', 'pricing', 'free_training', 'register'] },
    { key: 'leagues', links: ['leagues', 'liga_interamericana', 'gysl'] },
    { key: 'legal', links: ['privacy', 'terms', 'refund_policy', 'liability_waiver', 'media_release', 'accessibility'] },
];
</script>

<template>
    <footer class="gw-footer" data-test="site-footer">
        <div class="gw-wrap">
            <div class="gw-footer__grid">
                <div class="gw-footer__club">
                    <BrandLogo variant="crest" class="h-20" decorative />
                    <address class="gw-footer__address" data-test="footer-address">
                        {{ club.name }}<br />
                        {{ club.address?.venue }}<br />
                        {{ club.address?.street }}, {{ club.address?.locality }}, {{ club.address?.region }} {{ club.address?.postal_code }}
                    </address>
                    <ul class="gw-footer__contact">
                        <li v-if="club.email">
                            <a :href="`mailto:${club.email}`"><span class="sr-only">{{ t('common.footer.email') }}: </span>{{ club.email }}</a>
                        </li>
                        <li v-if="club.phone">
                            <a :href="`tel:${club.phone.replace(/[^+\d]/g, '')}`"><span class="sr-only">{{ t('common.footer.phone') }}: </span>{{ club.phone }}</a>
                        </li>
                        <li>
                            <a :href="club.mapsUrl" target="_blank" rel="noopener" data-test="footer-maps">{{ t('common.footer.maps') }} ↗</a>
                        </li>
                    </ul>
                    <ul v-if="socialLinks.length" class="gw-footer__social" :aria-label="t('common.social.label')">
                        <li v-for="link in socialLinks" :key="link.network">
                            <a :href="link.url" target="_blank" rel="noopener" :aria-label="t(`common.social.${link.network}`)">
                                <SocialIcon :network="link.network" />
                            </a>
                        </li>
                    </ul>
                </div>

                <nav class="gw-footer__columns" :aria-label="t('common.footer.label')">
                    <div v-for="column in columns" :key="column.key">
                        <h2 class="gw-footer__heading">{{ t(`common.footer.columns.${column.key}`) }}</h2>
                        <ul class="gw-footer__links">
                            <li v-for="name in column.links" :key="name">
                                <Link :href="navigation[name]" :data-test="`footer-${name}`">{{ t(`common.footer.links.${name}`) }}</Link>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <div class="gw-footer__bottom">
                <p class="flex items-center gap-3">
                    <BrandLogo variant="crest" class="h-8" decorative />
                    <span data-test="footer-copyright">{{ t('common.footer.copyright', { year, name: club.legalName }) }}</span>
                </p>
                <LanguageMenu placement="top" />
            </div>
        </div>
    </footer>
</template>

<style scoped>
.gw-footer {
    --gw-focus-ring: var(--color-gw-yellow);

    position: relative;
    z-index: 2;
    margin-top: -44px;
    padding: 80px 0 40px;
    border-radius: 44px 44px 0 0;
    background: #050505;
    color: #b3b3b3;
    font-size: 14px;
}

/* Room for the fixed mobile CTA bar (48 px button + 2 × 12 px padding + safe area). */
@media (max-width: 1023px) {
    .gw-footer {
        padding-bottom: calc(40px + 72px + env(safe-area-inset-bottom));
    }
}

.gw-footer__grid {
    display: grid;
    grid-template-columns: 1.4fr 4fr;
    gap: 40px;
}

.gw-footer__address {
    margin-top: 16px;
    font-style: normal;
    line-height: 1.6;
}

.gw-footer__contact {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 14px;
}

.gw-footer a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}

@media (hover: hover) and (pointer: fine) {
    .gw-footer a:hover {
        color: var(--color-gw-yellow);
    }
}

.gw-footer__social {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.gw-footer__social a {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: var(--color-gw-neu-dark);
    box-shadow:
        4px 4px 10px #000,
        -3px -3px 8px #1c1c1c;
    color: #ddd;
}

.gw-footer__social svg {
    width: 18px;
    height: 18px;
}

.gw-footer__columns {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
}

.gw-footer__heading {
    margin-bottom: 14px;
    color: var(--color-gw-white);
    font-family: var(--gw-font-barlow);
    font-size: 18px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.gw-footer__links {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.gw-footer__bottom {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    margin-top: 60px;
    padding-top: 24px;
    border-top: 1px solid #1a1a1a;
    color: #8c8c8c;
    font-size: 12.5px;
}

@media (max-width: 1023px) {
    .gw-footer__grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .gw-footer__columns {
        grid-template-columns: 1fr 1fr;
        gap: 32px 24px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-footer a {
        transition: none;
    }
}
</style>
