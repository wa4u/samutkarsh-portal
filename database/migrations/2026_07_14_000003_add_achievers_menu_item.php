<?php

use App\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;

/**
 * Add an "Achievers" item to the header menu so the new pages are discoverable.
 * Idempotent; placed just after "Results & Achievements" (top-level items below
 * it shift down one to make room). Admin can reorder/hide it afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = MenuItem::where('location', 'header')->whereNull('parent_id')
            ->where('link_value', 'public.achievers.index')->exists();

        if ($exists) {
            return;
        }

        MenuItem::where('location', 'header')->whereNull('parent_id')
            ->where('sort', '>=', 4)->increment('sort');

        MenuItem::create([
            'location'   => 'header',
            'parent_id'  => null,
            'label'      => 'Achievers',
            'link_type'  => 'route',
            'link_value' => 'public.achievers.index',
            'sort'       => 4,
            'is_active'  => true,
        ]);
    }

    public function down(): void
    {
        MenuItem::where('location', 'header')->whereNull('parent_id')
            ->where('link_value', 'public.achievers.index')->delete();
    }
};
