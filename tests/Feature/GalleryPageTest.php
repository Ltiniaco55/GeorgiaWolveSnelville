<?php

use App\Enums\PhotoCategory;
use App\Models\Photo;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * /gallery: only photos with the parents' media release, and only the filter tabs
 * that have photos.
 */
test('the gallery page renders in both languages', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Gallery')->where('currentPage', 'gallery'));
})->with(['/gallery', '/es/galeria']);

test('it lists only published photos, in order, and the categories that have them', function () {
    Photo::factory()->withoutMediaRelease()->create(['category' => PhotoCategory::Events]);
    Photo::factory()->create(['category' => PhotoCategory::Team, 'sort_order' => 2, 'title' => ['en' => 'Team', 'es' => 'Equipo']]);
    Photo::factory()->create([
        'category' => PhotoCategory::Training,
        'sort_order' => 1,
        'title' => ['en' => 'Warm-up', 'es' => 'Calentamiento'],
        'subtitle' => ['en' => 'Tuesday session', 'es' => 'Sesión del martes'],
    ]);

    $this->get('/es/galeria')
        ->assertInertia(fn (Assert $page) => $page
            ->has('photos', 2)
            ->where('photos.0.title', 'Calentamiento')
            ->where('photos.0.subtitle', 'Sesión del martes')
            ->where('photos.1.subtitle', '')
            ->where('categories', ['training', 'team'])
        );
});

test('without published photos there are no photos and no tabs', function () {
    Photo::factory()->withoutMediaRelease()->create();

    $this->get('/gallery')
        ->assertInertia(fn (Assert $page) => $page->where('photos', [])->where('categories', []));
});
