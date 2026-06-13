<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeds starter CMS pages + the header menu tree matching the proposed site
 * structure. Idempotent — safe to re-run; it won't duplicate or overwrite the
 * content you've edited (pages are created only if missing).
 */
class SiteSeeder extends Seeder
{
    public function run(): void
    {
        // --- Starter pages (placeholder content you can edit in admin) ---
        $pages = [
            'shradha'                => 'Shradha (Class 6–7 Foundation)',
            'medha'                  => 'Medha (Class 8–9 Advanced)',
            'parichaya'              => 'Parichaya (Class 7–10 Orientation / Bridge)',
            'results-achievements'   => 'Results & Achievements',
            'samutkarsh-trust'       => 'Samutkarsh Trust',
            'infrastructure-centers' => 'Infrastructure & Centers',
            'test-schedules'         => 'Test Schedules & Announcements',
            'study-material'         => 'Download Study Material / Prospectus',
        ];
        foreach ($pages as $slug => $title) {
            Page::firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => "<p>{$title} — content coming soon.</p>", 'is_published' => true],
            );
        }

        // --- Header menu tree ---
        $top = function (string $label, int $sort, string $type = 'none', ?string $value = null): MenuItem {
            return MenuItem::updateOrCreate(
                ['location' => 'header', 'parent_id' => null, 'label' => $label],
                ['link_type' => $type, 'link_value' => $value, 'sort' => $sort, 'is_active' => true],
            );
        };
        $child = function (MenuItem $parent, string $label, int $sort, string $type, string $value): void {
            MenuItem::updateOrCreate(
                ['location' => 'header', 'parent_id' => $parent->id, 'label' => $label],
                ['link_type' => $type, 'link_value' => $value, 'sort' => $sort, 'is_active' => true],
            );
        };

        $top('Home', 1, 'route', 'public.home');

        $courses = $top('Courses & Programs', 2);
        $child($courses, 'Shradha (Class 6–7)', 1, 'page', 'shradha');
        $child($courses, 'Medha (Class 8–9)', 2, 'page', 'medha');
        $child($courses, 'Parichaya (Class 7–10)', 3, 'page', 'parichaya');

        $top('Results & Achievements', 3, 'page', 'results-achievements');

        $about = $top('About Us', 4);
        $child($about, 'Samutkarsh Trust', 1, 'page', 'samutkarsh-trust');
        $child($about, 'Infrastructure & Centers', 2, 'page', 'infrastructure-centers');

        $corner = $top('Student Corner', 5);
        $child($corner, 'Test Schedules & Announcements', 1, 'page', 'test-schedules');
        $child($corner, 'Study Material / Prospectus', 2, 'page', 'study-material');

        $top('Gallery', 6, 'route', 'public.gallery.index');
        $top('Blog', 7, 'route', 'public.blog.index');
        // Repoint to the /contact page once the contact module ships (next pass).
        $top('Contact & Admissions', 8, 'route', 'public.register.create');
    }
}
