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

    public function test_public_page_filters_by_center(): void
    {
        Activity::create(['date' => '2024-01-06', 'center' => 'Hubballi', 'title' => 'Hubballi session', 'body' => 'x', 'is_published' => true]);
        Activity::create(['date' => '2024-01-13', 'center' => 'Raichur', 'title' => 'Raichur session', 'body' => 'y', 'is_published' => true]);

        $resp = $this->get('/activities?center=Hubballi');

        $resp->assertSuccessful();
        $resp->assertSee('Hubballi session');
        $resp->assertDontSee('Raichur session');
    }

    public function test_public_page_search_matches_title_and_body(): void
    {
        Activity::create(['date' => '2024-01-06', 'center' => 'Hubballi', 'title' => 'Yoga day celebration', 'body' => 'We did surya namaskar.', 'is_published' => true]);
        Activity::create(['date' => '2024-01-13', 'center' => 'Raichur', 'title' => 'Debate competition', 'body' => 'Students argued well.', 'is_published' => true]);

        $resp = $this->get('/activities?q=surya');

        $resp->assertSuccessful();
        $resp->assertSee('Yoga day celebration');
        $resp->assertDontSee('Debate competition');
    }

    public function test_detail_page_renders_with_og_tags_for_published(): void
    {
        $a = Activity::create(['date' => '2024-01-06', 'center' => 'Hubballi', 'title' => 'Yoga day', 'body' => 'We did surya namaskar today.', 'is_published' => true]);

        $resp = $this->get(route('public.activity.show', $a));

        $resp->assertSuccessful();
        $resp->assertSee('Yoga day');
        $resp->assertSee('og:title', false);
    }

    public function test_detail_page_404_for_unpublished(): void
    {
        $a = Activity::create(['date' => '2024-01-06', 'title' => 'Secret', 'body' => 'x', 'is_published' => false]);

        $this->get(route('public.activity.show', $a))->assertNotFound();
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
