<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $galleries = Gallery::query()
            ->live()                       // approved + published only
            ->withCount('items')
            ->with('center')
            ->when($request->filled('center'), fn ($q) => $q->where('center_id', $request->integer('center')))
            ->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->string('q') . '%'))
            ->orderByDesc('year')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Build filter option lists from the live album set only.
        $live = Gallery::query()->live();

        return view('public.gallery.index', [
            'galleries' => $galleries,
            'centers'   => Center::whereIn('id', (clone $live)->whereNotNull('center_id')->pluck('center_id'))
                ->orderBy('name')->get(),
            'years'     => (clone $live)->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'filters'   => $request->only(['center', 'year', 'q']),
        ]);
    }

    public function show(Gallery $gallery)
    {
        abort_unless($gallery->approval_status === 'approved' && $gallery->is_published, 404);

        $gallery->load(['items', 'center']);

        return view('public.gallery.show', ['gallery' => $gallery]);
    }
}
