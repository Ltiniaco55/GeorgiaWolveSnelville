<?php

namespace App\Enums;

/**
 * The three registration templates: a free training session, or joining on
 * the monthly or the annual plan (docs/design/sitemap.md).
 */
enum RegistrationType: string
{
    case FreeTrial = 'free_trial';
    case Monthly = 'monthly';
    case Annual = 'annual';

    /**
     * Whether this registration is for a paid plan (payment comes with Stripe, phase 7).
     */
    public function isPaid(): bool
    {
        return $this !== self::FreeTrial;
    }
}
