<?php

use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Starter policy pages (editable in admin → Pages) + footer menu links to them.
 * firstOrCreate so existing content/links are never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pages = [
            ['privacy-policy', 'Privacy Policy', 'How Samutkarsh collects, uses and protects personal information.'],
            ['refund-policy', 'Refund & Fee Policy', 'Admission fee, refund eligibility and the process to request a refund.'],
            ['terms', 'Terms & Conditions', 'The terms governing use of this website and our programmes.'],
        ];

        foreach ($pages as $i => [$slug, $title, $intro]) {
            Page::firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => "<p>{$intro}</p><p><em>This content is editable in admin → Pages.</em></p>", 'is_published' => true],
            );

            MenuItem::firstOrCreate(
                ['location' => 'footer', 'label' => $title],
                ['link_type' => 'page', 'link_value' => $slug, 'sort' => $i + 1, 'is_active' => true],
            );
        }
    }

    public function down(): void
    {
        MenuItem::where('location', 'footer')
            ->whereIn('link_value', ['privacy-policy', 'refund-policy', 'terms'])
            ->delete();
        Page::whereIn('slug', ['privacy-policy', 'refund-policy', 'terms'])->delete();
    }
};
