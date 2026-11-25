<script setup>
import { computed, nextTick, ref } from 'vue';
import { useTranslations } from '../Composables/useTranslations';

/**
 * "When do you join?" (docs/design/home-interacciones.md, "Tarifas"): the days of
 * the current month; hovering, focusing or tapping a day explains what joining
 * that day costs today and when the first monthly charge is.
 *
 * Everything comes from the server (HomePricing, config/billing.php): whether
 * each day pays now, the amounts and the dates. This component never applies the
 * day-15 rule itself. Days after the cut-off are in another tone, with a legend.
 *
 * Keyboard: one tab stop; ← → move a day, ↑ ↓ a week, Home / End jump to the
 * first / last day. The answer is announced (aria-live).
 *
 * @typedef {Object} JoinDay
 * @property {number} day
 * @property {string} date Localised, e.g. "November 3".
 * @property {boolean} paysNow
 * @property {string} dueToday e.g. "$100"
 * @property {string} firstCharge Localised date.
 */
const props = defineProps({
    /** @type {import('vue').PropType<{ monthName: string, monthLabel: string, today: number, leadingBlanks: number, days: JoinDay[] }>} */
    calendar: {
        type: Object,
        required: true,
    },
    /** Monthly price, e.g. "$100". */
    monthlyPrice: {
        type: String,
        required: true,
    },
    /** Cut-off day, localised ("15th" / "15"). */
    cutoffDay: {
        type: String,
        required: true,
    },
});

const { t, tList } = useTranslations();
const selectedDay = ref(props.calendar.today);
const grid = ref(null);

const selected = computed(() => props.calendar.days.find((day) => day.day === selectedDay.value) ?? props.calendar.days[0]);

const explanation = computed(() => {
    const day = selected.value;
    const replacements = { date: day.date, month: props.calendar.monthName, next: day.firstCharge, amount: props.monthlyPrice };

    return day.paysNow ? t('home.pricing.calendar.pays_now', replacements) : t('home.pricing.calendar.free_now', replacements);
});

/**
 * @param {number} day
 */
function select(day) {
    selectedDay.value = day;
}

/**
 * @param {KeyboardEvent} event
 */
async function onKeydown(event) {
    const last = props.calendar.days.length;
    const moves = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
    let next = null;

    if (event.key in moves) {
        next = Math.min(last, Math.max(1, selectedDay.value + moves[event.key]));
    } else if (event.key === 'Home') {
        next = 1;
    } else if (event.key === 'End') {
        next = last;
    }

    if (next === null) {
        return;
    }

    event.preventDefault();
    select(next);
    await nextTick();
    grid.value?.querySelector(`[data-day="${next}"]`)?.focus();
}
</script>

<template>
    <div class="gw-calc" data-test="join-calculator">
        <div>
            <p class="gw-calc__eyebrow"><span class="gw-calc__dot" aria-hidden="true" />{{ t('home.pricing.calendar.eyebrow') }}</p>
            <h3 id="join-month" class="gw-calc__month">{{ calendar.monthLabel }}</h3>
            <p class="gw-calc__hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M4 3l7 17 2.5-7.5L21 10z" />
                </svg>
                {{ t('home.pricing.calendar.hint') }}
            </p>

            <div class="gw-calc__weekdays" aria-hidden="true">
                <span v-for="(weekday, index) in tList('home.pricing.calendar.weekdays')" :key="index">{{ weekday }}</span>
            </div>
            <div
                ref="grid"
                class="gw-calc__days"
                role="group"
                :aria-label="t('home.pricing.calendar.days_label', { month: calendar.monthLabel })"
                @keydown="onKeydown"
            >
                <span v-for="blank in calendar.leadingBlanks" :key="`blank-${blank}`" aria-hidden="true" />
                <button
                    v-for="day in calendar.days"
                    :key="day.day"
                    type="button"
                    class="gw-calc__day"
                    :class="{ 'gw-calc__day--late': !day.paysNow, 'gw-calc__day--selected': day.day === selectedDay, 'gw-calc__day--today': day.day === calendar.today }"
                    :tabindex="day.day === selectedDay ? 0 : -1"
                    :aria-pressed="day.day === selectedDay ? 'true' : 'false'"
                    :aria-label="day.day === calendar.today ? `${day.date} (${t('home.pricing.calendar.today')})` : day.date"
                    :data-day="day.day"
                    @pointerenter="select(day.day)"
                    @focus="select(day.day)"
                    @click="select(day.day)"
                >
                    {{ day.day }}
                </button>
            </div>
            <p class="gw-calc__legend"><span class="gw-calc__swatch" aria-hidden="true" />{{ t('home.pricing.calendar.legend', { day: cutoffDay }) }}</p>
        </div>

        <div class="gw-calc__out" aria-live="polite" data-test="join-result">
            <p class="gw-calc__amount">{{ t('home.pricing.calendar.amount_today', { amount: selected.dueToday }) }}</p>
            <p class="gw-calc__text">{{ explanation }}</p>
        </div>
    </div>
