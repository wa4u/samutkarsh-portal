<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();

            // Denormalized for fast center scoping / financial reporting.
            $table->foreignId('center_id')
                ->constrained('centers')
                ->restrictOnDelete();

            $table->string('gateway', 40);                 // razorpay | upi_qr | cash | ...
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending')->index();

            // Gateway order/payment id, UPI UTR, or manual receipt no.
            $table->string('reference')->nullable();
            $table->json('meta')->nullable();              // raw gateway payload / notes

            // Who recorded a MANUAL (cash / UPI-confirm) entry; null for webhook-driven.
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Idempotency: a given gateway reference is recorded at most once.
            $table->unique(['gateway', 'reference'], 'payments_gateway_reference_unique');
            $table->index(['center_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
