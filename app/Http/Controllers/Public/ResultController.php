<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ResultController extends Controller
{
    public function form()
    {
        return view('public.result-form', [
            'centers' => Center::where('is_active', true)->orderBy('name')->get(),
            'year'    => config('admissions.academic_year'),
        ]);
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'center_id' => ['required', 'exists:centers,id'],
            'phone'     => ['required', 'regex:/^[6-9]\d{9}$/'],
        ]);

        $year = config('admissions.academic_year');

        $student = Student::where('center_id', $data['center_id'])
            ->where('phone', $data['phone'])
            ->first();

        $registration = $student
            ? Registration::with('center')
                ->where('student_id', $student->id)
                ->where('academic_year', $year)
                ->first()
            : null;

        // Issue a short-lived signed checkout link only for a Selected seat.
        $checkoutUrl = null;
        if ($registration && $registration->status === 'selected') {
            $checkoutUrl = URL::temporarySignedRoute(
                'public.checkout',
                now()->addHours(2),
                ['registration' => $registration->id],
            );
        }

        return view('public.result-status', [
            'registration' => $registration,
            'student'      => $student,
            'year'         => $year,
            'checkoutUrl'  => $checkoutUrl,
        ]);
    }
}
