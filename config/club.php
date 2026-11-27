<?php

$whatsappNumber = env('CLUB_WHATSAPP_NUMBER') ?: '16898087259';

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
    | (e.g. 14045550123), used for the wa.me links. While it is empty, the
    | WhatsApp links are not shown. It is the club's number: pending
    | confirmation from the client that it is the one on WhatsApp.
    |
    */

    'whatsapp_number' => $whatsappNumber,

    /*
    |--------------------------------------------------------------------------
    | Social profiles
    |--------------------------------------------------------------------------
    |
    | Full URLs of the club's profiles, shown as icons in the home hero notch
    | and in the footer. A network without a URL is not shown (Facebook is
    | pending from the client). WhatsApp reuses the number above.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Registrations
    |--------------------------------------------------------------------------
    |
    | - registrations_email: who receives new registrations (falls back to the
    |   club email, then to MAIL_FROM_ADDRESS).
    | - training_days: ISO weekdays of the sessions (1 = Monday): the free
    |   training form offers the next dates on these days.
    | - season_end_year and age_groups: a child's group is the smallest group
    |   that is at least (season_end_year − birth year), US Soccer style
    |   (born 2017 in the 2026–27 season → U10). Pending confirmation by the club.
    |
    */

    'registrations_email' => env('CLUB_REGISTRATIONS_EMAIL'),

    'training_days' => [2, 4],

    'season_end_year' => 2027,

    'age_groups' => [6, 8, 10, 12, 14],

    'leagues' => [
        // Crests of the home strip "Associated leagues and partners" and the footer.
        // The leagues have no pages on this site: they link to their Instagram.
        ['slug' => 'liga-interamericana', 'name' => 'Liga Interamericana', 'url' => 'https://www.instagram.com/ligainteramericana/'],
        ['slug' => 'gysl', 'name' => 'Gwinnett Youth Soccer League', 'url' => 'https://www.instagram.com/gwinnettfutbol/'],
    ],

    'partners' => [
        // Kit partner and sponsor of the home strip "Associated leagues and partners".
        // Pending from the client: while `logo` (a public path) is null the pill shows
        // the name in a placeholder circle; while `url` is null it is not a link.
        ['name' => 'Joma', 'initials' => 'JOMA', 'role' => 'kit_partner', 'logo' => null, 'url' => null],
        ['name' => 'CER', 'initials' => 'CER', 'role' => 'sponsor', 'logo' => null, 'url' => null],
    ],

    'social' => [
        'instagram' => env('CLUB_INSTAGRAM_URL') ?: 'https://www.instagram.com/georgiawolvessnellville/',
        'facebook' => env('CLUB_FACEBOOK_URL') ?: null,
        'whatsapp' => 'https://wa.me/'.$whatsappNumber,
    ],

];
