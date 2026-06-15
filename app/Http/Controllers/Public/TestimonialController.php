<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $query = Testimonial::published();

        if ($request->filled('center')) {
            $query->where('center', $request->string('center'));
        }
        if ($request->filled('year')) {
            $query->whereYear('date', $request->integer('year'));
        }

        $centers = Testimonial::published()->whereNotNull('center')
            ->distinct()->orderBy('center')->pluck('center');

        $years = Testimonial::published()->whereNotNull('date')->pluck('date')
            ->map(fn ($d) => $d->format('Y'))->unique()->sortDesc()->values();

        return view('public.testimonials', [
            'testimonials' => $query->paginate(24)->withQueryString(),
            'centers'      => $centers,
            'years'        => $years,
            'activeCenter' => $request->string('center')->toString(),
            'activeYear'   => $request->string('year')->toString(),
        ]);
    }
}
