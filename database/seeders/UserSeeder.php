<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LoanLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicLevel = LoanLevel::where('name', 'Basic')->first();

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@classicwallet.org',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@classicwallet.org',
                'role' => 'admin',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@classicwallet.org',
                'role' => 'manager',
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@classicwallet.org',
                'role' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'loan_level_id' => $basicLevel?->id,
                ]
            );

            $user->assignRole($userData['role']);
        }
    }
}
