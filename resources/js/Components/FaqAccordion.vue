<script setup>
import { ref } from 'vue';

/**
 * Neumorphic FAQ accordion on #ECECEC (docs/design/home-interacciones.md, "FAQ";
 * prototype home.html, ".qa"). Each question is a raised card; hovering it (fine
 * pointer) or focusing it with the keyboard fills it with yellow from left to
 * right. Opening it turns the "+" 135° (an ×) and makes it black with yellow.
 * Several can be open at once.
 *
 * Accessible pattern: heading > button with aria-expanded / aria-controls; the
 * answer is a region labelled by its question, inert while closed. With reduced
 * motion nothing slides or turns.
 *
 * @typedef {Object} FaqItem
 * @property {number} id
 * @property {string} question
 * @property {string} answer
 */
defineProps({
    /** @type {import('vue').PropType<FaqItem[]>} */
    items: {
        type: Array,
        required: true,
    },
    /** Heading level of the questions (h3 under a section h2). */
    headingLevel: {
        type: Number,
        default: 3,
    },
});

const openIds = ref(new Set());

/**
 * @param {number} id
 */
function toggle(id) {
    const next = new Set(openIds.value);

    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }

    openIds.value = next;
}
</script>

<template>
    <div class="gw-faq" data-test="faq-list">
        <div v-for="item in items" :key="item.id" class="gw-faq__item" :class="{ 'gw-faq__item--open': openIds.has(item.id) }" data-test="faq-item">
            <component :is="`h${headingLevel}`" class="gw-faq__heading">
                <button
                    :id="`faq-question-${item.id}`"
                    type="button"
                    class="gw-faq__question"
                    :aria-expanded="openIds.has(item.id) ? 'true' : 'false'"
                    :aria-controls="`faq-answer-${item.id}`"
                    @click="toggle(item.id)"
                >
                    <span>{{ item.question }}</span>
                    <span class="gw-faq__plus" aria-hidden="true">+</span>
                </button>
            </component>
            <div
                :id="`faq-answer-${item.id}`"
                class="gw-faq__answer"
                role="region"
                :aria-labelledby="`faq-question-${item.id}`"
                :inert="!openIds.has(item.id)"
            >
                <div>
                    <p>{{ item.answer }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.gw-faq {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.gw-faq__item {
    overflow: hidden;
    border-radius: 22px;
    background: var(--color-gw-neu-light);
    box-shadow:
        8px 8px 18px var(--color-gw-neu-light-shade),
        -8px -8px 18px var(--color-gw-neu-light-highlight);
}

.gw-faq__question {
    position: relative;
    isolation: isolate;
    display: flex;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 22px 24px;
    border: 0;
    background: none;
    color: #111;
    font-size: 17px;
    font-weight: 600;
    text-align: left;
    cursor: pointer;
}

/* Yellow fill from left to right. */
.gw-faq__question::before {
    position: absolute;
    inset: 0;
    z-index: -1;
    background: var(--color-gw-yellow);
    content: '';
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}

.gw-faq__question:focus-visible {
    outline-offset: -3px;
}

.gw-faq__question:focus-visible::before {
    transform: scaleX(1);
}

@media (hover: hover) and (pointer: fine) {
    .gw-faq__question:hover::before {
        transform: scaleX(1);
    }
}

.gw-faq__plus {
    display: grid;
    flex: none;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--color-gw-neu-light);
    box-shadow:
        3px 3px 7px var(--color-gw-neu-light-shade),
        -3px -3px 7px var(--color-gw-neu-light-highlight);
    font-size: 22px;
    font-weight: 500;
    line-height: 1;
    transition:
        transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
        background-color 0.3s,
        color 0.3s;
}

.gw-faq__item--open .gw-faq__plus {
    background: var(--color-gw-black);
    color: var(--color-gw-yellow);
    transform: rotate(135deg);
}

.gw-faq__answer {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}

.gw-faq__item--open .gw-faq__answer {
    grid-template-rows: 1fr;
}

.gw-faq__answer > div {
    overflow: hidden;
}

.gw-faq__answer p {
    padding: 0 24px 22px;
    color: #4d4d4d;
    line-height: 1.6;
}

@media (prefers-reduced-motion: reduce) {
    .gw-faq__question::before,
    .gw-faq__plus,
    .gw-faq__answer {
        transition: none;
    }
}
</style>
