<script setup>
import { computed, nextTick, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import CtaButton from '../Components/CtaButton.vue';
import FormField from '../Components/FormField.vue';
import HomePanel from '../Components/HomePanel.vue';
import NavLink from '../Components/NavLink.vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * Registration templates (docs/design/sitemap.md): free training (/free-training)
 * and the monthly or annual plan (/register?plan=…). One form, in sections:
 * plan or training day, players (up to 4), parent or guardian (+ optional second
 * one), emergency contact, the photos-and-videos choice (media release, an
 * explicit yes or no) and the agreements with a typed signature.
 *
 * Validation happens on the server (StoreRegistrationRequest); its errors show
 * under each field and in a summary at the top that gets the focus. Prices, the
 * day-15 figures, the training days and the accepted birth years come from the
 * server. `website` is a hidden honeypot.
 */
const props = defineProps({
    /** free_trial | monthly | annual */
    type: {
        type: String,
        required: true,
    },
    birthYears: {
        type: Object,
        required: true,
    },
    relationships: {
        type: Array,
        required: true,
    },
    /** Free training: [{ value, label }]. */
    trainingDates: {
        type: Array,
        default: () => [],
    },
    /** Paid plans: formatted prices (HomePricing). */
    prices: {
        type: Object,
        default: () => ({}),
    },
    cutoffDay: {
        type: String,
        default: '',
    },
    /** Paid plans: what joining today costs (monthly). */
    joiningToday: {
        type: Object,
        default: null,
    },
    shirtSizes: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t, tList } = useTranslations();
const navigation = computed(() => page.props.navigation ?? {});
const isFreeTrial = props.type === 'free_trial';
const kind = isFreeTrial ? 'free_trial' : 'paid';

const newPlayer = () => ({ first_name: '', last_name: '', date_of_birth: '', gender: '', medical_notes: '', needs_uniform: true, shirt_size: '' });

const form = useForm({
    plan: isFreeTrial ? undefined : props.type,
    preferred_training_date: isFreeTrial ? (props.trainingDates[0]?.value ?? '') : undefined,
    players: [newPlayer()],
    guardian: { first_name: '', last_name: '', relationship: '', email: '', phone: '' },
    address: { street: '', city: 'Snellville', state: 'GA', zip: '' },
    second_guardian: { name: '', email: '', phone: '' },
    emergency: { name: '', relationship: '', phone: '' },
    media_release: '',
    accepts_liability_waiver: false,
    accepts_terms: false,
    accepts_refund_policy: false,
    signature_name: '',
    marketing_opt_in: false,
    website: '',
});

const summary = ref(null);
const fieldId = (key) => `f-${key.replace(/\./g, '-')}`;
// In the order the fields appear on the page.
const errorList = computed(() => {
    const position = (key) => {
        const element = document.getElementById(fieldId(key));

        return element ? [...document.querySelectorAll('[data-test="registration-form"] [id]')].indexOf(element) : Number.MAX_SAFE_INTEGER;
    };

    return Object.entries(form.errors).sort(([a], [b]) => position(a) - position(b));
});

/**
 * @param {string} key
 */
function error(key) {
    return form.errors[key] ?? '';
}

function addPlayer() {
    form.players.push(newPlayer());
    nextTick(() => document.getElementById(fieldId(`players.${form.players.length - 1}.first_name`))?.focus());
}

/**
 * @param {number} index
 */
function removePlayer(index) {
    form.players.splice(index, 1);
}

/**
 * Splits a translation around its ":link" placeholder.
 *
 * @param {string} key
 * @returns {[string, string]}
 */
function aroundLink(key) {
    const [before, after = ''] = t(key, { link: '\u0000' }).split('\u0000');

    return [before, after];
}

function submit() {
    form.transform((data) => Object.fromEntries(Object.entries(data).filter(([, value]) => value !== undefined))).post(
        isFreeTrial ? navigation.value.free_training : navigation.value.register,
        {
            // Stay put only when there are errors (the summary gets the focus); the
            // thank-you page opens at the top.
            preserveScroll: (response) => Object.keys(response.props.errors ?? {}).length > 0,
            onError: () => nextTick(() => summary.value?.focus()),
        },
    );
}

const agreements = computed(() => [
    { key: 'accepts_liability_waiver', text: 'registration.agreements.liability_waiver', link: navigation.value.liability_waiver, label: t('registration.fields.liability_waiver') },
    { key: 'accepts_terms', text: 'registration.agreements.terms', link: navigation.value.terms, label: t('registration.fields.terms') },
    ...(isFreeTrial ? [] : [{ key: 'accepts_refund_policy', text: 'registration.agreements.refund_policy', link: navigation.value.refund_policy, label: t('registration.fields.refund_policy') }]),
]);
</script>

<template>
    <Head :title="t(`registration.meta_title.${kind}`)" />

    <section class="gw-reg-head" aria-labelledby="registration-title" data-site-hero>
        <div class="gw-wrap">
            <NavLink :href="navigation.pricing" class="gw-reg-head__back"><span aria-hidden="true">←</span> {{ t('registration.back_pricing') }}</NavLink>
            <p class="gw-reg-head__eyebrow">{{ t(`registration.eyebrow.${kind}`) }}</p>
            <h1 id="registration-title" class="gw-reg-head__title">
                {{ t(`registration.title.${kind}.lead`) }}
                <span class="text-gw-yellow">{{ t(`registration.title.${kind}.accent`) }}</span>
            </h1>
            <p class="gw-reg-head__intro">{{ t(`registration.intro.${kind}`) }}</p>
        </div>
    </section>

    <HomePanel tone="light" class="gw-reg-body" aria-labelledby="registration-title">
        <form class="gw-reg" novalidate data-test="registration-form" @submit.prevent="submit">
            <p class="text-sm text-gw-black/70">{{ t('registration.required_note') }}</p>

            <div v-if="errorList.length" ref="summary" class="gw-reg__summary" tabindex="-1" role="alert" data-test="error-summary">
                <p class="font-semibold">{{ t('registration.errors_title') }}</p>
                <ul>
                    <li v-for="[key, message] in errorList" :key="key">
                        <a :href="`#${fieldId(key)}`">{{ message }}</a>
                    </li>
                </ul>
            </div>

            <!-- Honeypot: hidden from people and assistive technology. -->
            <div class="gw-reg__trap" aria-hidden="true">
                <label for="f-website">Website</label>
                <input id="f-website" v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off" />
            </div>

            <!-- Plan (paid) -->
            <fieldset v-if="!isFreeTrial" class="gw-reg__card" data-test="plan-step">
                <legend class="gw-reg__legend">{{ t('registration.steps.plan') }}</legend>
                <div class="gw-reg__grid gw-reg__grid--2" role="radiogroup" :aria-label="t('registration.plan.label')">
                    <label v-for="plan in ['monthly', 'annual']" :key="plan" class="gw-choice" :data-test="`plan-${plan}`">
                        <input :id="plan === 'monthly' ? fieldId('plan') : undefined" v-model="form.plan" type="radio" name="plan" :value="plan" />
                        <span>
                            <span class="block font-display text-2xl font-extrabold uppercase">{{ t(`registration.plan.${plan}`) }}</span>
                            <span class="font-display text-3xl font-extrabold">{{ prices[plan] }}</span>
                            <span class="text-sm text-gw-black/70">{{ t(`registration.plan.${plan === 'monthly' ? 'per_month' : 'per_year'}`) }}</span>
                        </span>
                    </label>
                </div>
                <p class="gw-reg__note" data-test="plan-note">
                    {{
                        form.plan === 'monthly'
                            ? t('registration.plan.monthly_today', { amount: joiningToday?.dueToday, date: joiningToday?.firstCharge })
                            : t('registration.plan.annual_today', { day: cutoffDay })
                    }}
                </p>
                <p class="gw-reg__note">{{ t('registration.plan.uniform', { amount: prices.uniform }) }}</p>
            </fieldset>

            <!-- Training day (free trial) -->
            <fieldset v-else class="gw-reg__card" data-test="date-step">
                <legend class="gw-reg__legend">{{ t('registration.steps.date') }}</legend>
                <p class="gw-reg__note">{{ t('registration.date.hint', { time: t('registration.date.time') }) }}</p>
                <div class="gw-reg__grid gw-reg__grid--3" role="radiogroup" :aria-label="t('registration.date.label')">
                    <label v-for="(date, index) in trainingDates" :key="date.value" class="gw-choice">
                        <input
                            :id="index === 0 ? fieldId('preferred_training_date') : undefined"
                            v-model="form.preferred_training_date"
                            type="radio"
                            name="preferred_training_date"
                            :value="date.value"
                        />
                        <span class="font-semibold">{{ date.label }}</span>
                    </label>
                </div>
                <p v-if="error('preferred_training_date')" class="gw-reg__error">{{ error('preferred_training_date') }}</p>
            </fieldset>

            <!-- Players -->
            <fieldset class="gw-reg__card" data-test="players-step">
                <legend class="gw-reg__legend">{{ t('registration.steps.players') }}</legend>

                <div v-for="(player, index) in form.players" :key="index" class="gw-reg__player" data-test="player">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="font-display text-xl font-extrabold uppercase">{{ t('registration.players.player', { number: index + 1 }) }}</h3>
                        <button v-if="form.players.length > 1" type="button" class="gw-reg__link" @click="removePlayer(index)">
                            {{ t('registration.players.remove', { number: index + 1 }) }}
                        </button>
                    </div>

                    <div class="gw-reg__grid gw-reg__grid--2">
                        <FormField v-slot="field" :id="fieldId(`players.${index}.first_name`)" :label="t('registration.fields.first_name')" :error="error(`players.${index}.first_name`)" required>
                            <input :id="field.id" v-model="player.first_name" class="gw-input" type="text" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                        </FormField>
                        <FormField v-slot="field" :id="fieldId(`players.${index}.last_name`)" :label="t('registration.fields.last_name')" :error="error(`players.${index}.last_name`)" required>
                            <input :id="field.id" v-model="player.last_name" class="gw-input" type="text" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                        </FormField>
                        <FormField
                            v-slot="field"
                            :id="fieldId(`players.${index}.date_of_birth`)"
                            :label="t('registration.fields.date_of_birth')"
                            :hint="t('registration.players.birth_hint', birthYears)"
                            :error="error(`players.${index}.date_of_birth`)"
                            required
                        >
                            <input
                                :id="field.id"
                                v-model="player.date_of_birth"
                                class="gw-input"
                                type="date"
                                :min="`${birthYears.from}-01-01`"
                                :max="`${birthYears.to}-12-31`"
                                :aria-describedby="field.describedBy"
                                :aria-invalid="field.invalid"
                                required
                            />
                        </FormField>
                        <FormField v-slot="field" :id="fieldId(`players.${index}.gender`)" :label="t('registration.fields.gender')" :error="error(`players.${index}.gender`)">
                            <select :id="field.id" v-model="player.gender" class="gw-input" :aria-describedby="field.describedBy" :aria-invalid="field.invalid">
                                <option v-for="(label, value) in tList('registration.players.gender_options')" :key="value" :value="value">{{ label }}</option>
                            </select>
                        </FormField>
                    </div>

                    <div v-if="!isFreeTrial" class="gw-reg__grid gw-reg__grid--2">
                        <label class="gw-choice self-end">
                            <input v-model="player.needs_uniform" type="checkbox" />
                            <span class="font-semibold">{{ t('registration.players.needs_uniform') }}</span>
                        </label>
                        <FormField
                            v-if="player.needs_uniform"
                            v-slot="field"
                            :id="fieldId(`players.${index}.shirt_size`)"
                            :label="t('registration.fields.shirt_size')"
                            :error="error(`players.${index}.shirt_size`)"
                            required
                        >
                            <select :id="field.id" v-model="player.shirt_size" class="gw-input" :aria-describedby="field.describedBy" :aria-invalid="field.invalid">
                                <option value="" disabled>—</option>
                                <option v-for="size in shirtSizes" :key="size" :value="size">{{ size }}</option>
                            </select>
                        </FormField>
                    </div>

                    <FormField
                        v-slot="field"
                        :id="fieldId(`players.${index}.medical_notes`)"
                        :label="t('registration.fields.medical_notes')"
                        :hint="t('registration.players.medical_hint')"
                        :error="error(`players.${index}.medical_notes`)"
                    >
                        <textarea :id="field.id" v-model="player.medical_notes" class="gw-input" maxlength="1000" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" />
                    </FormField>
                </div>

                <p v-if="error('players')" :id="fieldId('players')" class="gw-reg__error">{{ error('players') }}</p>
                <button v-if="form.players.length < 4" type="button" class="gw-reg__add" data-test="add-player" @click="addPlayer">+ {{ t('registration.players.add') }}</button>
            </fieldset>

            <!-- Parent or guardian -->
            <fieldset class="gw-reg__card" data-test="guardian-step">
                <legend class="gw-reg__legend">{{ t('registration.steps.guardian') }}</legend>
                <div class="gw-reg__grid gw-reg__grid--2">
                    <FormField v-slot="field" :id="fieldId('guardian.first_name')" :label="t('registration.fields.first_name')" :error="error('guardian.first_name')" required>
                        <input :id="field.id" v-model="form.guardian.first_name" class="gw-input" type="text" autocomplete="given-name" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('guardian.last_name')" :label="t('registration.fields.last_name')" :error="error('guardian.last_name')" required>
                        <input :id="field.id" v-model="form.guardian.last_name" class="gw-input" type="text" autocomplete="family-name" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('guardian.relationship')" :label="t('registration.fields.relationship')" :error="error('guardian.relationship')" required>
                        <select :id="field.id" v-model="form.guardian.relationship" class="gw-input" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required>
                            <option value="" disabled>—</option>
                            <option v-for="relationship in relationships" :key="relationship" :value="relationship">{{ t(`registration.guardian.relationship_options.${relationship}`) }}</option>
                        </select>
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('guardian.email')" :label="t('registration.fields.email')" :error="error('guardian.email')" required>
                        <input :id="field.id" v-model="form.guardian.email" class="gw-input" type="email" autocomplete="email" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('guardian.phone')" :label="t('registration.fields.phone')" :error="error('guardian.phone')" required>
                        <input :id="field.id" v-model="form.guardian.phone" class="gw-input" type="tel" autocomplete="tel" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('address.street')" :label="t('registration.fields.street')" :error="error('address.street')" required>
                        <input :id="field.id" v-model="form.address.street" class="gw-input" type="text" autocomplete="street-address" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('address.city')" :label="t('registration.fields.city')" :error="error('address.city')" required>
                        <input :id="field.id" v-model="form.address.city" class="gw-input" type="text" autocomplete="address-level2" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <div class="grid grid-cols-2 gap-4">
                        <FormField v-slot="field" :id="fieldId('address.state')" :label="t('registration.fields.state')" :error="error('address.state')" required>
                            <input :id="field.id" v-model="form.address.state" class="gw-input uppercase" type="text" maxlength="2" autocomplete="address-level1" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                        </FormField>
                        <FormField v-slot="field" :id="fieldId('address.zip')" :label="t('registration.fields.zip')" :error="error('address.zip')" required>
                            <input :id="field.id" v-model="form.address.zip" class="gw-input" type="text" inputmode="numeric" maxlength="10" autocomplete="postal-code" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                        </FormField>
                    </div>
                </div>

                <h3 class="gw-reg__subtitle">{{ t('registration.guardian.second') }}</h3>
                <div class="gw-reg__grid gw-reg__grid--3">
                    <FormField v-slot="field" :id="fieldId('second_guardian.name')" :label="t('registration.fields.full_name')" :error="error('second_guardian.name')">
                        <input :id="field.id" v-model="form.second_guardian.name" class="gw-input" type="text" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('second_guardian.email')" :label="t('registration.fields.email')" :error="error('second_guardian.email')">
                        <input :id="field.id" v-model="form.second_guardian.email" class="gw-input" type="email" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('second_guardian.phone')" :label="t('registration.fields.phone')" :error="error('second_guardian.phone')">
                        <input :id="field.id" v-model="form.second_guardian.phone" class="gw-input" type="tel" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" />
                    </FormField>
                </div>
            </fieldset>

            <!-- Emergency contact -->
            <fieldset class="gw-reg__card" data-test="emergency-step">
                <legend class="gw-reg__legend">{{ t('registration.steps.emergency') }}</legend>
                <p class="gw-reg__note">{{ t('registration.emergency.hint') }}</p>
                <div class="gw-reg__grid gw-reg__grid--3">
                    <FormField v-slot="field" :id="fieldId('emergency.name')" :label="t('registration.fields.full_name')" :error="error('emergency.name')" required>
                        <input :id="field.id" v-model="form.emergency.name" class="gw-input" type="text" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('emergency.relationship')" :label="t('registration.fields.relationship')" :error="error('emergency.relationship')" required>
                        <input :id="field.id" v-model="form.emergency.relationship" class="gw-input" type="text" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                    <FormField v-slot="field" :id="fieldId('emergency.phone')" :label="t('registration.fields.phone')" :error="error('emergency.phone')" required>
                        <input :id="field.id" v-model="form.emergency.phone" class="gw-input" type="tel" autocomplete="off" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>
                </div>
            </fieldset>

            <!-- Photos and videos (media release) -->
            <fieldset class="gw-reg__card" data-test="media-step" :aria-describedby="error('media_release') ? 'media-error' : undefined">
                <legend class="gw-reg__legend">{{ t('registration.steps.media') }}</legend>
                <p class="font-semibold">{{ t('registration.media.question') }} <span class="text-[#b42318]" aria-hidden="true">*</span></p>
                <div class="gw-reg__grid gw-reg__grid--2">
                    <label v-for="choice in ['yes', 'no']" :key="choice" class="gw-choice" :data-test="`media-${choice}`">
                        <input :id="choice === 'yes' ? fieldId('media_release') : undefined" v-model="form.media_release" type="radio" name="media_release" :value="choice" required />
                        <span class="font-semibold">{{ t(`registration.media.${choice}`) }}</span>
                    </label>
                </div>
                <p v-if="error('media_release')" id="media-error" class="gw-reg__error">{{ error('media_release') }}</p>
                <a :href="navigation.media_release" target="_blank" rel="noopener" class="gw-reg__link">
                    {{ t('registration.media.more') }}<span class="sr-only"> ({{ t('registration.agreements.opens_new_tab') }})</span> ↗
                </a>
            </fieldset>

            <!-- Agreements -->
            <fieldset class="gw-reg__card" data-test="agreements-step">
                <legend class="gw-reg__legend">{{ t('registration.steps.agreements') }}</legend>
                <div class="flex flex-col gap-3">
                    <div v-for="agreement in agreements" :key="agreement.key">
                        <label class="gw-choice">
                            <input :id="fieldId(agreement.key)" v-model="form[agreement.key]" type="checkbox" :aria-invalid="error(agreement.key) ? 'true' : undefined" required />
                            <span>
                                {{ aroundLink(agreement.text)[0]
                                }}<a :href="agreement.link" target="_blank" rel="noopener" class="font-semibold underline"
                                    >{{ agreement.label }}<span class="sr-only"> ({{ t('registration.agreements.opens_new_tab') }})</span></a
                                >{{ aroundLink(agreement.text)[1] }} <span class="text-[#b42318]" aria-hidden="true">*</span>
                            </span>
                        </label>
                        <p v-if="error(agreement.key)" class="gw-reg__error">{{ error(agreement.key) }}</p>
                    </div>

                    <FormField v-slot="field" :id="fieldId('signature_name')" :label="t('registration.fields.signature')" :hint="t('registration.agreements.signature_hint')" :error="error('signature_name')" required>
                        <input :id="field.id" v-model="form.signature_name" class="gw-input" type="text" autocomplete="name" :aria-describedby="field.describedBy" :aria-invalid="field.invalid" required />
                    </FormField>

                    <label class="gw-choice">
                        <input v-model="form.marketing_opt_in" type="checkbox" />
                        <span>{{ t('registration.agreements.marketing') }}</span>
                    </label>
                </div>
            </fieldset>

            <div class="flex justify-center">
                <CtaButton variant="primary" surface="light" type="submit" :disabled="form.processing" always-open data-test="registration-submit">
                    {{ form.processing ? t('registration.submit.sending') : t(`registration.submit.${kind}`) }}
                </CtaButton>
            </div>
        </form>
    </HomePanel>
</template>

<style scoped>
.gw-reg-head {
    --gw-focus-ring: var(--color-gw-yellow);

    padding: calc(var(--gw-edge) + var(--gw-header-h) + 48px) 0 110px;
    background: var(--color-gw-black);
    color: var(--color-gw-white);
}

.gw-reg-head__back {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    color: #cfcfcf;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.gw-reg-head__eyebrow {
    margin-top: 40px;
    color: var(--color-gw-yellow);
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.gw-reg-head__title {
    margin-top: 12px;
    font-family: var(--gw-font-barlow);
    font-size: clamp(56px, min(9vw, 15vh), 150px);
    font-weight: 800;
    line-height: 0.86;
    text-transform: uppercase;
}

.gw-reg-head__intro {
    max-width: 640px;
    margin-top: 22px;
    color: #d4d4d4;
    font-size: clamp(16px, 1.3vw, 19px);
    line-height: 1.6;
}

.gw-reg-body {
    padding: 64px calc(var(--gw-edge) + var(--gw-gutter)) 160px;
}

.gw-reg {
    display: flex;
    flex-direction: column;
    gap: 32px;
    max-width: 920px;
    margin-inline: auto;
}

.gw-reg__card {
    display: flex;
    flex-direction: column;
    gap: 18px;
    min-width: 0;
    padding: 28px;
    border: 0;
    border-radius: 28px;
    background: var(--color-gw-neu-light);
    box-shadow: var(--shadow-gw-raised-light);
}

.gw-reg__legend {
    float: left;
    width: 100%;
    margin-bottom: 4px;
    font-family: var(--gw-font-barlow);
    font-size: 30px;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
}

.gw-reg__legend + * {
    clear: both;
}

.gw-reg__subtitle {
    margin-top: 8px;
    font-size: 15px;
    font-weight: 600;
}

.gw-reg__grid {
    display: grid;
    gap: 16px;
}

.gw-reg__grid--2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.gw-reg__grid--3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.gw-reg__player {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 20px;
    border-radius: 20px;
    box-shadow: var(--shadow-gw-inset-light-sm);
}

.gw-reg__note {
    color: #4d4d4d;
    font-size: 14px;
}

.gw-reg__error {
    color: #b42318;
    font-size: 13px;
    font-weight: 600;
}

.gw-reg__link {
    align-self: flex-start;
    border: 0;
    background: none;
    color: var(--color-gw-black);
    font-size: 14px;
    font-weight: 600;
    text-decoration: underline;
    cursor: pointer;
}

.gw-reg__add {
    align-self: flex-start;
    padding: 12px 20px;
    border: 0;
    border-radius: 999px;
    background: var(--color-gw-neu-light);
    box-shadow: var(--shadow-gw-raised-light-sm);
    font-weight: 600;
    cursor: pointer;
}

.gw-reg__summary {
    padding: 20px 24px;
    border: 2px solid #b42318;
    border-radius: 20px;
    background: #fff4f2;
    color: #7a1a12;
}

.gw-reg__summary ul {
    margin-top: 8px;
    padding-left: 20px;
    list-style: disc;
}

.gw-reg__summary a {
    color: inherit;
    text-decoration: underline;
}

.gw-reg__trap {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip-path: inset(50%);
}

@media (max-width: 760px) {
    .gw-reg-body {
        padding-block: 40px 120px;
    }

    .gw-reg__card {
        padding: 20px;
    }

    .gw-reg__grid--2,
    .gw-reg__grid--3 {
        grid-template-columns: 1fr;
    }

    .gw-reg__player {
        padding: 14px;
    }
}
</style>
