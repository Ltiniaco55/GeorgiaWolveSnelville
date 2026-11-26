<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\RegistrationPlayer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationPlayer>
 */
class RegistrationPlayerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'date_of_birth' => '2017-05-10',
            'category' => 'U10',
            'gender' => 'boy',
            'needs_uniform' => true,
            'shirt_size' => 'YM',
            'medical_notes' => null,
        ];
    }
}
