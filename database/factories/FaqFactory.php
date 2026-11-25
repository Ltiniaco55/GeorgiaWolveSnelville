<?php

namespace Database\Factories;

use App\Enums\FaqTopic;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * Published, not on the home page; see onHome() and unpublished().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $question = rtrim(fake()->sentence(), '.').'?';
        $answer = fake()->paragraph();

        return [
            'topic' => fake()->randomElement(FaqTopic::cases()),
            'question' => ['en' => $question, 'es' => $question],
            'answer' => ['en' => $answer, 'es' => $answer],
            'is_published' => true,
            'show_on_home' => false,
            'sort_order' => 0,
        ];
    }

    public function onHome(): static
    {
        return $this->state(fn (): array => ['show_on_home' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
