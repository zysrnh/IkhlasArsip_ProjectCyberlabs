<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyKitchenReport;
use App\Models\DailyKitchenReportItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Statistik & Resume Laporan Dapur & Omzet Penjualan
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = DailyKitchenReport::with(['branch', 'user']);

        // Jika user bukan Super Admin & bukan Viewer, batasi hanya cabangnya sendiri
        if (!$user->canAccessAllBranches() && !$user->isViewer()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } else {
            // Super Admin & Viewer bisa filter per cabang atau semua cabang
            $selectedBranchId = $request->get('branch_id');
            if (!empty($selectedBranchId)) {
                $query->where('branch_id', $selectedBranchId);
            }
        }

        // 1. Stat Cards Utama
        $totalIncome = (clone $query)->sum('total_omset');
        $totalSalesFood = (clone $query)->sum('grand_total_sales');
        $totalReports = (clone $query)->count();
        $totalWastedFood = (clone $query)->sum('total_wasted_food');
        $totalRemainingSellable = (clone $query)->sum('total_remaining_sellable');
        $totalDifference = (clone $query)->sum('difference_amount');

        // Porsi Terjual & Dimasak
        $reportIds = (clone $query)->pluck('id');
        $totalPortionsSold = 0;
        $totalPortionsCooked = 0;

        if ($reportIds->isNotEmpty()) {
            $totalPortionsSold = (int) DailyKitchenReportItem::whereIn('daily_kitchen_report_id', $reportIds)->sum('sold');
            $totalPortionsCooked = (int) DailyKitchenReportItem::whereIn('daily_kitchen_report_id', $reportIds)->sum('total_cooked');
        }

        // 2. Metrik Finansial Rata-rata
        $avgDailyOmset = $totalReports > 0 ? round($totalIncome / $totalReports) : 0;
        $activeBranchesCount = Branch::where('status', 'active')->count();

        // 3. Perbandingan Cabang & Top Performing Branch
        $allBranches = Branch::where('status', 'active')->with(['dailyKitchenReports'])->get();
        $maxBranchIncome = 1;
        $branchComparisons = [];
        $topBranchName = '-';
        $topBranchAmount = 0;

        foreach ($allBranches as $branch) {
            $branchReports = $branch->dailyKitchenReports;
            $sumAmount = (float) $branchReports->sum('total_omset');
            $countReports = $branchReports->count();

            if ($sumAmount > $maxBranchIncome) {
                $maxBranchIncome = $sumAmount;
            }

            if ($sumAmount > $topBranchAmount) {
                $topBranchAmount = $sumAmount;
                $topBranchName = $branch->name;
            }

            $branchComparisons[] = [
                'id' => $branch->id,
                'name' => $branch->name,
                'count' => $countReports,
                'amount' => $sumAmount,
            ];
        }

        // Urutkan perbandingan cabang dari omzet terbesar
        usort($branchComparisons, fn($a, $b) => $b['amount'] <=> $a['amount']);

        foreach ($branchComparisons as &$bc) {
            $bc['percent'] = $maxBranchIncome > 0 ? max(8, min(100, round(($bc['amount'] / $maxBranchIncome) * 100))) : 0;
        }

        // 4. Rekap Pembayaran Kasir (Tunai, QRIS, Online Food) & Data Donut Chart
        $totalCash = (float) (clone $query)->sum('cash_income');
        $totalQris = (float) (clone $query)->sum('qris_income');
        $totalOnline = (float) (clone $query)->sum('online_food_income');

        $paymentSummaries = [
            'Penjualan Tunai' => [
                'amount' => $totalCash,
                'color' => '#0A97B0'
            ],
            'QRIS / Transfer' => [
                'amount' => $totalQris,
                'color' => '#0284c7'
            ],
            'Online Food (Grab/Gojek/Shopee)' => [
                'amount' => $totalOnline,
                'color' => '#f59e0b'
            ],
        ];

        $paymentChartLabels = array_keys($paymentSummaries);
        $paymentChartData = array_map(fn($item) => $item['amount'], array_values($paymentSummaries));

        // 5. Tren Omzet Penjualan Harian untuk Line Chart (Chart.js)
        $dailySalesQuery = (clone $query)
            ->select(
                'report_date',
                DB::raw('SUM(total_omset) as total_amount'),
                DB::raw('SUM(grand_total_sales) as total_food_sales'),
                DB::raw('COUNT(id) as count_reports')
            )
            ->groupBy('report_date')
            ->orderBy('report_date', 'asc')
            ->get();

        $chartLabels = [];
        $chartAmounts = [];

        foreach ($dailySalesQuery as $daily) {
            $chartLabels[] = Carbon::parse($daily->report_date)->translatedFormat('d M');
            $chartAmounts[] = (int) $daily->total_amount;
        }

        // 6. Top 5 Menu Masakan Terlaris
        $topMenus = collect();
        if ($reportIds->isNotEmpty()) {
            $topMenus = DailyKitchenReportItem::whereIn('daily_kitchen_report_id', $reportIds)
                ->select(
                    'menu_id',
                    DB::raw('SUM(sold) as total_sold'),
                    DB::raw('SUM(total_sales) as total_revenue')
                )
                ->with('menu')
                ->groupBy('menu_id')
                ->orderByDesc('total_sold')
                ->take(5)
                ->get();
        }

        // 7. Laporan Dapur Terkini
        $recentReports = (clone $query)->latest('report_date')->latest('id')->take(6)->get();

        return view('dashboard', compact(
            'totalIncome',
            'totalSalesFood',
            'totalReports',
            'totalPortionsSold',
            'totalPortionsCooked',
            'totalWastedFood',
            'totalRemainingSellable',
            'totalDifference',
            'avgDailyOmset',
            'activeBranchesCount',
            'topBranchName',
            'topBranchAmount',
            'allBranches',
            'branchComparisons',
            'paymentSummaries',
            'paymentChartLabels',
            'paymentChartData',
            'chartLabels',
            'chartAmounts',
            'topMenus',
            'recentReports',
            'selectedBranchId'
        ));
    }
}
