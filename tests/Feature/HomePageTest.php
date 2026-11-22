<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the home page renders the Inertia Home component', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Home'));
});

test('pages share the locale and its translation groups', function () {
    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'en')
            ->where('translations.home.placeholder_title', 'Train like a wolf.')
            ->where('translations.brand.logos.crest', 'Georgia Wolves Soccer Club crest')
        );
});

test('pages share the Spanish translations when the locale is Spanish', function () {
    app()->setLocale('es');

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'es')
            ->where('translations.home.placeholder_title', 'Entrena como un lobo.')
        );
});

test('the root template links the favicons and the social image', function () {
    $this->get('/')
        ->assertSee('/favicon.ico', false)
        ->assertSee('/apple-touch-icon.png', false)
        ->assertSee(url('/og-image.png'), false);
});
