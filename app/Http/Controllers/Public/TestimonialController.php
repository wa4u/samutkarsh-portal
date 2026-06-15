<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index()
    {
        return view('public.testimonials', [
            'testimonials' => Testimonial::published()->paginate(24),
        ]);
    }
}
