<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Frequently asked questions: the home FAQ, the /faq page and (later) the
     * WhatsApp bot read the same rows. `question` and `answer` hold one text per
     * locale ({"en": …, "es": …}); answers may use :cutoff_day and :monthly_price,
     * filled from config/billing.php.
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('topic', 20)->index();
            $table->json('question');
            $table->json('answer');
            $table->boolean('is_published')->default(true)->index();
            $table->boolean('show_on_home')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
