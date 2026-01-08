<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Permission Management
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            // Loan Management
            'view loans',
            'create loans',
            'edit loans',
            'delete loans',
            'approve loans',
            'disburse loans',

            // Share Management
            'view shares',
            'create shares',
            'edit shares',
            'delete shares',
            'approve shares',

            // Dividend Management
            'view dividends',
            'create dividends',
            'edit dividends',
            'delete dividends',
            'process dividends',

            // Transaction Management
            'view transactions',
            'view own transactions',

            // Wallet Management
            'view wallets',
            'manage own wallet',

            // Utility Plans Management
            'view utility plans',
            'create utility plans',
            'edit utility plans',
            'delete utility plans',

            // Brand Management
            'view brands',
            'create brands',
            'edit brands',
            'delete brands',

            // Dashboard
            'view admin dashboard',
            'view user dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles and assign permissions

        // Super Admin - has all permissions
        $superAdmin = Role::create([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - has most permissions except super admin specific ones
        $admin = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view roles',
            'view permissions',
            'view loans',
            'create loans',
            'edit loans',
            'approve loans',
            'disburse loans',
            'view shares',
            'create shares',
            'edit shares',
            'approve shares',
            'view dividends',
            'create dividends',
            'edit dividends',
            'process dividends',
            'view transactions',
            'view wallets',
            'view utility plans',
            'create utility plans',
            'edit utility plans',
            'view brands',
            'create brands',
            'edit brands',
            'view admin dashboard',
        ]);

        // Manager - can manage loans, shares, and dividends
        $manager = Role::create([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);
        $manager->givePermissionTo([
            'view loans',
            'create loans',
            'edit loans',
            'approve loans',
            'view shares',
            'create shares',
            'edit shares',
            'approve shares',
            'view dividends',
            'create dividends',
            'edit dividends',
            'view transactions',
            'view wallets',
            'view admin dashboard',
        ]);

        // User - basic permissions for regular users
        $user = Role::create([
            'name' => 'user',
            'guard_name' => 'web',
        ]);
        $user->givePermissionTo([
            'view own transactions',
            'manage own wallet',
            'view user dashboard',
        ]);

        */
        // Create admin users
        $superAdminUser = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $superAdminUser->assignRole('super-admin');

        $adminUser = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Super Admin: superadmin@example.com / password');
        $this->command->info('Admin: admin@example.com / password');
    }
}
