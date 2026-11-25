<?php

namespace Database\Factories;

use App\Enums\PhotoCategory;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Published by default (with media release); see withoutMediaRelease().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'category' => fake()->randomElement(PhotoCategory::cases()),
            'title' => ['en' => ucfirst($title), 'es' => ucfirst($title)],
            'subtitle' => null,
            'alt' => ['en' => fake()->sentence(), 'es' => fake()->sentence()],
            'image' => 'images/gallery/team-talk',
            'width' => 382,
            'height' => 420,
            'has_media_release' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * A photo whose parents have not signed the media release: never shown.
     */
    public function withoutMediaRelease(): static
    {
        return $this->state(fn (): array => ['has_media_release' => false]);
    }
}
