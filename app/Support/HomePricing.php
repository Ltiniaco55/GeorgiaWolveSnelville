<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Number;

/**
 * Pricing data for the home page, already formatted for the current locale:
 * plan prices from config/billing.php and, for every day of the current month,
 * what joining that day costs today and when the first charge is
 * (BillingStartCalculator). The frontend only shows it: it never applies the rule.
 */
class HomePricing
{
    public function __construct(private BillingStartCalculator $calculator) {}

    /**
     * @return array{
     *     prices: array<string, string>,
     *     cutoffDay: string,
     *     calendar: array{
     *         monthName: string,
     *         monthLabel: string,
     *         today: int,
     *         leadingBlanks: int,
     *         days: list<array{day: int, date: string, paysNow: bool, dueToday: string, firstCharge: string}>
     *     }
     * }
     */
    public function toArray(?CarbonImmutable $today = null): array
    {
        $locale = app()->getLocale();
        $today = ($today ?? $this->calculator->today())->locale($locale);
        $prices = config('billing.prices');
        $dateFormat = __('home.pricing.calendar.date_format');

        $days = [];

        foreach (range(1, $today->daysInMonth) as $day) {
            $date = $today->setDay($day);
            $start = $this->calculator->forJoinDate($date);

            $days[] = [
                'day' => $day,
                'date' => $date->translatedFormat($dateFormat),
                'paysNow' => $start['pays_current_month'],
                'dueToday' => self::money($start['due_today']),
                'firstCharge' => $start['first_charge_on']->locale($locale)->translatedFormat($dateFormat),
            ];
        }

        return [
            'prices' => [
                'free_trial' => self::money($prices['free_trial']),
                'monthly' => self::money($prices['monthly']),
                'annual' => self::money($prices['annual']),
                'annual_saving' => self::money(12 * $prices['monthly'] - $prices['annual']),
                'uniform' => self::money($prices['uniform']),
            ],
            'cutoffDay' => self::cutoffLabel($this->calculator->cutoffDay),
            'calendar' => [
                'monthName' => $today->translatedFormat('F'),
                'monthLabel' => ucfirst($today->translatedFormat(__('home.pricing.calendar.month_format'))),
                'today' => $today->day,
                'leadingBlanks' => $today->startOfMonth()->dayOfWeekIso - 1,
                'days' => $days,
            ],
        ];
    }

    /**
     * The cut-off day as said in the current locale: "15th" in English, "15" in Spanish.
     */
    public static function cutoffLabel(int $day): string
    {
        return app()->getLocale() === 'en' ? Number::ordinal($day) : (string) $day;
    }

    /**
     * Whole dollars without decimals ("$1,000"), cents otherwise ("$99.50").
     */
    public static function money(int $cents): string
    {
        return '$'.number_format($cents / 100, $cents % 100 === 0 ? 0 : 2);
    }
}
