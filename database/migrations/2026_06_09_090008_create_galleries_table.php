<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();

            // NULL = Trust-wide album; otherwise scoped to a center.
            $table->foreignId('center_id')->nullable()->constrained('centers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();   // creator

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();

            // Moderation: nothing goes public until a Trust Admin approves.
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort')->default(0);

            $table->timestamps();

            $table->index(['center_id', 'approval_status', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
