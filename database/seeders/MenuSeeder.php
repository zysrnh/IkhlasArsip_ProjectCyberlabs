<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchMenuPrice;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuList = [
            // Sayuran & Cepat Basi (is_perishable = true)
            ['order' => 1, 'name' => 'Sayur Sop', 'price' => 2000, 'perishable' => true],
            ['order' => 2, 'name' => 'Sayur Toge', 'price' => 2000, 'perishable' => true],
            ['order' => 3, 'name' => 'Sayur Kacang Panjang', 'price' => 2000, 'perishable' => true],
            ['order' => 4, 'name' => 'Sayur Labu', 'price' => 2000, 'perishable' => true],
            ['order' => 5, 'name' => 'Sayur Sawi', 'price' => 2000, 'perishable' => true],
            ['order' => 6, 'name' => 'Sayur Kangkung', 'price' => 2000, 'perishable' => true],
            ['order' => 7, 'name' => 'Sayur Jamur', 'price' => 3000, 'perishable' => true],
            ['order' => 8, 'name' => 'Sayur Daun Singkong', 'price' => 3000, 'perishable' => true],
            ['order' => 9, 'name' => 'Sayur Nangka', 'price' => 3000, 'perishable' => true],
            ['order' => 42, 'name' => 'Mie Goreng', 'price' => 2000, 'perishable' => true],
            ['order' => 43, 'name' => 'Sayur Pare', 'price' => 2000, 'perishable' => true],
            ['order' => 44, 'name' => 'Sayur Acar Timun', 'price' => 2000, 'perishable' => true],
            ['order' => 45, 'name' => 'Sayur Acar Kuning', 'price' => 3000, 'perishable' => true],
            ['order' => 46, 'name' => 'Sayur Tahu Santan', 'price' => 3000, 'perishable' => true],
            ['order' => 47, 'name' => 'Sayur Capcay', 'price' => 3000, 'perishable' => true],
            ['order' => 54, 'name' => 'Gorengan', 'price' => 1000, 'perishable' => true],

            // Lauk Tahan Lama / Protein (is_perishable = false)
            ['order' => 10, 'name' => 'Terong Balado', 'price' => 3000, 'perishable' => false],
            ['order' => 11, 'name' => 'Kerang Balado', 'price' => 4000, 'perishable' => false],
            ['order' => 12, 'name' => 'Kikil', 'price' => 4000, 'perishable' => false],
            ['order' => 13, 'name' => 'Usus', 'price' => 4000, 'perishable' => false],
            ['order' => 14, 'name' => 'Ati Ampela', 'price' => 5000, 'perishable' => false],
            ['order' => 15, 'name' => 'Udang', 'price' => 5000, 'perishable' => false],
            ['order' => 16, 'name' => 'Teri Kacang', 'price' => 4000, 'perishable' => false],
            ['order' => 17, 'name' => 'Ikan Kembung Goreng', 'price' => 8000, 'perishable' => false],
            ['order' => 18, 'name' => 'Ikan Kembung Balado', 'price' => 8000, 'perishable' => false],
            ['order' => 19, 'name' => 'Ikan Lele Goreng', 'price' => 8000, 'perishable' => false],
            ['order' => 20, 'name' => 'Ikan Bawal Goreng', 'price' => 9000, 'perishable' => false],
            ['order' => 21, 'name' => 'Ikan Bandeng Goreng', 'price' => 7000, 'perishable' => false],
            ['order' => 22, 'name' => 'Ikan Bandeng Balado', 'price' => 7000, 'perishable' => false],
            ['order' => 23, 'name' => 'Ikan Nila Goreng', 'price' => 8000, 'perishable' => false],
            ['order' => 24, 'name' => 'Ikan Tongkol Balado', 'price' => 5000, 'perishable' => false],
            ['order' => 25, 'name' => 'Cumi Balado', 'price' => 5000, 'perishable' => false],
            ['order' => 26, 'name' => 'Ayam Goreng', 'price' => 9000, 'perishable' => false],
            ['order' => 27, 'name' => 'Ayam Balado', 'price' => 9000, 'perishable' => false],
            ['order' => 28, 'name' => 'Ayam Cabe Ijo', 'price' => 9000, 'perishable' => false],
            ['order' => 29, 'name' => 'Ayam Opor', 'price' => 9000, 'perishable' => false],
            ['order' => 30, 'name' => 'Ayam Semur', 'price' => 9000, 'perishable' => false],
            ['order' => 31, 'name' => 'Ayam Serundeng', 'price' => 9000, 'perishable' => false],
            ['order' => 32, 'name' => 'Kentang Balado', 'price' => 3000, 'perishable' => false],
            ['order' => 33, 'name' => 'Kentang Musthofa', 'price' => 3000, 'perishable' => false],
            ['order' => 34, 'name' => 'Perkedel', 'price' => 3000, 'perishable' => false],
            ['order' => 35, 'name' => 'Telur Dadar', 'price' => 5000, 'perishable' => false],
            ['order' => 36, 'name' => 'Telur Ceplok', 'price' => 5000, 'perishable' => false],
            ['order' => 37, 'name' => 'Telur Ceplok Balado', 'price' => 5000, 'perishable' => false],
            ['order' => 38, 'name' => 'Telur Bulat Balado', 'price' => 5000, 'perishable' => false],
            ['order' => 39, 'name' => 'Oreg Tempe Basah', 'price' => 3000, 'perishable' => false],
            ['order' => 40, 'name' => 'Oreg Tempe Kering', 'price' => 3000, 'perishable' => false],
            ['order' => 41, 'name' => 'Oreg Tahu Balado', 'price' => 3000, 'perishable' => false],
            ['order' => 48, 'name' => 'Telur Asin', 'price' => 2000, 'perishable' => false],
            ['order' => 49, 'name' => 'Tahu Kuning', 'price' => 2000, 'perishable' => false],
            ['order' => 50, 'name' => 'Bakso Sosis', 'price' => 2000, 'perishable' => false],
            ['order' => 51, 'name' => 'Otak Otak', 'price' => 5000, 'perishable' => false],
            ['order' => 52, 'name' => 'Semur Jengkol', 'price' => 5000, 'perishable' => false],
            ['order' => 53, 'name' => 'Nasi', 'price' => 5000, 'perishable' => false],
            ['order' => 56, 'name' => 'Paket Nasi Box 25', 'price' => 25000, 'perishable' => false],
            ['order' => 57, 'name' => 'Paket Snack', 'price' => 15000, 'perishable' => false],
            ['order' => 58, 'name' => 'Paket Nasi Box 20', 'price' => 20000, 'perishable' => false],
        ];

        // Urutkan berdasarkan order_number
        usort($menuList, fn ($a, $b) => $a['order'] <=> $b['order']);

        $branches = Branch::all();

        foreach ($menuList as $item) {
            $menu = Menu::updateOrCreate(
                ['name' => $item['name']],
                [
                    'order_number' => $item['order'],
                    'is_perishable' => $item['perishable'],
                    'default_price' => $item['price'],
                    'is_active' => true,
                ]
            );

            // Inisialisasi harga cabang
            foreach ($branches as $branch) {
                BranchMenuPrice::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'price' => $item['price'],
                    ]
                );
            }
        }
    }
}
