<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationReceivedAdmin;
use App\Mail\RegistrationReceivedStudent;
use App\Models\Center;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function create()
    {
        abort_unless(config('admissions.registration_open'), 403, 'Registrations are currently closed.');

        return view('public.register', [
            'centers' => Center::where('is_active', true)->orderBy('name')->get(),
            'year'    => config('admissions.academic_year'),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(config('admissions.registration_open'), 403);

        $data = $request->validate([
            'center_id'     => ['required', 'exists:centers,id'],
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'regex:/^[6-9]\d{9}$/'],   // Indian 10-digit mobile
            'email'         => ['nullable', 'email', 'max:255'],
            'student_class' => ['required', 'in:' . implode(',', array_keys(Student::CLASSES))],
            'dob'           => ['nullable', 'date', 'before:today'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'school_name'   => ['nullable', 'string', 'max:255'],
        ], [
            'phone.regex'           => 'Enter a valid 10-digit mobile number.',
            'student_class.required' => 'Please select the class.',
        ]);

        $year = config('admissions.academic_year');
        $student = null;

        try {
            DB::transaction(function () use ($data, $year, &$student) {
                // One student per (center, phone, name) — so siblings sharing a
                // phone are distinct people, each with their own registration.
                $student = Student::firstOrNew([
                    'center_id' => $data['center_id'],
                    'phone'     => $data['phone'],
                    'name'      => $data['name'],
                ]);
                $student->fill([
                    'name'          => $data['name'],
                    'email'         => $data['email'] ?? $student->email,
                    'student_class' => $data['student_class'],
                    'dob'           => $data['dob'] ?? $student->dob,
                    'gender'        => $data['gender'] ?? $student->gender,
                    'school_name'   => $data['school_name'] ?? $student->school_name,
                ])->save();

                // Composite unique (center_id, student_id, academic_year) blocks duplicates;
                // we check first for a friendly message and let the DB be the final guard.
                if (Registration::where('student_id', $student->id)->where('academic_year', $year)->exists()) {
                    throw new DuplicateRegistrationException();
                }

                Registration::create([
                    'student_id'    => $student->id,
                    'center_id'     => $data['center_id'],
                    'academic_year' => $year,
                    'status'        => 'pending',
                ]);
            });

            $this->sendNotifications($student, $year);
        } catch (DuplicateRegistrationException) {
            return back()->withInput()->withErrors([
                'name' => "This applicant is already registered at the selected center for {$year}. Use the Result Gateway to check the status.",
            ]);
        } catch (QueryException $e) {
            // Unique-constraint race fallback.
            if (str_contains($e->getMessage(), 'reg_center_student_year_unique') || $e->getCode() === '23000') {
                return back()->withInput()->withErrors(['phone' => "You are already registered for {$year}."]);
            }
            throw $e;
        }

        return redirect()->route('public.register.success');
    }

    public function success()
    {
        return view('public.register-success', ['year' => config('admissions.academic_year')]);
    }

    /**
     * Send the three registration emails: Head Office, the centre, and the
     * applicant. Mail failures must never break the registration, so the whole
     * block is guarded and logged.
     */
    protected function sendNotifications(Student $student, string $year): void
    {
        try {
            $center = $student->center ?: Center::find($student->center_id);
            if (! $center) {
                return;
            }

            $ho = Setting::get('notify.registration_email') ?: Setting::get('contact.email');

            if ($ho) {
                Mail::to($ho)->send(new RegistrationReceivedAdmin($student, $center, $year));
            }
            if ($center->contact_email && $center->contact_email !== $ho) {
                Mail::to($center->contact_email)->send(new RegistrationReceivedAdmin($student, $center, $year));
            }
            if ($student->email) {
                Mail::to($student->email)->send(new RegistrationReceivedStudent($student, $center, $year));
            }
        } catch (\Throwable $e) {
            Log::warning('Registration emails failed: ' . $e->getMessage());
        }
    }
}

/** @internal control-flow signal for an already-existing registration. */
class DuplicateRegistrationException extends \RuntimeException {}
