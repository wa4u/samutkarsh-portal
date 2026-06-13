<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            // Physical office (show address + map) vs virtual (phone-reachable).
            $table->boolean('is_physical')->default(false)->after('is_active');
            $table->boolean('is_head_office')->default(false)->after('is_physical');
            $table->unsignedInteger('sort')->default(0)->after('is_head_office');
        });
    }

    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn(['is_physical', 'is_head_office', 'sort']);
        });
    }
};
