<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // matches a partial in views/public/home/sections
            $table->string('label');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        $sections = [
            ['hero', 'Hero banner'],
            ['audience', 'Audience quick-links'],
            ['programmes', 'Programmes grid'],
            ['why', 'Why Samutkarsh (values + stats)'],
            ['blog', 'Latest blog posts'],
            ['cta', 'Closing call-to-action'],
        ];
        foreach ($sections as $i => [$key, $label]) {
            DB::table('home_sections')->insert([
                'key' => $key, 'label' => $label, 'is_enabled' => true, 'sort' => $i + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
