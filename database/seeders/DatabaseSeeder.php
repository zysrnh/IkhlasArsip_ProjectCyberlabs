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
     * Seed the application's database with realistic sales data.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Transaction::truncate();
        User::truncate();
        Branch::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Seed 4 Unit Cabang Resmi
        $jkt = Branch::create([
            'code' => 'CAB-JKT-01',
            'name' => 'Jakarta Pusat',
            'address' => 'Jl. M.H. Thamrin No. 10, Jakarta Pusat',
            'phone' => '021-3901234',
            'status' => 'active',
        ]);

        $bdg = Branch::create([
            'code' => 'CAB-BDG-02',
            'name' => 'Bandung',
            'address' => 'Jl. Asia Afrika No. 45, Bandung',
            'phone' => '022-4201234',
            'status' => 'active',
        ]);

        $sby = Branch::create([
            'code' => 'CAB-SBY-03',
            'name' => 'Surabaya',
            'address' => 'Jl. Pemuda No. 88, Surabaya',
            'phone' => '031-5341234',
            'status' => 'active',
        ]);

        $ble = Branch::create([
            'code' => 'CAB-BLE-04',
            'name' => 'Baleendah',
            'address' => 'Jl. Raya Banjaran No. 112, Baleendah, Kab. Bandung',
            'phone' => '022-5941234',
            'status' => 'active',
        ]);

        // 2. Seed Akun Pengguna
        // Super Administrator
        User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@ikhlas.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SUPERADMIN,
            'branch_id' => null,
            'status' => 'active',
        ]);

        // Kepala Cabang Jakarta Pusat
        $hendra = User::create([
            'name' => 'Hendra Wijaya',
            'email' => 'hendra@ikhlas.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_KEPALA_CABANG,
            'branch_id' => $jkt->id,
            'status' => 'active',
        ]);

        // Admin Cabang Jakarta
        $andi = User::create([
            'name' => 'Andi Saputra',
            'email' => 'andi@ikhlas.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN_CABANG,
            'branch_id' => $jkt->id,
            'status' => 'active',
        ]);

        // Admin Cabang Bandung
        $budi = User::create([
            'name' => 'Budi Raharjo',
            'email' => 'budi@ikhlas.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN_CABANG,
            'branch_id' => $bdg->id,
            'status' => 'active',
        ]);

        // Admin Cabang Surabaya
        $citra = User::create([
            'name' => 'Citra Dewi',
            'email' => 'citra@ikhlas.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN_CABANG,
            'branch_id' => $sby->id,
            'status' => 'active',
        ]);

        // Admin Cabang Baleendah
        $dani = User::create([
            'name' => 'Dani Permana',
            'email' => 'dani@ikhlas.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN_CABANG,
            'branch_id' => $ble->id,
            'status' => 'active',
        ]);

        // 3. Seed 24 Transaksi Penjualan Realistis Sepanjang September 2026
        $rawTransactions = [
            // 1 September 2026
            [
                'code' => 'TRX-20260901-001',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-01',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Makmur Jaya',
                'qty' => 25,
                'amount' => 31250000,
                'notes' => 'Pengadaan stok perlengkapan kantor Q3',
            ],
            [
                'code' => 'TRX-20260901-002',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-01',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 15,
                'amount' => 18750000,
                'notes' => 'Pembelian langsung unit display',
            ],

            // 2 September 2026
            [
                'code' => 'TRX-20260902-003',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-02',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'PT Harapan Nusantara',
                'qty' => 40,
                'amount' => 48000000,
                'notes' => 'Invoice tempo 30 hari term of payment',
            ],
            [
                'code' => 'TRX-20260902-004',
                'branch_id' => $ble->id,
                'user_id' => $dani->id,
                'transaction_date' => '2026-09-02',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Toko Berkah Abadi',
                'qty' => 10,
                'amount' => 12500000,
                'notes' => 'Penjualan tunai grosir daerah Bandung Selatan',
            ],

            // 3 September 2026
            [
                'code' => 'TRX-20260903-005',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-03',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'CV Sinar Mandiri',
                'qty' => 30,
                'amount' => 36000000,
                'notes' => 'Faktur pesanan PO-JKT-0982',
            ],
            [
                'code' => 'TRX-20260903-006',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-03',
                'type' => 'Retur Penjualan',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 2,
                'amount' => -2500000,
                'notes' => 'Retur cacat kemasan pengiriman logistik',
            ],

            // 4 September 2026
            [
                'code' => 'TRX-20260904-007',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-04',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'UD Sejahtera Abadi',
                'qty' => 18,
                'amount' => 22500000,
                'notes' => 'Order cash and carry',
            ],
            [
                'code' => 'TRX-20260904-008',
                'branch_id' => $ble->id,
                'user_id' => $dani->id,
                'transaction_date' => '2026-09-04',
                'type' => 'Transfer Cabang',
                'customer_name' => 'Cabang Bandung',
                'qty' => 15,
                'amount' => 16500000,
                'notes' => 'Distribusi pemindahan stok antar outlet',
            ],

            // 5 September 2026
            [
                'code' => 'TRX-20260905-009',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-05',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Makmur Jaya',
                'qty' => 35,
                'amount' => 43750000,
                'notes' => 'Repeat order batch kedua',
            ],
            [
                'code' => 'TRX-20260905-010',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-05',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'Toko Sinar Terang',
                'qty' => 20,
                'amount' => 24000000,
                'notes' => 'Pemesanan reguler mingguan',
            ],

            // 6 September 2026
            [
                'code' => 'TRX-20260906-011',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-06',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Mitra Global Sentosa',
                'qty' => 50,
                'amount' => 62500000,
                'notes' => 'Paket pengadaan instansi kemitraan',
            ],
            [
                'code' => 'TRX-20260906-012',
                'branch_id' => $ble->id,
                'user_id' => $dani->id,
                'transaction_date' => '2026-09-06',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Toko Berkah Abadi',
                'qty' => 12,
                'amount' => 15000000,
                'notes' => 'Pembelian retail tambahan',
            ],

            // 7 September 2026
            [
                'code' => 'TRX-20260907-013',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-07',
                'type' => 'Transfer Cabang',
                'customer_name' => 'Cabang Surabaya',
                'qty' => 20,
                'amount' => 22000000,
                'notes' => 'Supply alokasi stok Jawa Timur',
            ],
            [
                'code' => 'TRX-20260907-014',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-07',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Inti Makmur',
                'qty' => 14,
                'amount' => 17500000,
                'notes' => 'Pelunasan transaksi outlet cabang',
            ],

            // 8 September 2026
            [
                'code' => 'TRX-20260908-015',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-08',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 15,
                'amount' => 18750000,
                'notes' => 'Pembelian unit aksesoris',
            ],
            [
                'code' => 'TRX-20260908-016',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-08',
                'type' => 'Retur Penjualan',
                'customer_name' => 'UD Sejahtera Abadi',
                'qty' => 3,
                'amount' => -3750000,
                'notes' => 'Penukaran barang tidak sesuai spesifikasi',
            ],

            // 9 September 2026
            [
                'code' => 'TRX-20260909-017',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-09',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'Toko Sinar Terang',
                'qty' => 22,
                'amount' => 27500000,
                'notes' => 'Invoice nomor INV-BDG-0909',
            ],
            [
                'code' => 'TRX-20260909-018',
                'branch_id' => $ble->id,
                'user_id' => $dani->id,
                'transaction_date' => '2026-09-09',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Prima Mandiri',
                'qty' => 8,
                'amount' => 10000000,
                'notes' => 'Transaksi tunai kasir outlet',
            ],

            // 10 September 2026
            [
                'code' => 'TRX-20260910-019',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-10',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Mitra Global Sentosa',
                'qty' => 30,
                'amount' => 37500000,
                'notes' => 'Order cabang Jakarta korporat',
            ],
            [
                'code' => 'TRX-20260910-020',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-10',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Harapan Nusantara',
                'qty' => 16,
                'amount' => 20000000,
                'notes' => 'Pelunasan faktur cash',
            ],

            // 11 September 2026 (Hari Ini)
            [
                'code' => 'TRX-20260911-021',
                'branch_id' => $jkt->id,
                'user_id' => $andi->id,
                'transaction_date' => '2026-09-11',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Makmur Jaya',
                'qty' => 45,
                'amount' => 56250000,
                'notes' => 'Pesanan express pengiriman hari ini',
            ],
            [
                'code' => 'TRX-20260911-022',
                'branch_id' => $bdg->id,
                'user_id' => $budi->id,
                'transaction_date' => '2026-09-11',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Bumi Pertiwi',
                'qty' => 20,
                'amount' => 25000000,
                'notes' => 'Pengambilan di counter toko',
            ],
            [
                'code' => 'TRX-20260911-023',
                'branch_id' => $ble->id,
                'user_id' => $dani->id,
                'transaction_date' => '2026-09-11',
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Toko Berkah Abadi',
                'qty' => 18,
                'amount' => 22500000,
                'notes' => 'Pemesanan grosir harian',
            ],
            [
                'code' => 'TRX-20260911-024',
                'branch_id' => $sby->id,
                'user_id' => $citra->id,
                'transaction_date' => '2026-09-11',
                'type' => 'Penjualan Kredit',
                'customer_name' => 'UD Sejahtera Abadi',
                'qty' => 25,
                'amount' => 31250000,
                'notes' => 'Faktur termin 14 hari',
            ],
        ];

        foreach ($rawTransactions as $data) {
            Transaction::create($data);
        }
    }
}
