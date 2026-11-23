<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import BrandLogo from '../Components/BrandLogo.vue';
import BareLayout from '../Layouts/BareLayout.vue';
import { useTranslations } from '../Composables/useTranslations';

defineOptions({ layout: BareLayout });

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
    homeUrl: {
        type: String,
        required: true,
    },
});

const { t } = useTranslations();

const knownStatuses = [403, 404, 419, 429, 500, 503];
const messageKey = computed(() => (knownStatuses.includes(props.status) ? props.status : 500));

function reload() {
    window.location.reload();
}
</script>

<template>
    <Head :title="t(`errors.${messageKey}.title`)">
        <meta name="robots" content="noindex" />
    </Head>

    <section
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-gw-black px-4 py-16 text-center text-gw-white [--gw-focus-ring:var(--color-gw-yellow)]"
    >
        <BrandLogo variant="wolf-yellow" class="h-28 sm:h-36" decorative />
        <p class="text-sm font-semibold tracking-wide text-gw-yellow">{{ t('errors.status', { status }) }}</p>
        <h1 class="font-display text-5xl font-extrabold uppercase sm:text-7xl">{{ t(`errors.${messageKey}.title`) }}</h1>
        <p class="max-w-prose text-lg text-gw-white/80">{{ t(`errors.${messageKey}.body`) }}</p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <button
                v-if="status === 419"
                type="button"
                class="rounded-full bg-gw-yellow px-6 py-3 font-semibold text-gw-black"
                @click="reload"
            >
                {{ t('errors.reload') }}
            </button>
            <Link
                :href="homeUrl"
                class="rounded-full px-6 py-3 font-semibold text-gw-white ring-1 ring-inset ring-gw-white"
            >
                {{ t('errors.back_home') }}
            </Link>
        </div>
    </section>
</template>
