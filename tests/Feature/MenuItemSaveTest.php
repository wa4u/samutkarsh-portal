<?php

namespace Tests\Feature;

use App\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use App\Models\MenuItem;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuItemSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Role::findOrCreate('Trust Admin');
        $user = User::factory()->create();
        $user->assignRole('Trust Admin');
        $this->actingAs($user);
    }

    public function test_route_menu_item_persists_its_target_into_link_value(): void
    {
        Livewire::test(CreateMenuItem::class)
            ->fillForm([
                'label'        => 'Activities',
                'location'     => 'header',
                'link_type'    => 'route',
                'route_target' => 'public.activities',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // The bug: route_target was dropped on save, leaving link_value null (→ '#').
        $item = MenuItem::where('label', 'Activities')->firstOrFail();
        $this->assertSame('public.activities', $item->link_value);
        $this->assertStringEndsWith('/activities', $item->url());
    }

    public function test_external_url_menu_item_persists_link_value(): void
    {
        Livewire::test(CreateMenuItem::class)
            ->fillForm([
                'label'      => 'YouTube',
                'location'   => 'footer',
                'link_type'  => 'url',
                'url_target' => 'https://youtube.com/@samutkarsh',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('https://youtube.com/@samutkarsh', MenuItem::where('label', 'YouTube')->value('link_value'));
    }
}
