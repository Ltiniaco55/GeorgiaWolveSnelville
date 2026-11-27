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
            ->where('translations.home.hero.title_accent', 'wolf.')
            ->where('translations.brand.logos.crest', 'Georgia Wolves Soccer Club crest')
        );
});

test('the root template links the favicons and the social image', function () {
    $this->get('/')
        ->assertSee('/favicon.ico', false)
        ->assertSee('/apple-touch-icon.png', false)
        ->assertSee(url('/og-image.png'), false);
});

test('the intro is not forced by default', function () {
    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('forceIntro', false));
});

test('?intro=1 forces the intro in the local environment', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->get('/?intro=1')
        ->assertInertia(fn (Assert $page) => $page->where('forceIntro', true));
});

test('?intro=1 does nothing outside the local environment', function () {
    app()->detectEnvironment(fn (): string => 'production');

    $this->get('/?intro=1')
        ->assertInertia(fn (Assert $page) => $page->where('forceIntro', false));
});

test('pages share the navigation URLs of their locale; some entries are sections of the home', function (string $url, array $expected) {
    $this->get($url)
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentPage', 'home')
            ->where('navigation.home', $expected['home'])
            ->where('navigation.programs', $expected['programs'])
            ->where('navigation.register', $expected['register'])
            ->where('navigation.free_training', $expected['free_training'])
            ->where('navigation.faq', $expected['faq'])
            ->where('navigation.pricing', $expected['pricing'])
            ->where('navigation.about', $expected['about'])
            ->where('navigation.leagues', $expected['leagues'])
            ->missing('navigation.teams')
            ->missing('navigation.contact')
        );
})->with([
    'English' => ['/', [
        'home' => 'http://localhost',
        'programs' => 'http://localhost/programs',
        'register' => 'http://localhost/register',
        'free_training' => 'http://localhost/free-training',
        'faq' => 'http://localhost#faq',
        'pricing' => 'http://localhost#pricing',
        'about' => 'http://localhost#coach',
        'leagues' => 'http://localhost#leagues',
    ]],
    'Spanish' => ['/es', [
        'home' => 'http://localhost/es',
        'programs' => 'http://localhost/es/programas',
        'register' => 'http://localhost/es/inscripcion',
        'free_training' => 'http://localhost/es/entrenamiento-gratis',
        'faq' => 'http://localhost/es#faq',
        'pricing' => 'http://localhost/es#pricing',
        'about' => 'http://localhost/es#coach',
        'leagues' => 'http://localhost/es#leagues',
    ]],
]);

test('there is no WhatsApp link until there is a number', function () {
    config(['club.whatsapp_number' => null]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('whatsappUrl', null));
});

test('the WhatsApp link opens wa.me with the club number', function () {
    config(['club.whatsapp_number' => '14045550123']);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('whatsappUrl', 'https://wa.me/14045550123'));
});

test('the club Instagram and WhatsApp are shared, and Facebook stays hidden until it has a URL', function () {
    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('socialLinks', [
            ['network' => 'instagram', 'url' => 'https://www.instagram.com/georgiawolvessnellville/'],
            ['network' => 'whatsapp', 'url' => 'https://wa.me/16898087259'],
        ]));
});

test('only the social networks with a URL are shared', function () {
    config(['club.social' => [
        'instagram' => 'https://instagram.com/georgiawolves',
        'facebook' => null,
        'whatsapp' => 'https://wa.me/14045550123',
    ]]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('socialLinks', [
            ['network' => 'instagram', 'url' => 'https://instagram.com/georgiawolves'],
            ['network' => 'whatsapp', 'url' => 'https://wa.me/14045550123'],
        ]));
});

test('no social links are shared while the club has none', function () {
    config(['club.social' => ['instagram' => null, 'facebook' => null, 'whatsapp' => null]]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('socialLinks', []));
});
