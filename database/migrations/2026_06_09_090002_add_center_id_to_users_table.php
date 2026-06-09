<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NULL for Trust Admin / Education Council (global users).
            // Required (non-null) for Center Heads — enforced at the application layer.
            $table->foreignId('center_id')
                ->nullable()
                ->after('email')
                ->constrained('centers')
                ->nullOnDelete();   // detach user if a center is removed; never orphan-delete a user

            $table->index('center_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('center_id');
        });
    }
};
