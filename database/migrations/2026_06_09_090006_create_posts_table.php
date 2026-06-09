<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // NULL = Trust-wide post; otherwise scoped to one center
            $table->foreignId('center_id')
                ->nullable()
                ->constrained('centers')
                ->cascadeOnDelete();

            $table->foreignId('user_id')                  // author
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');                  // sanitized on output (XSS)
            $table->string('feature_image')->nullable();

            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['center_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
