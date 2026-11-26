<x-mail::message>
{{ __('registration.mail.guardian_greeting', ['name' => $registration->guardian_first_name]) }}

@if ($registration->type->isPaid())
{{ __('registration.mail.guardian_paid', ['plan' => __('registration.mail.types.'.$registration->type->value)]) }}
@else
{{ __('registration.mail.guardian_free_trial', ['date' => $registration->preferred_training_date->locale($registration->locale)->translatedFormat(__('registration.mail_date_format'))]) }}
@endif

**{{ __('registration.mail.players') }}**

@foreach ($registration->players as $player)
- {{ $player->first_name }} {{ $player->last_name }} · {{ $player->category }}
@endforeach

{{ __('registration.mail.reference') }}: {{ $registration->reference }}

{{ __('registration.mail.signoff') }}<br>
{{ config('club.name') }}
</x-mail::message>
