<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show(Page $page)
    {
        abort_unless($page->is_published, 404);

        return view('public.page', ['page' => $page]);
    }

    /**
     * Localized catch-all (/{locale}/{page}). Resolves the slug explicitly —
     * implicit model binding is unreliable when two route params are adjacent
     * ({locale}/{page}), so we look the page up by slug ourselves.
     */
    public function showLocalized(string $locale, string $page)
    {
        return $this->show(Page::where('slug', $page)->firstOrFail());
    }
}
