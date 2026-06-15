<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

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

        // Filter options (DB-agnostic: derive years in PHP).
        $centers = Activity::published()->whereNotNull('center')
            ->distinct()->orderBy('center')->pluck('center');

        $years = Activity::published()->pluck('date')
            ->map(fn ($d) => $d->format('Y'))->unique()->sortDesc()->values();

        return view('public.activities', [
            'activities'    => $query->paginate(12)->withQueryString(),
            'centers'       => $centers,
            'years'         => $years,
            'activeCenter'  => $request->string('center')->toString(),
            'activeYear'    => $request->string('year')->toString(),
        ]);
    }
}
