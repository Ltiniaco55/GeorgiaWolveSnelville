<?php

namespace Database\Seeders;

use App\Enums\FaqTopic;
use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * The questions of the approved prototype (docs/design/prototype/home.html),
 * shown on the home page. Safe to run again (matched by the English question).
 * Pending: confirm the answers with the client.
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [FaqTopic::Programs,
                ['en' => 'What age can my child start?', 'es' => '¿Desde qué edad puede empezar mi hijo?'],
                ['en' => 'From 5 years old (U6) up to 14 (U14). Boys and girls train in groups by age and level.', 'es' => 'Desde los 5 años (U6) hasta los 14 (U14). Niños y niñas entrenan en grupos por edad y nivel.']],
            [FaqTopic::Registration,
                ['en' => 'Is the first training really free?', 'es' => '¿El primer entrenamiento es gratis de verdad?'],
                ['en' => 'Yes. Book a session, come with cleats and shin guards, and meet the coach. No card needed.', 'es' => 'Sí. Reserva una sesión, ven con tacos y espinilleras y conoce al entrenador. Sin tarjeta.']],
            [FaqTopic::Registration,
                ['en' => 'How does the :cutoff_day-of-the-month rule work?', 'es' => '¿Cómo funciona la regla del día :cutoff_day?'],
                ['en' => 'Join between the 1st and the :cutoff_day and you pay that month (:monthly_price). Join after the :cutoff_day and the rest of the month is free: your first charge is on the 1st of the next month.', 'es' => 'Si te apuntas entre el día 1 y el :cutoff_day, pagas ese mes (:monthly_price). Si te apuntas después del día :cutoff_day, el resto del mes es gratis: tu primer cobro es el día 1 del mes siguiente.']],
            [FaqTopic::General,
                ['en' => 'Where and when do you train?', 'es' => '¿Dónde y cuándo entrenáis?'],
                ['en' => 'At South Gwinnett High School, 2288 Main St E, Snellville. The schedule of each category is on the Programs page.', 'es' => 'En South Gwinnett High School, 2288 Main St E, Snellville. El horario de cada categoría está en la página de programas.']],
            [FaqTopic::Registration,
                ['en' => 'Can I cancel anytime?', 'es' => '¿Puedo cancelar cuando quiera?'],
                ['en' => 'Monthly plans can be cancelled from the parent portal at any time.', 'es' => 'Los planes mensuales se cancelan desde el portal de padres cuando quieras.']],
        ];

        foreach ($faqs as $order => [$topic, $question, $answer]) {
            Faq::updateOrCreate(['question->en' => $question['en']], [
                'topic' => $topic,
                'question' => $question,
                'answer' => $answer,
                'is_published' => true,
                'show_on_home' => true,
                'sort_order' => $order,
            ]);
        }
    }
}
