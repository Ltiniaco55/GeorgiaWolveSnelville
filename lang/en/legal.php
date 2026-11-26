<?php

/*
 * Legal pages. DRAFTS written for the website build: they must be reviewed by a
 * lawyer before going live. Items in [brackets] are pending from the club.
 */
return [
    'draft' => 'DRAFT – pending legal review. This text is a working draft and is not yet in force.',
    'updated' => 'Last updated: :date',
    'updated_date' => 'November 26, 2026',
    'contents' => 'Contents',
    'contact_title' => 'Questions about this page',
    'contact_body' => 'Write to us or visit us:',
    'meta_title' => ':title · Georgia Wolves Soccer Club Snellville',

    'documents' => [
        'privacy' => [
            'title' => 'Privacy Policy',
            'intro' => 'How Georgia Wolves Soccer Club collects, uses and protects the personal information of families and players.',
            'sections' => [
                ['id' => 'who', 'title' => 'Who we are', 'body' => [
                    'Georgia Wolves Soccer Club ("the club", "we") runs a youth soccer academy for boys and girls aged 5 to 14 in Snellville, Georgia. [Legal name and address of the entity pending.]',
                ]],
                ['id' => 'data', 'title' => 'Information we collect', 'body' => [
                    'From parents or guardians, when they book a free training or register: their name, relationship to the player, email, phone and address; a second parent or guardian and an emergency contact, if given.',
                    'About each player, provided by the parent or guardian: name, date of birth, gender (optional), uniform size and any medical notes the coach should know (optional).',
                    'We do not collect information directly from children. Payments are processed by Stripe: we never see or store card numbers.',
                ]],
                ['id' => 'use', 'title' => 'How we use it', 'body' => [
                    'To organise trainings and teams, place each child in the right age group, contact families, keep players safe (emergency contacts and medical notes) and manage registrations and payments.',
                    'We only send news or promotions if you ticked the optional box to receive them, and you can opt out at any time.',
                ]],
                ['id' => 'photos', 'title' => 'Photos and videos', 'body' => [
                    'Images of a child are only published if a parent or guardian said yes in the registration form. See the Media Release for details and how to withdraw consent.',
                ]],
                ['id' => 'security', 'title' => 'How we protect it', 'body' => [
                    'Phone numbers, addresses, dates of birth and medical notes are stored encrypted. Access is limited to the club staff who need it. The website uses HTTPS.',
                ]],
                ['id' => 'sharing', 'title' => 'Who we share it with', 'body' => [
                    'Only with service providers that help us run the club (payments with Stripe, email delivery, website hosting) and, when needed for a competition, with the league a team plays in. We do not sell personal information.',
                ]],
                ['id' => 'rights', 'title' => 'Your choices and rights', 'body' => [
                    'You can ask us to see, correct or delete your family’s information, or to stop using it for news, by contacting the club. We keep registration records for [period pending] and then delete them.',
                ]],
            ],
        ],

        'terms' => [
            'title' => 'Terms of Service',
            'intro' => 'The terms that apply when you use this website and register a player with the club.',
            'sections' => [
                ['id' => 'acceptance', 'title' => 'Acceptance', 'body' => [
                    'By booking a free training or registering a player you accept these terms, the Liability Waiver and, for paid plans, the Refund Policy.',
                ]],
                ['id' => 'registration', 'title' => 'Registration', 'body' => [
                    'The parent or legal guardian must be at least 18 and give true information. The club places each child in an age group (U6–U14) and may change the group after the free training or for safety reasons.',
                ]],
                ['id' => 'plans', 'title' => 'Plans and payments', 'body' => [
                    'Monthly plan: joining from the 1st to the 15th, the current month is paid when you join; joining after the 15th, the rest of the month is free and the first charge is on the 1st. Annual plan: one payment for the year, with the same rule for the start date. The uniform is a one-time payment.',
                    'Prices are shown on the website and may change for future periods with notice. Payments are processed by Stripe.',
                ]],
                ['id' => 'conduct', 'title' => 'Conduct', 'body' => [
                    'Players, families and guests must respect coaches, teammates, opponents and referees. The club may suspend participation in case of serious or repeated misconduct.',
                ]],
                ['id' => 'changes', 'title' => 'Schedule changes', 'body' => [
                    'Trainings may be moved or cancelled because of weather, field availability or safety. We will let families know as soon as possible.',
                ]],
                ['id' => 'law', 'title' => 'Governing law', 'body' => [
                    'These terms are governed by the laws of the State of Georgia, United States.',
                ]],
            ],
        ],

        'refund_policy' => [
            'title' => 'Refund Policy',
            'intro' => 'When and how payments to the club can be refunded.',
            'sections' => [
                ['id' => 'monthly', 'title' => 'Monthly plan', 'body' => [
                    'You can cancel at any time from the parent portal. The cancellation takes effect at the end of the month already paid; months already started are not refunded.',
                ]],
                ['id' => 'annual', 'title' => 'Annual plan', 'body' => [
                    '[Pending from the club: whether a part of the annual payment is refunded when cancelling, and how it is worked out.]',
                ]],
                ['id' => 'uniform', 'title' => 'Uniform', 'body' => [
                    'The uniform is made for the player and is not refundable once delivered, unless it has a manufacturing defect.',
                ]],
                ['id' => 'club', 'title' => 'Cancellations by the club', 'body' => [
                    'If the club cancels a program or a large part of it, families will get a refund or credit for the part not delivered.',
                ]],
                ['id' => 'how', 'title' => 'How to ask for a refund', 'body' => [
                    'Contact the club with the registration reference you received by email. Approved refunds go back to the original payment method within [number] business days.',
                ]],
            ],
        ],

        'liability_waiver' => [
            'title' => 'Liability Waiver',
            'intro' => 'Assumption of risk, release and medical authorization accepted by the parent or guardian when registering a player. It can be read here and is accepted inside the registration form, where the name, date and time are recorded.',
            'sections' => [
                ['id' => 'risk', 'title' => 'Assumption of risk', 'body' => [
                    'Soccer is a contact sport. I understand that taking part in trainings, matches and club events involves risks, including falls, collisions and injuries, and I accept them on behalf of my child.',
                ]],
                ['id' => 'health', 'title' => 'Health', 'body' => [
                    'I confirm that, to my knowledge, my child is fit to take part and I have told the club about any condition, allergy or medication the coach should know.',
                ]],
                ['id' => 'medical', 'title' => 'Medical authorization', 'body' => [
                    'If my child needs urgent care and I cannot be reached, I authorize the club staff to seek emergency medical treatment for my child. I will cover the costs of that care.',
                ]],
                ['id' => 'release', 'title' => 'Release', 'body' => [
                    'To the extent allowed by Georgia law, I release Georgia Wolves Soccer Club, its coaches, volunteers and venues from claims for injuries arising from ordinary participation, except those caused by gross negligence or willful misconduct.',
                ]],
                ['id' => 'rules', 'title' => 'Rules', 'body' => [
                    'My child will follow the instructions of the coaches and the club rules, and wear shin guards in every session.',
                ]],
            ],
        ],

        'media_release' => [
            'title' => 'Media Release',
            'intro' => 'How the club may use photos and videos where players appear, and how to change your choice.',
            'sections' => [
                ['id' => 'choice', 'title' => 'Your choice', 'body' => [
                    'In the registration form the parent or guardian answers yes or no. Without a yes, the club does not publish images where the child can be identified.',
                ]],
                ['id' => 'use', 'title' => 'Where images may appear', 'body' => [
                    'On this website (gallery, pages), the club’s social media and printed material about the club. Images are used to show the life of the club, never to sell to third parties.',
                ]],
                ['id' => 'privacy', 'title' => 'Privacy', 'body' => [
                    'We never publish a child’s full name, school, address or other personal details next to an image.',
                ]],
                ['id' => 'withdraw', 'title' => 'Changing your mind', 'body' => [
                    'You can withdraw consent at any time by contacting the club. We will remove the images from the website and our social media within [number] days; printed material already distributed cannot be recalled.',
                ]],
            ],
        ],

        'accessibility' => [
            'title' => 'Accessibility Statement',
            'intro' => 'Our commitment to making this website usable by everyone.',
            'sections' => [
                ['id' => 'standard', 'title' => 'Our goal', 'body' => [
                    'We aim to meet the Web Content Accessibility Guidelines (WCAG) 2.1, level AA.',
                ]],
                ['id' => 'features', 'title' => 'What we do', 'body' => [
                    'Semantic HTML, keyboard navigation with visible focus, sufficient color contrast, alternative text for images, forms with labels and clear error messages, and reduced motion for people who prefer it (no intro, animations or moving effects).',
                ]],
                ['id' => 'limits', 'title' => 'Known limitations', 'body' => [
                    'Some photos in the gallery are samples and may be replaced. If you find a barrier, please tell us.',
                ]],
                ['id' => 'feedback', 'title' => 'Feedback', 'body' => [
                    'If you have trouble using any part of the website, contact the club and we will help you and fix it as soon as possible.',
                ]],
            ],
        ],
    ],
];
