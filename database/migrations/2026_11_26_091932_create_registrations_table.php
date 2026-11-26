<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registrations (free training, monthly or annual plan) and their players.
     * Personal data of families and children: phones, the street address, dates
     * of birth and medical notes are stored encrypted (APP_KEY); there is no card
     * data here (payment goes through Stripe Checkout, phase 7).
     */
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->string('type', 20)->index();
            $table->string('status', 20)->index();
            $table->string('locale', 5);

            $table->string('guardian_first_name');
            $table->string('guardian_last_name');
            $table->string('guardian_relationship', 20);
            $table->string('guardian_email')->index();
            $table->text('guardian_phone');
            $table->text('address_street');
            $table->string('address_city');
            $table->string('address_state', 2);
            $table->string('address_zip', 10);

            $table->string('second_guardian_name')->nullable();
            $table->string('second_guardian_email')->nullable();
            $table->text('second_guardian_phone')->nullable();

            $table->string('emergency_contact_name');
            $table->string('emergency_contact_relationship');
            $table->text('emergency_contact_phone');

            $table->date('preferred_training_date')->nullable();

            $table->boolean('media_release');
            $table->boolean('marketing_opt_in')->default(false);
            $table->string('signature_name');
            $table->timestamp('liability_waiver_accepted_at');
            $table->timestamp('terms_accepted_at');
            $table->timestamp('refund_policy_accepted_at')->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->text('signed_user_agent')->nullable();

            $table->timestamps();
        });

        Schema::create('registration_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->text('date_of_birth');
            $table->string('category', 4);
            $table->string('gender', 20)->nullable();
            $table->boolean('needs_uniform')->default(false);
            $table->string('shirt_size', 4)->nullable();
            $table->text('medical_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_players');
        Schema::dropIfExists('registrations');
    }
};
