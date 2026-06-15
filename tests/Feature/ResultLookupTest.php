<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_all_children_on_one_phone_without_marks(): void
    {
        $year = config('admissions.academic_year');
        $center = Center::create(['name' => 'Hubballi', 'code' => 'HBL', 'city' => 'Hubballi', 'is_active' => true]);

        $kids = [
            ['Aanya Kumar', 'selected'],
            ['Vivaan Kumar', 'not_selected'],
        ];
        foreach ($kids as [$name, $status]) {
            $student = Student::create(['center_id' => $center->id, 'name' => $name, 'phone' => '9876543210']);
            Registration::create([
                'student_id' => $student->id, 'center_id' => $center->id,
                'academic_year' => $year, 'status' => $status, 'exam_marks' => 87.5,
            ]);
        }

        $resp = $this->post('/result', ['center_id' => $center->id, 'phone' => '9876543210']);

        $resp->assertSuccessful();
        $resp->assertSee('Aanya Kumar');
        $resp->assertSee('Vivaan Kumar');         // both siblings shown
        $resp->assertSee('Selected for admission');
        $resp->assertSee('Not selected');
        $resp->assertDontSee('Marks');             // marks never shown
        $resp->assertDontSee('87.5');
    }
}
