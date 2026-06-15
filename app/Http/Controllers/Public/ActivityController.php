<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        return view('public.activities', [
            'activities' => Activity::published()->paginate(15),
        ]);
    }
}
