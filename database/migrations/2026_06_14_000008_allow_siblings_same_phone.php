<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Siblings can share one mobile number. Drop the unique (center_id, phone)
 * constraint so two children with the same phone (distinguished by name) can
 * each have their own registration. The plain (center_id, phone) index — used
 * by the result lookup — is kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['center_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unique(['center_id', 'phone']);
        });
    }
};
