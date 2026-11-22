<?php

/*
 * Slugs de las rutas localizadas (docs/design/sitemap.md), sin el prefijo /es.
 * Las claves son los nombres de ruta: Localization::slug('about', 'es') devuelve "/nosotros".
 */
return [
    'home' => '/',
    'about' => 'nosotros',
    'programs' => 'programas',
    'teams' => 'equipos',
    'leagues' => 'ligas',
    'league' => 'ligas/{league}',
    'league_thanks' => 'ligas/{league}/gracias',
    'gallery' => 'galeria',
    'pricing' => 'precios',
    'register' => 'inscripcion',
    'register_success' => 'inscripcion/completada',
    'register_cancelled' => 'inscripcion/cancelada',
    'free_training' => 'entrenamiento-gratis',
    'free_training_thanks' => 'entrenamiento-gratis/gracias',
    'contact' => 'contacto',
    'contact_thanks' => 'contacto/gracias',
    'faq' => 'preguntas-frecuentes',
    'privacy' => 'privacidad',
    'terms' => 'terminos',
    'refund_policy' => 'politica-de-reembolso',
    'liability_waiver' => 'exencion-de-responsabilidad',
    'media_release' => 'autorizacion-de-imagen',
    'accessibility' => 'accesibilidad',
];
