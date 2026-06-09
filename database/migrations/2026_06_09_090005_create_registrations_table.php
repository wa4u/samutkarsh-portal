<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();    // a student's attempts die with the student

            // Denormalized for fast, index-friendly Filament center scoping
            $table->foreignId('center_id')
                ->constrained('centers')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('academic_year'); // e.g. 2026

            $table->decimal('exam_marks', 6, 2)->nullable();
            $table->enum('status', ['pending', 'selected', 'not_selected', 'admitted'])
                ->default('pending')
                ->index();

            // Payment lifecycle (Razorpay/Cashfree)
            $table->string('payment_reference')->nullable()->index(); // gateway order/payment id
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('payment_status', 30)->nullable();         // created | paid | failed
            $table->timestamp('paid_at')->nullable();

            $table->text('remarks')->nullable();
            $table->timestamps();

            // Enforces the [phone + center_id + academic_year] invariant transitively
            $table->unique(['center_id', 'student_id', 'academic_year'], 'reg_center_student_year_unique');
            // Center Head dashboards: WHERE center_id = ? AND status = ?
            $table->index(['center_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
