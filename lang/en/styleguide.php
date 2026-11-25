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

    'cards' => [
        'title' => 'Neumorphic cards',
        'intro' => '<NeuCard surface="light|dark" variant="raised|inset|featured" hover="expand|tilt|none">. Sample content: prices, career and stats are placeholders until the client confirms them.',
        'variants_title' => 'Variants',
        'variant_raised' => 'Raised',
        'variant_inset' => 'Inset',
        'variant_featured' => 'Featured',
        'hint_expand' => 'Move over a card',
        'hint_tilt' => 'Move over the card',

        'pricing' => [
            'title' => 'Pricing',
            'free_trial' => [
                'name' => 'Free trial',
                'price' => '$0',
                'period' => 'first session',
                'saving' => 'No card needed',
                'description' => 'Come to one training session and meet the coaches before you decide.',
                'features' => ['One full training session', 'All categories, U6 to U14', 'Talk to the coach afterwards'],
                'cta' => 'Book a free training',
            ],
            'monthly' => [
                'name' => 'Monthly',
                'price' => '$100',
                'period' => '/ month',
                'saving' => 'Cancel any time',
                'description' => 'Pay month by month during the season.',
                'features' => ['Two training sessions a week', 'League matches', 'Coach feedback', 'Cancel any time'],
                'cta' => 'Join monthly',
            ],
            'annual' => [
                'name' => 'Annual',
                'badge' => 'Best value',
                'price' => '$1,000',
                'period' => '/ year',
                'saving' => 'Save $200',
                'description' => 'One payment for the whole season, two months free.',
                'features' => ['Everything in Monthly', 'Two months free', 'Priority for tournaments', 'Season photo pack'],
                'cta' => 'Join for the year',
            ],
            'billing_note' => 'Join from the 1st to the 15th and you pay this month now. From the 16th on, you pay $0 today and your first charge is on the 1st.',
            'uniform_note' => 'Uniform: $80, one-time payment, the first time you join.',
        ],

        'coach' => [
            'title' => 'Coach',
            'role' => 'Head coach',
            'name' => 'Christian Tiniaco',
            'initials' => 'CT',
            'photo_alt' => 'Photo of Christian Tiniaco (coming soon)',
            'licence' => 'UEFA PRO License',
            'bio' => 'Coach with a UEFA PRO License and experience in academies in three countries. He builds players who think fast, work as a team and enjoy the game.',
            'stats' => [
                ['value' => 'UEFA', 'label' => 'PRO License'],
                ['value' => '3', 'label' => 'Countries'],
                ['value' => '4', 'label' => 'Academies'],
            ],
            'coaching_title' => 'Coaching career',
            'coaching' => [
                ['period' => '2024 – now', 'title' => 'Head coach', 'place' => 'Georgia Wolves SC, Snellville'],
                ['period' => '2019 – 2024', 'title' => 'Academy coach', 'place' => 'Sample academy A'],
                ['period' => '2015 – 2019', 'title' => 'Assistant coach', 'place' => 'Sample academy B'],
            ],
            'playing_title' => 'Playing career',
            'playing' => ['Sample club A', 'Sample club B', 'Sample club C'],
            'primary_cta' => 'Train with Christian',
            'secondary_cta' => 'Read full bio',
        ],
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

    'stage' => [
        'title' => 'Hero stage · 3D jersey',
        'intro' => '<HeroStage mode="jersey">: the spinning jersey in a 4:5 pressed-in stage. Not in the home for now (the hero shows a photo); kept for later.',
    ],
];
