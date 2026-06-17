<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentExportController extends Controller
{
    /** Stream a CSV of students (optionally filtered by centre). Excel-friendly (UTF-8 BOM). */
    public function students(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $query = Student::query()->with(['center', 'registrations'])->orderBy('name');

        // Centre Heads are locked to their own centre; others may filter or get all.
        if (method_exists($user, 'isCenterHead') && $user->isCenterHead()) {
            $query->where('center_id', $user->center_id);
        } elseif ($request->filled('center')) {
            $query->where('center_id', $request->integer('center'));
        }

        if ($request->filled('class')) {
            $query->where('student_class', $request->string('class'));
        }

        // Registration-date range (on the "Registered on" date), inclusive.
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $filename = 'students-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads Unicode (Kannada) names
            fputcsv($out, ['Centre', 'Name', 'Class', 'Phone', 'Email', 'Date of birth', 'Gender', 'School / College', 'Guardian', 'Registered on', 'Latest year', 'Latest status']);

            $query->chunk(500, function ($students) use ($out) {
                foreach ($students as $s) {
                    $reg = $s->registrations->sortByDesc('academic_year')->first();
                    fputcsv($out, [
                        $s->center?->name,
                        $s->name,
                        $s->classLabel(),
                        $s->phone,
                        $s->email,
                        optional($s->dob)->format('Y-m-d'),
                        $s->gender,
                        $s->school_name,
                        $s->guardian_name,
                        optional($s->created_at)->format('Y-m-d'),
                        $reg?->academic_year,
                        $reg?->status,
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
