<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * To the club: a new registration. Reply-to is the parent or guardian. Sensitive
 * data (phones, address, dates of birth, medical notes) stays out of the email.
 */
class RegistrationSubmitted extends Mailable
{
    public function __construct(public Registration $registration) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('registration.mail.club_subject', [
                'type' => __("registration.mail.types.{$this->registration->type->value}"),
                'name' => $this->registration->guardianName(),
            ]),
            replyTo: [new Address($this->registration->guardian_email, $this->registration->guardianName())],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.registration-submitted');
    }
}
