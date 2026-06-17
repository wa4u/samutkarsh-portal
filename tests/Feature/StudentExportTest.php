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

    public function test_guest_cannot_download(): void
    {
        $this->get(route('admin.students.export'))->assertForbidden();
    }
}
