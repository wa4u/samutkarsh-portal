<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('public.contact', [
            'centers' => Center::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'regex:/^[6-9]\d{9}$/'],
            'email'     => ['nullable', 'email', 'max:255'],
            'center_id' => ['nullable', 'exists:centers,id'],
            'subject'   => ['nullable', 'string', 'max:255'],
            'message'   => ['required', 'string', 'max:5000'],
        ], [
            'phone.regex' => 'Enter a valid 10-digit mobile number.',
        ]);

        Inquiry::create($data + ['status' => 'new']);

        return back()->with('status', 'Thank you — we have received your message and will contact you soon.');
    }
}
