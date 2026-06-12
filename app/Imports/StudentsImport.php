<?php

namespace App\Imports;

use App\Models\Center;
use App\Models\Student;
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
 * Bulk student import.
 *
 * Heading row: name, phone, email, dob, gender, guardian_name, center_code
 *
 * - Center Heads: center is FORCED to their own (center_code in the file is
 *   ignored), so they can only import into their container.
 * - Trust Admin / Education Council: each row's `center_code` selects the center.
 *
 * Upserts on (center_id, phone) — re-importing updates rather than duplicating.
 */
class StudentsImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    use Importable, SkipsFailures, SkipsErrors;

    public int $imported = 0;
    public int $updated = 0;

    /** @var array<int,string> */
    public array $unmatched = [];

    public function __construct(private readonly ?int $forcedCenterId = null) {}

    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $centerId = $this->forcedCenterId;
        if (! $centerId) {
            $center = Center::where('code', $data['center_code'] ?? null)->first();
            if (! $center) {
                $this->unmatched[] = "Row {$row->getIndex()}: unknown center_code '" . ($data['center_code'] ?? '') . "'";
                return;
            }
            $centerId = $center->id;
        }

        $student = Student::firstOrNew([
            'center_id' => $centerId,
            'phone'     => (string) $data['phone'],
        ]);
        $existed = $student->exists;

        $student->fill([
            'name'          => $data['name'],
            'email'         => $data['email'] ?? $student->email,
            'dob'           => ! empty($data['dob']) ? $data['dob'] : $student->dob,
            'gender'        => $data['gender'] ?? $student->gender,
            'guardian_name' => $data['guardian_name'] ?? $student->guardian_name,
        ])->save();

        $existed ? $this->updated++ : $this->imported++;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'regex:/^[6-9]\d{9}$/'],
            'email'         => ['nullable', 'email'],
            'dob'           => ['nullable'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            // center_code only required for global users (no forced center).
            'center_code'   => [$this->forcedCenterId ? 'nullable' : 'required'],
        ];
    }
}
