<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Gallery;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::query()
            ->live()                       // approved + published only
            ->withCount('items')
            ->with('center')
            ->orderBy('sort')
            ->latest()
            ->paginate(12);

        return view('public.gallery.index', ['galleries' => $galleries]);
    }

    public function show(Gallery $gallery)
    {
        // Never expose pending/rejected or unpublished albums publicly.
        abort_unless($gallery->approval_status === 'approved' && $gallery->is_published, 404);

        $gallery->load(['items', 'center']);

        return view('public.gallery.show', ['gallery' => $gallery]);
    }
}
