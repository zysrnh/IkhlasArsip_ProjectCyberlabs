<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchMonthlyCost;
use App\Models\DailyKitchenReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonthlyCostController extends Controller
{
    /**
     * Tampilkan daftar dan rekapitulasi Biaya Bulanan (Cost Cabang).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = BranchMonthlyCost::with(['branch', 'user']);

        // Scoping akses berdasarkan role
        if ($user->isAdminCabang() || $user->isAdminDapur()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } elseif ($user->isKepalaCabang()) {
            $accessibleIds = $user->getAccessibleBranchIds();
            $query->whereIn('branch_id', $accessibleIds);
            $selectedBranchId = $request->get('branch_id');
        } else {
            $selectedBranchId = $request->get('branch_id');
        }

        // Filter Cabang
        if (!empty($selectedBranchId)) {
            $query->where('branch_id', $selectedBranchId);
        }

        // Filter Tahun & Bulan
        $currentYear = (int) date('Y');
        $selectedYear = $request->filled('year') ? (int) $request->get('year') : null;
        $selectedMonth = $request->filled('month') ? (int) $request->get('month') : null;

        if ($selectedYear) {
            $query->where('year', $selectedYear);
        }
        if ($selectedMonth) {
            $query->where('month', $selectedMonth);
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->orderBy('year', 'asc')->orderBy('month', 'asc')->orderBy('id', 'asc');
                break;
            case 'terbesar':
                $query->orderBy('total_monthly_cost', 'desc');
                break;
            case 'terkecil':
                $query->orderBy('total_monthly_cost', 'asc');
                break;
            case 'terbaru':
            default:
                $query->orderBy('year', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');
                break;
        }

        // Ambil data cabang untuk dropdown filter & input
        $branches = $user->getAccessibleBranches();

        $costs = $query->paginate(12)->withQueryString();

        // Ambil data Belanja Harian di bulan dan cabang yang sesuai untuk setiap item
        foreach ($costs as $cost) {
            $dailyExpenseData = DailyKitchenReport::where('branch_id', $cost->branch_id)
                ->whereYear('report_date', $cost->year)
                ->whereMonth('report_date', $cost->month)
                ->select(
                    DB::raw('COALESCE(SUM(total_expense), 0) as total_daily'),
                    DB::raw('COALESCE(SUM(expense_raw_material), 0) as total_raw'),
                    DB::raw('COALESCE(SUM(expense_non_raw_material), 0) as total_non_raw'),
                    DB::raw('COALESCE(SUM(expense_personal), 0) as total_personal'),
                    DB::raw('COUNT(id) as total_days')
                )
                ->first();

            $cost->daily_expense_total = (float) ($dailyExpenseData->total_daily ?? 0);
            $cost->daily_expense_raw = (float) ($dailyExpenseData->total_raw ?? 0);
            $cost->daily_expense_non_raw = (float) ($dailyExpenseData->total_non_raw ?? 0);
            $cost->daily_expense_personal = (float) ($dailyExpenseData->total_personal ?? 0);
            $cost->daily_expense_days = (int) ($dailyExpenseData->total_days ?? 0);
            $cost->grand_total_cost = $cost->total_monthly_cost + $cost->daily_expense_total;
        }

        // Kalkulasi Statistik Global Keseluruhan Query
        $statsMonthlyTotal = (float) (clone $query)->sum('total_monthly_cost');

        // Kalkulasi Total Belanja Harian untuk filter aktif
        $dailyQuery = DailyKitchenReport::query();
        if ($user->isAdminCabang() || $user->isAdminDapur()) {
            $dailyQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isKepalaCabang()) {
            $dailyQuery->whereIn('branch_id', $user->getAccessibleBranchIds());
        }
        if (!empty($selectedBranchId)) {
            $dailyQuery->where('branch_id', $selectedBranchId);
        }
        if ($selectedYear) {
            $dailyQuery->whereYear('report_date', $selectedYear);
        }
        if ($selectedMonth) {
            $dailyQuery->whereMonth('report_date', $selectedMonth);
        }

        $statsDailyTotal = (float) $dailyQuery->sum('total_expense');
        $statsGrandTotal = $statsMonthlyTotal + $statsDailyTotal;

        // Daftar tahun untuk filter (dari 2024 hingga tahun depan)
        $years = range($currentYear + 1, 2024);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('monthly-costs.index', compact(
            'costs',
            'branches',
            'selectedBranchId',
            'selectedYear',
            'selectedMonth',
            'years',
            'monthNames',
            'statsMonthlyTotal',
            'statsDailyTotal',
            'statsGrandTotal'
        ));
    }

    /**
     * Simpan data Biaya Bulanan baru atau perbarui jika sudah ada periode yang sama.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer()) {
            return redirect()->back()->with('error', 'Akun Viewer tidak memiliki hak untuk menambah/mengubah biaya bulanan.');
        }

        // Scoping cabang otomatis jika Admin Cabang
        if ($user->isAdminCabang() && $user->branch_id) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        // Sanitasi nilai currency format '1.000.000'
        $cleanedValues = $this->cleanCurrencyInputs($request->all());
        $request->merge($cleanedValues);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'year' => 'required|integer|min:2020|max:2099',
            'month' => 'required|integer|between:1,12',
            'rent_cost' => 'nullable|numeric|min:0',
            'wifi_cost' => 'nullable|numeric|min:0',
            'trash_cost' => 'nullable|numeric|min:0',
            'utilities_cost' => 'nullable|numeric|min:0',
            'netflix_cost' => 'nullable|numeric|min:0',
            'ipl_cost' => 'nullable|numeric|min:0',
            'salary_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ], [
            'branch_id.required' => 'Cabang wajib dipilih.',
            'year.required' => 'Tahun wajib dipilih.',
            'month.required' => 'Bulan wajib dipilih.',
        ]);

        // Cek hak akses ke cabang
        if ($user->isKepalaCabang()) {
            $accessibleIds = $user->getAccessibleBranchIds();
            if (!in_array((int) $validated['branch_id'], $accessibleIds)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke cabang yang dipilih.');
            }
        } elseif ($user->isAdminCabang() && (int) $validated['branch_id'] !== (int) $user->branch_id) {
            return redirect()->back()->with('error', 'Anda hanya dapat menginput data untuk cabang Anda sendiri.');
        }

        $totalMonthlyCost = (float) (
            ($validated['rent_cost'] ?? 0) +
            ($validated['wifi_cost'] ?? 0) +
            ($validated['trash_cost'] ?? 0) +
            ($validated['utilities_cost'] ?? 0) +
            ($validated['netflix_cost'] ?? 0) +
            ($validated['ipl_cost'] ?? 0) +
            ($validated['salary_cost'] ?? 0) +
            ($validated['other_cost'] ?? 0)
        );

        $cost = BranchMonthlyCost::updateOrCreate(
            [
                'branch_id' => $validated['branch_id'],
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'rent_cost' => $validated['rent_cost'] ?? 0,
                'wifi_cost' => $validated['wifi_cost'] ?? 0,
                'trash_cost' => $validated['trash_cost'] ?? 0,
                'utilities_cost' => $validated['utilities_cost'] ?? 0,
                'netflix_cost' => $validated['netflix_cost'] ?? 0,
                'ipl_cost' => $validated['ipl_cost'] ?? 0,
                'salary_cost' => $validated['salary_cost'] ?? 0,
                'other_cost' => $validated['other_cost'] ?? 0,
                'total_monthly_cost' => $totalMonthlyCost,
                'notes' => $validated['notes'] ?? null,
                'user_id' => auth()->id(),
            ]
        );

        $monthName = Carbon::createFromDate($cost->year, $cost->month, 1)->translatedFormat('F Y');

        return redirect()->route('monthly-costs.index')
            ->with('success', "Data Cost Cabang {$cost->branch->name} periode {$monthName} berhasil disimpan.");
    }

    /**
     * Update data Biaya Bulanan yang sudah ada.
     */
    public function update(Request $request, BranchMonthlyCost $monthlyCost): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer()) {
            return redirect()->back()->with('error', 'Akun Viewer tidak memiliki hak untuk mengedit biaya bulanan.');
        }

        // Cek hak akses ke cabang
        if ($user->isKepalaCabang()) {
            $accessibleIds = $user->getAccessibleBranchIds();
            if (!in_array($monthlyCost->branch_id, $accessibleIds)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke cabang ini.');
            }
        } elseif ($user->isAdminCabang() && $monthlyCost->branch_id !== $user->branch_id) {
            return redirect()->back()->with('error', 'Anda hanya dapat mengedit data cabang Anda sendiri.');
        }

        $cleanedValues = $this->cleanCurrencyInputs($request->all());
        $request->merge($cleanedValues);

        $validated = $request->validate([
            'rent_cost' => 'nullable|numeric|min:0',
            'wifi_cost' => 'nullable|numeric|min:0',
            'trash_cost' => 'nullable|numeric|min:0',
            'utilities_cost' => 'nullable|numeric|min:0',
            'netflix_cost' => 'nullable|numeric|min:0',
            'ipl_cost' => 'nullable|numeric|min:0',
            'salary_cost' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $totalMonthlyCost = (float) (
            ($validated['rent_cost'] ?? 0) +
            ($validated['wifi_cost'] ?? 0) +
            ($validated['trash_cost'] ?? 0) +
            ($validated['utilities_cost'] ?? 0) +
            ($validated['netflix_cost'] ?? 0) +
            ($validated['ipl_cost'] ?? 0) +
            ($validated['salary_cost'] ?? 0) +
            ($validated['other_cost'] ?? 0)
        );

        $monthlyCost->update([
            'rent_cost' => $validated['rent_cost'] ?? 0,
            'wifi_cost' => $validated['wifi_cost'] ?? 0,
            'trash_cost' => $validated['trash_cost'] ?? 0,
            'utilities_cost' => $validated['utilities_cost'] ?? 0,
            'netflix_cost' => $validated['netflix_cost'] ?? 0,
            'ipl_cost' => $validated['ipl_cost'] ?? 0,
            'salary_cost' => $validated['salary_cost'] ?? 0,
            'other_cost' => $validated['other_cost'] ?? 0,
            'total_monthly_cost' => $totalMonthlyCost,
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('monthly-costs.index')
            ->with('success', "Data Cost Cabang {$monthlyCost->branch->name} periode {$monthlyCost->period_name} berhasil diperbarui.");
    }

    /**
     * Hapus data Biaya Bulanan.
     */
    public function destroy(BranchMonthlyCost $monthlyCost): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer()) {
            return redirect()->back()->with('error', 'Akun Viewer tidak memiliki hak menghapus data.');
        }

        if ($user->isKepalaCabang()) {
            $accessibleIds = $user->getAccessibleBranchIds();
            if (!in_array($monthlyCost->branch_id, $accessibleIds)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki hak menghapus data cabang ini.');
            }
        } elseif ($user->isAdminCabang() && $monthlyCost->branch_id !== $user->branch_id) {
            return redirect()->back()->with('error', 'Anda hanya dapat menghapus data cabang Anda sendiri.');
        }

        $periodName = $monthlyCost->period_name;
        $branchName = $monthlyCost->branch->name;
        $monthlyCost->delete();

        return redirect()->route('monthly-costs.index')
            ->with('success', "Data Cost Cabang {$branchName} periode {$periodName} berhasil dihapus.");
    }

    /**
     * Dapatkan data cost bulanan via AJAX untuk auto-populate saat pilih Cabang, Bulan, dan Tahun
     */
    public function getData(Request $request): \Illuminate\Http\JsonResponse
    {
        $branchId = (int) $request->get('branch_id');
        $year = (int) $request->get('year');
        $month = (int) $request->get('month');

        if (!$branchId || !$year || !$month) {
            return response()->json(['found' => false]);
        }

        $cost = BranchMonthlyCost::where('branch_id', $branchId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($cost) {
            return response()->json([
                'found' => true,
                'data' => [
                    'id' => $cost->id,
                    'rent_cost' => (float) $cost->rent_cost,
                    'wifi_cost' => (float) $cost->wifi_cost,
                    'trash_cost' => (float) $cost->trash_cost,
                    'utilities_cost' => (float) $cost->utilities_cost,
                    'netflix_cost' => (float) $cost->netflix_cost,
                    'ipl_cost' => (float) $cost->ipl_cost,
                    'salary_cost' => (float) $cost->salary_cost,
                    'other_cost' => (float) $cost->other_cost,
                    'total_monthly_cost' => (float) $cost->total_monthly_cost,
                    'notes' => $cost->notes ?? '',
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * Helper sanitasi input format rupiah (1.000.000 -> 1000000)
     */
    private function cleanCurrencyInputs(array $inputs): array
    {
        $fields = [
            'rent_cost', 'wifi_cost', 'trash_cost', 'utilities_cost',
            'netflix_cost', 'ipl_cost', 'salary_cost', 'other_cost'
        ];

        $cleaned = [];
        foreach ($fields as $field) {
            if (isset($inputs[$field])) {
                $val = str_replace(['.', ','], ['', '.'], (string) $inputs[$field]);
                $cleaned[$field] = is_numeric($val) ? (float) $val : 0;
            }
        }

        return $cleaned;
    }

    /**
     * Export PDF Rekapitulasi Cost Cabang & Belanja Harian (A4 Landscape)
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $query = BranchMonthlyCost::with(['branch', 'user']);

        // Scoping akses berdasarkan role
        if ($user->isAdminCabang() || $user->isAdminDapur()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } elseif ($user->isKepalaCabang()) {
            $accessibleIds = $user->getAccessibleBranchIds();
            $query->whereIn('branch_id', $accessibleIds);
            $selectedBranchId = $request->get('branch_id');
        } else {
            $selectedBranchId = $request->get('branch_id');
        }

        // Filter Cabang
        if (!empty($selectedBranchId)) {
            $query->where('branch_id', $selectedBranchId);
        }

        // Filter Tahun & Bulan
        $selectedYear = $request->filled('year') ? (int) $request->get('year') : null;
        $selectedMonth = $request->filled('month') ? (int) $request->get('month') : null;

        if ($selectedYear) {
            $query->where('year', $selectedYear);
        }
        if ($selectedMonth) {
            $query->where('month', $selectedMonth);
        }

        $query->orderBy('year', 'desc')->orderBy('month', 'desc')->orderBy('branch_id', 'asc');

        $costs = $query->get();

        // Ambil data Belanja Harian di bulan dan cabang yang sesuai untuk setiap item
        foreach ($costs as $cost) {
            $dailyExpenseData = DailyKitchenReport::where('branch_id', $cost->branch_id)
                ->whereYear('report_date', $cost->year)
                ->whereMonth('report_date', $cost->month)
                ->select(
                    DB::raw('COALESCE(SUM(total_expense), 0) as total_daily'),
                    DB::raw('COALESCE(SUM(expense_raw_material), 0) as total_raw'),
                    DB::raw('COALESCE(SUM(expense_non_raw_material), 0) as total_non_raw'),
                    DB::raw('COALESCE(SUM(expense_personal), 0) as total_personal'),
                    DB::raw('COUNT(id) as total_days')
                )
                ->first();

            $cost->daily_expense_total = (float) ($dailyExpenseData->total_daily ?? 0);
            $cost->daily_expense_raw = (float) ($dailyExpenseData->total_raw ?? 0);
            $cost->daily_expense_non_raw = (float) ($dailyExpenseData->total_non_raw ?? 0);
            $cost->daily_expense_personal = (float) ($dailyExpenseData->total_personal ?? 0);
            $cost->daily_expense_days = (int) ($dailyExpenseData->total_days ?? 0);
            $cost->grand_total_cost = $cost->total_monthly_cost + $cost->daily_expense_total;
        }

        // Kalkulasi Total Belanja Harian untuk filter aktif (jika ada data harian tanpa data cost bulanan atau sebaliknya)
        $dailyQuery = DailyKitchenReport::query();
        if ($user->isAdminCabang() || $user->isAdminDapur()) {
            $dailyQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isKepalaCabang()) {
            $dailyQuery->whereIn('branch_id', $user->getAccessibleBranchIds());
        }
        if (!empty($selectedBranchId)) {
            $dailyQuery->where('branch_id', $selectedBranchId);
        }
        if ($selectedYear) {
            $dailyQuery->whereYear('report_date', $selectedYear);
        }
        if ($selectedMonth) {
            $dailyQuery->whereMonth('report_date', $selectedMonth);
        }

        $statsMonthlyTotal = (float) $costs->sum('total_monthly_cost');
        $statsDailyTotal = (float) $dailyQuery->sum('total_expense');
        $statsGrandTotal = $statsMonthlyTotal + $statsDailyTotal;

        // Label Filter
        $branchLabel = 'Semua Cabang';
        if (!empty($selectedBranchId)) {
            $b = Branch::find($selectedBranchId);
            if ($b) $branchLabel = $b->name;
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthLabel = ($selectedMonth && isset($monthNames[$selectedMonth])) ? $monthNames[$selectedMonth] : 'Semua Bulan';
        $yearLabel = $selectedYear ? (string) $selectedYear : 'Semua Tahun';

        $pdf = Pdf::loadView('monthly-costs.pdf', compact(
            'costs',
            'statsMonthlyTotal',
            'statsDailyTotal',
            'statsGrandTotal',
            'branchLabel',
            'monthLabel',
            'yearLabel',
            'user'
        ))->setPaper('a4', 'landscape');

        $fileName = 'Laporan_Cost_Cabang_' . str_replace(' ', '_', $branchLabel) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Export PDF Lembar Rincian 1 Periode Cost Cabang (A4 Portrait)
     */
    public function exportSinglePdf(BranchMonthlyCost $monthlyCost)
    {
        $user = auth()->user();
        if ($user->isAdminCabang() || $user->isAdminDapur()) {
            if ($monthlyCost->branch_id !== $user->branch_id) {
                abort(403, 'Akses ditolak.');
            }
        } elseif ($user->isKepalaCabang()) {
            if (!in_array($monthlyCost->branch_id, $user->getAccessibleBranchIds())) {
                abort(403, 'Akses ditolak.');
            }
        }

        $monthlyCost->load(['branch', 'user']);

        $dailyExpenseData = DailyKitchenReport::where('branch_id', $monthlyCost->branch_id)
            ->whereYear('report_date', $monthlyCost->year)
            ->whereMonth('report_date', $monthlyCost->month)
            ->select(
                DB::raw('COALESCE(SUM(total_expense), 0) as total_daily'),
                DB::raw('COALESCE(SUM(expense_raw_material), 0) as total_raw'),
                DB::raw('COALESCE(SUM(expense_non_raw_material), 0) as total_non_raw'),
                DB::raw('COALESCE(SUM(expense_personal), 0) as total_personal'),
                DB::raw('COUNT(id) as total_days')
            )
            ->first();

        $monthlyCost->daily_expense_total = (float) ($dailyExpenseData->total_daily ?? 0);
        $monthlyCost->daily_expense_raw = (float) ($dailyExpenseData->total_raw ?? 0);
        $monthlyCost->daily_expense_non_raw = (float) ($dailyExpenseData->total_non_raw ?? 0);
        $monthlyCost->daily_expense_personal = (float) ($dailyExpenseData->total_personal ?? 0);
        $monthlyCost->daily_expense_days = (int) ($dailyExpenseData->total_days ?? 0);
        $monthlyCost->grand_total_cost = $monthlyCost->total_monthly_cost + $monthlyCost->daily_expense_total;

        // Ambil list detail laporan belanja harian di bulan tersebut
        $dailyReports = DailyKitchenReport::where('branch_id', $monthlyCost->branch_id)
            ->whereYear('report_date', $monthlyCost->year)
            ->whereMonth('report_date', $monthlyCost->month)
            ->orderBy('report_date', 'asc')
            ->get();

        $pdf = Pdf::loadView('monthly-costs.pdf-single', compact(
            'monthlyCost',
            'dailyReports',
            'user'
        ))->setPaper('a4', 'portrait');

        $fileName = 'Rincian_Cost_' . str_replace(' ', '_', $monthlyCost->branch->name) . '_' . $monthlyCost->month_name . '_' . $monthlyCost->year . '.pdf';
        return $pdf->download($fileName);
    }
}
