<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Centre + date on testimonials, so the public page can filter by centre and
 * year (same as Activities) and feedback can be shown chronologically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('center')->nullable()->after('role');
            $table->date('date')->nullable()->after('event');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['center', 'date']);
        });
    }
};
