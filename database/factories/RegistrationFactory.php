<?php

namespace Database\Factories;

use App\Enums\GuardianRelationship;
use App\Enums\RegistrationStatus;
use App\Enums\RegistrationType;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * A pending monthly registration; see freeTrial() and annual().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => (string) Str::uuid(),
            'type' => RegistrationType::Monthly,
            'status' => RegistrationStatus::Pending,
            'locale' => 'en',
            'guardian_first_name' => fake()->firstName(),
            'guardian_last_name' => fake()->lastName(),
            'guardian_relationship' => GuardianRelationship::Mother,
            'guardian_email' => fake()->safeEmail(),
            'guardian_phone' => '(404) 555-0123',
            'address_street' => fake()->streetAddress(),
            'address_city' => 'Snellville',
            'address_state' => 'GA',
            'address_zip' => '30078',
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_relationship' => 'Aunt',
            'emergency_contact_phone' => '(404) 555-0199',
            'media_release' => true,
            'marketing_opt_in' => false,
            'signature_name' => fake()->name(),
            'liability_waiver_accepted_at' => now(),
            'terms_accepted_at' => now(),
            'refund_policy_accepted_at' => now(),
        ];
    }

    public function freeTrial(): static
    {
        return $this->state(fn (): array => [
            'type' => RegistrationType::FreeTrial,
            'preferred_training_date' => now()->addDays(3)->toDateString(),
            'refund_policy_accepted_at' => null,
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (): array => ['type' => RegistrationType::Annual]);
    }
}
