<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Club details (footer, contact)
    |--------------------------------------------------------------------------
    |
    | Pending from the client: the legal name (for the © line and the legal
    | texts), the contact email and phone. While email or phone are empty they
    | are not shown.
    |
    */

    'name' => 'Georgia Wolves Soccer Club',

    'legal_name' => env('CLUB_LEGAL_NAME', 'Georgia Wolves Soccer Club'),

    'email' => env('CLUB_EMAIL'),

    'phone' => env('CLUB_PHONE'),

    'address' => [
        'venue' => 'South Gwinnett High School',
        'street' => '2288 Main St E',
        'locality' => 'Snellville',
        'region' => 'GA',
        'postal_code' => '30078',
    ],

    'maps_url' => 'https://www.google.com/maps/search/?api=1&query=South+Gwinnett+High+School%2C+2288+Main+St+E%2C+Snellville%2C+GA+30078',

    /*
    |--------------------------------------------------------------------------
    | Club contact details
    |--------------------------------------------------------------------------
    |
    | WhatsApp number in international format without "+" or spaces
    | (e.g. 14045550123), used for the wa.me links. Pending from the client:
    | while it is empty, WhatsApp links point to the contact page.
    |
    */

    'whatsapp_number' => env('CLUB_WHATSAPP_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Social profiles
    |--------------------------------------------------------------------------
    |
    | Full URLs of the club's profiles, shown as icons in the home hero (and
    | later in the footer). Pending from the client: a network without a URL is
    | not shown. WhatsApp reuses the number above.
    |
    */

    'partners' => [
        // Kit partner and sponsor of the home strip "Associated leagues and partners".
        // Pending from the client: while `logo` (a public path) is null the pill shows
        // the name in a placeholder circle; while `url` is null it is not a link.
        ['name' => 'Joma', 'initials' => 'JOMA', 'role' => 'kit_partner', 'logo' => null, 'url' => null],
        ['name' => 'CER', 'initials' => 'CER', 'role' => 'sponsor', 'logo' => null, 'url' => null],
    ],

    'social' => [
        'instagram' => env('CLUB_INSTAGRAM_URL'),
        'facebook' => env('CLUB_FACEBOOK_URL'),
        'whatsapp' => env('CLUB_WHATSAPP_NUMBER') ? 'https://wa.me/'.env('CLUB_WHATSAPP_NUMBER') : null,
    ],

];
