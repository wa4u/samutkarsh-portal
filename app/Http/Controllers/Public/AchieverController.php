<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Achiever;

class AchieverController extends Controller
{
    public function index()
    {
        return view('public.achievers.index', [
            'achievers' => Achiever::published()->paginate(12),
        ]);
    }

    public function show(Achiever $achiever)
    {
        abort_unless($achiever->is_published, 404);

        return view('public.achievers.show', ['achiever' => $achiever]);
    }
}
