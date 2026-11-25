<script setup>
/**
 * Line icon of a social network (Instagram, Facebook, WhatsApp), drawn with
 * currentColor. Decorative: the link around it carries the accessible name.
 */
defineProps({
    network: {
        type: String,
        required: true,
        validator: (value) => Object.hasOwn(paths, value),
    },
});
</script>

<script>
const paths = {
    instagram: 'M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM17.5 6.5h.01',
    facebook: 'M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z',
    whatsapp: 'M20 12a8 8 0 0 1-11.8 7L4 20l1.1-4A8 8 0 1 1 20 12z',
};
</script>

<template>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path :d="paths[network]" />
    </svg>
</template>
