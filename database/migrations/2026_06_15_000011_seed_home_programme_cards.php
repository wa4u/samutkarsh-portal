<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed the Programmes + Audience cards into the home_sections content so they
 * become editable in admin (until now they were template defaults with no DB
 * rows, so the admin Repeater showed nothing to edit). Idempotent: only fills
 * cards when none are saved yet, so it never clobbers admin edits.
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'programmes' => [
                'heading' => 'Our programmes',
                'intro'   => 'From early school foundation to full civil-services coaching — a continuous path, rooted in Bharatiya ethos.',
                'cards'   => [
                    ['title' => 'Shraddha & Medha', 'desc' => 'Pre-IAS foundation for Classes 6–9 — officer-like qualities, life skills and values.', 'link' => 'shraddha-medha-foundation-program'],
                    ['title' => 'Utkarsh', 'desc' => 'For degree students of any stream — orientation and motivation toward civil services.', 'link' => 'utkarsh'],
                    ['title' => 'IAS Coaching', 'desc' => 'Complete UPSC & KPSC preparation with faculty from Sankalp, New Delhi.', 'link' => 'ias-coaching'],
                    ['title' => 'Dristi & Disha', 'desc' => 'Short orientation workshops and certificate courses for colleges.', 'link' => 'dristi-disha'],
                    ['title' => 'Short Programs', 'desc' => 'Parichaya, Pravinya & Prabuddha — certificate camps for school students.', 'link' => 'short-programs'],
                    ['title' => 'Results & Achievements', 'desc' => 'Topper stories and the impact of our mentoring across Karnataka.', 'link' => 'results-achievements'],
                ],
            ],
            'audience' => [
                'cards' => [
                    ['title' => 'School Students', 'desc' => 'Classes 6–9 foundation', 'link' => 'shraddha-medha-foundation-program'],
                    ['title' => 'Degree Students', 'desc' => 'Utkarsh guidance', 'link' => 'utkarsh'],
                    ['title' => 'IAS Aspirants', 'desc' => 'Full UPSC / KPSC coaching', 'link' => 'ias-coaching'],
                    ['title' => 'Colleges', 'desc' => 'Dristi & Disha orientation', 'link' => 'dristi-disha'],
                ],
            ],
        ];

        foreach ($defaults as $key => $seed) {
            $row = DB::table('home_sections')->where('key', $key)->first();
            if (! $row) {
                continue;
            }
            $content = json_decode($row->content ?? '', true) ?: [];
            if (! empty($content['cards'])) {
                continue; // already has cards — don't overwrite admin edits
            }
            $content = array_merge($seed, $content); // keep any existing heading/intro
            $content['cards'] = $seed['cards'];

            DB::table('home_sections')->where('key', $key)->update([
                'content'    => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive: leave seeded content in place.
    }
};
