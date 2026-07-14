<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Achiever;
use App\Models\HomeSection;
use App\Models\Post;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'homeSections' => HomeSection::ordered(),
            'latestPosts' => Post::published()
                ->with('category')
                ->latest('published_at')
                ->latest()
                ->take(3)
                ->get(),
            'testimonials' => Testimonial::published()->take(6)->get(),
            'achievers' => Achiever::featured()->take(4)->get(),
        ]);
    }
}
