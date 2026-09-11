<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Transaction;
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
        // 1. Seed 3 Cabang Utama
        $jkt = Branch::updateOrCreate(
            ['code' => 'CAB-JKT-01'],
            [
                'name' => 'Jakarta Pusat',
                'address' => 'Jl. Thamrin No. 10, Jakarta Pusat',
                'phone' => '021-3901234',
                'status' => 'active',
            ]
        );

        $bdg = Branch::updateOrCreate(
            ['code' => 'CAB-BDG-02'],
            [
                'name' => 'Bandung',
                'address' => 'Jl. Asia Afrika No. 45, Bandung',
                'phone' => '022-4201234',
                'status' => 'active',
            ]
        );

        $sby = Branch::updateOrCreate(
            ['code' => 'CAB-SBY-03'],
            [
                'name' => 'Surabaya',
                'address' => 'Jl. Pemuda No. 88, Surabaya',
                'phone' => '031-5341234',
                'status' => 'active',
            ]
        );

        // 2. Seed Users Sesuai Brief & Mockup
        // Kepala Cabang (Membawahi semua cabang)
        $hendra = User::updateOrCreate(
            ['email' => 'hendra@ikhlas.com'],
            [
                'name' => 'Hendra Wijaya',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_KEPALA_CABANG,
                'branch_id' => null,
                'status' => 'active',
            ]
        );

        // Super Admin (Cadangan / Developer)
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

        // Admin Jakarta Pusat
        $andi = User::updateOrCreate(
            ['email' => 'andi@ikhlas.com'],
            [
                'name' => 'Andi Saputra',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $jkt->id,
                'status' => 'active',
            ]
        );

        // Admin Bandung
        $budi = User::updateOrCreate(
            ['email' => 'budi@ikhlas.com'],
            [
                'name' => 'Budi Raharjo',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $bdg->id,
                'status' => 'active',
            ]
        );

        // Admin Surabaya
        $citra = User::updateOrCreate(
            ['email' => 'citra@ikhlas.com'],
            [
                'name' => 'Citra Dewi',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $sby->id,
                'status' => 'active',
            ]
        );

        // 3. Seed 12 Data Transaksi (Total Rp 191.250.000, Total Qty 160)
        Transaction::truncate();

        $transactions = [
            // Jakarta Pusat (4 trx = Rp 74.000.000, Qty 65)
            [
                'code' => 'TRX-001',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-08',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 15,
                'amount' => 16700000,
                'notes' => 'Resume penjualan tunai produk A & B',
            ],
            [
                'code' => 'TRX-002',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-08',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'PT Makmur Jaya',
                'qty' => 20,
                'amount' => 25000000,
                'notes' => 'Penjualan invoice tempo 30 hari',
            ],
            [
                'code' => 'TRX-003',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-09',
                'type' => 'Transfer Cabang',
                'customer_name' => 'Cabang Bandung',
                'qty' => 20,
                'amount' => 22300000,
                'notes' => 'Transfer stok barang cabang',
            ],
            [
                'code' => 'TRX-004',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-10',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Toko Sinar Terang',
                'qty' => 10,
                'amount' => 10000000,
                'notes' => 'Penjualan tunai langsung',
            ],

            // Bandung (4 trx = Rp 46.250.000, Qty 45)
            [
                'code' => 'TRX-005',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-08',
                'type' => 'Retur Penjualan',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 5,
                'amount' => -3900000,
                'notes' => 'Retur barang rusak pengiriman',
            ],
            [
                'code' => 'TRX-006',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-09',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 15,
                'amount' => 16700000,
                'notes' => 'Pembelian grosir tunai',
            ],
            [
                'code' => 'TRX-007',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-09',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'UD Sejahtera',
                'qty' => 15,
                'amount' => 18450000,
                'notes' => 'Kredit usaha mikro',
            ],
            [
                'code' => 'TRX-008',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-10',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Toko Barokah',
                'qty' => 10,
                'amount' => 15000000,
                'notes' => 'Penjualan offline store',
            ],

            // Surabaya (4 trx = Rp 71.000.000, Qty 50)
            [
                'code' => 'TRX-009',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-08',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 15,
                'amount' => 16700000,
                'notes' => 'Invoice pengadaan bulanan',
            ],
            [
                'code' => 'TRX-010',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-09',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 15,
                'amount' => 16700000,
                'notes' => 'Penjualan tunai kasir',
            ],
            [
                'code' => 'TRX-011',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-09',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Mitra Usaha',
                'qty' => 10,
                'amount' => 20000000,
                'notes' => 'Penjualan instansi',
            ],
            [
                'code' => 'TRX-012',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-10',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'CV Gemilang',
                'qty' => 10,
                'amount' => 17600000,
                'notes' => 'Invoice tempo 14 hari',
            ],
        ];

        foreach ($transactions as $trx) {
            Transaction::create($trx);
        }
    }
}
