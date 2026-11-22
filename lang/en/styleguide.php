<?php

return [
    'meta_title' => 'Styleguide · Georgia Wolves',
    'title' => 'Styleguide',
    'intro' => 'Design tokens and brand assets of the Georgia Wolves website. This page only exists in the local environment.',

    'logos' => [
        'title' => 'Logos',
        'intro' => 'Inline SVG through <BrandLogo variant="…">. Monochrome variants use currentColor.',
    ],

    'palette' => [
        'title' => 'Colour palette',
        'intro' => 'Tailwind tokens defined with @theme in resources/css/app.css.',
        'colors' => [
            'white' => ['name' => 'White', 'usage' => 'Main colour. Light sections and text on black.'],
            'black' => ['name' => 'Black', 'usage' => 'Main colour. Intro, hero, dark sections and text.'],
            'charcoal' => ['name' => 'Charcoal', 'usage' => 'Buttons on hover, so the inset effect stays visible.'],
            'yellow' => ['name' => 'Yellow', 'usage' => 'Accents and CTAs only. Never as text on white (contrast too low).'],
            'neu-light' => ['name' => 'Neumorphic light', 'usage' => 'Surface of light card sections.'],
            'neu-light-shade' => ['name' => 'Light shade', 'usage' => 'Dark shadow on the light surface.'],
            'neu-light-highlight' => ['name' => 'Light highlight', 'usage' => 'Light shadow on the light surface.'],
            'neu-dark' => ['name' => 'Neumorphic dark', 'usage' => 'Surface of dark card sections.'],
            'neu-dark-shade' => ['name' => 'Dark shade', 'usage' => 'Dark shadow on the dark surface.'],
            'neu-dark-highlight' => ['name' => 'Dark highlight', 'usage' => 'Light shadow on the dark surface.'],
        ],
    ],

    'shadows' => [
        'title' => 'Neumorphic shadows',
        'intro' => 'A card has the same colour as its section and only stands out through two outer shadows. Never on pure white or pure black.',
        'light_surface' => 'Light surface #ECECEC',
        'dark_surface' => 'Dark surface #161616',
        'raised' => 'Raised',
        'raised_hover' => 'Raised, hover',
        'raised_sm' => 'Raised, small',
        'inset' => 'Inset',
        'inset_sm' => 'Inset, small',
    ],

    'buttons' => [
        'title' => 'CTA button',
        'intro' => '<CtaButton variant="primary|secondary" surface="light|dark">. A circle that rolls open into a pill when it enters the screen and folds back when it leaves. Hover the buttons with the mouse, tab to them with the keyboard and scroll down.',
        'on_light' => 'On white',
        'on_dark' => 'On black',
        'sample_primary' => 'Book a free training',
        'sample_secondary' => 'View programs',
        'sample_long' => 'Register now',
        'sample_back' => 'Back to the buttons',
        'scroll_hint' => 'More buttons below: scroll down and up again to see them open and fold.',
        'scroll_band' => 'Scroll test :number',
    ],

    'typography' => [
        'title' => 'Typography',
        'intro' => 'Barlow Condensed is provisional until we have the wordmark font.',
        'display_800' => 'Barlow Condensed 800 · headlines',
        'display_700' => 'Barlow Condensed 700 · names and prices',
        'text_400' => 'Inter 400 · body text',
        'text_500' => 'Inter 500 · labels',
        'text_600' => 'Inter 600 · buttons',
        'sample_headline' => 'Train like a wolf.',
        'sample_name' => 'Christian Tiniaco · $100 / month',
        'sample_body' => 'Boys and girls from 5 to 14, U6 to U14. The first training session is free.',
        'sample_label' => 'Registration open · Fall season',
        'sample_button' => 'Book a free training',
    ],
];
