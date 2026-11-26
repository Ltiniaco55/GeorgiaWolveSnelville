<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * A child's category (U6…U14) from the date of birth, US Soccer style: the age
 * a child turns in the year the season ends, rounded up to the club's groups
 * (config/club.php: season_end_year, age_groups).
 */
class AgeGroup
{
    /**
     * @param  list<int>  $groups  Ascending, e.g. [6, 8, 10, 12, 14].
     */
    public function __construct(
        public readonly int $seasonEndYear,
        public readonly array $groups,
    ) {}

    public static function fromConfig(): self
    {
        return new self((int) config('club.season_end_year'), array_map('intval', config('club.age_groups')));
    }

    /**
     * "U10", or null when the child is too young or too old for the club.
     */
    public function forBirthDate(CarbonInterface $dateOfBirth): ?string
    {
        $age = $this->seasonEndYear - $dateOfBirth->year;

        if ($age < $this->groups[0] - 1 || $age > max($this->groups)) {
            return null;
        }

        foreach ($this->groups as $group) {
            if ($age <= $group) {
                return "U{$group}";
            }
        }

        return null;
    }

    /**
     * Oldest and youngest accepted birth years, for the form.
     *
     * @return array{from: int, to: int}
     */
    public function birthYears(): array
    {
        return [
            'from' => $this->seasonEndYear - max($this->groups),
            'to' => $this->seasonEndYear - ($this->groups[0] - 1),
        ];
    }
}
