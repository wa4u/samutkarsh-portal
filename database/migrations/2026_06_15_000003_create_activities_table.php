<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Weekly session / event reports — the "what we did" diary written by
 * centre coordinators (originally posted in the WhatsApp group).
 * Imported once from the chat export, then maintained in admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('center')->nullable();      // e.g. "Belagavi North", "Raichur"
            $table->string('title');
            $table->longText('body');                   // limited HTML (strong/em/br)
            $table->string('source')->nullable();       // 'whatsapp' for imported rows
            $table->string('source_hash')->nullable()->unique(); // idempotent re-import guard
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
