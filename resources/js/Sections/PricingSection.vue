<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import HomePanel from '../Components/HomePanel.vue';
import JoinDayCalculator from '../Components/JoinDayCalculator.vue';
import PricingDeck from '../Components/PricingDeck.vue';
import SectionIntro from '../Components/SectionIntro.vue';
import { vReveal } from '../Composables/useReveal';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Pricing (#ECECEC; prototype home.html, "#pricing"): Free trial, Monthly and
 * Annual (featured) in PricingDeck (a row on desktop, the "deck" below 640 px),
 * the uniform note and the "When do you join?" calculator.
 *
 * Prices, the cut-off day and the calendar come from the server (`pricing` prop,
 * config/billing.php); the texts from lang/. Nothing about the rule lives here.
 */
const props = defineProps({
    pricing: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const { t, tList } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});

const plans = computed(() => {
    const prices = props.pricing.prices;
    const replacements = { day: props.pricing.cutoffDay, amount: prices.annual_saving };
    const plan = (id, href) => {
        const key = `home.pricing.plans.${id}`;

        return {
            id,
            name: t(`${key}.name`),
            price: prices[id],
            period: t(`${key}.period`),
            saving: t(`${key}.saving`, replacements),
            features: tList(`${key}.features`),
            note: t(`${key}.note`, replacements),
            ctaLabel: t(`${key}.cta`),
            ctaHref: href,
            badge: id === 'annual' ? t(`${key}.badge`) : null,
        };
    };

    // Each plan opens its registration form; /register?plan=… preselects the plan.
    return [
        plan('free_trial', navigation.value.free_training),
        plan('monthly', `${navigation.value.register}?plan=monthly`),
        plan('annual', `${navigation.value.register}?plan=annual`),
    ];
});
</script>

<template>
    <HomePanel tone="light" id="pricing" class="gw-section-pad" aria-labelledby="pricing-title" data-test="pricing">
        <div class="gw-wrap">
            <SectionIntro
                tone="light"
                :eyebrow="t('home.pricing.eyebrow')"
                :title-lead="t('home.pricing.title_lead')"
                :title-accent="t('home.pricing.title_accent')"
                title-id="pricing-title"
            />

            <div class="mt-14">
                <PricingDeck :plans="plans" featured-id="annual" :mobile-order="['annual', 'monthly', 'free_trial']" />
            </div>

            <p class="mt-8 text-center text-sm text-gw-black/70" data-test="uniform-note">{{ t('home.pricing.uniform_note', { amount: pricing.prices.uniform }) }}</p>

            <div v-reveal class="mt-16 sm:mt-[70px]">
                <JoinDayCalculator :calendar="pricing.calendar" :monthly-price="pricing.prices.monthly" :cutoff-day="pricing.cutoffDay" />
            </div>
        </div>
    </HomePanel>
</template>
