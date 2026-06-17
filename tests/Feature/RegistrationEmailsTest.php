<?php

namespace Tests\Feature;

use App\Mail\RegistrationReceivedAdmin;
use App\Mail\RegistrationReceivedStudent;
use App\Models\Center;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_ho_centre_and_student_emails(): void
    {
        config(['admissions.registration_open' => true, 'admissions.academic_year' => '2026']);
        Mail::fake();

        Setting::updateOrCreate(['key' => 'notify.registration_email'], ['value' => 'ho@example.com', 'type' => 'text', 'group' => 'notifications']);
        $center = Center::create(['name' => 'Hubballi', 'code' => 'HBL', 'city' => 'Hubballi', 'is_active' => true, 'contact_email' => 'centre@example.com']);

        $this->post('/register', [
            'center_id'     => $center->id,
            'name'          => 'Asha Kumar',
            'phone'         => '9876543210',
            'email'         => 'asha@example.com',
            'student_class' => '7',
        ])->assertRedirect(route('public.register.success'));

        $this->assertDatabaseHas('students', ['name' => 'Asha Kumar', 'student_class' => '7']);

        // HO + centre (distinct addresses) → 2 admin mails.
        Mail::assertQueued(RegistrationReceivedAdmin::class, 2);
        Mail::assertQueued(RegistrationReceivedStudent::class, fn ($m) => $m->hasTo('asha@example.com'));
    }

    public function test_no_student_email_when_none_provided(): void
    {
        config(['admissions.registration_open' => true, 'admissions.academic_year' => '2026']);
        Mail::fake();

        Setting::updateOrCreate(['key' => 'notify.registration_email'], ['value' => 'ho@example.com', 'type' => 'text', 'group' => 'notifications']);
        $center = Center::create(['name' => 'Raichur', 'code' => 'RCR', 'city' => 'Raichur', 'is_active' => true]);

        $this->post('/register', [
            'center_id'     => $center->id,
            'name'          => 'No Email Kid',
            'phone'         => '9876500000',
            'student_class' => 'college',
        ])->assertRedirect(route('public.register.success'));

        Mail::assertNotQueued(RegistrationReceivedStudent::class);
        Mail::assertQueued(RegistrationReceivedAdmin::class, 1); // HO only (no centre email set)
    }
}
