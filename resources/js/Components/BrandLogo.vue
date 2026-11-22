<script>
/**
 * Inline brand SVG from resources/svg (optimized with SVGO).
 *
 * Each SVG is loaded on demand as its own chunk. The box keeps the SVG's
 * aspect ratio while it loads, so there is no layout shift.
 * Monochrome variants use currentColor; their brand colour comes from the variant.
 */
const svgLoaders = import.meta.glob('../../svg/*.svg', { query: '?raw', import: 'default' });

/**
 * @type {Record<string, { width: number, height: number, colorClass?: string }>}
 */
const variants = {
    'wolf-yellow': { width: 512, height: 512, colorClass: 'text-gw-yellow' },
    'wolf-black': { width: 512, height: 512, colorClass: 'text-gw-black' },
    'wolf-white': { width: 512, height: 512, colorClass: 'text-gw-white' },
    'wolf-yellow-black': { width: 512, height: 512 },
    'wordmark-yellow': { width: 637, height: 100, colorClass: 'text-gw-yellow' },
    'wordmark-black': { width: 667, height: 100, colorClass: 'text-gw-black' },
    'wordmark-white': { width: 671, height: 100, colorClass: 'text-gw-white' },
    crest: { width: 437, height: 512 },
    'liga-interamericana': { width: 489, height: 512 },
    gysl: { width: 561, height: 512 },
};
</script>

<script setup>
import { computed, ref, watch } from 'vue';
import { useTranslations } from '../Composables/useTranslations';

const props = defineProps({
    variant: {
        type: String,
        required: true,
        validator: (value) => Object.hasOwn(variants, value),
    },
    /** Accessible name. Defaults to brand.logos.{variant} in lang/. */
    label: {
        type: String,
        default: null,
    },
    /** Hide from assistive technology when the logo sits next to the same text. */
    decorative: {
        type: Boolean,
        default: false,
    },
});

const { t } = useTranslations();
const markup = ref('');

const definition = computed(() => variants[props.variant]);
const accessibleName = computed(() => props.label ?? t(`brand.logos.${props.variant}`));

watch(
    () => props.variant,
    async (variant) => {
        markup.value = '';
        const svg = await svgLoaders[`../../svg/${variant}.svg`]();

        if (variant === props.variant) {
            markup.value = svg.replace('<svg ', '<svg aria-hidden="true" focusable="false" ');
        }
    },
    { immediate: true },
);
</script>

<template>
    <span
        class="gw-brand-logo"
        :class="definition.colorClass"
        :style="{ aspectRatio: `${definition.width} / ${definition.height}` }"
        :role="decorative ? undefined : 'img'"
        :aria-label="decorative ? undefined : accessibleName"
        :aria-hidden="decorative ? 'true' : undefined"
        v-html="markup"
    />
</template>

<style scoped>
.gw-brand-logo {
    display: inline-block;
    line-height: 0;
}

.gw-brand-logo :deep(svg) {
    display: block;
    width: 100%;
    height: 100%;
}
</style>
