<?php

namespace App\Imports;

use App\Models\Center;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * Result-processing import for the Education Council.
 *
 * Expected heading row: center_code, phone, academic_year, exam_marks, status (optional)
 *
 * Each row is matched to an EXISTING registration via (center + student phone +
 * academic_year) and its score/status updated. Rows that fail structural
 * validation or do not match a registration are skipped and reported, never
 * silently dropped — see the failures()/errors() surfaced to the import action.
 */
class RegistrationsImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    /** @var array<int,string> Human-readable notes for rows that validated but matched nothing. */
    public array $unmatched = [];

    public int $updated = 0;

    private const ALLOWED_STATUS = ['pending', 'selected', 'not_selected', 'admitted'];

    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $center = Center::where('code', $data['center_code'])->first();
        if (! $center) {
            $this->unmatched[] = "Row {$row->getIndex()}: unknown center_code '{$data['center_code']}'";
            return;
        }

        $student = Student::where('center_id', $center->id)
            ->where('phone', (string) $data['phone'])
            ->first();

        if (! $student) {
            $this->unmatched[] = "Row {$row->getIndex()}: no student with phone '{$data['phone']}' in center '{$data['center_code']}'";
            return;
        }

        $registration = Registration::where('student_id', $student->id)
            ->where('center_id', $center->id)
            ->where('academic_year', (int) $data['academic_year'])
            ->first();

        if (! $registration) {
            $this->unmatched[] = "Row {$row->getIndex()}: no {$data['academic_year']} registration for phone '{$data['phone']}'";
            return;
        }

        $update = [];
        // Marks aren't used for the admission decision, but the column is kept
        // and updated if a value happens to be present in the sheet.
        if (isset($data['exam_marks']) && $data['exam_marks'] !== null && $data['exam_marks'] !== '') {
            $update['exam_marks'] = $data['exam_marks'];
        }
        if (! empty($data['status']) && in_array($data['status'], self::ALLOWED_STATUS, true)) {
            $update['status'] = $data['status'];
        }

        // Never let an import flip a paid/admitted seat back; that transition is webhook-owned.
        if ($registration->status === 'admitted') {
            unset($update['status']);
        }

        if ($update === []) {
            return;
        }

        $registration->update($update);
        $this->updated++;
    }

    /** Structural, per-row validation. */
    public function rules(): array
    {
        return [
            'center_code'   => ['required', 'string'],
            'phone'         => ['required'],
            'academic_year' => ['required', 'integer', 'digits:4'],
            'exam_marks'    => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'status'        => ['nullable', 'string', 'in:' . implode(',', self::ALLOWED_STATUS)],
        ];
    }
}
