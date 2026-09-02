<?php

namespace Database\Seeders;

use App\Models\Branch;
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
        // 1. Seed Cabang / Outlet Sample
        $branchA = Branch::updateOrCreate(
            ['code' => 'CAB-BDG-01'],
            [
                'name' => 'Cabang A - Bandung Pusat',
                'address' => 'Jl. Asia Afrika No. 12, Bandung',
                'phone' => '022-4201234',
                'status' => 'active',
            ]
        );

        $branchB = Branch::updateOrCreate(
            ['code' => 'CAB-JKT-02'],
            [
                'name' => 'Cabang B - Jakarta Selatan',
                'address' => 'Jl. TB Simatupang No. 88, Jakarta Selatan',
                'phone' => '021-7891234',
                'status' => 'active',
            ]
        );

        $branchC = Branch::updateOrCreate(
            ['code' => 'CAB-SBY-03'],
            [
                'name' => 'Cabang C - Surabaya',
                'address' => 'Jl. Pemuda No. 45, Surabaya',
                'phone' => '031-5341234',
                'status' => 'active',
            ]
        );

        // 2. Seed Super Admin Default
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

        // 3. Seed Admin Cabang A
        User::updateOrCreate(
            ['email' => 'cabang.a@ikhlas.com'],
            [
                'name' => 'Admin Cabang A',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $branchA->id,
                'status' => 'active',
            ]
        );

        // 4. Seed Admin Cabang B
        User::updateOrCreate(
            ['email' => 'cabang.b@ikhlas.com'],
            [
                'name' => 'Admin Cabang B',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $branchB->id,
                'status' => 'active',
            ]
        );

        // 5. Seed Viewer
        User::updateOrCreate(
            ['email' => 'viewer@ikhlas.com'],
            [
                'name' => 'Staf Viewer',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_VIEWER,
                'branch_id' => $branchA->id,
                'status' => 'active',
            ]
        );
    }
}
