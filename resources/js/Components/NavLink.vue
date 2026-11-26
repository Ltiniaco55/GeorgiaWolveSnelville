<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { isSamePageAnchor } from '../Composables/useSamePageAnchor';

/**
 * Internal link: an Inertia <Link> to another page, or a plain <a> when it points
 * to an anchor of the current page (menu entries such as Pricing or FAQ are
 * sections of the home), so the browser jumps there without a new visit.
 * Attributes and listeners go to the rendered element.
 */
const props = defineProps({
    href: {
        type: String,
        required: true,
    },
});

const page = usePage();

// Recomputed when the page changes (the same link can be local on one page and not on another).
const isAnchor = computed(() => (page.url, isSamePageAnchor(props.href)));
</script>

<template>
    <a v-if="isAnchor" :href="href"><slot /></a>
    <Link v-else :href="href"><slot /></Link>
</template>
