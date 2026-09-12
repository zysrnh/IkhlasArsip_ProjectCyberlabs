<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Transaction::truncate();
        User::truncate();
        Branch::truncate();
        Schema::enableForeignKeyConstraints();

        // Seed Akun Super Administrator (SU) Saja
        User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@ikhlas.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SUPERADMIN,
            'branch_id' => null,
            'status' => 'active',
        ]);
    }
}
