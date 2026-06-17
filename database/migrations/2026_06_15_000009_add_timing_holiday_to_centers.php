<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contact timing (all centres) and holiday information (shown on the Head
 * Office) for the Contact page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->string('contact_timing')->nullable()->after('contact_email');
            $table->text('holiday_info')->nullable()->after('contact_timing');
        });

        // Pre-fill the holiday note on the Head Office so it shows immediately.
        DB::table('centers')->where('is_head_office', true)->whereNull('holiday_info')->update([
            'holiday_info' => 'Sunday: closes at 1 PM when classes are on; otherwise closed on Sunday.',
        ]);
    }

    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn(['contact_timing', 'holiday_info']);
        });
    }
};
