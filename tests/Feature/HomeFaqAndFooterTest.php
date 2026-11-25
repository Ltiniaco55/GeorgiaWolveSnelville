<?php

use App\Models\Faq;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Home FAQ prop (Faq model) and the footer data shared with every page.
 */
test('the home gets the published questions marked for it, in order, at most 6', function () {
    Faq::factory()->onHome()->unpublished()->create(['question' => ['en' => 'Hidden?', 'es' => '¿Oculta?']]);
    Faq::factory()->create(['question' => ['en' => 'Only on /faq?', 'es' => '¿Solo en /faq?']]);
    Faq::factory()->onHome()->create(['sort_order' => 2, 'question' => ['en' => 'Second?', 'es' => '¿Segunda?']]);
    Faq::factory()->onHome()->create(['sort_order' => 1, 'question' => ['en' => 'First?', 'es' => '¿Primera?']]);
    Faq::factory()->onHome()->count(6)->create(['sort_order' => 5]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->has('faqs', 6)
            ->where('faqs.0.question', 'First?')
            ->where('faqs.1.question', 'Second?')
        );
});

test('questions and answers fill the billing figures from config/billing.php, in the locale', function () {
    config(['billing.cutoff_day' => 10, 'billing.prices.monthly' => 12_000]);
    Faq::factory()->onHome()->create([
        'question' => ['en' => 'How does the :cutoff_day-of-the-month rule work?', 'es' => '¿Cómo funciona la regla del día :cutoff_day?'],
        'answer' => ['en' => 'Up to the :cutoff_day you pay :monthly_price now.', 'es' => 'Hasta el día :cutoff_day pagas :monthly_price ahora.'],
    ]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('faqs.0.question', 'How does the 10th-of-the-month rule work?')
            ->where('faqs.0.answer', 'Up to the 10th you pay $120 now.')
        );

    $this->get('/es')
        ->assertInertia(fn (Assert $page) => $page
            ->where('faqs.0.question', '¿Cómo funciona la regla del día 10?')
            ->where('faqs.0.answer', 'Hasta el día 10 pagas $120 ahora.')
        );
});

test('every page shares the club details and the footer links of its locale', function () {
    config(['club.legal_name' => 'Georgia Wolves SC LLC', 'club.email' => 'hello@example.com', 'club.phone' => null]);

    $this->get('/es')
        ->assertInertia(fn (Assert $page) => $page
            ->where('club.legalName', 'Georgia Wolves SC LLC')
            ->where('club.email', 'hello@example.com')
            ->where('club.phone', null)
            ->where('club.address.street', '2288 Main St E')
            ->where('navigation.privacy', 'http://localhost/es/privacidad')
            ->where('navigation.liga_interamericana', 'http://localhost/es/ligas/liga-interamericana')
            ->where('navigation.gysl', 'http://localhost/es/ligas/gysl')
        );
});
