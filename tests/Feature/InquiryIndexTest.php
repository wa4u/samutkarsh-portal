<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InquiryIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_inquiries_index_renders_with_a_row(): void
    {
        Role::findOrCreate('Trust Admin');
        $user = User::factory()->create();
        $user->assignRole('Trust Admin');

        Inquiry::create([
            'name' => 'Test Parent', 'phone' => '9876543210', 'email' => null,
            'center_id' => null, 'subject' => null, 'message' => 'Hello', 'status' => 'new',
        ]);

        $this->withoutExceptionHandling();
        $this->actingAs($user)->get('/admin/inquiries')->assertSuccessful();
    }
}
