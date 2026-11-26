<?php

use App\Enums\RegistrationType;
use App\Mail\RegistrationConfirmation;
use App\Mail\RegistrationSubmitted;
use App\Models\Registration;
use App\Support\TrainingCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Registration templates: free training (/free-training) and the monthly or
 * annual plan (/register).
 */
beforeEach(function () {
    Mail::fake();
    config(['club.registrations_email' => 'club@example.com', 'club.season_end_year' => 2027]);
    $this->travelTo(CarbonImmutable::parse('2026-11-24 10:00', 'America/New_York'));
});

/**
 * A complete, valid submission (paid plan unless $freeTrial).
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function registrationData(bool $freeTrial = false, array $overrides = []): array
{
    $data = [
        'players' => [
            ['first_name' => 'Mateo', 'last_name' => 'García', 'date_of_birth' => '2017-05-10', 'gender' => 'boy', 'medical_notes' => 'Asthma inhaler in the bag', 'needs_uniform' => true, 'shirt_size' => 'YM'],
            ['first_name' => 'Lucía', 'last_name' => 'García', 'date_of_birth' => '2020-02-01', 'gender' => 'girl', 'medical_notes' => '', 'needs_uniform' => false, 'shirt_size' => ''],
        ],
        'guardian' => ['first_name' => 'Ana', 'last_name' => 'García', 'relationship' => 'mother', 'email' => 'ana@example.com', 'phone' => '(404) 555-0123'],
        'address' => ['street' => '123 Oak St', 'city' => 'Snellville', 'state' => 'ga', 'zip' => '30078'],
        'second_guardian' => ['name' => '', 'email' => '', 'phone' => ''],
        'emergency' => ['name' => 'Rosa López', 'relationship' => 'Aunt', 'phone' => '404-555-0199'],
        'media_release' => 'no',
        'accepts_liability_waiver' => true,
        'accepts_terms' => true,
        'signature_name' => 'Ana García',
        'marketing_opt_in' => false,
        'website' => '',
    ];

    if ($freeTrial) {
        $data['preferred_training_date'] = TrainingCalendar::upcoming()[0]->toDateString();
    } else {
        $data['plan'] = 'annual';
        $data['accepts_refund_policy'] = true;
    }

    return array_replace_recursive($data, $overrides);
}

test('the three templates render with their options', function () {
    $this->get('/free-training')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Registration')
            ->where('type', 'free_trial')
            ->where('trainingDates.0', ['value' => '2026-11-26', 'label' => 'Thursday, November 26'])
            ->where('birthYears', ['from' => 2013, 'to' => 2022])
        );

    $this->get('/register?plan=annual')
        ->assertInertia(fn (Assert $page) => $page->where('type', 'annual')->where('prices.annual', '$1,000'));

    $this->get('/es/inscripcion')
        ->assertInertia(fn (Assert $page) => $page
            ->where('type', 'monthly')
            ->where('joiningToday', ['dueToday' => '$0', 'firstCharge' => '1 de diciembre'])
        );
});

test('a paid registration is stored with its players, sensitive data encrypted, and both emails are sent', function () {
    $this->post('/register', registrationData())
        ->assertRedirect('/register/success')
        ->assertSessionHas('registration.players', [['name' => 'Mateo', 'category' => 'U10'], ['name' => 'Lucía', 'category' => 'U8']]);

    $registration = Registration::with('players')->sole();

    expect($registration->type)->toBe(RegistrationType::Annual)
        ->and($registration->address_state)->toBe('GA')
        ->and($registration->media_release)->toBeFalse()
        ->and($registration->refund_policy_accepted_at)->not->toBeNull()
        ->and($registration->guardian_phone)->toBe('(404) 555-0123')
        ->and($registration->players->pluck('category')->all())->toBe(['U10', 'U8'])
        ->and($registration->players[0]->shirt_size)->toBe('YM')
        ->and($registration->players[1]->shirt_size)->toBeNull();

    // At rest, the sensitive columns are not readable.
    $raw = DB::table('registration_players')->first();
    expect($raw->medical_notes)->not->toContain('Asthma')
        ->and($raw->date_of_birth)->not->toContain('2017')
        ->and(DB::table('registrations')->value('guardian_phone'))->not->toContain('555');

    Mail::assertSent(RegistrationSubmitted::class, fn (RegistrationSubmitted $mail): bool => $mail->hasTo('club@example.com') && $mail->hasReplyTo('ana@example.com'));
    Mail::assertSent(RegistrationConfirmation::class, fn (RegistrationConfirmation $mail): bool => $mail->hasTo('ana@example.com'));
});

test('the club email leaves the sensitive data out', function () {
    $this->post('/register', registrationData());

    $html = (new RegistrationSubmitted(Registration::with('players')->sole()))->render();

    expect($html)->toContain('Mateo García · U10')
        ->not->toContain('Asthma')
        ->not->toContain('555-0123')
        ->not->toContain('123 Oak St');
});

test('a free training registration needs one of the offered training days', function () {
    $this->post('/free-training', registrationData(freeTrial: true, overrides: ['preferred_training_date' => '2026-11-25']))
        ->assertSessionHasErrors(['preferred_training_date' => 'Please choose one of the training days shown.']);

    $this->post('/free-training', registrationData(freeTrial: true))
        ->assertRedirect('/free-training/thanks');

    expect(Registration::sole())
        ->type->toBe(RegistrationType::FreeTrial)
        ->preferred_training_date->toDateString()->toBe('2026-11-26')
        ->refund_policy_accepted_at->toBeNull();
});

test('the server validates the players, the contacts and the agreements', function () {
    $this->post('/register', registrationData(overrides: [
        'players' => [['date_of_birth' => '2010-01-01', 'needs_uniform' => true, 'shirt_size' => '']],
        'guardian' => ['email' => 'not-an-email', 'phone' => '123'],
        'address' => ['zip' => 'ABC'],
        'media_release' => '',
        'accepts_liability_waiver' => false,
        'accepts_refund_policy' => false,
    ]))->assertSessionHasErrors([
        'players.0.date_of_birth' => 'Our groups are for children born from 2013 to 2022 (U6–U14).',
        'players.0.shirt_size',
        'guardian.email' => 'Please enter a valid email address.',
        'guardian.phone' => 'Please enter a phone number with at least 10 digits.',
        'address.zip' => 'Please enter a 5-digit ZIP code.',
        'media_release' => 'Please fill in Photos and videos.',
        'accepts_liability_waiver' => 'You need to accept the Liability Waiver.',
        'accepts_refund_policy',
    ]);

    expect(Registration::count())->toBe(0);
    Mail::assertNothingSent();
});

test('the messages are in the language of the form', function () {
    $this->post('/es/entrenamiento-gratis', registrationData(freeTrial: true, overrides: ['guardian' => ['email' => 'x']]))
        ->assertSessionHasErrors(['guardian.email' => 'Escribe un email válido.']);
});

test('the honeypot rejects bots', function () {
    $this->post('/register', registrationData(overrides: ['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(Registration::count())->toBe(0);
});

test('the forms allow 5 submissions a minute per IP', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/register', ['website' => 'bot'])->assertSessionHasErrors();
    }

    $this->post('/register', ['website' => 'bot'])->assertTooManyRequests();
});
