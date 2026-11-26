<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import BrandLogo from '../Components/BrandLogo.vue';
import CtaButton from '../Components/CtaButton.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Thank-you page after a registration (/free-training/thanks, /register/success).
 * The summary (type, training day, players and their categories) only exists
 * right after sending; opened later, a generic message shows. No personal data
 * beyond first names. Not indexed.
 */
const props = defineProps({
    /** @type {import('vue').PropType<{ type: string, trainingDate: string|null, players: { name: string, category: string }[] }|null>} */
    summary: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const { t } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});

const message = computed(() => {
    if (!props.summary) {
        return t('registration.received.generic');
    }

    return props.summary.type === 'free_trial'
        ? t('registration.received.free_trial', { date: props.summary.trainingDate })
        : t('registration.received.paid', { plan: t(`registration.mail.types.${props.summary.type}`) });
});
</script>

<template>
    <Head :title="t('registration.meta_title.received')">
        <meta name="robots" content="noindex" />
    </Head>

    <section class="gw-received" aria-labelledby="received-title" data-site-hero data-test="registration-received">
        <BrandLogo variant="wolf-yellow" class="h-24 sm:h-32" decorative />
        <p class="gw-received__eyebrow">{{ t('registration.received.eyebrow') }}</p>
        <h1 id="received-title" class="gw-received__title">
            {{ t('registration.received.title.lead') }}
            <span class="block text-gw-yellow">{{ t('registration.received.title.accent') }}</span>
        </h1>
        <p class="max-w-prose text-lg text-gw-white/80" data-test="received-message">{{ message }}</p>

        <div v-if="summary?.players?.length" class="gw-received__players">
            <h2 class="text-sm font-semibold tracking-widest text-gw-white/70 uppercase">{{ t('registration.received.players') }}</h2>
            <ul class="flex flex-wrap justify-center gap-3">
                <li v-for="player in summary.players" :key="player.name + player.category" class="gw-received__chip">
                    {{ t('registration.received.category', { name: player.name, category: player.category }) }}
                </li>
            </ul>
        </div>

        <CtaButton variant="secondary" surface="dark" :href="navigation.home">{{ t('registration.received.back_home') }}</CtaButton>
    </section>
</template>

<style scoped>
.gw-received {
    --gw-focus-ring: var(--color-gw-yellow);

    display: flex;
    flex-direction: column;
    gap: 22px;
    align-items: center;
    justify-content: center;
    min-height: 100svh;
    padding: calc(var(--gw-edge) + var(--gw-header-h) + 40px) calc(var(--gw-edge) + var(--gw-gutter)) 140px;
    background: var(--color-gw-black);
    color: var(--color-gw-white);
    text-align: center;
}

.gw-received__eyebrow {
    color: var(--color-gw-yellow);
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.gw-received__title {
    font-family: var(--gw-font-barlow);
    font-size: clamp(56px, 9vw, 130px);
    font-weight: 800;
    line-height: 0.86;
    text-transform: uppercase;
}

.gw-received__players {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}

.gw-received__chip {
    padding: 10px 18px;
    border-radius: 999px;
    background: var(--color-gw-neu-dark);
    box-shadow:
        5px 5px 12px var(--color-gw-neu-dark-shade),
        -3px -3px 9px var(--color-gw-neu-dark-highlight);
    font-weight: 600;
}
</style>
