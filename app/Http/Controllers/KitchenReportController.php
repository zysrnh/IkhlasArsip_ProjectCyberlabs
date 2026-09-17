<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyKitchenReport;
use App\Models\DailyKitchenReportItem;
use App\Models\Menu;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class KitchenReportController extends Controller
{
    /**
     * Tampilkan daftar riwayat input masakan dapur harian per cabang.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        // Admin Cabang tidak memiliki akses ke dapur (dikhususkan untuk Admin Dapur, Kepala Cabang, Super Admin)
        if ($user->isAdminCabang()) {
            return redirect()->route('transactions.index')->with('error', 'Akses ditolak. Fitur laporan masakan dapur dikhususkan untuk Admin Dapur dan Kepala Cabang.');
        }

        $query = DailyKitchenReport::with(['branch', 'user'])->orderBy('report_date', 'desc');

        // Scoping akses berdasarkan role
        if ($user->isAdminDapur()) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isKepalaCabang()) {
            if ($user->managedBranches()->count() > 0) {
                $query->whereIn('branch_id', $user->managedBranches()->pluck('branches.id'));
            } elseif ($user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
        }

        // Filter Cabang
        if ($request->filled('branch_id') && ($user->isSuperAdmin() || $user->isKepalaCabang())) {
            $query->where('branch_id', $request->get('branch_id'));
        }

        // Filter Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->get('date_to'));
        }

        $reports = $query->paginate(15)->withQueryString();

        // Data cabang untuk dropdown filter
        if ($user->isSuperAdmin()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->isKepalaCabang() && $user->managedBranches()->count() > 0) {
            $branches = $user->managedBranches()->where('status', 'active')->orderBy('name')->get();
        } elseif ($user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        }

        $stats = [
            'total_reports' => (clone $query)->count(),
            'total_omset' => (clone $query)->sum('total_omset'),
            'total_wasted' => (clone $query)->sum('total_wasted_food'),
            'total_sellable' => (clone $query)->sum('total_remaining_sellable'),
        ];

        return view('kitchen.index', compact('reports', 'branches', 'stats'));
    }

    /**
     * Buka form input masakan dapur harian.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        // Admin Cabang & Viewer tidak memiliki izin input
        if ($user->isAdminCabang() || $user->isViewer()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin menginput laporan harian masakan dapur.');
        }

        // Tentukan cabang yang aktif
        $branchId = $request->get('branch_id');
        if (!$branchId || $user->isAdminDapur()) {
            $branchId = $user->branch_id ?? Branch::where('status', 'active')->first()?->id;
        }

        // Validasi akses cabang untuk Admin Dapur
        if ($user->isAdminDapur() && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        $targetDate = $request->filled('report_date') ? Carbon::parse($request->get('report_date')) : Carbon::today();
        $dateString = $targetDate->format('Y-m-d');

        // Cek apakah sudah ada laporan untuk cabang dan tanggal ini
        $existingReport = DailyKitchenReport::where('branch_id', $branchId)
            ->whereDate('report_date', $dateString)
            ->first();

        if ($existingReport) {
            return redirect()->route('kitchen-reports.edit', $existingReport->id)
                ->with('info', 'Laporan dapur untuk cabang ini pada tanggal ' . $targetDate->translatedFormat('d F Y') . ' sudah ada. Silakan lakukan edit/penyesuaian.');
        }

        // Ambil laporan hari sebelumnya (H-1) untuk auto-pull sisa kemarin
        $previousReport = DailyKitchenReport::with('items')
            ->where('branch_id', $branchId)
            ->whereDate('report_date', '<', $dateString)
            ->orderBy('report_date', 'desc')
            ->first();

        $prevItemsMap = [];
        if ($previousReport) {
            foreach ($previousReport->items as $pItem) {
                $prevItemsMap[$pItem->menu_id] = $pItem->remaining;
            }
        }

        // Ambil semua master menu yang aktif
        $menus = Menu::where('is_active', true)
            ->orderBy('order_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Siapkan item dengan harga cabang dan sisa kemarin otomatis
        $preparedItems = [];
        foreach ($menus as $menu) {
            $unitPrice = $menu->getPriceForBranch($branchId);

            // Logika Sisa Kemarin:
            // Jika Cepat Basi (is_perishable = true) -> Sisa kemarin otomatis 0 (rumus dimatikan karena dibuang)
            // Jika Lauk Biasa (is_perishable = false) -> Ambil dari sisa H-1
            $yesterdayRemaining = 0;
            if (!$menu->is_perishable && isset($prevItemsMap[$menu->id])) {
                $yesterdayRemaining = (int) $prevItemsMap[$menu->id];
            }

            $preparedItems[] = [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'is_perishable' => $menu->is_perishable,
                'order_number' => $menu->order_number,
                'unit_price' => $unitPrice,
                'yesterday_remaining' => $yesterdayRemaining,
                'cooked_today' => 0,
                'sold' => 0,
            ];
        }

        // Pilihan cabang untuk dropdown jika role mengizinkan
        if ($user->isSuperAdmin()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->isKepalaCabang() && $user->managedBranches()->count() > 0) {
            $branches = $user->managedBranches()->where('status', 'active')->orderBy('name')->get();
        } elseif ($user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        }

        $activeBranch = Branch::find($branchId);

        return view('kitchen.create', compact('branches', 'activeBranch', 'dateString', 'preparedItems', 'previousReport'));
    }

    /**
     * Simpan input masakan dapur harian.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin menginput laporan harian masakan dapur.');
        }

        // Jika Admin Dapur, pastikan branch_id terkunci ke cabangnya
        if ($user->isAdminDapur() && $user->branch_id) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        // Bersihkan format rupiah
        $cleanCash = (float) str_replace(['.', ','], ['', '.'], $request->input('cash_income', '0'));
        $cleanQris = (float) str_replace(['.', ','], ['', '.'], $request->input('qris_income', '0'));
        $cleanOnline = (float) str_replace(['.', ','], ['', '.'], $request->input('online_food_income', '0'));

        $request->merge([
            'cash_income' => $cleanCash,
            'qris_income' => $cleanQris,
            'online_food_income' => $cleanOnline,
        ]);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'report_date' => 'required|date',
            'cash_income' => 'required|numeric|min:0',
            'qris_income' => 'required|numeric|min:0',
            'online_food_income' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.yesterday_remaining' => 'required|integer|min:0',
            'items.*.cooked_today' => 'required|integer|min:0',
            'items.*.sold' => 'required|integer|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'branch_id.required' => 'Cabang wajib dipilih.',
            'report_date.required' => 'Tanggal laporan wajib diisi.',
            'items.required' => 'Data menu masakan tidak boleh kosong.',
        ]);

        // Cek duplikasi
        $existing = DailyKitchenReport::where('branch_id', $validated['branch_id'])
            ->whereDate('report_date', $validated['report_date'])
            ->first();

        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'Laporan dapur untuk cabang dan tanggal tersebut sudah pernah dibuat.');
        }

        DB::beginTransaction();
        try {
            $grandTotalSales = 0;
            $totalRemainingSellable = 0;
            $totalWastedFood = 0;

            // Pre-fetch menus untuk cek is_perishable
            $menuIds = array_column($validated['items'], 'menu_id');
            $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

            $itemsToInsert = [];

            foreach ($validated['items'] as $itemData) {
                $menu = $menus->get($itemData['menu_id']);
                $isPerishable = $menu ? $menu->is_perishable : false;

                $yesterdayRem = (int) $itemData['yesterday_remaining'];
                $cookedToday = (int) $itemData['cooked_today'];
                $totalCooked = $yesterdayRem + $cookedToday;
                $sold = (int) $itemData['sold'];
                $remaining = max(0, $totalCooked - $sold);
                $unitPrice = (float) $itemData['unit_price'];

                $totalSales = $sold * $unitPrice;
                $remainingSellableAmount = $isPerishable ? 0 : ($remaining * $unitPrice);
                $wastedFoodAmount = $isPerishable ? ($remaining * $unitPrice) : 0;

                $grandTotalSales += $totalSales;
                $totalRemainingSellable += $remainingSellableAmount;
                $totalWastedFood += $wastedFoodAmount;

                $itemsToInsert[] = [
                    'menu_id' => $itemData['menu_id'],
                    'yesterday_remaining' => $yesterdayRem,
                    'cooked_today' => $cookedToday,
                    'total_cooked' => $totalCooked,
                    'sold' => $sold,
                    'remaining' => $remaining,
                    'unit_price' => $unitPrice,
                    'total_sales' => $totalSales,
                    'remaining_sellable_amount' => $remainingSellableAmount,
                    'wasted_food_amount' => $wastedFoodAmount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $totalOmset = $cleanCash + $cleanQris + $cleanOnline;
            $diffAmount = $totalOmset - $grandTotalSales;

            $report = DailyKitchenReport::create([
                'branch_id' => $validated['branch_id'],
                'report_date' => $validated['report_date'],
                'user_id' => auth()->id(),
                'grand_total_sales' => $grandTotalSales,
                'total_remaining_sellable' => $totalRemainingSellable,
                'total_wasted_food' => $totalWastedFood,
                'cash_income' => $cleanCash,
                'qris_income' => $cleanQris,
                'online_food_income' => $cleanOnline,
                'total_omset' => $totalOmset,
                'difference_amount' => $diffAmount,
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($itemsToInsert as &$row) {
                $row['daily_kitchen_report_id'] = $report->id;
            }

            DailyKitchenReportItem::insert($itemsToInsert);

            DB::commit();

            return redirect()->route('kitchen-reports.show', $report->id)
                ->with('success', 'Laporan masakan dapur harian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail rincian laporan masakan dapur.
     */
    public function show(DailyKitchenReport $kitchenReport): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdminCabang()) {
            return redirect()->route('transactions.index')->with('error', 'Akses ditolak. Fitur laporan masakan dapur dikhususkan untuk Admin Dapur dan Kepala Cabang.');
        }

        if ($user->isAdminDapur() && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan cabang lain.');
        }

        $kitchenReport->load(['branch', 'user', 'items.menu' => function ($q) {
            $q->orderBy('order_number', 'asc')->orderBy('id', 'asc');
        }]);

        return view('kitchen.show', compact('kitchenReport'));
    }

    /**
     * Buka form edit laporan masakan dapur.
     */
    public function edit(DailyKitchenReport $kitchenReport): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin mengubah laporan.');
        }

        if ($user->isAdminDapur() && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan cabang lain.');
        }

        $kitchenReport->load(['branch', 'user', 'items.menu' => function ($q) {
            $q->orderBy('order_number', 'asc')->orderBy('id', 'asc');
        }]);

        if ($user->isSuperAdmin()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->isKepalaCabang() && $user->managedBranches()->count() > 0) {
            $branches = $user->managedBranches()->where('status', 'active')->orderBy('name')->get();
        } else {
            $branches = Branch::where('id', $kitchenReport->branch_id)->get();
        }

        return view('kitchen.edit', compact('kitchenReport', 'branches'));
    }

    /**
     * Update data laporan masakan dapur.
     */
    public function update(Request $request, DailyKitchenReport $kitchenReport): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin mengubah laporan.');
        }

        if ($user->isAdminDapur() && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan cabang lain.');
        }

        $cleanCash = (float) str_replace(['.', ','], ['', '.'], $request->input('cash_income', '0'));
        $cleanQris = (float) str_replace(['.', ','], ['', '.'], $request->input('qris_income', '0'));
        $cleanOnline = (float) str_replace(['.', ','], ['', '.'], $request->input('online_food_income', '0'));

        $request->merge([
            'cash_income' => $cleanCash,
            'qris_income' => $cleanQris,
            'online_food_income' => $cleanOnline,
        ]);

        $validated = $request->validate([
            'cash_income' => 'required|numeric|min:0',
            'qris_income' => 'required|numeric|min:0',
            'online_food_income' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:daily_kitchen_report_items,id',
            'items.*.yesterday_remaining' => 'required|integer|min:0',
            'items.*.cooked_today' => 'required|integer|min:0',
            'items.*.sold' => 'required|integer|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $grandTotalSales = 0;
            $totalRemainingSellable = 0;
            $totalWastedFood = 0;

            $existingItems = $kitchenReport->items()->with('menu')->get()->keyBy('id');

            foreach ($validated['items'] as $itemData) {
                $item = $existingItems->get($itemData['id']);
                if (!$item) continue;

                $isPerishable = $item->menu ? $item->menu->is_perishable : false;
                $yesterdayRem = (int) $itemData['yesterday_remaining'];
                $cookedToday = (int) $itemData['cooked_today'];
                $totalCooked = $yesterdayRem + $cookedToday;
                $sold = (int) $itemData['sold'];
                $remaining = max(0, $totalCooked - $sold);
                $unitPrice = (float) $itemData['unit_price'];

                $totalSales = $sold * $unitPrice;
                $remainingSellableAmount = $isPerishable ? 0 : ($remaining * $unitPrice);
                $wastedFoodAmount = $isPerishable ? ($remaining * $unitPrice) : 0;

                $grandTotalSales += $totalSales;
                $totalRemainingSellable += $remainingSellableAmount;
                $totalWastedFood += $wastedFoodAmount;

                $item->update([
                    'yesterday_remaining' => $yesterdayRem,
                    'cooked_today' => $cookedToday,
                    'total_cooked' => $totalCooked,
                    'sold' => $sold,
                    'remaining' => $remaining,
                    'unit_price' => $unitPrice,
                    'total_sales' => $totalSales,
                    'remaining_sellable_amount' => $remainingSellableAmount,
                    'wasted_food_amount' => $wastedFoodAmount,
                ]);
            }

            $totalOmset = $cleanCash + $cleanQris + $cleanOnline;
            $diffAmount = $totalOmset - $grandTotalSales;

            $kitchenReport->update([
                'grand_total_sales' => $grandTotalSales,
                'total_remaining_sellable' => $totalRemainingSellable,
                'total_wasted_food' => $totalWastedFood,
                'cash_income' => $cleanCash,
                'qris_income' => $cleanQris,
                'online_food_income' => $cleanOnline,
                'total_omset' => $totalOmset,
                'difference_amount' => $diffAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('kitchen-reports.show', $kitchenReport->id)
                ->with('success', 'Laporan masakan dapur harian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus laporan masakan dapur.
     */
    public function destroy(DailyKitchenReport $kitchenReport): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer() || $user->isAdminCabang()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin menghapus laporan.');
        }

        if ($user->isAdminDapur() && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan cabang lain.');
        }

        $dateStr = $kitchenReport->report_date->translatedFormat('d F Y');
        $branchName = $kitchenReport->branch->name ?? 'Cabang';

        $kitchenReport->delete();

        return redirect()->route('kitchen-reports.index')
            ->with('success', "Laporan dapur {$branchName} tanggal {$dateStr} berhasil dihapus.");
    }

    /**
     * Export Laporan Masakan Dapur Harian ke Format PDF Resmi (A4 Landscape)
     */
    public function exportPdf(DailyKitchenReport $kitchenReport): Response|RedirectResponse
    {
        $user = auth()->user();
        if ($user->isAdminCabang()) {
            return redirect()->route('transactions.index')->with('error', 'Akses ditolak.');
        }

        if ($user->isAdminDapur() && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan cabang lain.');
        }

        $kitchenReport->load(['branch', 'user', 'items.menu' => function ($q) {
            $q->orderBy('order_number', 'asc')->orderBy('id', 'asc');
        }]);

        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('kitchen.pdf', [
            'kitchenReport' => $kitchenReport,
            'printedAt' => now()->translatedFormat('d F Y, H:i'),
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'landscape');

        $branchSlug = str_replace(' ', '_', $kitchenReport->branch->name ?? 'Cabang');
        $dateSlug = $kitchenReport->report_date->format('Ymd');
        $fileName = 'Laporan_Dapur_' . $branchSlug . '_' . $dateSlug . '.pdf';

        return $pdf->download($fileName);
    }
}
