<?php

namespace App\Http\Controllers;

use App\Enums\GuardianRelationship;
use App\Enums\RegistrationStatus;
use App\Enums\RegistrationType;
use App\Http\Requests\StoreRegistrationRequest;
use App\Mail\RegistrationConfirmation;
use App\Mail\RegistrationSubmitted;
use App\Models\Registration;
use App\Support\AgeGroup;
use App\Support\HomePricing;
use App\Support\Localization;
use App\Support\TrainingCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * The registration templates (docs/design/sitemap.md): /free-training and
 * /register (?plan=monthly|annual). A registration is stored as pending, the
 * club gets an email and the family a confirmation; payment (Stripe Checkout)
 * comes in phase 7.
 */
class RegistrationController extends Controller
{
    public function freeTraining(): Response
    {
        return Inertia::render('Registration', [
            ...$this->commonProps(),
            'type' => RegistrationType::FreeTrial->value,
            'trainingDates' => collect(TrainingCalendar::upcoming())
                ->map(fn (CarbonImmutable $date): array => [
                    'value' => $date->toDateString(),
                    'label' => $date->locale(app()->getLocale())->translatedFormat(__('registration.mail_date_format')),
                ])
                ->all(),
        ]);
    }

    public function register(Request $request, HomePricing $pricing): Response
    {
        $billing = $pricing->toArray();
        $today = $billing['calendar']['days'][$billing['calendar']['today'] - 1];

        return Inertia::render('Registration', [
            ...$this->commonProps(),
            'type' => $request->query('plan') === RegistrationType::Annual->value ? RegistrationType::Annual->value : RegistrationType::Monthly->value,
            'prices' => $billing['prices'],
            'cutoffDay' => $billing['cutoffDay'],
            'joiningToday' => ['dueToday' => $today['dueToday'], 'firstCharge' => $today['firstCharge']],
            'shirtSizes' => StoreRegistrationRequest::SHIRT_SIZES,
        ]);
    }

    public function store(StoreRegistrationRequest $request, AgeGroup $ageGroup): RedirectResponse
    {
        $type = $request->registrationType();
        $data = $request->validated();
        $now = now();

        $registration = DB::transaction(function () use ($request, $data, $type, $now, $ageGroup): Registration {
            $registration = Registration::create([
                'reference' => (string) Str::uuid(),
                'type' => $type,
                'status' => RegistrationStatus::Pending,
                'locale' => app()->getLocale(),
                'guardian_first_name' => $data['guardian']['first_name'],
                'guardian_last_name' => $data['guardian']['last_name'],
                'guardian_relationship' => $data['guardian']['relationship'],
                'guardian_email' => $data['guardian']['email'],
                'guardian_phone' => $data['guardian']['phone'],
                'address_street' => $data['address']['street'],
                'address_city' => $data['address']['city'],
                'address_state' => strtoupper($data['address']['state']),
                'address_zip' => $data['address']['zip'],
                'second_guardian_name' => $data['second_guardian']['name'] ?? null,
                'second_guardian_email' => $data['second_guardian']['email'] ?? null,
                'second_guardian_phone' => $data['second_guardian']['phone'] ?? null,
                'emergency_contact_name' => $data['emergency']['name'],
                'emergency_contact_relationship' => $data['emergency']['relationship'],
                'emergency_contact_phone' => $data['emergency']['phone'],
                'preferred_training_date' => $data['preferred_training_date'] ?? null,
                'media_release' => $data['media_release'] === 'yes',
                'marketing_opt_in' => (bool) ($data['marketing_opt_in'] ?? false),
                'signature_name' => $data['signature_name'],
                'liability_waiver_accepted_at' => $now,
                'terms_accepted_at' => $now,
                'refund_policy_accepted_at' => $type->isPaid() ? $now : null,
                'signed_ip' => $request->ip(),
                'signed_user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            ]);

            foreach ($data['players'] as $player) {
                $registration->players()->create([
                    'first_name' => $player['first_name'],
                    'last_name' => $player['last_name'],
                    'date_of_birth' => $player['date_of_birth'],
                    'category' => $ageGroup->forBirthDate(CarbonImmutable::parse($player['date_of_birth'])),
                    'gender' => $player['gender'] ?? null,
                    'needs_uniform' => $type->isPaid() && (bool) ($player['needs_uniform'] ?? false),
                    'shirt_size' => $type->isPaid() && ($player['needs_uniform'] ?? false) ? ($player['shirt_size'] ?? null) : null,
                    'medical_notes' => $player['medical_notes'] ?? null,
                ]);
            }

            return $registration->load('players');
        });

        $this->sendEmails($registration);

        return redirect(Localization::pageUrl($type->isPaid() ? 'register_success' : 'free_training_thanks'))
            ->with('registration', [
                'type' => $type->value,
                'trainingDate' => $registration->preferred_training_date?->locale(app()->getLocale())->translatedFormat(__('registration.mail_date_format')),
                'players' => $registration->players->map(fn ($player): array => [
                    'name' => $player->first_name,
                    'category' => $player->category,
                ])->all(),
            ]);
    }

    /**
     * Thank-you page. The summary only exists right after sending (session flash).
     */
    public function received(Request $request): Response
    {
        return Inertia::render('RegistrationReceived', [
            'summary' => $request->session()->get('registration'),
        ]);
    }

    /**
     * Options shared by the three templates.
     *
     * @return array<string, mixed>
     */
    private function commonProps(): array
    {
        return [
            'birthYears' => AgeGroup::fromConfig()->birthYears(),
            'relationships' => array_map(fn (GuardianRelationship $relationship): string => $relationship->value, GuardianRelationship::cases()),
        ];
    }

    /**
     * To the club and to the family. A mail failure never loses the registration:
     * it is stored already, the error is logged.
     */
    private function sendEmails(Registration $registration): void
    {
        $clubAddress = config('club.registrations_email') ?: config('club.email') ?: config('mail.from.address');

        try {
            Mail::to($clubAddress)->send(new RegistrationSubmitted($registration));
            Mail::to($registration->guardian_email)->send(new RegistrationConfirmation($registration));
        } catch (Throwable $exception) {
            Log::error('Registration emails could not be sent', ['reference' => $registration->reference, 'exception' => $exception->getMessage()]);
        }
    }
}
