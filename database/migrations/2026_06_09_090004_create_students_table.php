<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')
                ->constrained('centers')
                ->restrictOnDelete();   // protect: cannot delete a center with students attached

            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('guardian_name')->nullable();

            // Unique biometric / external profile reference (nullable until captured)
            $table->string('biometric_id', 64)->nullable()->unique();
            $table->string('photo_path')->nullable();     // scoped storage path

            $table->json('meta')->nullable();              // extensible profile fields
            $table->timestamps();

            // One human per center (see schema note in project docs)
            $table->unique(['center_id', 'phone']);
            // Result-gateway lookup: WHERE center_id = ? AND phone = ?
            $table->index(['center_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
