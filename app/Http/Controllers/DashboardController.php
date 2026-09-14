<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Statistik & Resume Transaksi Penjualan Lengkap
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Transaction::with(['branch', 'user']);

        // Jika user bukan Super Admin & bukan Viewer, batasi hanya cabangnya sendiri (Kepala Cabang & Admin Cabang)
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
        $totalIncome = (clone $query)->sum('amount');
        $totalTransactions = (clone $query)->count();
        $totalQty = (clone $query)->sum('qty');
        $activeBranchesCount = Branch::where('status', 'active')->count();

        // 2. Metrik Finansial Tambahan (AOV, Return Rate, Top Branch)
        $avgOrderValue = $totalTransactions > 0 ? round($totalIncome / $totalTransactions) : 0;
        
        $grossSales = (clone $query)->where('amount', '>', 0)->sum('amount');
        $totalReturns = abs((clone $query)->where('amount', '<', 0)->sum('amount'));
        $returnRate = $grossSales > 0 ? round(($totalReturns / $grossSales) * 100, 1) : 0;

        // 3. Perbandingan Cabang & Top Performing Branch
        $allBranches = Branch::where('status', 'active')->with(['transactions'])->get();
        $maxBranchIncome = 1;
        $branchComparisons = [];
        $topBranchName = '-';
        $topBranchAmount = 0;

        foreach ($allBranches as $branch) {
            $branchTrx = $branch->transactions;
            $sumAmount = $branchTrx->sum('amount');
            $countTrx = $branchTrx->count();

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
                'count' => $countTrx,
                'amount' => $sumAmount,
            ];
        }

        // Urutkan perbandingan cabang dari omzet terbesar
        usort($branchComparisons, fn($a, $b) => $b['amount'] <=> $a['amount']);

        foreach ($branchComparisons as &$bc) {
            $bc['percent'] = $maxBranchIncome > 0 ? max(8, min(100, round(($bc['amount'] / $maxBranchIncome) * 100))) : 0;
        }

        // 4. Rekap Jenis Transaksi & Data Donut Chart
        $types = ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'];
        $typeSummaries = [];
        $typeChartLabels = [];
        $typeChartData = [];
        $typeChartColors = [
            'Penjualan Tunai' => '#0A97B0',
            'Penjualan Kredit' => '#0284c7',
            'Retur Penjualan' => '#f43f5e',
            'Transfer Cabang' => '#f59e0b',
        ];

        foreach ($types as $type) {
            $tQuery = (clone $query)->where('type', $type);
            $count = (clone $tQuery)->count();
            $amount = (clone $tQuery)->sum('amount');

            $typeSummaries[$type] = [
                'count' => $count,
                'amount' => $amount,
                'color' => $typeChartColors[$type] ?? '#64748b'
            ];

            $typeChartLabels[] = $type;
            $typeChartData[] = abs($amount);
        }

        // 5. Tren Penjualan Harian untuk Area Line Chart (Chart.js)
        $dailySalesQuery = (clone $query)
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('COUNT(id) as trx_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $chartLabels = [];
        $chartAmounts = [];
        $chartQtys = [];

        foreach ($dailySalesQuery as $daily) {
            $chartLabels[] = Carbon::parse($daily->date)->translatedFormat('d M');
            $chartAmounts[] = (int) $daily->total_amount;
            $chartQtys[] = (int) $daily->total_qty;
        }

        // 6. Top 5 Customers (Pelanggan dengan Kontribusi Terbesar)
        $topCustomers = (clone $query)
            ->select('customer_name', DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(qty) as total_qty'), DB::raw('COUNT(id) as count_trx'))
            ->groupBy('customer_name')
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        // 7. Transaksi Terkini
        $recentTransactions = (clone $query)->latest('transaction_date')->latest('id')->take(6)->get();

        return view('dashboard', compact(
            'totalIncome',
            'totalTransactions',
            'totalQty',
            'activeBranchesCount',
            'avgOrderValue',
            'returnRate',
            'topBranchName',
            'topBranchAmount',
            'allBranches',
            'branchComparisons',
            'typeSummaries',
            'typeChartLabels',
            'typeChartData',
            'chartLabels',
            'chartAmounts',
            'chartQtys',
            'topCustomers',
            'recentTransactions',
            'selectedBranchId'
        ));
    }
}
