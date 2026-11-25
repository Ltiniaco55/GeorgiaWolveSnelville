<?php

use App\Support\BillingStartCalculator;
use Carbon\CarbonImmutable;

/**
 * The day-15 rule: what joining the monthly plan on a given day costs today and
 * when the first monthly charge is.
 */
function calculator(): BillingStartCalculator
{
    return new BillingStartCalculator(cutoffDay: 15, monthlyPrice: 10_000, timezone: 'America/New_York');
}

test('joining up to the cut-off day pays the month now', function (string $date) {
    $start = calculator()->forJoinDate(CarbonImmutable::parse($date, 'America/New_York'));

    expect($start['pays_current_month'])->toBeTrue()
        ->and($start['due_today'])->toBe(10_000)
        ->and($start['first_charge_on']->toDateString())->toBe('2026-12-01');
})->with(['the 1st' => '2026-11-01', 'the 15th' => '2026-11-15']);

test('joining after the cut-off day pays nothing today and is first charged on the 1st', function (string $date) {
    $start = calculator()->forJoinDate(CarbonImmutable::parse($date, 'America/New_York'));

    expect($start['pays_current_month'])->toBeFalse()
        ->and($start['due_today'])->toBe(0)
        ->and($start['first_charge_on']->toDateString())->toBe('2026-12-01');
})->with(['the 16th' => '2026-11-16', 'the 30th' => '2026-11-30']);

test('the first charge rolls over to January in December', function () {
    expect(calculator()->forJoinDate(CarbonImmutable::parse('2026-12-31', 'America/New_York'))['first_charge_on']->toDateString())
        ->toBe('2027-01-01');
});

test('the first charge after January 31 is February 1, not March', function () {
    expect(calculator()->forJoinDate(CarbonImmutable::parse('2027-01-31', 'America/New_York'))['first_charge_on']->toDateString())
        ->toBe('2027-02-01');
});
