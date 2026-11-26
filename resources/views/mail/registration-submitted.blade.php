<x-mail::message>
{{ __('registration.mail.club_intro') }}

- **{{ __('registration.mail.type') }}:** {{ __('registration.mail.types.'.$registration->type->value) }}
- **{{ __('registration.mail.guardian') }}:** {{ $registration->guardianName() }} · {{ $registration->guardian_email }}
@if ($registration->preferred_training_date)
- **{{ __('registration.mail.training_date') }}:** {{ $registration->preferred_training_date->locale(app()->getLocale())->translatedFormat(__('registration.mail_date_format')) }}
@endif
- **{{ __('registration.mail.media_release') }}:** {{ $registration->media_release ? __('registration.mail.yes') : __('registration.mail.no') }}
- **{{ __('registration.mail.reference') }}:** {{ $registration->reference }}

**{{ __('registration.mail.players') }}**

@foreach ($registration->players as $player)
- {{ $player->first_name }} {{ $player->last_name }} · {{ $player->category }}
@endforeach

<x-mail::subcopy>
{{ __('registration.mail.sensitive_note') }}
</x-mail::subcopy>
</x-mail::message>
