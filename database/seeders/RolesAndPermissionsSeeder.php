<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate Shield permissions for all resources, pages, widgets, etc.
        Artisan::call('shield:generate --all --panel=admin --no-interaction');

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create core roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // Define custom/manual permissions
        $permissions = [
            'view_admin_panel',
            'manage_users',
            'manage_settings',
            'manage_loans',
            'manage_shares',
            'manage_plans',
            'manage_kycs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to super_admin
        $superAdmin->givePermissionTo(Permission::all());

        // Assign specific permissions to other roles
        $admin->givePermissionTo([
            'view_admin_panel',
            'manage_loans',
            'manage_shares',
            'manage_plans',
            'manage_kycs',
        ]);

        $manager->givePermissionTo([
            'view_admin_panel',
            'manage_loans',
            'manage_plans',
        ]);
    }
}
