<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        $permissions = [
            'view_dashboard',
            'create_readings',
            'edit_readings',
            'delete_readings',
            'view_own_consumption',
            'export_own_data',
            'view_global_dashboard',
            'manage_users',
            'manage_meters',
            'manage_readings',
            'export_all_data',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $citizen = Role::firstOrCreate(['name' => 'citoyen']);
        $admin = Role::firstOrCreate(['name' => 'administrateur']);

        // Assign permissions to roles
        $citizen->givePermissionTo([
            'view_dashboard',
            'create_readings',
            'edit_readings',
            'delete_readings',
            'view_own_consumption',
            'export_own_data'
        ]);
        $admin->givePermissionTo(Permission::all());
    }
}
