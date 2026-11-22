<?php

/*
 * URL slugs of the localized routes (docs/design/sitemap.md), without the locale prefix.
 * Keys are route names: Localization::slug('about', 'en') returns "/about".
 */
return [
    'home' => '/',
    'about' => 'about',
    'programs' => 'programs',
    'teams' => 'teams',
    'leagues' => 'leagues',
    'league' => 'leagues/{league}',
    'league_thanks' => 'leagues/{league}/thanks',
    'gallery' => 'gallery',
    'pricing' => 'pricing',
    'register' => 'register',
    'register_success' => 'register/success',
    'register_cancelled' => 'register/cancelled',
    'free_training' => 'free-training',
    'free_training_thanks' => 'free-training/thanks',
    'contact' => 'contact',
    'contact_thanks' => 'contact/thanks',
    'faq' => 'faq',
    'privacy' => 'privacy',
    'terms' => 'terms',
    'refund_policy' => 'refund-policy',
    'liability_waiver' => 'liability-waiver',
    'media_release' => 'media-release',
    'accessibility' => 'accessibility',
];
