<?php

namespace App\Enums;

/**
 * Pending: received, the club has been told. The payment states arrive with
 * Stripe (phase 7).
 */
enum RegistrationStatus: string
{
    case Pending = 'pending';
}
