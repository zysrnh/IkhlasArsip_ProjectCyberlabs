<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat / Update Master Cabang
        $branchBdg = Branch::firstOrCreate(
            ['code' => 'BDG-01'],
            [
                'name' => 'Cabang Bandung',
                'address' => 'Jl. Asia Afrika No. 12, Bandung, Jawa Barat',
                'phone' => '022-4201234',
                'status' => 'active',
            ]
        );

        $branchJkt = Branch::firstOrCreate(
            ['code' => 'JKT-01'],
            [
                'name' => 'Cabang Jakarta',
                'address' => 'Jl. Jend. Sudirman Kav. 45, Jakarta Pusat, DKI Jakarta',
                'phone' => '021-5705678',
                'status' => 'active',
            ]
        );

        $branchSby = Branch::firstOrCreate(
            ['code' => 'SBY-01'],
            [
                'name' => 'Cabang Surabaya',
                'address' => 'Jl. Basuki Rahmat No. 88, Surabaya, Jawa Timur',
                'phone' => '031-5348899',
                'status' => 'active',
            ]
        );

        // 2. Buat / Update Akun Pengguna Semua Role
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@ikhlas.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_SUPERADMIN,
                'branch_id' => null,
                'status' => 'active',
            ]
        );

        $kepalaBdg = User::updateOrCreate(
            ['email' => 'kepala.bandung@ikhlas.com'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_KEPALA_CABANG,
                'branch_id' => $branchBdg->id,
                'status' => 'active',
            ]
        );

        $adminBdg = User::updateOrCreate(
            ['email' => 'admin.bandung@ikhlas.com'],
            [
                'name' => 'Dimas Pratama',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $branchBdg->id,
                'status' => 'active',
            ]
        );

        $adminJkt = User::updateOrCreate(
            ['email' => 'admin.jakarta@ikhlas.com'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $branchJkt->id,
                'status' => 'active',
            ]
        );

        $adminSby = User::updateOrCreate(
            ['email' => 'admin.surabaya@ikhlas.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_CABANG,
                'branch_id' => $branchSby->id,
                'status' => 'active',
            ]
        );

        // Akun Admin Dapur Cabang
        $dapurBdg = User::updateOrCreate(
            ['email' => 'dapur.bandung@ikhlas.com'],
            [
                'name' => 'Chef Asep (Dapur Bandung)',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_DAPUR,
                'branch_id' => $branchBdg->id,
                'status' => 'active',
            ]
        );

        $dapurJkt = User::updateOrCreate(
            ['email' => 'dapur.jakarta@ikhlas.com'],
            [
                'name' => 'Chef Joko (Dapur Jakarta)',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN_DAPUR,
                'branch_id' => $branchJkt->id,
                'status' => 'active',
            ]
        );

        $viewerUser = User::updateOrCreate(
            ['email' => 'viewer@ikhlas.com'],
            [
                'name' => 'Staff Pengawas Pusat',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_VIEWER,
                'branch_id' => null,
                'status' => 'active',
            ]
        );

        // 3. Seed Menu Masakan & Harga Default Cabang
        $this->call(MenuSeeder::class);

        // 4. Buat Data Dummy Transaksi (Aktif & Sampah)
        $now = Carbon::now();

        $transactionsData = [
            // Cabang Bandung
            [
                'code' => 'TRX-BDG-20260901',
                'branch_id' => $branchBdg->id,
                'user_id' => $adminBdg->id,
                'transaction_date' => $now->copy()->subDays(13)->format('Y-m-d'),
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Surya Mega Pratama',
                'qty' => 12,
                'amount' => 15500000.00,
                'notes' => 'Pengadaan paket arsip digital kantor cabang.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-BDG-20260903',
                'branch_id' => $branchBdg->id,
                'user_id' => $adminBdg->id,
                'transaction_date' => $now->copy()->subDays(11)->format('Y-m-d'),
                'type' => 'Penjualan Kredit',
                'customer_name' => 'Dinas Kearsipan Kota Bandung',
                'qty' => 5,
                'amount' => 6250000.00,
                'notes' => 'Layanan backup cloud berkas perkara.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-BDG-20260905',
                'branch_id' => $branchBdg->id,
                'user_id' => $adminBdg->id,
                'transaction_date' => $now->copy()->subDays(9)->format('Y-m-d'),
                'type' => 'Jasa Layanan',
                'customer_name' => 'Koperasi Mandiri Sejahtera',
                'qty' => 20,
                'amount' => 24000000.00,
                'notes' => 'Restorasi dan digitalisasi dokumen fisik.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-BDG-20260908',
                'branch_id' => $branchBdg->id,
                'user_id' => $adminBdg->id,
                'transaction_date' => $now->copy()->subDays(6)->format('Y-m-d'),
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Klinik Medika Bandung',
                'qty' => 8,
                'amount' => 9800000.00,
                'notes' => 'Sistem rekam medis digital.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-BDG-20260911',
                'branch_id' => $branchBdg->id,
                'user_id' => $adminBdg->id,
                'transaction_date' => $now->copy()->subDays(3)->format('Y-m-d'),
                'type' => 'Penjualan Kredit',
                'customer_name' => 'Universitas Pasundan',
                'qty' => 15,
                'amount' => 18750000.00,
                'notes' => 'Arsip ijazah & transkrip elektronik.',
                'deleted_at' => null,
            ],

            // Cabang Jakarta
            [
                'code' => 'TRX-JKT-20260902',
                'branch_id' => $branchJkt->id,
                'user_id' => $adminJkt->id,
                'transaction_date' => $now->copy()->subDays(12)->format('Y-m-d'),
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Astra Graha Sentosa',
                'qty' => 30,
                'amount' => 45000000.00,
                'notes' => 'Lisensi sistem manajemen arsip enterprise.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-JKT-20260904',
                'branch_id' => $branchJkt->id,
                'user_id' => $adminJkt->id,
                'transaction_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'type' => 'Jasa Layanan',
                'customer_name' => 'Kementerian Investasi',
                'qty' => 50,
                'amount' => 75000000.00,
                'notes' => 'Pemeliharaan server data center arsip.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-JKT-20260906',
                'branch_id' => $branchJkt->id,
                'user_id' => $adminJkt->id,
                'transaction_date' => $now->copy()->subDays(8)->format('Y-m-d'),
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Bank Central Asia Tbk',
                'qty' => 25,
                'amount' => 37500000.00,
                'notes' => 'Enkripsi data nasabah prioritas.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-JKT-20260909',
                'branch_id' => $branchJkt->id,
                'user_id' => $adminJkt->id,
                'transaction_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'type' => 'Penjualan Kredit',
                'customer_name' => 'PT Telkom Indonesia',
                'qty' => 40,
                'amount' => 60000000.00,
                'notes' => 'Integrasi API kearsipan korporat.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-JKT-20260912',
                'branch_id' => $branchJkt->id,
                'user_id' => $adminJkt->id,
                'transaction_date' => $now->copy()->subDays(2)->format('Y-m-d'),
                'type' => 'Jasa Layanan',
                'customer_name' => 'Kantor Notaris & PPAT Jakarta',
                'qty' => 10,
                'amount' => 12500000.00,
                'notes' => 'Vault penyimpanan akta digital bersertifikat.',
                'deleted_at' => null,
            ],

            // Cabang Surabaya
            [
                'code' => 'TRX-SBY-20260901',
                'branch_id' => $branchSby->id,
                'user_id' => $adminSby->id,
                'transaction_date' => $now->copy()->subDays(13)->format('Y-m-d'),
                'type' => 'Penjualan Tunai',
                'customer_name' => 'PT Maspion Surabaya',
                'qty' => 18,
                'amount' => 22000000.00,
                'notes' => 'Peralatan scanner high-speed & software arsip.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-SBY-20260904',
                'branch_id' => $branchSby->id,
                'user_id' => $adminSby->id,
                'transaction_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'type' => 'Penjualan Kredit',
                'customer_name' => 'RSUD Dr. Soetomo',
                'qty' => 22,
                'amount' => 27500000.00,
                'notes' => 'Migrasi rekam medis rawat inap ke microcloud.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-SBY-20260907',
                'branch_id' => $branchSby->id,
                'user_id' => $adminSby->id,
                'transaction_date' => $now->copy()->subDays(7)->format('Y-m-d'),
                'type' => 'Jasa Layanan',
                'customer_name' => 'Pelabuhan Tanjung Perak',
                'qty' => 35,
                'amount' => 42000000.00,
                'notes' => 'Sistem manifest kargo & audit kearsipan logistik.',
                'deleted_at' => null,
            ],
            [
                'code' => 'TRX-SBY-20260910',
                'branch_id' => $branchSby->id,
                'user_id' => $adminSby->id,
                'transaction_date' => $now->copy()->subDays(4)->format('Y-m-d'),
                'type' => 'Penjualan Tunai',
                'customer_name' => 'CV Sumber Rejeki Abadi',
                'qty' => 7,
                'amount' => 8400000.00,
                'notes' => 'Perangkat pembaca RFID dokumen arsip kantor.',
                'deleted_at' => null,
            ],

            // Data Sampah (Soft Deleted) untuk testing menu Sampah Transaksi
            [
                'code' => 'TRX-TRASH-001',
                'branch_id' => $branchBdg->id,
                'user_id' => $adminBdg->id,
                'transaction_date' => $now->copy()->subDays(15)->format('Y-m-d'),
                'type' => 'Penjualan Tunai',
                'customer_name' => 'Pelanggan Salah Input (Dummy)',
                'qty' => 1,
                'amount' => 500000.00,
                'notes' => 'Dihapus karena salah memilih jenis produk.',
                'deleted_at' => $now->copy()->subDays(2),
            ],
            [
                'code' => 'TRX-TRASH-002',
                'branch_id' => $branchJkt->id,
                'user_id' => $adminJkt->id,
                'transaction_date' => $now->copy()->subDays(14)->format('Y-m-d'),
                'type' => 'Penjualan Kredit',
                'customer_name' => 'Invoice Dibatalkan Customer',
                'qty' => 3,
                'amount' => 3500000.00,
                'notes' => 'Transaksi dibatalkan karena customer mengubah PO.',
                'deleted_at' => $now->copy()->subDay(),
            ],
            [
                'code' => 'TRX-TRASH-003',
                'branch_id' => $branchSby->id,
                'user_id' => $adminSby->id,
                'transaction_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'type' => 'Retur Penjualan',
                'customer_name' => 'Data Duplikat Uji Coba',
                'qty' => 2,
                'amount' => 1200000.00,
                'notes' => 'Duplikasi input data pada saat uji coba migrasi.',
                'deleted_at' => $now->copy()->subHours(6),
            ],
        ];

        foreach ($transactionsData as $trx) {
            $deletedAt = $trx['deleted_at'];
            unset($trx['deleted_at']);

            $transaction = Transaction::withTrashed()->firstOrNew(['code' => $trx['code']]);
            $transaction->fill($trx);
            $transaction->save();

            if ($deletedAt) {
                $transaction->deleted_at = $deletedAt;
                $transaction->save();
            } else {
                if ($transaction->trashed()) {
                    $transaction->restore();
                }
            }
        }
    }
}
