<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * The day-15 billing rule (config/billing.php): what a family pays the day it
 * joins on the monthly plan, and when the first monthly charge is.
 *
 * - Joining from the 1st to the cut-off day: the current month is paid now and
 *   the plan renews on the 1st of the next month.
 * - Joining after the cut-off day: nothing today; the rest of the month is free
 *   and the first charge is on the 1st of the next month.
 *
 * Dates are counted in the club's timezone.
 */
class BillingStartCalculator
{
    public function __construct(
        public readonly int $cutoffDay,
        public readonly int $monthlyPrice,
        public readonly string $timezone,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            cutoffDay: (int) config('billing.cutoff_day'),
            monthlyPrice: (int) config('billing.prices.monthly'),
            timezone: (string) config('billing.timezone'),
        );
    }

    /**
     * Today in the club's timezone, at midnight.
     */
    public function today(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone)->startOfDay();
    }

    /**
     * @return array{pays_current_month: bool, due_today: int, first_charge_on: CarbonImmutable}
     */
    public function forJoinDate(CarbonImmutable $joinDate): array
    {
        $paysCurrentMonth = $joinDate->day <= $this->cutoffDay;

        return [
            'pays_current_month' => $paysCurrentMonth,
            'due_today' => $paysCurrentMonth ? $this->monthlyPrice : 0,
            'first_charge_on' => $joinDate->startOfMonth()->addMonthNoOverflow()->startOfDay(),
        ];
    }
}
