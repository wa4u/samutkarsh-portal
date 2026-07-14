<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add the "Achievers" home section (featured alumni strip), placed just after
 * the "Why Samutkarsh" section. Idempotent: skips if the row already exists.
 * Disabled by default so it only appears once achievers are added + featured.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('home_sections')->where('key', 'achievers')->exists()) {
            return;
        }

        $why = DB::table('home_sections')->where('key', 'why')->first();
        $anchor = $why->sort ?? 4;
        DB::table('home_sections')->where('sort', '>', $anchor)->increment('sort');

        DB::table('home_sections')->insert([
            'key'        => 'achievers',
            'label'      => 'Achievers (featured alumni)',
            'is_enabled' => true,
            'sort'       => $anchor + 1,
            'content'    => json_encode([
                'heading' => 'Our Achievers',
                'intro'   => 'Students who went on to do extraordinary things after their Samutkarsh journey.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('home_sections')->where('key', 'achievers')->delete();
    }
};
