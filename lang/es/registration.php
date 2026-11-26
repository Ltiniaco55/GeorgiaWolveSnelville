<?php

return [
    'meta_title' => [
        'free_trial' => 'Reserva un entrenamiento gratis · Georgia Wolves Soccer Club Snellville',
        'paid' => 'Inscripción · Georgia Wolves Soccer Club Snellville',
        'received' => 'Inscripción recibida · Georgia Wolves Soccer Club Snellville',
    ],
    'back_pricing' => 'Volver a las tarifas',
    'eyebrow' => [
        'free_trial' => 'Entrenamiento gratis',
        'paid' => 'Inscripción',
    ],
    'title' => [
        'free_trial' => ['lead' => 'Tu primer entrenamiento', 'accent' => 'es gratis.'],
        'paid' => ['lead' => 'Únete a la', 'accent' => 'manada.'],
    ],
    'intro' => [
        'free_trial' => 'Elige un día, cuéntanos sobre tu hijo y ven con tacos, espinilleras y agua. Sin tarjeta.',
        'paid' => 'Cuéntanos sobre tus jugadores y sobre ti. Te enviaremos un email con los siguientes pasos; el pago es un enlace seguro de Stripe, nunca vemos tu tarjeta.',
    ],
    'required_note' => 'Los campos con * son obligatorios.',
    'errors_title' => 'Revisa estos campos:',

    'steps' => [
        'plan' => 'Plan',
        'date' => 'Día de entrenamiento',
        'players' => 'Jugadores',
        'guardian' => 'Padre, madre o tutor',
        'emergency' => 'Contacto de emergencia',
        'media' => 'Fotos y vídeos',
        'agreements' => 'Acuerdos',
    ],

    'plan' => [
        'label' => 'Elige un plan',
        'monthly' => 'Mensual',
        'annual' => 'Anual',
        'per_month' => '/mes',
        'per_year' => '/año',
        'monthly_today' => 'Si te apuntas hoy: :amount hoy y el primer cobro mensual el :date.',
        'annual_today' => 'Un solo pago al año. La misma regla del día :day para la fecha de inicio.',
        'uniform' => 'Uniforme :amount por cada jugador que lo necesite (pago único).',
    ],

    'date' => [
        'label' => 'Elige un día de entrenamiento',
        'hint' => ':time en South Gwinnett High School, 2288 Main St E, Snellville.',
        'time' => '18:00–19:30',
    ],

    'players' => [
        'player' => 'Jugador :number',
        'add' => 'Añadir otro jugador',
        'remove' => 'Quitar el jugador :number',
        'birth_hint' => 'Niños y niñas nacidos entre :from y :to (U6–U14).',
        'medical_hint' => 'Alergias, condiciones o medicación que el entrenador deba conocer. Opcional, se guarda cifrado.',
        'needs_uniform' => 'Necesita el uniforme del club',
        'gender_options' => [
            '' => 'Prefiero no decirlo',
            'boy' => 'Niño',
            'girl' => 'Niña',
        ],
    ],

    'guardian' => [
        'relationship_options' => [
            'mother' => 'Madre',
            'father' => 'Padre',
            'legal_guardian' => 'Tutor legal',
            'other' => 'Otro',
        ],
        'second' => 'Segundo padre, madre o tutor (opcional)',
    ],

    'emergency' => [
        'hint' => 'Alguien a quien llamar si no podemos localizarte durante una sesión.',
    ],

    'media' => [
        'question' => '¿Puede el club publicar fotos y vídeos en los que aparezca tu hijo (web, redes sociales)?',
        'yes' => 'Sí, lo autorizo',
        'no' => 'No, prefiero que no se publiquen imágenes de mi hijo',
        'more' => 'Leer la autorización de imagen',
    ],

    'agreements' => [
        'liability_waiver' => 'He leído y acepto la :link.',
        'terms' => 'Acepto los :link.',
        'refund_policy' => 'Acepto la :link.',
        'signature_hint' => 'Escribe tu nombre completo como firma.',
        'marketing' => 'Quiero recibir noticias del club por email o WhatsApp (opcional).',
        'opens_new_tab' => 'se abre en una pestaña nueva',
    ],

    'submit' => [
        'free_trial' => 'Reservar mi entrenamiento gratis',
        'paid' => 'Enviar inscripción',
        'sending' => 'Enviando…',
    ],

    'fields' => [
        'first_name' => 'Nombre',
        'last_name' => 'Apellidos',
        'full_name' => 'Nombre completo',
        'date_of_birth' => 'Fecha de nacimiento',
        'gender' => 'Sexo',
        'medical_notes' => 'Notas médicas',
        'shirt_size' => 'Talla del uniforme',
        'relationship' => 'Relación con el jugador',
        'email' => 'Email',
        'phone' => 'Teléfono',
        'street' => 'Dirección',
        'city' => 'Ciudad',
        'state' => 'Estado',
        'zip' => 'Código postal',
        'media_release' => 'Fotos y vídeos',
        'liability_waiver' => 'exención de responsabilidad',
        'terms' => 'términos',
        'refund_policy' => 'política de reembolsos',
        'signature' => 'Firma (nombre completo)',
        'training_date' => 'Día de entrenamiento',
        'plan' => 'Plan',
    ],

    'attributes' => [
        'player' => ':field del jugador :position',
        'guardian' => ':field del padre, madre o tutor',
        'second_guardian' => ':field del segundo padre, madre o tutor',
        'emergency' => ':field del contacto de emergencia',
    ],

    'validation' => [
        'required' => 'Rellena el campo :attribute.',
        'required_if_accepted' => 'Elige :attribute.',
        'email' => 'Escribe un email válido.',
        'max' => 'El campo :attribute es demasiado largo.',
        'min' => 'El campo :attribute es demasiado corto.',
        'in' => 'Elige una opción válida en :attribute.',
        'enum' => 'Elige una opción válida en :attribute.',
        'date_format' => 'Escribe una fecha válida en :attribute.',
        'before' => 'El campo :attribute debe ser una fecha pasada.',
        'regex' => 'El formato de :attribute no es válido.',
        'accepted' => 'Tienes que aceptar: :attribute.',
        'alpha' => 'Usa el código de 2 letras en :attribute (por ejemplo, GA).',
        'size' => 'Usa el código de 2 letras en :attribute (por ejemplo, GA).',
        'array' => 'Revisa el campo :attribute.',
        'boolean' => 'Revisa el campo :attribute.',
        'string' => 'Revisa el campo :attribute.',
        'phone' => 'Escribe un teléfono de al menos 10 dígitos.',
        'zip' => 'Escribe un código postal de 5 dígitos.',
        'players' => 'Añade al menos un jugador.',
        'birth_past' => 'La fecha de nacimiento debe ser pasada.',
        'age_group' => 'Nuestros grupos son para niños nacidos entre :from y :to (U6–U14).',
        'training_date' => 'Elige uno de los días de entrenamiento que se muestran.',
        'honeypot' => 'Algo ha fallado. Inténtalo de nuevo.',
    ],

    'received' => [
        'eyebrow' => 'Gracias',
        'title' => ['lead' => 'Bienvenido a la', 'accent' => 'manada.'],
        'free_trial' => 'Tu entrenamiento gratis está reservado para el :date. Te hemos enviado un email con los detalles. Trae tacos, espinilleras y agua.',
        'paid' => 'Hemos recibido la inscripción en el plan :plan. Te enviaremos por email los siguientes pasos y el enlace de pago seguro.',
        'players' => 'Jugadores',
        'category' => ':name · :category',
        'generic' => 'Hemos recibido tu inscripción y te hemos enviado un email con los detalles.',
        'back_home' => 'Volver al inicio',
    ],

    'mail_date_format' => 'l j \\d\\e F',

    'mail' => [
        'club_subject' => 'Nueva inscripción: :type · :name',
        'club_intro' => 'Se ha recibido una inscripción nueva.',
        'guardian_subject' => 'Hemos recibido tu inscripción · Georgia Wolves',
        'guardian_greeting' => 'Hola, :name:',
        'guardian_free_trial' => 'Tu entrenamiento gratis está reservado para el :date (18:00–19:30) en South Gwinnett High School, 2288 Main St E, Snellville. Trae tacos, espinilleras y agua.',
        'guardian_paid' => 'Gracias por inscribirte en el plan :plan. Pronto te enviaremos los siguientes pasos y el enlace de pago seguro.',
        'type' => 'Tipo',
        'guardian' => 'Padre, madre o tutor',
        'players' => 'Jugadores',
        'training_date' => 'Día de entrenamiento',
        'media_release' => 'Autorización de imagen',
        'yes' => 'Sí',
        'no' => 'No',
        'reference' => 'Referencia',
        'sensitive_note' => 'Los teléfonos, la dirección, las fechas de nacimiento y las notas médicas se guardan cifrados y no van en este email.',
        'types' => [
            'free_trial' => 'Entrenamiento gratis',
            'monthly' => 'Plan mensual',
            'annual' => 'Plan anual',
        ],
        'signoff' => 'Nos vemos en el campo,',
    ],
];
