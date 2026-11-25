<?php

use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * The home's `pricing` prop: prices and the "When do you join?" calendar come
 * from config/billing.php, formatted for the locale.
 */
test('the home shares the plan prices from config/billing.php', function () {
    config(['billing.prices' => ['free_trial' => 0, 'monthly' => 12_000, 'annual' => 120_050, 'uniform' => 8_000]]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('pricing.prices', [
                'free_trial' => '$0',
                'monthly' => '$120',
                'annual' => '$1,200.50',
                'annual_saving' => '$239.50',
                'uniform' => '$80',
            ])
            ->where('pricing.cutoffDay', '15th')
        );
});

test('the calendar shows the current month in the club timezone and applies the cut-off day', function () {
    config(['billing.cutoff_day' => 10]);
    // 03:00 UTC on December 1 is still November 30 in Snellville.
    $this->travelTo(CarbonImmutable::parse('2026-12-01 03:00', 'UTC'));

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('pricing.calendar.monthLabel', 'November 2026')
            ->where('pricing.calendar.today', 30)
            ->where('pricing.calendar.leadingBlanks', 6)
            ->has('pricing.calendar.days', 30)
            ->where('pricing.calendar.days.9', ['day' => 10, 'date' => 'November 10', 'paysNow' => true, 'dueToday' => '$100', 'firstCharge' => 'December 1'])
            ->where('pricing.calendar.days.10', ['day' => 11, 'date' => 'November 11', 'paysNow' => false, 'dueToday' => '$0', 'firstCharge' => 'December 1'])
            ->where('pricing.cutoffDay', '10th')
        );
});

test('in Spanish the dates and the cut-off day are localised', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-25 12:00', 'America/New_York'));

    $this->get('/es')
        ->assertInertia(fn (Assert $page) => $page
            ->where('pricing.calendar.monthLabel', 'Noviembre de 2026')
            ->where('pricing.calendar.monthName', 'noviembre')
            ->where('pricing.calendar.days.15.date', '16 de noviembre')
            ->where('pricing.calendar.days.15.firstCharge', '1 de diciembre')
            ->where('pricing.cutoffDay', '15')
        );
});
