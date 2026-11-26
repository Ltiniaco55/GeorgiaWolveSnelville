<?php

return [
    'meta_title' => [
        'free_trial' => 'Book a free training · Georgia Wolves Soccer Club Snellville',
        'paid' => 'Register · Georgia Wolves Soccer Club Snellville',
        'received' => 'Registration received · Georgia Wolves Soccer Club Snellville',
    ],
    'back_pricing' => 'Back to pricing',
    'eyebrow' => [
        'free_trial' => 'Free training',
        'paid' => 'Registration',
    ],
    'title' => [
        'free_trial' => ['lead' => 'Your first training is', 'accent' => 'on us.'],
        'paid' => ['lead' => 'Join the', 'accent' => 'pack.'],
    ],
    'intro' => [
        'free_trial' => 'Pick a day, tell us about your child and come with cleats, shin guards and water. No card needed.',
        'paid' => 'Tell us about your players and yourself. You will get an email with the next steps; payment is a secure Stripe link, we never see your card.',
    ],
    'required_note' => 'Fields marked * are required.',
    'errors_title' => 'Please check these fields:',

    'steps' => [
        'plan' => 'Plan',
        'date' => 'Training day',
        'players' => 'Players',
        'guardian' => 'Parent or guardian',
        'emergency' => 'Emergency contact',
        'media' => 'Photos and videos',
        'agreements' => 'Agreements',
    ],

    'plan' => [
        'label' => 'Choose a plan',
        'monthly' => 'Monthly',
        'annual' => 'Annual',
        'per_month' => '/month',
        'per_year' => '/year',
        'monthly_today' => 'Joining today: :amount today, first monthly charge on :date.',
        'annual_today' => 'One payment for the year. Same :day-of-the-month rule for your start date.',
        'uniform' => 'Uniform :amount per player who needs it (one-time payment).',
    ],

    'date' => [
        'label' => 'Choose a training day',
        'hint' => ':time at South Gwinnett High School, 2288 Main St E, Snellville.',
        'time' => '6–7:30 pm',
    ],

    'players' => [
        'player' => 'Player :number',
        'add' => 'Add another player',
        'remove' => 'Remove player :number',
        'birth_hint' => 'Boys and girls born :from–:to (U6–U14).',
        'medical_hint' => 'Allergies, conditions or medication the coach should know. Optional, kept encrypted.',
        'needs_uniform' => 'Needs the club uniform',
        'gender_options' => [
            '' => 'Prefer not to say',
            'boy' => 'Boy',
            'girl' => 'Girl',
        ],
    ],

    'guardian' => [
        'relationship_options' => [
            'mother' => 'Mother',
            'father' => 'Father',
            'legal_guardian' => 'Legal guardian',
            'other' => 'Other',
        ],
        'second' => 'Second parent or guardian (optional)',
    ],

    'emergency' => [
        'hint' => 'Someone we can call if we cannot reach you during a session.',
    ],

    'media' => [
        'question' => 'May the club publish photos and videos where your child appears (website, social media)?',
        'yes' => 'Yes, I allow it',
        'no' => 'No, please do not publish images of my child',
        'more' => 'Read the Media Release',
    ],

    'agreements' => [
        'liability_waiver' => 'I have read and accept the :link.',
        'terms' => 'I accept the :link.',
        'refund_policy' => 'I accept the :link.',
        'signature_hint' => 'Type your full name as your signature.',
        'marketing' => 'Send me club news by email or WhatsApp (optional).',
        'opens_new_tab' => 'opens in a new tab',
    ],

    'submit' => [
        'free_trial' => 'Book my free training',
        'paid' => 'Send registration',
        'sending' => 'Sending…',
    ],

    'fields' => [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'full_name' => 'Full name',
        'date_of_birth' => 'Date of birth',
        'gender' => 'Gender',
        'medical_notes' => 'Medical notes',
        'shirt_size' => 'Uniform size',
        'relationship' => 'Relationship to the player',
        'email' => 'Email',
        'phone' => 'Phone',
        'street' => 'Street address',
        'city' => 'City',
        'state' => 'State',
        'zip' => 'ZIP code',
        'media_release' => 'Photos and videos',
        'liability_waiver' => 'Liability Waiver',
        'terms' => 'Terms',
        'refund_policy' => 'Refund Policy',
        'signature' => 'Signature (full name)',
        'training_date' => 'Training day',
        'plan' => 'Plan',
    ],

    'attributes' => [
        'player' => 'player :position :field',
        'guardian' => 'parent or guardian :field',
        'second_guardian' => 'second parent or guardian :field',
        'emergency' => 'emergency contact :field',
    ],

    'validation' => [
        'required' => 'Please fill in :attribute.',
        'required_if_accepted' => 'Please choose :attribute.',
        'email' => 'Please enter a valid email address.',
        'max' => ':attribute is too long.',
        'min' => ':attribute is too short.',
        'in' => 'Please choose a valid option for :attribute.',
        'enum' => 'Please choose a valid option for :attribute.',
        'date_format' => 'Please enter a valid date for :attribute.',
        'before' => ':attribute must be in the past.',
        'regex' => 'The format of :attribute is not valid.',
        'accepted' => 'You need to accept the :attribute.',
        'alpha' => 'Use the 2-letter code for :attribute (e.g. GA).',
        'size' => 'Use the 2-letter code for :attribute (e.g. GA).',
        'array' => 'Please check :attribute.',
        'boolean' => 'Please check :attribute.',
        'string' => 'Please check :attribute.',
        'phone' => 'Please enter a phone number with at least 10 digits.',
        'zip' => 'Please enter a 5-digit ZIP code.',
        'players' => 'Add at least one player.',
        'birth_past' => 'The date of birth must be in the past.',
        'age_group' => 'Our groups are for children born from :from to :to (U6–U14).',
        'training_date' => 'Please choose one of the training days shown.',
        'honeypot' => 'Something went wrong. Please try again.',
    ],

    'received' => [
        'eyebrow' => 'Thank you',
        'title' => ['lead' => 'Welcome to the', 'accent' => 'pack.'],
        'free_trial' => 'Your free training is booked for :date. We have sent you an email with the details. Bring cleats, shin guards and water.',
        'paid' => 'We have received the registration for the :plan plan. We will email you the next steps and the secure payment link.',
        'players' => 'Players',
        'category' => ':name · :category',
        'generic' => 'We have received your registration and sent you an email with the details.',
        'back_home' => 'Back to home',
    ],

    'mail_date_format' => 'l, F j',

    'mail' => [
        'club_subject' => 'New registration: :type · :name',
        'club_intro' => 'A new registration has been received.',
        'guardian_subject' => 'We received your registration · Georgia Wolves',
        'guardian_greeting' => 'Hi :name,',
        'guardian_free_trial' => 'Your free training is booked for :date (6–7:30 pm) at South Gwinnett High School, 2288 Main St E, Snellville. Bring cleats, shin guards and water.',
        'guardian_paid' => 'Thank you for registering for the :plan plan. We will send you the next steps and the secure payment link soon.',
        'type' => 'Type',
        'guardian' => 'Parent or guardian',
        'players' => 'Players',
        'training_date' => 'Training day',
        'media_release' => 'Media release',
        'yes' => 'Yes',
        'no' => 'No',
        'reference' => 'Reference',
        'sensitive_note' => 'Phones, address, dates of birth and medical notes are stored encrypted and are not included in this email.',
        'types' => [
            'free_trial' => 'Free training',
            'monthly' => 'Monthly plan',
            'annual' => 'Annual plan',
        ],
        'signoff' => 'See you on the pitch,',
    ],
];
