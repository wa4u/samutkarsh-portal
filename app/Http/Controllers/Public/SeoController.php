<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** XML sitemap of all public, indexable URLs. */
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('public.home'),          'freq' => 'weekly',  'pri' => '1.0'],
            ['loc' => route('public.blog.index'),    'freq' => 'daily',   'pri' => '0.8'],
            ['loc' => route('public.gallery.index'), 'freq' => 'weekly',  'pri' => '0.6'],
            ['loc' => route('public.testimonials'),  'freq' => 'weekly',  'pri' => '0.5'],
            ['loc' => route('public.activities'),    'freq' => 'weekly',  'pri' => '0.6'],
            ['loc' => route('public.contact'),       'freq' => 'monthly', 'pri' => '0.7'],
        ];

        foreach (Page::where('is_published', true)->get() as $page) {
            $urls[] = ['loc' => url('/' . $page->slug), 'freq' => 'monthly', 'pri' => '0.7', 'mod' => $page->updated_at];
        }

        foreach (Post::published()->get() as $post) {
            $urls[] = ['loc' => route('public.blog.show', $post->slug), 'freq' => 'monthly', 'pri' => '0.6', 'mod' => $post->updated_at];
        }

        if (class_exists(Gallery::class)) {
            foreach (Gallery::query()->get() as $gallery) {
                if ($gallery->slug ?? null) {
                    $urls[] = ['loc' => route('public.gallery.show', $gallery->slug), 'freq' => 'monthly', 'pri' => '0.5', 'mod' => $gallery->updated_at];
                }
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>' . e($u['loc']) . '</loc>'
                . (isset($u['mod']) && $u['mod'] ? '<lastmod>' . $u['mod']->toAtomString() . '</lastmod>' : '')
                . '<changefreq>' . $u['freq'] . '</changefreq>'
                . '<priority>' . $u['pri'] . '</priority></url>' . "\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /** robots.txt — allow crawling of public content, block admin/private paths. */
    public function robots(): Response
    {
        $body = "User-agent: *\n"
            . "Disallow: /admin\n"
            . "Disallow: /checkout\n"
            . "Disallow: /result\n"
            . "Disallow: /__setup\n"
            . "Allow: /\n\n"
            . 'Sitemap: ' . url('/sitemap.xml') . "\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
