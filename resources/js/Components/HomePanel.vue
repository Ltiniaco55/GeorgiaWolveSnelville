<script setup>
/**
 * A home section that slides up over the previous one (prototype home.html,
 * ".panel"): top corners rounded 44 px, pulled up 44 px and lifted with a soft
 * shadow, above the sticky hero (z-index 2). The home alternates the tones:
 * "dark" #161616 and "black" #0A0A0A with white text, "light" #ECECEC with black
 * text (the neumorphic surfaces of the design system).
 */
defineProps({
    tone: {
        type: String,
        default: 'dark',
        validator: (value) => ['dark', 'black', 'light'].includes(value),
    },
    as: {
        type: String,
        default: 'section',
    },
});
</script>

<template>
    <component :is="as" class="gw-panel" :class="`gw-panel--${tone}`" :data-tone="tone">
        <slot />
    </component>
</template>

<style scoped>
.gw-panel {
    position: relative;
    z-index: 2;
    margin-top: -44px;
    border-radius: 44px 44px 0 0;
    box-shadow: 0 -30px 60px rgb(0 0 0 / 0.45);
}

.gw-panel--dark {
    --gw-focus-ring: var(--color-gw-yellow);

    background: var(--color-gw-neu-dark);
    color: var(--color-gw-white);
}

.gw-panel--black {
    --gw-focus-ring: var(--color-gw-yellow);

    background: var(--color-gw-black);
    color: var(--color-gw-white);
}

.gw-panel--light {
    background: var(--color-gw-neu-light);
    color: var(--color-gw-black);
}
</style>
