<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * To the parent or guardian, in the language of the form: what was received and
 * what comes next.
 */
class RegistrationConfirmation extends Mailable
{
    public function __construct(public Registration $registration)
    {
        $this->locale($registration->locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('registration.mail.guardian_subject'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.registration-confirmation');
    }
}
