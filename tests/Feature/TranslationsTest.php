<?php

use Illuminate\Support\Arr;

/**
 * Translation group names (lang/{locale}/*.php) of a locale, sorted.
 * Uses plain paths because datasets are resolved before the application boots.
 *
 * @return array<int, string>
 */
function translationGroups(string $locale): array
{
    $groups = array_map(
        fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
        glob(dirname(__DIR__, 2)."/lang/{$locale}/*.php") ?: [],
    );
    sort($groups);

    return $groups;
}

test('English and Spanish have the same translation groups', function () {
    expect(translationGroups('en'))->not->toBeEmpty()
        ->and(translationGroups('es'))->toBe(translationGroups('en'));
});

test('every translation group has the same keys in English and Spanish', function (string $group) {
    $englishKeys = array_keys(Arr::dot(require lang_path("en/{$group}.php")));
    $spanishKeys = array_keys(Arr::dot(require lang_path("es/{$group}.php")));

    expect(array_values(array_diff($englishKeys, $spanishKeys)))->toBe([], "Missing in es/{$group}.php")
        ->and(array_values(array_diff($spanishKeys, $englishKeys)))->toBe([], "Missing in en/{$group}.php");
})->with(translationGroups('en'));
