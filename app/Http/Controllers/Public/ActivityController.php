<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::published();

        if ($request->filled('center')) {
            $query->where('center', $request->string('center'));
        }
        if ($request->filled('year')) {
            $query->whereYear('date', $request->integer('year'));
        }
        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('body', 'like', $term));
        }

        $activities = $query->paginate(12)->withQueryString();

        // Match each activity to its month photo album (only live/approved ones),
        // preloaded to avoid N+1.
        $monthSlugs = $activities->getCollection()
            ->map(fn ($a) => 'samutkarsh-' . $a->date->format('Y-m'))->unique()->values();

        $galleries = Gallery::live()->whereIn('slug', $monthSlugs)
            ->with(['items' => fn ($q) => $q->where('type', 'image')->orderBy('sort')])
            ->get()->keyBy('slug');

        // Filter options (DB-agnostic: derive years in PHP).
        $centers = Activity::published()->whereNotNull('center')
            ->distinct()->orderBy('center')->pluck('center');

        $years = Activity::published()->pluck('date')
            ->map(fn ($d) => $d->format('Y'))->unique()->sortDesc()->values();

        return view('public.activities', [
            'activities'   => $activities,
            'galleries'    => $galleries,
            'centers'      => $centers,
            'years'        => $years,
            'activeCenter' => $request->string('center')->toString(),
            'activeYear'   => $request->string('year')->toString(),
            'searchTerm'   => $request->string('q')->toString(),
        ]);
    }

    public function show(Activity $activity)
    {
        abort_unless($activity->is_published, Response::HTTP_NOT_FOUND);

        return view('public.activity-show', [
            'activity' => $activity,
            'gallery'  => $activity->monthGallery(),
        ]);
    }
}