</template>

<style scoped>
.gw-calc {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 40px;
    align-items: center;
    padding: 40px;
    border-radius: 32px;
    background: var(--color-gw-neu-light);
    box-shadow: var(--shadow-gw-inset-light);
}

.gw-calc__eyebrow {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.gw-calc__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--color-gw-yellow);
    box-shadow: 0 0 0 1px var(--color-gw-black);
}

.gw-calc__month {
    margin-top: 10px;
    font-family: var(--gw-font-barlow);
    font-size: 44px;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
}

.gw-calc__hint,
.gw-calc__legend {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    margin-top: 14px;
    color: #595959;
    font-size: 13px;
}

.gw-calc__hint svg {
    width: 15px;
    height: 15px;
}

/* At most 520 px wide, so the days stay key-sized on wide screens. */
.gw-calc__weekdays,
.gw-calc__days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    max-width: 520px;
}

.gw-calc__weekdays {
    margin-top: 22px;
    margin-bottom: 8px;
    color: #6b6b6b;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
}

.gw-calc__day {
    aspect-ratio: 1;
    border: 0;
    border-radius: 12px;
    background: var(--color-gw-neu-light);
    box-shadow:
        3px 3px 7px var(--color-gw-neu-light-shade),
        -3px -3px 7px var(--color-gw-neu-light-highlight);
    color: #333;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition:
        background-color 0.2s,
        box-shadow 0.2s;
}

/* After the cut-off day: a warm tone (#6b5600 on #F3EFE0 keeps 4.5:1). */
.gw-calc__day--late,
.gw-calc__swatch {
    background: #f3efe0;
    color: #6b5600;
}

.gw-calc__day--today {
    text-decoration: underline;
    text-underline-offset: 3px;
}

.gw-calc__day--selected {
    background: var(--color-gw-yellow);
    box-shadow: inset 2px 2px 5px rgb(0 0 0 / 0.2);
    color: var(--color-gw-black);
}

.gw-calc__swatch {
    width: 14px;
    height: 14px;
    border-radius: 4px;
    box-shadow: 0 0 0 1px #d8cfae;
}

.gw-calc__out {
    min-height: 220px;
    padding: 28px;
    border-radius: 24px;
    background: var(--color-gw-featured);
    color: var(--color-gw-white);
}

.gw-calc__amount {
    color: var(--color-gw-yellow);
    font-family: var(--gw-font-barlow);
    font-size: 64px;
    font-weight: 800;
    line-height: 1;
}

.gw-calc__text {
    margin-top: 10px;
    color: #ccc;
    line-height: 1.6;
}

@media (max-width: 900px) {
    .gw-calc {
        grid-template-columns: 1fr;
        padding: 24px;
    }
}

@media (max-width: 420px) {
    .gw-calc__days,
    .gw-calc__weekdays {
        gap: 5px;
    }

    .gw-calc__day {
        border-radius: 9px;
        font-size: 13px;
    }

    .gw-calc__amount {
        font-size: 52px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gw-calc__day {
        transition: none;
    }
}
</style>
