<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();

            $table->enum('type', ['image', 'youtube']);

            // Base path of the processed image (without size suffix), e.g.
            // centers/1/gallery/ab12cd — variants are <base>_display.webp etc.
            $table->string('image_path')->nullable();
            $table->string('youtube_id', 20)->nullable();   // parsed 11-char id (room to spare)

            $table->string('caption')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['gallery_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
