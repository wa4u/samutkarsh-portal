<?php

namespace Tests\Feature;

use App\Models\HomeSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomeSectionEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_section_index_and_edit_forms_render(): void
    {
        Role::findOrCreate('Trust Admin');
        $user = User::factory()->create();
        $user->assignRole('Trust Admin');

        $this->actingAs($user)->get('/admin/home-sections')->assertSuccessful();

        // Each section's edit form must render (catches duplicate-statePath bugs).
        foreach (['hero', 'why', 'programmes', 'audience', 'cta', 'blog', 'testimonials'] as $key) {
            $section = HomeSection::where('key', $key)->firstOrFail();
            $this->actingAs($user)
                ->get("/admin/home-sections/{$section->id}/edit")
                ->assertSuccessful();
        }
    }
}
