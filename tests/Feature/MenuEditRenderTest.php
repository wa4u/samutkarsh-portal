<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuEditRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_edit_page_renders(): void
    {
        Role::findOrCreate('Trust Admin');
        $user = User::factory()->create();
        $user->assignRole('Trust Admin');

        $item = MenuItem::create([
            'location'   => 'header',
            'label'      => 'Home',
            'link_type'  => 'route',
            'link_value' => 'public.home',
            'sort'       => 1,
            'is_active'  => true,
        ]);

        $this->actingAs($user)->get('/admin/posts/create')->assertSuccessful();   // other relationship selects
        $this->actingAs($user)->get('/admin/menu-items/create')->assertSuccessful(); // menu form, no record
        $this->actingAs($user)->get("/admin/menu-items/{$item->id}/edit")->assertSuccessful();
    }
}
