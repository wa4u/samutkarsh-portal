<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add the "Exam schedule" home section (admission-test dates banner) and place
 * it right after the hero. Idempotent: skips if the row already exists, so it
 * never clobbers admin edits or re-inserts on a host where it already ran.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('home_sections')->where('key', 'exam')->exists()) {
            return;
        }

        $hero = DB::table('home_sections')->where('key', 'hero')->first();
        $heroSort = $hero->sort ?? 1;
        DB::table('home_sections')->where('sort', '>', $heroSort)->increment('sort');

        DB::table('home_sections')->insert([
            'key'        => 'exam',
            'label'      => 'Exam schedule (admission test)',
            'is_enabled' => true,
            'sort'       => $heroSort + 1,
            'content'    => json_encode([
                'heading'   => 'Entrance Exam Schedule',
                'intro'     => 'Mark your calendar — admission test dates for our centres.',
                'reporting' => '9:30 AM',
                'exam_time' => '10:00 AM to 12:00 PM',
                'centres'   => [
                    ['name' => 'Hubli Centre', 'dates' => '12th July'],
                    ['name' => 'Belagavi Centre', 'dates' => '5th & 12th July'],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('home_sections')->where('key', 'exam')->delete();
    }
};
