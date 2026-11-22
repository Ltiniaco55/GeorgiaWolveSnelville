<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | The default locale (English) has no URL prefix. Every other locale is
    | served under /{locale} with translated slugs, which live in
    | lang/{locale}/routes.php. See docs/design/sitemap.md.
    |
    | The default is kept here, not read from app.locale, because
    | app()->setLocale() overwrites app.locale on every Spanish request.
    |
    */

    'locales' => ['en', 'es'],

    'default' => env('APP_LOCALE', 'en'),

];
