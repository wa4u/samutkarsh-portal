<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::published()
            ->with(['category', 'center'])
            ->when($request->filled('category'),
                fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->latest('published_at')
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.blog.index', [
            'posts'      => $posts,
            'categories' => Category::orderBy('name')->get(),
            'active'     => (string) $request->string('category'),
        ]);
    }

    public function show(Post $post)
    {
        abort_unless(
            $post->status === 'published' && (is_null($post->published_at) || $post->published_at <= now()),
            404,
        );

        $post->load(['category', 'center', 'author']);

        return view('public.blog.show', ['post' => $post]);
    }
}
