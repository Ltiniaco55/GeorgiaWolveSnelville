<?php

use App\Enums\PhotoCategory;
use App\Models\Photo;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Home props of the "Associated leagues and partners" strip and the gallery.
 */
test('the gallery only gets photos with the parents media release, in order, at most 9', function () {
    Photo::factory()->withoutMediaRelease()->create(['sort_order' => 0, 'title' => ['en' => 'Hidden', 'es' => 'Oculta']]);
    Photo::factory()->create(['sort_order' => 2, 'title' => ['en' => 'Second', 'es' => 'Segunda']]);
    Photo::factory()->create([
        'sort_order' => 1,
        'category' => PhotoCategory::Champions,
        'title' => ['en' => 'First', 'es' => 'Primera'],
        'alt' => ['en' => 'Players with the cup', 'es' => 'Jugadores con la copa'],
        'image' => 'images/gallery/with-the-cup',
        'width' => 420,
        'height' => 385,
    ]);
    Photo::factory()->count(10)->create(['sort_order' => 5]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->has('galleryPhotos', 9)
            ->where('galleryPhotos.0.title', 'First')
            ->where('galleryPhotos.0.alt', 'Players with the cup')
            ->where('galleryPhotos.0.category', 'champions')
            ->where('galleryPhotos.0.avif', 'http://localhost/images/gallery/with-the-cup.avif')
            ->where('galleryPhotos.0.webp', 'http://localhost/images/gallery/with-the-cup.webp')
            ->where('galleryPhotos.0.width', 420)
            ->where('galleryPhotos.1.title', 'Second')
        );
});

test('the photo texts follow the locale', function () {
    Photo::factory()->create(['alt' => ['en' => 'Players with the cup', 'es' => 'Jugadores con la copa']]);

    $this->get('/es')
        ->assertInertia(fn (Assert $page) => $page->where('galleryPhotos.0.alt', 'Jugadores con la copa'));
});

test('without published photos the gallery gets none', function () {
    Photo::factory()->withoutMediaRelease()->count(3)->create();

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('galleryPhotos', []));
});

test('the league crests link to their league pages in the current locale', function (string $url, array $expected) {
    $this->get($url)
        ->assertInertia(fn (Assert $page) => $page
            ->where('leagues', [
                ['slug' => 'liga-interamericana', 'url' => $expected[0]],
                ['slug' => 'gysl', 'url' => $expected[1]],
            ])
            ->where('partners.0.name', 'Joma')
            ->where('partners.1.name', 'CER')
        );
})->with([
    'English' => ['/', ['http://localhost/leagues/liga-interamericana', 'http://localhost/leagues/gysl']],
    'Spanish' => ['/es', ['http://localhost/es/ligas/liga-interamericana', 'http://localhost/es/ligas/gysl']],
]);
