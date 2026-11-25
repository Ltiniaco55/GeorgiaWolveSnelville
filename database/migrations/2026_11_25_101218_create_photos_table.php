<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gallery photos. Only those with the parents' media release are published.
     * `title`, `subtitle` and `alt` hold one text per locale ({"en": …, "es": …}).
     * `image` is the public path without extension: the files are {image}.avif and
     * {image}.webp, `width` × `height` pixels.
     */
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20)->index();
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->json('alt');
            $table->string('image');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->boolean('has_media_release')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
