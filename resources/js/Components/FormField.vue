<script setup>
import { computed } from 'vue';

/**
 * Label, control, hint and error of one form field. The control comes in the
 * default slot, which receives `{ id, describedBy, invalid }` to wire up
 * aria-describedby and aria-invalid. Error text comes from the server.
 */
const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const describedBy = computed(() => [props.hint ? `${props.id}-hint` : null, props.error ? `${props.id}-error` : null].filter(Boolean).join(' ') || undefined);
</script>

<template>
    <div class="gw-field">
        <label :for="id" class="gw-field__label">
            {{ label }}<span v-if="required" class="gw-field__required" aria-hidden="true"> *</span>
        </label>
        <slot :id="id" :describedBy="describedBy" :invalid="error ? 'true' : undefined" />
        <p v-if="hint" :id="`${id}-hint`" class="gw-field__hint">{{ hint }}</p>
        <p v-if="error" :id="`${id}-error`" class="gw-field__error" data-test="field-error">{{ error }}</p>
    </div>
</template>

<style scoped>
.gw-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.gw-field__label {
    font-size: 14px;
    font-weight: 600;
}

.gw-field__required {
    color: #b42318;
}

.gw-field__hint {
    color: #595959;
    font-size: 13px;
}

.gw-field__error {
    color: #b42318;
    font-size: 13px;
    font-weight: 600;
}
</style>
