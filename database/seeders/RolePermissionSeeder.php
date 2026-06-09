<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Granular permissions, generated per resource. Trust Admin bypasses all of
     * these via a Gate::before short-circuit (see AppServiceProvider), so it is
     * intentionally NOT listed in the maps below.
     */
    public function run(): void
    {
        // Reset cached roles/permissions before (re)seeding.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = ['center', 'user', 'student', 'registration', 'post', 'setting'];
        $abilities = ['view_any', 'view', 'create', 'update', 'delete'];

        $permissions = [];
        foreach ($resources as $resource) {
            foreach ($abilities as $ability) {
                $permissions[] = "{$ability}_{$resource}";
            }
        }

        // Cross-cutting domain permissions (not plain CRUD).
        $permissions = array_merge($permissions, [
            'score_registration',   // enter / edit exam_marks
            'import_registration',  // bulk Excel/CSV import
            'record_payment',       // record cash / confirm UPI manually
            'view_any_payment',     // financial audit list
            'view_payment',
        ]);

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // ---- Education Council: global academic + scoring authority ----
        $council = Role::findOrCreate('Education Council', 'web');
        $council->syncPermissions([
            'view_any_registration', 'view_registration', 'update_registration',
            'score_registration', 'import_registration',
            'view_any_student', 'view_student',
            'view_any_setting', 'view_setting', 'update_setting',
            'view_any_post', 'view_post',
            'view_any_payment', 'view_payment',
        ]);

        // ---- Center Head: full control, but only over their own center's rows ----
        // (Row containment is enforced separately by ScopesToCenter + Policies.)
        $head = Role::findOrCreate('Center Head', 'web');
        $head->syncPermissions([
            'view_any_student', 'view_student', 'create_student', 'update_student', 'delete_student',
            'view_any_registration', 'view_registration', 'create_registration', 'update_registration', 'delete_registration',
            'score_registration', 'record_payment',
            'view_any_payment', 'view_payment',
            'view_any_post', 'view_post', 'create_post', 'update_post', 'delete_post',
        ]);

        // ---- Trust Admin: no explicit permissions needed (Gate::before grants all) ----
        Role::findOrCreate('Trust Admin', 'web');
    }
}
