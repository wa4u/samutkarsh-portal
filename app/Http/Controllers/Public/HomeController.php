<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'homeSections' => HomeSection::enabledKeys(),
            'latestPosts' => Post::published()
                ->with('category')
                ->latest('published_at')
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
