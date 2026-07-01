<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds CMS pages + the header menu, populated from the Samutkarsh handbook so
 * the site reflects the real programmes. Idempotent — pages are created only if
 * missing, so it won't overwrite content you've edited in admin.
 */
class SiteSeeder extends Seeder
{
    public function run(): void
    {
        // Editable site/contact settings (managed in admin → Settings).
        $settings = [
            'site.logo_url'          => ['', 'site'],   // paste a logo image URL (e.g. /storage/branding/logo.png)
            'site.tagline'           => ['Nation Building through IAS', 'site'],
            'site.hero_title'        => ['Shape your civil services journey', 'site'],
            'site.hero_subtitle'     => ['Nation Building through IAS — from school foundation to civil services, across Karnataka.', 'site'],
            'site.hero_image'        => ['', 'site'],   // optional hero background image URL
            'site.hero_video'        => ['', 'site'],   // optional hero background MP4 URL
            'site.ga_id'             => ['G-TDYG1WK6KY', 'site'],   // Google Analytics Measurement ID (blank disables tracking) (image acts as poster)
            'contact.whatsapp'       => ['', 'contact'],   // digits only incl. country code, e.g. 919663424767
            'contact.email'          => ['samutkarshias@gmail.com', 'contact'],
            'contact.phone_hubballi' => ['96634 24767', 'contact'],
            'contact.phone_bengaluru'=> ['95918 55055', 'contact'],
            'contact.address_hubballi'  => ['Samutkarsh Study Center, KLE Tech, BVB Campus, Vidyanagar, Hubballi 580031', 'contact'],
            'contact.address_bengaluru' => ['Shanders Group, 1097, 18th B Main Road, 5th Block, Rajajinagar, Bengaluru 560010', 'contact'],
            'contact.map_embed'      => ['', 'contact'],  // paste a Google Maps embed URL
            'social.facebook'        => ['', 'social'],
            'social.instagram'       => ['', 'social'],
            'social.youtube'         => ['', 'social'],
        ];
        foreach ($settings as $key => [$value, $group]) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'type' => 'text', 'group' => $group]);
        }

        foreach ($this->pages() as $slug => [$title, $content]) {
            Page::firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => $content, 'is_published' => true],
            );
        }

        $top = fn (string $label, int $sort, string $type = 'none', ?string $value = null): MenuItem =>
            MenuItem::updateOrCreate(
                ['location' => 'header', 'parent_id' => null, 'label' => $label],
                ['link_type' => $type, 'link_value' => $value, 'sort' => $sort, 'is_active' => true],
            );

        $child = fn (MenuItem $parent, string $label, int $sort, string $type, string $value): MenuItem =>
            MenuItem::updateOrCreate(
                ['location' => 'header', 'parent_id' => $parent->id, 'label' => $label],
                ['link_type' => $type, 'link_value' => $value, 'sort' => $sort, 'is_active' => true],
            );

        $top('Home', 1, 'route', 'public.home');

        $courses = $top('Courses & Programs', 2);
        $child($courses, 'Shraddha & Medha (Class 6–9)', 1, 'page', 'shraddha-medha');
        $child($courses, 'Utkarsh (Degree)', 2, 'page', 'utkarsh');
        $child($courses, 'IAS Coaching', 3, 'page', 'ias-coaching');
        $child($courses, 'Dristi & Disha (College)', 4, 'page', 'dristi-disha');
        $child($courses, 'Short Programs', 5, 'page', 'short-programs');

        $top('Results & Achievements', 3, 'page', 'results-achievements');

        $about = $top('About Us', 4);
        $child($about, 'Samutkarsh Trust', 1, 'page', 'samutkarsh-trust');
        $child($about, 'Infrastructure & Centers', 2, 'page', 'infrastructure-centers');

        $corner = $top('Student Corner', 5);
        $child($corner, 'Test Schedules & Announcements', 1, 'page', 'test-schedules');
        $child($corner, 'Study Material / Prospectus', 2, 'page', 'study-material');

        $top('Gallery', 6, 'route', 'public.gallery.index');
        $top('Blog', 7, 'route', 'public.blog.index');
        $top('Contact & Admissions', 8, 'route', 'public.contact');
    }

    /** @return array<string,array{0:string,1:string}> */
    private function pages(): array
    {
        return [
            'shraddha-medha' => ['Shraddha & Medha — Pre-IAS Foundation (Class 6–9)',
                '<p>An integrated foundation course that starts early to help children discover their potential through life skills and the values needed for IAS, IPS and other civil services. <em>(Earlier offered as Spoorthi–Keerthi.)</em></p>'
                . '<p><strong>For:</strong> school students, classes 6–9. <strong>Duration:</strong> 16–20 weeks, weekly classes (usually Sundays).</p>'
                . '<h3>What students gain</h3><p>Officer-like qualities, emotional intelligence, communication and creativity, reading/writing/speaking skills, and an early orientation to civil services.</p>'
                . '<p>The detailed weekly curriculum is shared with enrolled students and parents.</p>'],

            'utkarsh' => ['Utkarsh — for Degree Students',
                '<p>For students pursuing a degree in any stream. Guides and motivates them towards civil services preparation while building an overall successful personality.</p>'
                . '<p><strong>Duration:</strong> 16–20 weeks, weekly classes (usually Saturdays).</p>'
                . '<p>Covers the pattern, preparation and approach to civil services exams, alongside officer-like qualities, communication, and creativity.</p>'],

            'ias-coaching' => ['IAS Coaching Course',
                '<p>Complete preparation for Civil Services aspirants who have completed their degree, with faculty from <strong>Sankalp, New Delhi</strong>.</p>'
                . '<p><strong>Duration:</strong> 8 months. <strong>Covers:</strong> the full UPSC and KPSC syllabus, with care taken to nurture good future civil servants.</p>'],

            'dristi-disha' => ['Dristi & Disha — College Orientation',
                '<p>Short motivational and orientation programmes for college students and aspirants, with special emphasis on civil-services orientation. Held at the college on request; topics can be customised.</p>'
                . '<ul><li><strong>Dristi</strong> — 1-day workshop: self-awareness (SWOT), current affairs, aptitude, decolonisation of Indian minds.</li>'
                . '<li><strong>Disha</strong> — 1-day orientation / 3-day certificate course: the what &amp; how of civil services, officer-like qualities, essay, model parliament, polity, and interaction with officers.</li></ul>'],

            'short-programs' => ['Short-Term School Programs',
                '<p>Short certificate programmes for school students (classes 6–9):</p>'
                . '<ul><li><strong>Parichaya</strong> — 1-day certificate: current affairs, aptitude, officer-like qualities.</li>'
                . '<li><strong>Pravinya</strong> — 2-day workshop: debating, history of Bharat, self-awareness (SWOT), creativity, model parliament, current affairs.</li>'
                . '<li><strong>Prabuddha</strong> — 4-day camp: SWOT, current affairs, aptitude, OLQs, debate, group discussion, creativity, art, Kutumb Prabodha, basics of Sanskrit, ethical decision-making, model parliament, polity &amp; governance.</li></ul>'],

            'results-achievements' => ['Results & Achievements',
                '<p>Each year, hundreds of school and college students and aspirants are mentored through screening tests, foundation courses, orientation camps and regular coaching across Karnataka.</p>'
                . '<p>Detailed results and topper stories will be published here.</p>'],

            'samutkarsh-trust' => ['Samutkarsh Trust',
                '<p>Samutkarsh is a socially committed Trust formed in <strong>2015 at Hubballi, Karnataka</strong>. It is guided by an Executive Council of professionals from across the state — supported by eminent civil servants, social workers and academicians — with an Academic Council overseeing the curriculum.</p>'
                . '<h3>Vision</h3><p>Inspiring a new generation of civil servants, rooted in Bharatiya ethos and driven by the challenges of nation building.</p>'
                . '<h3>Mission</h3><p>Accomplishing the vision by establishing &ldquo;Centres of Excellence&rdquo; for civil services across Karnataka.</p>'
                . '<h3>Our values</h3><p>Excellence, Compassion, Integrity, Knowledge, Attitude and Skill — together shaping Transformation.</p>'
                . '<p><em>Nation Building through IAS.</em></p>'],

            'infrastructure-centers' => ['Infrastructure & Centers',
                '<h3>Hubballi <em>(Head Office)</em></h3><p>Samutkarsh Study Center, KLE Tech, BVB Campus, Vidyanagar, Hubballi 580031<br>Phone: 96634 24767</p>'
                . '<h3>Bengaluru</h3><p>Shanders Group, 5th Block, 1097, 18th B Main Road, Rajajinagar, Bengaluru 560010<br>Phone: 95918 55055</p>'
                . '<p>Samutkarsh also runs school, college and orientation activities across Karnataka — including Belagavi, Ballari, Gangavati and other districts.</p>'],

            'test-schedules' => ['Test Schedules & Announcements',
                '<p>Upcoming screening tests, class schedules and announcements will be posted here.</p>'],

            'study-material' => ['Study Material / Prospectus',
                '<p>Downloadable study material and the course prospectus will be available here.</p>'],
        ];
    }
}
