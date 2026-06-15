<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Parent / student feedback collected after events (often from WhatsApp).
 * Short verbatim quotes — any language — shown on the home page + /testimonials.
 * Also registers a toggleable "What parents say" home section.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('role')->nullable();       // e.g. "Parent", "Student", program name
            $table->text('body');
            $table->string('event')->nullable();       // e.g. "Village Visit 2025"
            $table->string('photo')->nullable();       // optional image path (public disk)
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Add the home section (after 'why' by default) if not already present.
        if (Schema::hasTable('home_sections') && ! DB::table('home_sections')->where('key', 'testimonials')->exists()) {
            $maxSort = (int) DB::table('home_sections')->max('sort');
            DB::table('home_sections')->insert([
                'key' => 'testimonials', 'label' => 'What parents say (testimonials)',
                'is_enabled' => true, 'sort' => $maxSort + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('home_sections')->where('key', 'testimonials')->delete();
        Schema::dropIfExists('testimonials');
    }
};
