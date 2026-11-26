<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * The next training dates (config/club.php "training_days", in the club's
 * timezone), offered in the free training form.
 */
class TrainingCalendar
{
    /**
     * @return list<CarbonImmutable> From tomorrow on.
     */
    public static function upcoming(int $count = 6, ?CarbonImmutable $today = null): array
    {
        $days = array_map('intval', config('club.training_days'));
        $date = ($today ?? CarbonImmutable::now((string) config('billing.timezone')))->startOfDay();
        $dates = [];

        while (count($dates) < $count && $days !== []) {
            $date = $date->addDay();

            if (in_array($date->dayOfWeekIso, $days, true)) {
                $dates[] = $date;
            }
        }

        return $dates;
    }
}
