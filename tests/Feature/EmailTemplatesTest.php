<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\MailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_trust_admin_can_open_email_templates_page(): void
    {
        Role::findOrCreate('Trust Admin');
        $user = User::factory()->create();
        $user->assignRole('Trust Admin');

        $this->actingAs($user)->get('/admin/email-templates')->assertSuccessful();
    }

    public function test_template_renders_tokens_with_custom_value(): void
    {
        Setting::updateOrCreate(['key' => 'mail.student_subject'], ['value' => 'Hi {student_name} ({year})', 'type' => 'text', 'group' => 'mail']);

        $subject = MailTemplate::subject('mail.student_subject', [
            '{student_name}' => 'Asha', '{year}' => '2026',
        ]);

        $this->assertSame('Hi Asha (2026)', $subject);
    }
}
