<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            // Self-referencing for dropdowns (one level of nesting).
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();

            $table->string('location', 20)->default('header');  // header | footer
            $table->string('label');

            // link_type: route | page | url | none (dropdown header)
            $table->string('link_type', 10)->default('none');
            $table->string('link_value')->nullable();           // route name, page slug, or URL
            $table->boolean('target_blank')->default(false);

            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location', 'parent_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
