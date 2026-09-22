<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyKitchenReport;
use App\Models\DailyKitchenReportItem;
use App\Models\Menu;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KitchenReportController extends Controller
{
    /**
     * Tampilkan daftar riwayat input masakan dapur harian per cabang dengan filter, search & sorting lengkap.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = DailyKitchenReport::with(['branch', 'user']);

        // Scoping akses berdasarkan role
        if ($user->isAdminCabang() || $user->isAdminDapur()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } elseif ($user->isKepalaCabang() || $user->isViewer()) {
            $accessibleBranchIds = $user->getAccessibleBranchIds();
            $selectedBranchId = $request->get('branch_id');
            if (!empty($selectedBranchId) && in_array($selectedBranchId, $accessibleBranchIds)) {
                $query->where('branch_id', $selectedBranchId);
            } else {
                $query->whereIn('branch_id', $accessibleBranchIds);
            }
        } else {
            $selectedBranchId = $request->get('branch_id');
            if (!empty($selectedBranchId)) {
                $query->where('branch_id', $selectedBranchId);
            }
        }

        // Search Keyword (PIC / Nama Cabang / Catatan)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('branch', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Status Selisih Kasir
        if ($request->filled('diff_status')) {
            $diffStatus = $request->get('diff_status');
            if ($diffStatus === 'match') {
                $query->whereRaw('ABS(difference_amount) < 1');
            } elseif ($diffStatus === 'over') {
                $query->where('difference_amount', '>=', 1);
            } elseif ($diffStatus === 'under') {
                $query->where('difference_amount', '<=', -1);
            }
        }

        // Filter Tanggal Dari
        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->get('date_from'));
        }

        // Filter Tanggal Sampai
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->get('date_to'));
        }

        // Data Cabang untuk Dropdown Filter
        if ($user->isSuperAdmin()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->isKepalaCabang() || $user->isViewer()) {
            $branches = $user->getAccessibleBranches();
        } elseif ($user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        }

        // Hitung Statistik Dinamis Sesuai Filter
        $stats = [
            'total_reports' => (clone $query)->count(),
            'total_sales' => (float) (clone $query)->sum('grand_total_sales'),
            'total_omset' => (float) (clone $query)->sum('total_omset'),
            'total_wasted' => (float) (clone $query)->sum('total_wasted_food'),
            'total_sellable' => (float) (clone $query)->sum('total_remaining_sellable'),
            'total_diff' => (float) (clone $query)->sum('difference_amount'),
            'total_cash' => (float) (clone $query)->sum('cash_income'),
            'total_qris' => (float) (clone $query)->sum('qris_income'),
            'total_online' => (float) (clone $query)->sum('online_food_income'),
        ];

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->orderBy('report_date', 'asc')->orderBy('id', 'asc');
                break;
            case 'omset_terbanyak':
                $query->orderBy('total_omset', 'desc');
                break;
            case 'omset_tersedikit':
                $query->orderBy('total_omset', 'asc');
                break;
            case 'penjualan_terbanyak':
                $query->orderBy('grand_total_sales', 'desc');
                break;
            case 'selisih_terbesar':
                $query->orderByRaw('ABS(difference_amount) DESC');
                break;
            case 'terbaru':
            default:
                $query->orderBy('report_date', 'desc')->orderBy('id', 'desc');
                break;
        }

        $reports = $query->paginate(15)->withQueryString();

        return view('kitchen.index', compact('reports', 'branches', 'stats', 'selectedBranchId'));
    }

    /**
     * Buka form input masakan dapur harian.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isViewer()) {
            return redirect()->route('kitchen-reports.index')->with('error', 'Akun Viewer tidak memiliki izin menginput laporan.');
        }

        // Tentukan cabang yang aktif
        $branchId = $request->get('branch_id');
        if (!$branchId || $user->isAdminCabang() || $user->isAdminDapur()) {
            $branchId = $user->branch_id ?? Branch::where('status', 'active')->first()?->id;
        }

        // Validasi akses cabang untuk Admin Cabang / Admin Dapur
        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id) {
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

        $today = Carbon::today();
        $isBackday = $targetDate->lt($today);
        $daysDiff = (int) $targetDate->diffInDays($today);
        $yesterdayString = $today->copy()->subDay()->format('Y-m-d');
        $twoDaysAgoString = $today->copy()->subDays(2)->format('Y-m-d');
        $todayString = $today->format('Y-m-d');

        return view('kitchen.create', compact(
            'branches',
            'activeBranch',
            'dateString',
            'preparedItems',
            'previousReport',
            'isBackday',
            'daysDiff',
            'yesterdayString',
            'twoDaysAgoString',
            'todayString'
        ));
    }

    /**
     * Simpan input masakan dapur harian.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->isViewer()) {
            return redirect()->route('kitchen-reports.index')->with('error', 'Akun Viewer tidak memiliki izin menginput laporan.');
        }

        // Kunci branch_id jika user cabang
        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        // Bersihkan format rupiah uang kasir & belanja
        $cleanCash = (float) str_replace(['.', ','], ['', '.'], $request->input('cash_income', '0'));
        $cleanQris = (float) str_replace(['.', ','], ['', '.'], $request->input('qris_income', '0'));
        $cleanOnline = (float) str_replace(['.', ','], ['', '.'], $request->input('online_food_income', '0'));

        $cleanRawExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_raw_material', '0'));
        $cleanNonRawExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_non_raw_material', '0'));
        $cleanPersonalExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_personal', '0'));

        $request->merge([
            'cash_income' => $cleanCash,
            'qris_income' => $cleanQris,
            'online_food_income' => $cleanOnline,
            'expense_raw_material' => $cleanRawExpense,
            'expense_non_raw_material' => $cleanNonRawExpense,
            'expense_personal' => $cleanPersonalExpense,
        ]);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'report_date' => 'required|date',
            'cash_income' => 'required|numeric|min:0',
            'qris_income' => 'required|numeric|min:0',
            'online_food_income' => 'required|numeric|min:0',
            'expense_raw_material' => 'nullable|numeric|min:0',
            'expense_non_raw_material' => 'nullable|numeric|min:0',
            'expense_personal' => 'nullable|numeric|min:0',
            'expense_notes' => 'nullable|string|max:1000',
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

            $totalExpense = $cleanRawExpense + $cleanNonRawExpense + $cleanPersonalExpense;
            $netCashIncome = $totalOmset - $totalExpense;

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
                'expense_raw_material' => $cleanRawExpense,
                'expense_non_raw_material' => $cleanNonRawExpense,
                'expense_personal' => $cleanPersonalExpense,
                'total_expense' => $totalExpense,
                'net_cash_income' => $netCashIncome,
                'expense_notes' => $validated['expense_notes'] ?? null,
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
    public function show(DailyKitchenReport $kitchenReport): View
    {
        $user = auth()->user();

        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
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
        if ($user->isViewer()) {
            return redirect()->route('kitchen-reports.index')->with('error', 'Anda tidak memiliki izin mengubah laporan.');
        }

        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
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
        if ($user->isViewer()) {
            return redirect()->route('kitchen-reports.index')->with('error', 'Anda tidak memiliki izin mengubah laporan.');
        }

        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke laporan cabang lain.');
        }

        $cleanCash = (float) str_replace(['.', ','], ['', '.'], $request->input('cash_income', '0'));
        $cleanQris = (float) str_replace(['.', ','], ['', '.'], $request->input('qris_income', '0'));
        $cleanOnline = (float) str_replace(['.', ','], ['', '.'], $request->input('online_food_income', '0'));

        $cleanRawExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_raw_material', '0'));
        $cleanNonRawExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_non_raw_material', '0'));
        $cleanPersonalExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_personal', '0'));

        $request->merge([
            'cash_income' => $cleanCash,
            'qris_income' => $cleanQris,
            'online_food_income' => $cleanOnline,
            'expense_raw_material' => $cleanRawExpense,
            'expense_non_raw_material' => $cleanNonRawExpense,
            'expense_personal' => $cleanPersonalExpense,
        ]);

        $validated = $request->validate([
            'cash_income' => 'required|numeric|min:0',
            'qris_income' => 'required|numeric|min:0',
            'online_food_income' => 'required|numeric|min:0',
            'expense_raw_material' => 'nullable|numeric|min:0',
            'expense_non_raw_material' => 'nullable|numeric|min:0',
            'expense_personal' => 'nullable|numeric|min:0',
            'expense_notes' => 'nullable|string|max:1000',
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

            $totalExpense = $cleanRawExpense + $cleanNonRawExpense + $cleanPersonalExpense;
            $netCashIncome = $totalOmset - $totalExpense;

            $kitchenReport->update([
                'grand_total_sales' => $grandTotalSales,
                'total_remaining_sellable' => $totalRemainingSellable,
                'total_wasted_food' => $totalWastedFood,
                'cash_income' => $cleanCash,
                'qris_income' => $cleanQris,
                'online_food_income' => $cleanOnline,
                'total_omset' => $totalOmset,
                'difference_amount' => $diffAmount,
                'expense_raw_material' => $cleanRawExpense,
                'expense_non_raw_material' => $cleanNonRawExpense,
                'expense_personal' => $cleanPersonalExpense,
                'total_expense' => $totalExpense,
                'net_cash_income' => $netCashIncome,
                'expense_notes' => $validated['expense_notes'] ?? null,
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
        if ($user->isViewer()) {
            return redirect()->route('kitchen-reports.index')->with('error', 'Anda tidak memiliki izin menghapus laporan.');
        }

        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
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
    public function exportPdf(DailyKitchenReport $kitchenReport): Response
    {
        $user = auth()->user();

        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id && $kitchenReport->branch_id != $user->branch_id) {
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

    /**
     * Export PDF Rekapitulasi Laporan Dapur Harian (A4 Landscape)
     */
    public function exportSummaryPdf(Request $request): Response
    {
        $user = auth()->user();
        $query = DailyKitchenReport::with(['branch', 'user']);

        // Scoping akses berdasarkan role
        if ($user->isAdminCabang() || $user->isAdminDapur()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } elseif ($user->isKepalaCabang()) {
            if ($user->managedBranches()->count() > 0) {
                $query->whereIn('branch_id', $user->managedBranches()->pluck('branches.id'));
            } elseif ($user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }
            $selectedBranchId = $request->get('branch_id');
        } else {
            $selectedBranchId = $request->get('branch_id');
        }

        // Filter Cabang
        if (!empty($selectedBranchId)) {
            $query->where('branch_id', $selectedBranchId);
        }

        // Filter Tanggal Dari & Sampai
        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->get('date_to'));
        }

        // Search Keyword
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhere('expense_notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('branch', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $query->orderBy('report_date', 'desc')->orderBy('id', 'desc');

        $reports = $query->get();

        // Hitung Statistik Ringkasan
        $stats = [
            'total_reports' => $reports->count(),
            'grand_total_sales' => (float) $reports->sum('grand_total_sales'),
            'total_remaining_sellable' => (int) $reports->sum('total_remaining_sellable'),
            'total_wasted_food' => (int) $reports->sum('total_wasted_food'),
            'total_omset' => (float) $reports->sum('total_omset'),
            'total_expense' => (float) $reports->sum('total_expense'),
            'total_net_cash' => (float) $reports->sum('net_cash_income'),
            'total_difference' => (float) $reports->sum('difference_amount'),
        ];

        // Filter Info Labels
        $branchLabel = 'Semua Cabang';
        if (!empty($selectedBranchId)) {
            $b = Branch::find($selectedBranchId);
            if ($b) $branchLabel = $b->name;
        }

        $dateRangeLabel = 'Semua Periode Tanggal';
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateRangeLabel = Carbon::parse($request->get('date_from'))->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($request->get('date_to'))->translatedFormat('d F Y');
        } elseif ($request->filled('date_from')) {
            $dateRangeLabel = 'Dari ' . Carbon::parse($request->get('date_from'))->translatedFormat('d F Y');
        } elseif ($request->filled('date_to')) {
            $dateRangeLabel = 'Sampai ' . Carbon::parse($request->get('date_to'))->translatedFormat('d F Y');
        }

        $pdf = Pdf::loadView('kitchen.summary-pdf', compact('reports', 'stats', 'branchLabel', 'dateRangeLabel', 'user'))
            ->setPaper('a4', 'landscape');

        $fileName = 'Rekap_Laporan_Dapur_' . str_replace(' ', '_', $branchLabel) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Download Template Format Excel untuk Input Laporan Dapur Harian
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $branchId = $request->get('branch_id');
        if (!$branchId || $user->isAdminCabang() || $user->isAdminDapur()) {
            $branchId = $user->branch_id ?? Branch::where('status', 'active')->first()?->id;
        }

        $branch = Branch::find($branchId);
        $branchName = $branch ? $branch->name : 'Semua Cabang';

        $menus = Menu::where('is_active', true)
            ->orderBy('order_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $masterTemplatePath = base_path('Template.xls');
        if (!file_exists($masterTemplatePath)) {
            $masterTemplatePath = storage_path('app/templates/template_dapur_master.xls');
        }

        if (file_exists($masterTemplatePath)) {
            try {
                $spreadsheet = IOFactory::load($masterTemplatePath);
                
                // Sheet 1: Update metadata & harga cabang aktif
                $sheet1 = $spreadsheet->getSheet(0);
                $sheet1->setCellValue('A3', 'Cabang: ' . $branchName . ' | Tanggal: ' . now()->translatedFormat('d F Y'));
                
                $row1 = 11;
                foreach ($menus as $idx => $menu) {
                    $price = $menu->getPriceForBranch($branchId);
                    $sheet1->setCellValue('A' . $row1, $idx + 1);
                    $sheet1->setCellValue('B' . $row1, $menu->id);
                    $sheet1->setCellValue('C' . $row1, $menu->name);
                    $sheet1->setCellValue('D' . $row1, $menu->is_perishable ? 'Sayur (Cepat Basi)' : 'Lauk Biasa');
                    $sheet1->setCellValue('E' . $row1, $price);
                    $sheet1->setCellValue('I' . $row1, "=F{$row1}+G{$row1}");
                    $sheet1->setCellValue('J' . $row1, "=I{$row1}-H{$row1}");
                    $sheet1->setCellValue('K' . $row1, "=H{$row1}*E{$row1}");
                    $row1++;
                }

                // Sheet 2: Update metadata jika ada
                if ($spreadsheet->getSheetCount() > 1) {
                    $sheet2 = $spreadsheet->getSheet(1);
                    $sheet2->setCellValue('A3', 'Cabang: ' . $branchName . ' | Tanggal: ' . now()->translatedFormat('d F Y'));
                }

                $spreadsheet->setActiveSheetIndex(0);
                $fileName = 'Template_Input_Dapur_' . str_replace(' ', '_', $branchName) . '.xlsx';

                return new StreamedResponse(function () use ($spreadsheet) {
                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');
                }, 200, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]);
            } catch (\Exception $e) {
                // Fallback ke programmatic builder
            }
        }

        $spreadsheet = new Spreadsheet();
        
        // ==========================================
        // SHEET 1: INPUT PORSI MASAKAN & KASIR
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('INPUT_DAPUR');

        // Header Title Sheet 1
        $sheet1->setCellValue('A1', 'TEMPLATE INPUT MASAKAN DAPUR & KASIR HARIAN');
        $sheet1->setCellValue('A2', 'IKHLAS SOLUSI - SISTEM MANAJEMEN DAPUR & OUTLET');

        $sheet1->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet1->getStyle('A1')->getFont()->setSize(13)->getColor()->setRGB('0B192C');
        $sheet1->getStyle('A2')->getFont()->setSize(9.5)->getColor()->setRGB('64748B');

        // Meta Info Baris 3
        $sheet1->setCellValue('A3', 'Cabang: ' . $branchName . ' | Tanggal: ' . now()->translatedFormat('d F Y'));
        $sheet1->getStyle('A3')->getFont()->setBold(true)->setSize(9.5)->getColor()->setRGB('059669');

        // ==========================================
        // DASHBOARD CARD SUMMARY KASIR & BELANJA (Baris 5 - 8)
        // ==========================================
        
        // CARD 1: REKAPAN UANG KASIR (A5:D8)
        $sheet1->setCellValue('A5', '1. REKAPAN PENDAPATAN UANG KASIR (HARI INI)');
        $sheet1->mergeCells('A5:D5');
        $sheet1->getStyle('A5:D5')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('A5:D5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0B192C');
        $sheet1->getStyle('A5:D5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);

        $sheet1->setCellValue('A6', '1. Pendapatan Cash (Laci):');
        $sheet1->setCellValue('B6', 1250000);
        $sheet1->setCellValue('A7', '2. Pendapatan QRIS / Transfer:');
        $sheet1->setCellValue('B7', 450000);
        $sheet1->setCellValue('A8', '3. Pendapatan Online Food:');
        $sheet1->setCellValue('B8', 300000);

        $sheet1->setCellValue('C6', 'TOTAL OMSET KASIR:');
        $sheet1->mergeCells('C6:D6');
        $sheet1->setCellValue('C7', '=SUM(B6:B8)');
        $sheet1->mergeCells('C7:D8');

        $sheet1->getStyle('A6:A8')->getFont()->setSize(8.5)->setBold(true)->getColor()->setRGB('334155');
        $sheet1->getStyle('B6:B8')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet1->getStyle('B6:B8')->getNumberFormat()->setFormatCode('#,##0');
        $sheet1->getStyle('B6:B8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEFCE8'); // Highlight editable
        $sheet1->getStyle('B6:B8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet1->getStyle('C6:D6')->getFont()->setSize(8)->setBold(true)->getColor()->setRGB('065F46');
        $sheet1->getStyle('C6:D6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('C7:D8')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('047857');
        $sheet1->getStyle('C7:D8')->getNumberFormat()->setFormatCode('"Rp " #,##0');
        $sheet1->getStyle('C7:D8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getStyle('C6:D8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
        $sheet1->getStyle('A5:D8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // CARD 2: REKAPAN BELANJA HARIAN (E5:G8)
        $sheet1->setCellValue('E5', '2. REKAPAN BELANJA CABANG (SHEET 2)');
        $sheet1->mergeCells('E5:G5');
        $sheet1->getStyle('E5:G5')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('E5:G5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D97706');
        $sheet1->getStyle('E5:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);

        $sheet1->setCellValue('E6', 'Belanja Bahan Baku:');
        $sheet1->setCellValue('F6', 712000);
        $sheet1->setCellValue('E7', 'Belanja Non Bahan Baku:');
        $sheet1->setCellValue('F7', 89000);
        $sheet1->setCellValue('E8', 'Belanja Pribadi / Kasbon:');
        $sheet1->setCellValue('F8', 50000);

        $sheet1->setCellValue('G6', 'TOTAL BELANJA');
        $sheet1->setCellValue('G7', '=SUM(F6:F8)');

        $sheet1->getStyle('E6:E8')->getFont()->setSize(8.5)->setBold(true)->getColor()->setRGB('334155');
        $sheet1->getStyle('F6:F8')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet1->getStyle('F6:F8')->getNumberFormat()->setFormatCode('#,##0');
        $sheet1->getStyle('F6:F8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
        $sheet1->getStyle('F6:F8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet1->getStyle('G6')->getFont()->setSize(8)->setBold(true)->getColor()->setRGB('991B1B');
        $sheet1->getStyle('G6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('G7')->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('B91C1C');
        $sheet1->getStyle('G7')->getNumberFormat()->setFormatCode('"Rp " #,##0');
        $sheet1->getStyle('G7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet1->getStyle('G6:G8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
        $sheet1->getStyle('E5:G8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // CARD 3: KONTROL PENJUALAN & SELISIH (H5:K8)
        $sheet1->setCellValue('H5', '3. KONTROL PENJUALAN & SISA SETORAN');
        $sheet1->mergeCells('H5:K5');
        $sheet1->getStyle('H5:K5')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle('H5:K5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB');
        $sheet1->getStyle('H5:K5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);

        $sheet1->setCellValue('H6', 'Grand Total Penjualan:');
        $sheet1->setCellValue('I6', 0); // Di-link ke grand total tabel bawah
        $sheet1->setCellValue('H7', 'Selisih Kasir vs Masakan:');
        $sheet1->setCellValue('I7', '=C7-I6');
        $sheet1->setCellValue('H8', 'Sisa Kas Setoran Bersih:');
        $sheet1->setCellValue('I8', '=B6-G7');

        $sheet1->getStyle('H6:H8')->getFont()->setSize(8.5)->setBold(true)->getColor()->setRGB('334155');
        $sheet1->getStyle('I6:I8')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet1->getStyle('I6:I8')->getNumberFormat()->setFormatCode('+#,##0;-#,##0;0');
        $sheet1->getStyle('I6:I8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle('I7')->getFont()->getColor()->setRGB('B91C1C');
        $sheet1->getStyle('I8')->getFont()->getColor()->setRGB('047857');
        $sheet1->getStyle('H5:K8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // ==========================================
        // TABEL PORSI MASAKAN (Mulai Baris 10)
        // ==========================================
        $headers1 = [
            'A10' => 'No',
            'B10' => 'ID Menu',
            'C10' => 'Nama Masakan',
            'D10' => 'Kategori / Sifat',
            'E10' => 'Harga Satuan (Rp)',
            'F10' => 'Sisa Kemarin [Input]',
            'G10' => 'Masak Hari Ini [Input]',
            'H10' => 'Terjual [Input]',
            'I10' => 'Total Masak [Auto]',
            'J10' => 'Sisa Hari Ini [Auto]',
            'K10' => 'Subtotal Penjualan (Rp) [Auto]',
        ];

        foreach ($headers1 as $cell => $text) {
            $sheet1->setCellValue($cell, $text);
        }

        $headerStyle1 = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B192C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];
        $sheet1->getStyle('A10:K10')->applyFromArray($headerStyle1);
        $sheet1->getRowDimension(10)->setRowHeight(28);

        // Highlight header input columns
        $sheet1->getStyle('F10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D97706'); // Amber
        $sheet1->getStyle('G10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('059669'); // Emerald
        $sheet1->getStyle('H10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB'); // Blue

        $sampleCooked = [30, 25, 20, 15, 40, 25, 35, 20, 15, 10];
        $sampleSold   = [28, 22, 18, 15, 38, 20, 32, 18, 14, 10];

        $row1 = 11;
        foreach ($menus as $idx => $menu) {
            $price = $menu->getPriceForBranch($branchId);
            $sheet1->setCellValue('A' . $row1, $idx + 1);
            $sheet1->setCellValue('B' . $row1, $menu->id);
            $sheet1->setCellValue('C' . $row1, $menu->name);
            $sheet1->setCellValue('D' . $row1, $menu->is_perishable ? 'Sayur (Cepat Basi)' : 'Lauk Biasa');
            $sheet1->setCellValue('E' . $row1, $price);
            
            $sisaKemarinVal = ($idx < 3 && !$menu->is_perishable) ? 5 : 0;
            $masakVal = isset($sampleCooked[$idx]) ? $sampleCooked[$idx] : 0;
            $terjualVal = isset($sampleSold[$idx]) ? $sampleSold[$idx] : 0;

            $sheet1->setCellValue('F' . $row1, $sisaKemarinVal);
            $sheet1->setCellValue('G' . $row1, $masakVal);
            $sheet1->setCellValue('H' . $row1, $terjualVal);
            $sheet1->setCellValue('I' . $row1, "=F{$row1}+G{$row1}");
            $sheet1->setCellValue('J' . $row1, "=I{$row1}-H{$row1}");
            $sheet1->setCellValue('K' . $row1, "=H{$row1}*E{$row1}");

            $sheet1->getStyle('A' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('B' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('D' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('E' . $row1)->getNumberFormat()->setFormatCode('#,##0');
            $sheet1->getStyle('F' . $row1 . ':K' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle('F' . $row1 . ':J' . $row1)->getNumberFormat()->setFormatCode('#,##0');
            $sheet1->getStyle('K' . $row1)->getNumberFormat()->setFormatCode('#,##0');

            // Highlight editable user input columns (F, G, H)
            $sheet1->getStyle('F' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
            $sheet1->getStyle('G' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
            $sheet1->getStyle('H' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EFF6FF');
            $sheet1->getStyle('I' . $row1 . ':K' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

            $row1++;
        }

        // Tambah 5 baris custom menu kosong
        for ($c = 0; $c < 5; $c++) {
            $sheet1->setCellValue('A' . $row1, count($menus) + $c + 1);
            $sheet1->setCellValue('B' . $row1, '');
            $sheet1->setCellValue('C' . $row1, '');
            $sheet1->setCellValue('D' . $row1, 'Lauk Biasa');
            $sheet1->setCellValue('E' . $row1, 0);
            $sheet1->setCellValue('F' . $row1, 0);
            $sheet1->setCellValue('G' . $row1, 0);
            $sheet1->setCellValue('H' . $row1, 0);
            $sheet1->setCellValue('I' . $row1, "=F{$row1}+G{$row1}");
            $sheet1->setCellValue('J' . $row1, "=I{$row1}-H{$row1}");
            $sheet1->setCellValue('K' . $row1, "=H{$row1}*E{$row1}");

            $sheet1->getStyle('A' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('B' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('D' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('E' . $row1)->getNumberFormat()->setFormatCode('#,##0');
            $sheet1->getStyle('F' . $row1 . ':K' . $row1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet1->getStyle('F' . $row1 . ':K' . $row1)->getNumberFormat()->setFormatCode('#,##0');

            $sheet1->getStyle('F' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
            $sheet1->getStyle('G' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
            $sheet1->getStyle('H' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EFF6FF');
            $sheet1->getStyle('I' . $row1 . ':K' . $row1)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

            $row1++;
        }

        $lastRow1 = $row1 - 1;
        $sheet1->getStyle("A11:K{$lastRow1}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

        // Sticky Grand Total Baris Bawah Sheet 1
        $sheet1->setCellValue('A' . $row1, 'GRAND TOTAL:');
        $sheet1->mergeCells("A{$row1}:E{$row1}");
        $sheet1->setCellValue('F' . $row1, "=SUM(F11:F{$lastRow1})");
        $sheet1->setCellValue('G' . $row1, "=SUM(G11:G{$lastRow1})");
        $sheet1->setCellValue('H' . $row1, "=SUM(H11:H{$lastRow1})");
        $sheet1->setCellValue('I' . $row1, "=SUM(I11:I{$lastRow1})");
        $sheet1->setCellValue('J' . $row1, "=SUM(J11:J{$lastRow1})");
        $sheet1->setCellValue('K' . $row1, "=SUM(K11:K{$lastRow1})");

        $sheet1->getStyle("A{$row1}:K{$row1}")->getFont()->setBold(true)->setSize(9.5)->getColor()->setRGB('FFFFFF');
        $sheet1->getStyle("A{$row1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet1->getStyle("F{$row1}:K{$row1}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet1->getStyle("A{$row1}:K{$row1}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0B192C');
        $sheet1->getStyle("A{$row1}:K{$row1}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');

        // Link Grand Total ke Box Kontrol I6
        $sheet1->setCellValue('I6', "=K{$row1}");

        // SET LEBAR KOLOM PROPORSIONAL EKSPLISIT (NO DISTORTION)
        $sheet1->getColumnDimension('A')->setWidth(6);
        $sheet1->getColumnDimension('B')->setWidth(10);
        $sheet1->getColumnDimension('C')->setWidth(30);
        $sheet1->getColumnDimension('D')->setWidth(18);
        $sheet1->getColumnDimension('E')->setWidth(14);
        $sheet1->getColumnDimension('F')->setWidth(15);
        $sheet1->getColumnDimension('G')->setWidth(16);
        $sheet1->getColumnDimension('H')->setWidth(14);
        $sheet1->getColumnDimension('I')->setWidth(14);
        $sheet1->getColumnDimension('J')->setWidth(14);
        $sheet1->getColumnDimension('K')->setWidth(20);

        // ==========================================
        // SHEET 2: REKAPAN BELANJA HARIAN CABANG
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('REKAPAN_BELANJA');

        // Header Title Sheet 2
        $sheet2->setCellValue('A1', 'REKAPAN BELANJA & PENGELUARAN HARIAN CABANG');
        $sheet2->setCellValue('A2', 'Rincian Belanja Bahan Baku Pasar, Operasional Dapur, dan Belanja Pribadi');

        $sheet2->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet2->getStyle('A1')->getFont()->setSize(13)->getColor()->setRGB('059669');
        $sheet2->getStyle('A2')->getFont()->setSize(9.5)->getColor()->setRGB('64748B');

        // Meta Info Sheet 2
        $sheet2->setCellValue('A3', 'Cabang: ' . $branchName . ' | Tanggal: ' . now()->translatedFormat('d F Y'));
        $sheet2->getStyle('A3')->getFont()->setBold(true)->setSize(9.5)->getColor()->setRGB('0B192C');

        // Summary Card Belanja di Sheet 2 (Baris 5 - 6)
        $sheet2->setCellValue('A5', 'TOTAL BAHAN BAKU');
        $sheet2->setCellValue('C5', 'TOTAL NON BAHAN BAKU');
        $sheet2->setCellValue('E5', 'TOTAL PRIBADI / KASBON');
        $sheet2->setCellValue('G5', 'GRAND TOTAL BELANJA');
        $sheet2->mergeCells('A5:B5');
        $sheet2->mergeCells('C5:D5');
        $sheet2->mergeCells('E5:F5');
        $sheet2->mergeCells('G5:H5');

        $sheet2->setCellValue('A6', '=SUMIF(B9:B35, "*Bahan Baku*", G9:G35)');
        $sheet2->setCellValue('C6', '=SUMIF(B9:B35, "*Non*", G9:G35)');
        $sheet2->setCellValue('E6', '=SUMIF(B9:B35, "*Pribadi*", G9:G35)');
        $sheet2->setCellValue('G6', '=SUM(G9:G35)');
        $sheet2->mergeCells('A6:B6');
        $sheet2->mergeCells('C6:D6');
        $sheet2->mergeCells('E6:F6');
        $sheet2->mergeCells('G6:H6');

        $sheet2->getStyle('A5:H5')->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB('FFFFFF');
        $sheet2->getStyle('A5:H5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A5:B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('047857');
        $sheet2->getStyle('C5:D5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D97706');
        $sheet2->getStyle('E5:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4B5563');
        $sheet2->getStyle('G5:H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B91C1C');

        $sheet2->getStyle('A6:H6')->getFont()->setBold(true)->setSize(11);
        $sheet2->getStyle('A6:H6')->getNumberFormat()->setFormatCode('"Rp " #,##0');
        $sheet2->getStyle('A6:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A6:H6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $sheet2->getStyle('A5:H6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Table Header Sheet 2 (Baris 8)
        $headers2 = [
            'A8' => 'No',
            'B8' => 'Kategori Belanja',
            'C8' => 'Nama Barang / Kebutuhan Belanja',
            'D8' => 'Qty [Input]',
            'E8' => 'Satuan',
            'F8' => 'Harga Satuan (Rp) [Input]',
            'G8' => 'Total Nominal (Rp) [Auto]',
            'H8' => 'Keterangan / Sumber Nota',
        ];

        foreach ($headers2 as $cell => $text) {
            $sheet2->setCellValue($cell, $text);
        }

        $headerStyle2 = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];
        $sheet2->getStyle('A8:H8')->applyFromArray($headerStyle2);
        $sheet2->getRowDimension(8)->setRowHeight(28);

        // Contoh Realistis Pengeluaran Belanja Harian Cabang
        $sampleExpenses = [
            ['Bahan Baku', 'Ayam Broiler Segar (Potong)', 10, 'kg', 35000, 'Pasar Pagi'],
            ['Bahan Baku', 'Minyak Goreng Sawit', 4, 'liter', 18000, 'Toko Grosir'],
            ['Bahan Baku', 'Cabai Rawit Merah & Keriting', 3, 'kg', 40000, 'Pasar Induk'],
            ['Bahan Baku', 'Bawang Merah & Putih Kupas', 2.5, 'kg', 32000, 'Pasar Induk'],
            ['Bahan Baku', 'Sayur Kangkung & Bayam', 20, 'ikat', 2500, 'Pasar Subuh'],
            ['Bahan Baku', 'Santan Kelapa Murni', 3, 'kg', 19000, 'Pasar Pagi'],
            ['Non Bahan Baku', 'Gas LPG 3kg', 2, 'tabung', 22000, 'Pangkalan LPG'],
            ['Non Bahan Baku', 'Plastik Bungkus & Mika Nasi', 3, 'pack', 15000, 'Toko Plastik'],
            ['Pribadi', 'Uang Makan / Kasbon Karyawan', 1, 'kali', 50000, 'Kasir Cabang'],
        ];

        $row2 = 9;
        foreach ($sampleExpenses as $idx => $exp) {
            $sheet2->setCellValue('A' . $row2, $idx + 1);
            $sheet2->setCellValue('B' . $row2, $exp[0]);
            $sheet2->setCellValue('C' . $row2, $exp[1]);
            $sheet2->setCellValue('D' . $row2, $exp[2]);
            $sheet2->setCellValue('E' . $row2, $exp[3]);
            $sheet2->setCellValue('F' . $row2, $exp[4]);
            $sheet2->setCellValue('G' . $row2, "=D{$row2}*F{$row2}");
            $sheet2->setCellValue('H' . $row2, $exp[5]);

            $sheet2->getStyle('A' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('B' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('D' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet2->getStyle('E' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('F' . $row2 . ':G' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet2->getStyle('D' . $row2)->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet2->getStyle('F' . $row2 . ':G' . $row2)->getNumberFormat()->setFormatCode('#,##0');

            $sheet2->getStyle('B' . $row2 . ':F' . $row2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
            $sheet2->getStyle('G' . $row2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

            $row2++;
        }

        // Tambah 15 baris belanja kosong ber-rumus
        for ($k = 0; $k < 15; $k++) {
            $sheet2->setCellValue('A' . $row2, count($sampleExpenses) + $k + 1);
            $sheet2->setCellValue('B' . $row2, 'Bahan Baku');
            $sheet2->setCellValue('C' . $row2, '');
            $sheet2->setCellValue('D' . $row2, 0);
            $sheet2->setCellValue('E' . $row2, 'pcs');
            $sheet2->setCellValue('F' . $row2, 0);
            $sheet2->setCellValue('G' . $row2, "=D{$row2}*F{$row2}");
            $sheet2->setCellValue('H' . $row2, '');

            $sheet2->getStyle('A' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('B' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('D' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet2->getStyle('E' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('F' . $row2 . ':G' . $row2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet2->getStyle('D' . $row2)->getNumberFormat()->setFormatCode('#,##0.##');
            $sheet2->getStyle('F' . $row2 . ':G' . $row2)->getNumberFormat()->setFormatCode('#,##0');

            $sheet2->getStyle('B' . $row2 . ':F' . $row2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
            $sheet2->getStyle('G' . $row2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

            $row2++;
        }

        $lastRow2 = $row2 - 1;
        $sheet2->getStyle("A9:H{$lastRow2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

        // Ringkasan Total Belanja di Bawah Sheet 2
        $sheet2->setCellValue('A' . $row2, 'TOTAL BELANJA HARIAN:');
        $sheet2->mergeCells("A{$row2}:F{$row2}");
        $sheet2->setCellValue('G' . $row2, "=SUM(G9:G{$lastRow2})");
        $sheet2->getStyle("A{$row2}:H{$row2}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet2->getStyle("A{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet2->getStyle("G{$row2}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet2->getStyle("A{$row2}:H{$row2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('059669');
        $sheet2->getStyle("A{$row2}:H{$row2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('047857');

        // SET LEBAR KOLOM PROPORSIONAL EKSPLISIT SHEET 2
        $sheet2->getColumnDimension('A')->setWidth(6);
        $sheet2->getColumnDimension('B')->setWidth(18);
        $sheet2->getColumnDimension('C')->setWidth(32);
        $sheet2->getColumnDimension('D')->setWidth(12);
        $sheet2->getColumnDimension('E')->setWidth(10);
        $sheet2->getColumnDimension('F')->setWidth(16);
        $sheet2->getColumnDimension('G')->setWidth(18);
        $sheet2->getColumnDimension('H')->setWidth(25);

        // Set active sheet ke Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'Template_Input_Dapur_' . str_replace(' ', '_', $branchName) . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Parse File Excel yang Diupload untuk Mengisi Form Input Laporan Dapur & Belanja secara Real-Time
     */
    public function parseExcel(Request $request): JsonResponse
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'excel_file.required' => 'File Excel wajib dipilih.',
            'excel_file.mimes' => 'Format file harus berupa Excel (.xlsx atau .xls).',
            'excel_file.max' => 'Ukuran file Excel maksimal 5MB.',
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            
            // Sheet 1: Input Masakan Dapur & Kasir
            $sheet1 = $spreadsheet->getSheet(0);
            $highestRow1 = $sheet1->getHighestRow();

            $parsedItems = [];
            $allMenus = Menu::all()->keyBy('id');
            $menuNameMap = [];
            foreach ($allMenus as $m) {
                $menuNameMap[strtolower(trim($m->name))] = $m->id;
            }

            // Cari baris awal data tabel masakan (deteksi baris header)
            $startRow1 = 11;
            for ($testRow = 6; $testRow <= 15; $testRow++) {
                $cellA = strtoupper(trim((string)$sheet1->getCell('A' . $testRow)->getValue()));
                $cellC = strtoupper(trim((string)$sheet1->getCell('C' . $testRow)->getValue()));
                if (str_contains($cellA, 'NO') || str_contains($cellC, 'NAMA MASAKAN')) {
                    $startRow1 = $testRow + 1;
                    break;
                }
            }

            // Baca Data Tabel Menu
            for ($r = $startRow1; $r <= $highestRow1; $r++) {
                $menuId = $sheet1->getCell('B' . $r)->getValue();
                $menuName = trim((string) $sheet1->getCell('C' . $r)->getValue());
                
                if (empty($menuId) && empty($menuName)) {
                    continue;
                }

                // Jangan membaca baris Grand Total
                if (str_contains(strtoupper($menuName), 'TOTAL') || str_contains(strtoupper((string)$menuId), 'TOTAL')) {
                    continue;
                }

                // Cocokkan menu ID atau Name
                $matchedMenuId = null;
                if (!empty($menuId) && isset($allMenus[$menuId])) {
                    $matchedMenuId = (int) $menuId;
                } elseif (!empty($menuName) && isset($menuNameMap[strtolower($menuName)])) {
                    $matchedMenuId = (int) $menuNameMap[strtolower($menuName)];
                }

                if ($matchedMenuId) {
                    $yesterdayRem = (int) ($sheet1->getCell('F' . $r)->getCalculatedValue() ?? 0);
                    $cookedToday = (int) ($sheet1->getCell('G' . $r)->getCalculatedValue() ?? 0);
                    $sold = (int) ($sheet1->getCell('H' . $r)->getCalculatedValue() ?? 0);

                    $parsedItems[$matchedMenuId] = [
                        'menu_id' => $matchedMenuId,
                        'menu_name' => $allMenus[$matchedMenuId]->name ?? $menuName,
                        'yesterday_remaining' => max(0, $yesterdayRem),
                        'cooked_today' => max(0, $cookedToday),
                        'sold' => max(0, $sold),
                    ];
                }
            }

            // Baca Kasir dari Sheet 1 (Mendukung B6:B8 baru, atau N5:N7, atau K5:K7)
            $cashIncome = (float) ($sheet1->getCell('B6')->getCalculatedValue() ?? $sheet1->getCell('N5')->getCalculatedValue() ?? $sheet1->getCell('K5')->getCalculatedValue() ?? 0);
            $qrisIncome = (float) ($sheet1->getCell('B7')->getCalculatedValue() ?? $sheet1->getCell('N6')->getCalculatedValue() ?? $sheet1->getCell('K6')->getCalculatedValue() ?? 0);
            $onlineIncome = (float) ($sheet1->getCell('B8')->getCalculatedValue() ?? $sheet1->getCell('N7')->getCalculatedValue() ?? $sheet1->getCell('K7')->getCalculatedValue() ?? 0);

            $expenseRaw = (float) ($sheet1->getCell('F6')->getCalculatedValue() ?? $sheet1->getCell('K8')->getCalculatedValue() ?? 0);
            $expenseNonRaw = (float) ($sheet1->getCell('F7')->getCalculatedValue() ?? $sheet1->getCell('K9')->getCalculatedValue() ?? 0);
            $expensePersonal = (float) ($sheet1->getCell('F8')->getCalculatedValue() ?? $sheet1->getCell('K10')->getCalculatedValue() ?? 0);

            // ==========================================
            // SHEET 2: BACA RINCIAN BELANJA HARIAN
            // ==========================================
            $expenseItemsList = [];
            if ($spreadsheet->getSheetCount() > 1) {
                $sheet2 = $spreadsheet->getSheet(1);
                $highestRow2 = $sheet2->getHighestRow();

                $sheet2Raw = 0;
                $sheet2NonRaw = 0;
                $sheet2Personal = 0;
                $hasValidSheet2Data = false;

                // Cari baris awal data belanja (deteksi header)
                $startRow2 = 9;
                for ($testRow2 = 6; $testRow2 <= 12; $testRow2++) {
                    $cellA2 = strtoupper(trim((string)$sheet2->getCell('A' . $testRow2)->getValue()));
                    $cellC2 = strtoupper(trim((string)$sheet2->getCell('C' . $testRow2)->getValue()));
                    if (str_contains($cellA2, 'NO') || str_contains($cellC2, 'NAMA BARANG')) {
                        $startRow2 = $testRow2 + 1;
                        break;
                    }
                }

                for ($r2 = $startRow2; $r2 <= $highestRow2; $r2++) {
                    $category = trim((string) $sheet2->getCell('B' . $r2)->getValue());
                    $itemName = trim((string) $sheet2->getCell('C' . $r2)->getValue());
                    $amount = (float) ($sheet2->getCell('G' . $r2)->getCalculatedValue() ?? 0);

                    if (empty($itemName) && $amount <= 0) {
                        continue;
                    }

                    // Abaikan baris grand total
                    if (str_contains(strtoupper($category), 'TOTAL') || str_contains(strtoupper($itemName), 'TOTAL')) {
                        continue;
                    }

                    if ($amount > 0) {
                        $hasValidSheet2Data = true;
                        $catLower = strtolower($category);
                        if (str_contains($catLower, 'non') || str_contains($catLower, 'operasional') || str_contains($catLower, 'alat')) {
                            $sheet2NonRaw += $amount;
                        } elseif (str_contains($catLower, 'pribadi') || str_contains($catLower, 'kasbon') || str_contains($catLower, 'makan')) {
                            $sheet2Personal += $amount;
                        } else {
                            $sheet2Raw += $amount;
                        }

                        $expenseItemsList[] = [
                            'category' => $category ?: 'Bahan Baku',
                            'name' => $itemName,
                            'qty' => $sheet2->getCell('D' . $r2)->getCalculatedValue() ?? 1,
                            'unit' => $sheet2->getCell('E' . $r2)->getValue() ?? 'pcs',
                            'price' => (float) ($sheet2->getCell('F' . $r2)->getCalculatedValue() ?? $amount),
                            'amount' => $amount,
                            'notes' => trim((string) $sheet2->getCell('H' . $r2)->getValue()),
                        ];
                    }
                }

                // Jika sheet 2 memiliki item belanja riil, gunakan total dari sheet 2
                if ($hasValidSheet2Data) {
                    $expenseRaw = $sheet2Raw;
                    $expenseNonRaw = $sheet2NonRaw;
                    $expensePersonal = $sheet2Personal;
                }
            }

            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => 'File Excel berhasil dibaca. ' . count($parsedItems) . ' menu masakan dan ' . count($expenseItemsList) . ' item belanja siap diterapkan ke form.',
                'data' => [
                    'items' => $parsedItems,
                    'cash_income' => $cashIncome,
                    'qris_income' => $qrisIncome,
                    'online_food_income' => $onlineIncome,
                    'expense_raw_material' => $expenseRaw,
                    'expense_non_raw_material' => $expenseNonRaw,
                    'expense_personal' => $expensePersonal,
                    'expense_items' => $expenseItemsList,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage()
            ], 422);
        }
    }
}
