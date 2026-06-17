<?php

namespace Tests\Feature;

use App\Models\Center;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_trust_admin_can_download_students_csv_filtered_by_centre(): void
    {
        Role::findOrCreate('Trust Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Trust Admin');

        $hbl = Center::create(['name' => 'Hubballi', 'code' => 'HBL', 'city' => 'Hubballi', 'is_active' => true]);
        $rcr = Center::create(['name' => 'Raichur', 'code' => 'RCR', 'city' => 'Raichur', 'is_active' => true]);
        Student::create(['center_id' => $hbl->id, 'name' => 'Asha Kumar', 'phone' => '9876543210']);
        Student::create(['center_id' => $rcr->id, 'name' => 'Ravi Patil', 'phone' => '9876500000']);

        // All centres
        $all = $this->actingAs($admin)->get(route('admin.students.export'));
        $all->assertOk();
        $this->assertStringContainsString('text/csv', $all->headers->get('Content-Type'));
        $body = $all->streamedContent();
        $this->assertStringContainsString('Asha Kumar', $body);
        $this->assertStringContainsString('Ravi Patil', $body);

        // Filtered by centre
        $filtered = $this->actingAs($admin)->get(route('admin.students.export', ['center' => $hbl->id]));
        $filteredBody = $filtered->streamedContent();
        $this->assertStringContainsString('Asha Kumar', $filteredBody);
        $this->assertStringNotContainsString('Ravi Patil', $filteredBody);
    }

    public function test_filters_by_registration_date_range(): void
    {
        Role::findOrCreate('Trust Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Trust Admin');

        $c = Center::create(['name' => 'Hubballi', 'code' => 'HBL', 'city' => 'Hubballi', 'is_active' => true]);
        $old = Student::create(['center_id' => $c->id, 'name' => 'Old Student', 'phone' => '9000000001']);
        Student::where('id', $old->id)->update(['created_at' => '2024-01-01 10:00:00']);
        Student::create(['center_id' => $c->id, 'name' => 'Recent Student', 'phone' => '9000000002']); // created now (2026)

        $resp = $this->actingAs($admin)->get(route('admin.students.export', ['from' => '2026-01-01']));
        $body = $resp->streamedContent();

        $this->assertStringContainsString('Recent Student', $body);
        $this->assertStringNotContainsString('Old Student', $body);
    }

    public function test_filters_by_class(): void
    {
        Role::findOrCreate('Trust Admin');
        $admin = User::factory()->create();
        $admin->assignRole('Trust Admin');

        $c = Center::create(['name' => 'Hubballi', 'code' => 'HBL', 'city' => 'Hubballi', 'is_active' => true]);
        Student::create(['center_id' => $c->id, 'name' => 'Sixth Kid', 'phone' => '9000000003', 'student_class' => '6']);
        Student::create(['center_id' => $c->id, 'name' => 'College Kid', 'phone' => '9000000004', 'student_class' => 'college']);

        $body = $this->actingAs($admin)->get(route('admin.students.export', ['class' => '6']))->streamedContent();

        $this->assertStringContainsString('Sixth Kid', $body);
        $this->assertStringContainsString('Class 6', $body);   // label, not raw code
        $this->assertStringNotContainsString('College Kid', $body);
    }

    public function test_guest_cannot_download(): void
    {
        $this->get(route('admin.students.export'))->assertForbidden();
    }
}
