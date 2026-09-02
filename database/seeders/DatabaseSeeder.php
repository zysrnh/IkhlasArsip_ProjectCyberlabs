<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Admin Default
        User::updateOrCreate(
            ['email' => 'admin@ikhlas.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_SUPERADMIN,
                'branch_id' => null,
                'status' => 'active',
            ]
        );

        // Admin Cabang A Sample
        User::updateOrCreate(
            ['email' => 'cabang.a@ikhlas.com'],
            [
                'name' => 'Admin Cabang A',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => 1,
                'status' => 'active',
            ]
        );
    }
}
