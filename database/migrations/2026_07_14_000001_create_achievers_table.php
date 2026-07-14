<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Achievers — notable alumni / success stories. Each is a small "post": photo,
 * achievement headline, programme + year, short story, and optional rich-text
 * body for its own shareable page. Featured ones surface on the home page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('headline');            // achievement, e.g. "Cleared UPSC 2025 — AIR 128"
            $table->string('programme')->nullable();
            $table->string('year')->nullable();
            $table->string('photo')->nullable();   // _display.webp path on the media disk
            $table->text('excerpt')->nullable();   // short story shown on cards
            $table->longText('story')->nullable(); // full rich-text body for the detail page
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievers');
    }
};
