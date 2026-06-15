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

        // A single mobile number can have more than one child (siblings) at a
        // center — return every registration for this year so the parent can
        // see each one by name.
        $results = Student::where('center_id', $data['center_id'])
            ->where('phone', $data['phone'])
            ->get()
            ->map(function (Student $student) use ($year) {
                $registration = Registration::with('center')
                    ->where('student_id', $student->id)
                    ->where('academic_year', $year)
                    ->first();

                if (! $registration) {
                    return null;
                }

                // Short-lived signed checkout link, only for a Selected seat.
                $checkoutUrl = $registration->status === 'selected'
                    ? URL::temporarySignedRoute('public.checkout', now()->addHours(2), ['registration' => $registration->id])
                    : null;

                return [
                    'student'      => $student,
                    'registration' => $registration,
                    'checkoutUrl'  => $checkoutUrl,
                ];
            })
            ->filter()
            ->values();

        return view('public.result-status', [
            'results' => $results,
            'year'    => $year,
        ]);
    }
}
