<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchMonthlyCost;
use App\Models\DailyKitchenReport;
use App\Models\DailyKitchenReportItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Statistik & Resume Laporan Dapur, Omzet Penjualan & Analisis Finansial
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = DailyKitchenReport::with(['branch', 'user']);

        // 1. Scoping Akses Cabang
        if ($user->isKepalaCabang()) {
            $accessibleBranchIds = $user->getAccessibleBranchIds();
            $selectedBranchId = $request->get('branch_id');
            if (!empty($selectedBranchId) && in_array($selectedBranchId, $accessibleBranchIds)) {
                $query->where('branch_id', $selectedBranchId);
            } else {
                $query->whereIn('branch_id', $accessibleBranchIds);
            }
        } elseif (!$user->canAccessAllBranches()) {
            // Admin Cabang, Admin Dapur, dan Viewer terkunci ke cabangnya sendiri
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } else {
            // Super Admin bisa filter per cabang atau semua cabang
            $selectedBranchId = $request->get('branch_id');
            if (!empty($selectedBranchId)) {
                $query->where('branch_id', $selectedBranchId);
            }
        }

        // 2. Filter Rentang Waktu (Date From & Date To)
        $now = Carbon::now();
        $dateFrom = $request->filled('date_from') ? $request->get('date_from') : $now->copy()->startOfMonth()->toDateString();
        $dateTo = $request->filled('date_to') ? $request->get('date_to') : $now->copy()->toDateString();

        $query->whereDate('report_date', '>=', $dateFrom)
              ->whereDate('report_date', '<=', $dateTo);

        // 3. Stat Cards Utama
        $totalIncome = (float) (clone $query)->sum('total_omset');
        $totalSalesFood = (float) (clone $query)->sum('grand_total_sales');
        $totalReports = (clone $query)->count();
        $totalWastedFood = (float) (clone $query)->sum('total_wasted_food');
        $totalRemainingSellable = (float) (clone $query)->sum('total_remaining_sellable');
        $totalDifference = (float) (clone $query)->sum('difference_amount');

        // Porsi Terjual & Dimasak
        $reportIds = (clone $query)->pluck('id');
        $totalPortionsSold = 0;
        $totalPortionsCooked = 0;

        if ($reportIds->isNotEmpty()) {
            $totalPortionsSold = (int) DailyKitchenReportItem::whereIn('daily_kitchen_report_id', $reportIds)->sum('sold');
            $totalPortionsCooked = (int) DailyKitchenReportItem::whereIn('daily_kitchen_report_id', $reportIds)->sum('total_cooked');
        }

        // 4. Kalkulasi Biaya & Gross Margin
        $startCarbon = Carbon::parse($dateFrom);
        $endCarbon = Carbon::parse($dateTo);

        $monthlyCostQuery = BranchMonthlyCost::query();
        if (!empty($selectedBranchId)) {
            $monthlyCostQuery->where('branch_id', $selectedBranchId);
        } elseif ($user->isKepalaCabang()) {
            $monthlyCostQuery->whereIn('branch_id', $user->getAccessibleBranchIds());
        } elseif (!$user->canAccessAllBranches()) {
            $monthlyCostQuery->where('branch_id', $user->branch_id);
        }

        $monthlyCostQuery->where(function ($q) use ($startCarbon, $endCarbon) {
            $startYear = $startCarbon->year;
            $startMonth = $startCarbon->month;
            $endYear = $endCarbon->year;
            $endMonth = $endCarbon->month;

            if ($startYear === $endYear) {
                $q->where('year', $startYear)->whereBetween('month', [$startMonth, $endMonth]);
            } else {
                $q->where(function ($sq) use ($startYear, $startMonth) {
                    $sq->where('year', $startYear)->where('month', '>=', $startMonth);
                })->orWhere(function ($sq) use ($endYear, $endMonth) {
                    $sq->where('year', $endYear)->where('month', '<=', $endMonth);
                })->orWhere(function ($sq) use ($startYear, $endYear) {
                    $sq->where('year', '>', $startYear)->where('year', '<', $endYear);
                });
            }
        });

        $totalFixedCost = (float) $monthlyCostQuery->sum('total_monthly_cost');
        $totalDailyExpense = (float) (clone $query)->sum('total_expense');
        $totalCost = $totalFixedCost + $totalDailyExpense;

        $grossMargin = $totalIncome - $totalCost;
        $grossMarginPercent = $totalIncome > 0 ? round(($grossMargin / $totalIncome) * 100, 1) : 0;
        $surplusKasir = $totalDifference;

        // 5. Metrik Finansial Rata-rata
        $avgDailyOmset = $totalReports > 0 ? round($totalIncome / $totalReports) : 0;
        $activeBranchesCount = Branch::where('status', 'active')->count();

        // 6. Daftar Cabang yang Berhak Diakses
        if ($user->isKepalaCabang()) {
            $allBranches = $user->getAccessibleBranches()->load(['dailyKitchenReports']);
        } elseif (!$user->canAccessAllBranches()) {
            $allBranches = Branch::where('id', $user->branch_id)->where('status', 'active')->with(['dailyKitchenReports'])->get();
        } else {
            $allBranches = Branch::where('status', 'active')->with(['dailyKitchenReports'])->get();
        }

        // Top Performing Branch
        $maxBranchIncome = 1;
        $branchComparisons = [];
        $topBranchName = '-';
        $topBranchAmount = 0;

        foreach ($allBranches as $branch) {
            $branchReports = $branch->dailyKitchenReports()
                ->whereDate('report_date', '>=', $dateFrom)
                ->whereDate('report_date', '<=', $dateTo)
                ->get();
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

        usort($branchComparisons, fn($a, $b) => $b['amount'] <=> $a['amount']);

        foreach ($branchComparisons as &$bc) {
            $bc['percent'] = $maxBranchIncome > 0 ? max(8, min(100, round(($bc['amount'] / $maxBranchIncome) * 100))) : 0;
        }

        // 7. Komparasi 1, 2, atau 3 Cabang
        $compareBranchIdsInput = $request->input('compare_branch_ids');
        if (is_string($compareBranchIdsInput)) {
            $compareBranchIds = array_filter(explode(',', $compareBranchIdsInput));
        } elseif (is_array($compareBranchIdsInput)) {
            $compareBranchIds = array_filter($compareBranchIdsInput);
        } else {
            $compareBranchIds = [];
        }

        if (!empty($compareBranchIds)) {
            $compareBranchIds = array_slice($compareBranchIds, 0, 3);
        } else {
            $compareBranchIds = [];
        }

        $comparisonData = [];
        foreach ($allBranches->whereIn('id', $compareBranchIds) as $cBranch) {
            $bDailyQuery = DailyKitchenReport::where('branch_id', $cBranch->id)
                ->whereDate('report_date', '>=', $dateFrom)
                ->whereDate('report_date', '<=', $dateTo);

            $bOmset = (float) (clone $bDailyQuery)->sum('total_omset');
            $bFoodSales = (float) (clone $bDailyQuery)->sum('grand_total_sales');
            $bDailyExpense = (float) (clone $bDailyQuery)->sum('total_expense');
            $bReportsCount = (clone $bDailyQuery)->count();
            $bSurplusKasir = (float) (clone $bDailyQuery)->sum('difference_amount');

            $bReportIds = (clone $bDailyQuery)->pluck('id');
            $bPortionsSold = $bReportIds->isNotEmpty() ? (int) DailyKitchenReportItem::whereIn('daily_kitchen_report_id', $bReportIds)->sum('sold') : 0;
            $bPortionsCooked = $bReportIds->isNotEmpty() ? (int) DailyKitchenReportItem::whereIn('daily_kitchen_report_id', $bReportIds)->sum('total_cooked') : 0;

            $bFixedCostQuery = BranchMonthlyCost::where('branch_id', $cBranch->id);
            $bFixedCostQuery->where(function ($q) use ($startCarbon, $endCarbon) {
                $startYear = $startCarbon->year;
                $startMonth = $startCarbon->month;
                $endYear = $endCarbon->year;
                $endMonth = $endCarbon->month;
                if ($startYear === $endYear) {
                    $q->where('year', $startYear)->whereBetween('month', [$startMonth, $endMonth]);
                } else {
                    $q->where(function ($sq) use ($startYear, $startMonth) {
                        $sq->where('year', $startYear)->where('month', '>=', $startMonth);
                    })->orWhere(function ($sq) use ($endYear, $endMonth) {
                        $sq->where('year', $endYear)->where('month', '<=', $endMonth);
                    });
                }
            });
            $bFixedCost = (float) $bFixedCostQuery->sum('total_monthly_cost');
            $bTotalCost = $bFixedCost + $bDailyExpense;
            $bGrossMargin = $bOmset - $bTotalCost;
            $bGrossMarginPercent = $bOmset > 0 ? round(($bGrossMargin / $bOmset) * 100, 1) : 0;

            $bCash = (float) (clone $bDailyQuery)->sum('cash_income');
            $bQris = (float) (clone $bDailyQuery)->sum('qris_income');
            $bOnline = (float) (clone $bDailyQuery)->sum('online_food_income');

            $comparisonData[] = [
                'id' => $cBranch->id,
                'name' => $cBranch->name,
                'omset' => $bOmset,
                'food_sales' => $bFoodSales,
                'fixed_cost' => $bFixedCost,
                'daily_expense' => $bDailyExpense,
                'total_cost' => $bTotalCost,
                'gross_margin' => $bGrossMargin,
                'gross_margin_percent' => $bGrossMarginPercent,
                'surplus_kasir' => $bSurplusKasir,
                'portions_sold' => $bPortionsSold,
                'portions_cooked' => $bPortionsCooked,
                'total_reports' => $bReportsCount,
                'cash' => $bCash,
                'qris' => $bQris,
                'online' => $bOnline,
            ];
        }

        // 8. Rekap Pembayaran Kasir & Data Donut Chart
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

        // 9. Rentang Tanggal Harian untuk Multi-Line & Single-Line Chart
        $periodDates = [];
        $dateCursor = Carbon::parse($dateFrom);
        $dateEndLimit = Carbon::parse($dateTo);
        while ($dateCursor->lte($dateEndLimit)) {
            $periodDates[] = $dateCursor->toDateString();
            $dateCursor->addDay();
        }
        $chartLabels = array_map(fn($d) => Carbon::parse($d)->translatedFormat('d M'), $periodDates);

        $colorPalette = [
            ['border' => '#0A97B0', 'bg' => 'rgba(10, 151, 176, 0.08)'],
            ['border' => '#6366f1', 'bg' => 'rgba(99, 102, 241, 0.08)'],
            ['border' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.08)'],
        ];

        $hasCompareBranches = !empty($compareBranchIds) && count($comparisonData) > 0;
        $multiLineDatasets = [];

        if ($hasCompareBranches) {
            foreach ($comparisonData as $cIdx => $cBranchData) {
                $bReportsByDate = DailyKitchenReport::where('branch_id', $cBranchData['id'])
                    ->whereDate('report_date', '>=', $dateFrom)
                    ->whereDate('report_date', '<=', $dateTo)
                    ->pluck('total_omset', 'report_date')
                    ->toArray();

                $branchSeries = [];
                foreach ($periodDates as $pDate) {
                    $branchSeries[] = (int) ($bReportsByDate[$pDate] ?? 0);
                }

                $color = $colorPalette[$cIdx % count($colorPalette)];
                $multiLineDatasets[] = [
                    'label' => $cBranchData['name'] . ' (Rp)',
                    'data' => $branchSeries,
                    'borderColor' => $color['border'],
                    'backgroundColor' => $color['bg'],
                    'borderWidth' => 2.5,
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 3.5,
                    'pointHoverRadius' => 6,
                    'pointBackgroundColor' => $color['border'],
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2
                ];
            }
        } else {
            // Single Line Dataset (Global / Cabang Aktif)
            $dailySalesQuery = (clone $query)
                ->select(
                    'report_date',
                    DB::raw('SUM(total_omset) as total_amount')
                )
                ->groupBy('report_date')
                ->pluck('total_amount', 'report_date')
                ->toArray();

            $chartAmounts = [];
            foreach ($periodDates as $pDate) {
                $chartAmounts[] = (int) ($dailySalesQuery[$pDate] ?? 0);
            }

            $multiLineDatasets[] = [
                'label' => 'Total Omzet Kasir (Rp)',
                'data' => $chartAmounts,
                'borderColor' => '#0A97B0',
                'backgroundColor' => 'rgba(10, 151, 176, 0.08)',
                'borderWidth' => 2.5,
                'fill' => true,
                'tension' => 0.35,
                'pointRadius' => 3.5,
                'pointHoverRadius' => 6,
                'pointBackgroundColor' => '#0A97B0',
                'pointBorderColor' => '#ffffff',
                'pointBorderWidth' => 2
            ];
        }

        // 10. Top 5 Menu Masakan Terlaris
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

        // 11. Laporan Dapur Terkini
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
            'totalFixedCost',
            'totalDailyExpense',
            'totalCost',
            'grossMargin',
            'grossMarginPercent',
            'surplusKasir',
            'avgDailyOmset',
            'activeBranchesCount',
            'topBranchName',
            'topBranchAmount',
            'allBranches',
            'branchComparisons',
            'comparisonData',
            'compareBranchIds',
            'hasCompareBranches',
            'totalCash',
            'totalQris',
            'totalOnline',
            'paymentSummaries',
            'paymentChartLabels',
            'paymentChartData',
            'chartLabels',
            'multiLineDatasets',
            'topMenus',
            'recentReports',
            'selectedBranchId',
            'dateFrom',
            'dateTo'
        ));
    }
}
