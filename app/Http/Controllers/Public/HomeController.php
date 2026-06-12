<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'latestPosts' => Post::published()
                ->with('category')
                ->latest('published_at')
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
