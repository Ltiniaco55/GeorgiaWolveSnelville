<?php

use App\Support\AgeGroup;
use Carbon\CarbonImmutable;

/**
 * Categories in the 2026–27 season: the age turned in 2027, up to the groups.
 */
function ageGroup(string $birthDate): ?string
{
    return (new AgeGroup(2027, [6, 8, 10, 12, 14]))->forBirthDate(CarbonImmutable::parse($birthDate));
}

test('the category is the age turned in the season end year, rounded up to a group', function (string $birthDate, ?string $category) {
    expect(ageGroup($birthDate))->toBe($category);
})->with([
    'born 2022, turns 5: U6' => ['2022-03-01', 'U6'],
    'born 2021, turns 6: U6' => ['2021-12-31', 'U6'],
    'born 2020, turns 7: U8' => ['2020-01-01', 'U8'],
    'born 2017, turns 10: U10' => ['2017-06-15', 'U10'],
    'born 2014, turns 13: U14' => ['2014-09-09', 'U14'],
    'born 2013, turns 14: U14' => ['2013-01-01', 'U14'],
    'born 2023, turns 4: too young' => ['2023-01-01', null],
    'born 2012, turns 15: too old' => ['2012-12-31', null],
]);

test('the accepted birth years come from the same rule', function () {
    expect((new AgeGroup(2027, [6, 8, 10, 12, 14]))->birthYears())->toBe(['from' => 2013, 'to' => 2022]);
});
