<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles & permissions must exist before we assign them.
        $this->call(RolePermissionSeeder::class);

        // 2. Trust Admin (global super-user, no center).
        $admin = User::firstOrCreate(
            ['email' => 'akash@ser.vi'],
            ['name' => 'Akash (Trust Admin)', 'password' => Hash::make('password'), 'center_id' => null],
        );
        $admin->syncRoles('Trust Admin');

        // 3. A demo center + Center Head so the scoping can be tested immediately.
        $center = Center::firstOrCreate(
            ['code' => 'AMD'],
            ['name' => 'Samutkarsh — Ahmedabad', 'city' => 'Ahmedabad', 'is_active' => true],
        );

        $head = User::firstOrCreate(
            ['email' => 'head.amd@ser.vi'],
            ['name' => 'Ahmedabad Center Head', 'password' => Hash::make('password'), 'center_id' => $center->id],
        );
        $head->syncRoles('Center Head');
    }
}
