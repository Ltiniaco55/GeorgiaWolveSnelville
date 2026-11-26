<?php

namespace App\Http\Requests;

use App\Enums\GuardianRelationship;
use App\Enums\RegistrationType;
use App\Support\AgeGroup;
use App\Support\TrainingCalendar;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * A registration from one of the three templates: free training (/free-training)
 * or a paid plan (/register, plan monthly or annual). Server-side validation of
 * the players, the parent or guardian, the emergency contact, the media release
 * choice and the agreements. `website` is a honeypot that must stay empty.
 * Messages and field names: lang/{locale}/registration.php.
 */
class StoreRegistrationRequest extends FormRequest
{
    public const SHIRT_SIZES = ['YS', 'YM', 'YL', 'AS', 'AM', 'AL'];

    private const PHONE = '/^\+?[0-9\s().-]{10,20}$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Free training or the plan chosen in the form.
     */
    public function registrationType(): RegistrationType
    {
        if (str_ends_with((string) $this->route()?->getName(), 'free_training.store')) {
            return RegistrationType::FreeTrial;
        }

        return $this->input('plan') === RegistrationType::Annual->value ? RegistrationType::Annual : RegistrationType::Monthly;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isPaid = $this->registrationType()->isPaid();

        $rules = [
            'website' => ['nullable', 'max:0'],

            'players' => ['required', 'array', 'min:1', 'max:4'],
            'players.*.first_name' => ['required', 'string', 'max:60'],
            'players.*.last_name' => ['required', 'string', 'max:60'],
            'players.*.date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today', $this->ageGroupRule()],
            'players.*.gender' => ['nullable', 'in:boy,girl'],
            'players.*.medical_notes' => ['nullable', 'string', 'max:1000'],

            'guardian.first_name' => ['required', 'string', 'max:60'],
            'guardian.last_name' => ['required', 'string', 'max:60'],
            'guardian.relationship' => ['required', new Enum(GuardianRelationship::class)],
            'guardian.email' => ['required', 'email:rfc', 'max:120'],
            'guardian.phone' => ['required', 'regex:'.self::PHONE],

            'address.street' => ['required', 'string', 'max:120'],
            'address.city' => ['required', 'string', 'max:60'],
            'address.state' => ['required', 'alpha', 'size:2'],
            'address.zip' => ['required', 'regex:/^\d{5}(-\d{4})?$/'],

            'second_guardian.name' => ['nullable', 'string', 'max:120'],
            'second_guardian.email' => ['nullable', 'email:rfc', 'max:120'],
            'second_guardian.phone' => ['nullable', 'regex:'.self::PHONE],

            'emergency.name' => ['required', 'string', 'max:120'],
            'emergency.relationship' => ['required', 'string', 'max:40'],
            'emergency.phone' => ['required', 'regex:'.self::PHONE],

            'media_release' => ['required', 'in:yes,no'],
            'accepts_liability_waiver' => ['accepted'],
            'accepts_terms' => ['accepted'],
            'signature_name' => ['required', 'string', 'max:120'],
            'marketing_opt_in' => ['boolean'],
        ];

        if ($isPaid) {
            return [
                ...$rules,
                'plan' => ['required', 'in:monthly,annual'],
                'players.*.needs_uniform' => ['boolean'],
                'players.*.shirt_size' => ['nullable', 'required_if_accepted:players.*.needs_uniform', Rule::in(self::SHIRT_SIZES)],
                'accepts_refund_policy' => ['accepted'],
            ];
        }

        return [
            ...$rules,
            'preferred_training_date' => ['required', Rule::in(array_map(
                fn (CarbonImmutable $date): string => $date->toDateString(),
                TrainingCalendar::upcoming(),
            ))],
        ];
    }

    /**
     * The child must be in one of the club's groups (U6…U14).
     */
    private function ageGroupRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $date = is_string($value) ? CarbonImmutable::createFromFormat('Y-m-d', $value) : false;

            if ($date && AgeGroup::fromConfig()->forBirthDate($date) === null) {
                $fail(__('registration.validation.age_group', AgeGroup::fromConfig()->birthYears()));
            }
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $rules = ['required', 'required_if_accepted', 'email', 'max', 'min', 'in', 'enum', 'date_format', 'before', 'regex', 'accepted', 'alpha', 'size', 'array', 'boolean', 'string'];
        $messages = collect($rules)->mapWithKeys(fn (string $rule): array => [$rule => __("registration.validation.{$rule}")])->all();

        return [
            ...$messages,
            'website.max' => __('registration.validation.honeypot'),
            'guardian.phone.regex' => __('registration.validation.phone'),
            'second_guardian.phone.regex' => __('registration.validation.phone'),
            'emergency.phone.regex' => __('registration.validation.phone'),
            'address.zip.regex' => __('registration.validation.zip'),
            'players.min' => __('registration.validation.players'),
            'players.required' => __('registration.validation.players'),
            'players.*.date_of_birth.before' => __('registration.validation.birth_past'),
            'preferred_training_date.in' => __('registration.validation.training_date'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $fields = __('registration.fields');
        $named = fn (string $group, string $field): string => __("registration.attributes.{$group}", ['field' => mb_strtolower($fields[$field])]);

        // Who each field belongs to, so the error summary is unambiguous
        // (":position" is the player's number, from 1).
        return [
            'players.*.first_name' => $named('player', 'first_name'),
            'players.*.last_name' => $named('player', 'last_name'),
            'players.*.date_of_birth' => $named('player', 'date_of_birth'),
            'players.*.gender' => $named('player', 'gender'),
            'players.*.medical_notes' => $named('player', 'medical_notes'),
            'players.*.shirt_size' => $named('player', 'shirt_size'),
            'guardian.first_name' => $named('guardian', 'first_name'),
            'guardian.last_name' => $named('guardian', 'last_name'),
            'guardian.relationship' => $named('guardian', 'relationship'),
            'guardian.email' => $named('guardian', 'email'),
            'guardian.phone' => $named('guardian', 'phone'),
            'address.street' => $fields['street'],
            'address.city' => $fields['city'],
            'address.state' => $fields['state'],
            'address.zip' => $fields['zip'],
            'second_guardian.name' => $named('second_guardian', 'full_name'),
            'second_guardian.email' => $named('second_guardian', 'email'),
            'second_guardian.phone' => $named('second_guardian', 'phone'),
            'emergency.name' => $named('emergency', 'full_name'),
            'emergency.relationship' => $named('emergency', 'relationship'),
            'emergency.phone' => $named('emergency', 'phone'),
            'media_release' => $fields['media_release'],
            'accepts_liability_waiver' => $fields['liability_waiver'],
            'accepts_terms' => $fields['terms'],
            'accepts_refund_policy' => $fields['refund_policy'],
            'signature_name' => $fields['signature'],
            'preferred_training_date' => $fields['training_date'],
            'plan' => $fields['plan'],
        ];
    }
}
