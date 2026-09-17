<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchMenuPrice;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Tampilkan daftar master menu masakan dan harga per cabang.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        // Admin Cabang tidak memiliki akses ke dapur/menu
        if ($user->isAdminCabang()) {
            return redirect()->route('transactions.index')->with('error', 'Akses ditolak. Pengelolaan menu masakan dikhususkan untuk Admin Dapur dan Kepala Cabang.');
        }

        $query = Menu::with('branchPrices.branch')->orderBy('order_number', 'asc')->orderBy('id', 'asc');

        // Filter pencarian nama menu
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter kategori sifat lauk (cepat basi / lauk biasa)
        if ($request->filled('category')) {
            $cat = $request->get('category');
            if ($cat === 'perishable') {
                $query->where('is_perishable', true);
            } elseif ($cat === 'non_perishable') {
                $query->where('is_perishable', false);
            }
        }

        // Filter status aktif
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $menus = $query->paginate(20)->withQueryString();

        // Tentukan daftar cabang yang ditampilkan di tabel
        if ($user->isSuperAdmin()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        }

        $stats = [
            'total_menus' => Menu::count(),
            'total_sayur' => Menu::where('is_perishable', true)->count(),
            'total_lauk' => Menu::where('is_perishable', false)->count(),
            'total_active' => Menu::where('is_active', true)->count(),
        ];

        return view('menus.index', compact('menus', 'branches', 'stats'));
    }

    /**
     * Simpan menu masakan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('menus.index')->with('error', 'Anda tidak memiliki izin menambah menu.');
        }

        // Bersihkan format titik rupiah pada harga
        if ($request->filled('default_price')) {
            $request->merge([
                'default_price' => str_replace('.', '', $request->input('default_price'))
            ]);
        }

        $validated = $request->validate([
            'order_number' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255|unique:menus,name',
            'is_perishable' => 'required|boolean',
            'default_price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama masakan wajib diisi.',
            'name.unique' => 'Nama masakan sudah terdaftar.',
            'is_perishable.required' => 'Sifat lauk wajib dipilih.',
            'default_price.required' => 'Harga default wajib diisi.',
            'default_price.numeric' => 'Harga harus berupa angka yang valid.',
        ]);

        $orderNumber = $validated['order_number'] ?? (Menu::max('order_number') + 1);

        $menu = Menu::create([
            'order_number' => $orderNumber,
            'name' => trim($validated['name']),
            'is_perishable' => (bool) $validated['is_perishable'],
            'default_price' => $validated['default_price'],
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        // Otomatis assign harga default ke seluruh cabang
        $branches = Branch::all();
        foreach ($branches as $branch) {
            BranchMenuPrice::updateOrCreate(
                ['branch_id' => $branch->id, 'menu_id' => $menu->id],
                ['price' => $validated['default_price']]
            );
        }

        return redirect()->route('menus.index')->with('success', 'Menu masakan baru "' . $menu->name . '" berhasil ditambahkan.');
    }

    /**
     * Update data menu masakan.
     */
    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('menus.index')->with('error', 'Anda tidak memiliki izin mengubah menu.');
        }

        // Bersihkan format titik rupiah pada harga
        if ($request->filled('default_price')) {
            $request->merge([
                'default_price' => str_replace('.', '', $request->input('default_price'))
            ]);
        }

        $validated = $request->validate([
            'order_number' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255|unique:menus,name,' . $menu->id,
            'is_perishable' => 'required|boolean',
            'default_price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama masakan wajib diisi.',
            'name.unique' => 'Nama masakan sudah digunakan menu lain.',
            'is_perishable.required' => 'Sifat lauk wajib dipilih.',
            'default_price.required' => 'Harga default wajib diisi.',
        ]);

        $menu->update([
            'order_number' => $validated['order_number'] ?? $menu->order_number,
            'name' => trim($validated['name']),
            'is_perishable' => (bool) $validated['is_perishable'],
            'default_price' => $validated['default_price'],
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : false,
        ]);

        return redirect()->route('menus.index')->with('success', 'Data menu masakan "' . $menu->name . '" berhasil diperbarui.');
    }

    /**
     * Update harga menu khusus per cabang.
     */
    public function updatePrices(Request $request, Menu $menu): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('menus.index')->with('error', 'Anda tidak memiliki izin mengatur harga.');
        }

        $rawPrices = $request->input('prices', []);
        $cleanedPrices = [];
        foreach ($rawPrices as $branchId => $priceVal) {
            $cleanedPrices[$branchId] = $priceVal !== null ? str_replace('.', '', $priceVal) : null;
        }
        $request->merge(['prices' => $cleanedPrices]);

        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['prices'] as $branchId => $price) {
            // Jika admin dapur, hanya boleh update harga cabangnya sendiri
            if ($user->isAdminDapur() && $user->branch_id && $branchId != $user->branch_id) {
                continue;
            }

            if ($price !== null) {
                BranchMenuPrice::updateOrCreate(
                    ['branch_id' => $branchId, 'menu_id' => $menu->id],
                    ['price' => $price]
                );
            }
        }

        return redirect()->route('menus.index')->with('success', 'Harga cabang untuk menu "' . $menu->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus menu masakan.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('menus.index')->with('error', 'Anda tidak memiliki izin menghapus menu.');
        }

        $menuName = $menu->name;
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu masakan "' . $menuName . '" berhasil dihapus.');
    }
}
