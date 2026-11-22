<?php

return [
    'meta_title' => 'Guía de estilo · Georgia Wolves',
    'title' => 'Guía de estilo',
    'intro' => 'Tokens de diseño y recursos de marca de la web de Georgia Wolves. Esta página solo existe en el entorno local.',

    'logos' => [
        'title' => 'Logos',
        'intro' => 'SVG en línea con <BrandLogo variant="…">. Las variantes monocromas usan currentColor.',
    ],

    'palette' => [
        'title' => 'Paleta de colores',
        'intro' => 'Tokens de Tailwind definidos con @theme en resources/css/app.css.',
        'colors' => [
            'white' => ['name' => 'Blanco', 'usage' => 'Color principal. Secciones claras y texto sobre negro.'],
            'black' => ['name' => 'Negro', 'usage' => 'Color principal. Intro, hero, secciones oscuras y texto.'],
            'charcoal' => ['name' => 'Carbón', 'usage' => 'Botones en hover, para que se vea el efecto hundido.'],
            'yellow' => ['name' => 'Amarillo', 'usage' => 'Solo acentos y CTA. Nunca como texto sobre blanco (contraste insuficiente).'],
            'neu-light' => ['name' => 'Neumórfico claro', 'usage' => 'Superficie de las secciones de cards claras.'],
            'neu-light-shade' => ['name' => 'Sombra clara', 'usage' => 'Sombra oscura sobre la superficie clara.'],
            'neu-light-highlight' => ['name' => 'Brillo claro', 'usage' => 'Sombra clara sobre la superficie clara.'],
            'neu-dark' => ['name' => 'Neumórfico oscuro', 'usage' => 'Superficie de las secciones de cards oscuras.'],
            'neu-dark-shade' => ['name' => 'Sombra oscura', 'usage' => 'Sombra oscura sobre la superficie oscura.'],
            'neu-dark-highlight' => ['name' => 'Brillo oscuro', 'usage' => 'Sombra clara sobre la superficie oscura.'],
        ],
    ],

    'shadows' => [
        'title' => 'Sombras neumórficas',
        'intro' => 'La card es del mismo color que su sección y se distingue solo por dos sombras exteriores. Nunca sobre blanco ni negro puros.',
        'light_surface' => 'Superficie clara #ECECEC',
        'dark_surface' => 'Superficie oscura #161616',
        'raised' => 'En relieve',
        'raised_hover' => 'En relieve, hover',
        'raised_sm' => 'En relieve, pequeña',
        'inset' => 'Hundida',
        'inset_sm' => 'Hundida, pequeña',
    ],

    'typography' => [
        'title' => 'Tipografía',
        'intro' => 'Barlow Condensed es provisional hasta tener la fuente del wordmark.',
        'display_800' => 'Barlow Condensed 800 · titulares',
        'display_700' => 'Barlow Condensed 700 · nombres y precios',
        'text_400' => 'Inter 400 · texto',
        'text_500' => 'Inter 500 · etiquetas',
        'text_600' => 'Inter 600 · botones',
        'sample_headline' => 'Entrena como un lobo.',
        'sample_name' => 'Christian Tiniaco · 100 $ / mes',
        'sample_body' => 'Niños y niñas de 5 a 14 años, de U6 a U14. El primer entrenamiento es gratis.',
        'sample_label' => 'Inscripciones abiertas · Temporada de otoño',
        'sample_button' => 'Reserva un entrenamiento gratis',
    ],
];
