<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_shows_published_activities_only(): void
    {
        Activity::create([
            'date' => '2024-01-06', 'center' => 'Hubballi',
            'title' => 'Reading skills session', 'body' => '<strong>Great</strong> session today.',
            'is_published' => true,
        ]);
        Activity::create([
            'date' => '2024-01-13', 'center' => 'Raichur',
            'title' => 'Secret draft activity', 'body' => 'Not ready yet.',
            'is_published' => false,
        ]);

        $resp = $this->get('/activities');

        $resp->assertSuccessful();
        $resp->assertSee('Reading skills session');
        $resp->assertSee('Hubballi');
        $resp->assertDontSee('Secret draft activity');
    }

    public function test_admin_can_render_activity_resource_pages(): void
    {
        Role::findOrCreate('Trust Admin');
        $user = User::factory()->create();
        $user->assignRole('Trust Admin');

        $activity = Activity::create([
            'date' => '2024-01-06', 'center' => 'Hubballi',
            'title' => 'Reading skills session', 'body' => 'Body.', 'is_published' => false,
        ]);

        $this->actingAs($user)->get('/admin/activities')->assertSuccessful();
        $this->actingAs($user)->get('/admin/activities/create')->assertSuccessful();
        $this->actingAs($user)->get("/admin/activities/{$activity->id}/edit")->assertSuccessful();
    }
}
