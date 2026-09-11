<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Statistik & Resume Transaksi
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Transaction::with('branch');

        // Jika user adalah Admin Cabang, batasi hanya cabangnya sendiri
        if ($user->isAdminCabang()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } else {
            // Kepala Cabang / Super Admin bisa filter per cabang
            $selectedBranchId = $request->get('branch_id');
            if (!empty($selectedBranchId)) {
                $query->where('branch_id', $selectedBranchId);
            }
        }

        // 1. Stat Cards
        $totalIncome = (clone $query)->sum('amount');
        $totalTransactions = (clone $query)->count();
        $totalQty = (clone $query)->sum('qty');
        $activeBranchesCount = Branch::where('status', 'active')->count();

        // 2. Perbandingan Cabang
        $allBranches = Branch::where('status', 'active')->with(['transactions'])->get();
        $maxBranchIncome = 1;
        $branchComparisons = [];

        foreach ($allBranches as $branch) {
            $branchTrx = $branch->transactions;
            $sumAmount = $branchTrx->sum('amount');
            $countTrx = $branchTrx->count();

            if ($sumAmount > $maxBranchIncome) {
                $maxBranchIncome = $sumAmount;
            }

            $branchComparisons[] = [
                'id' => $branch->id,
                'name' => $branch->name,
                'count' => $countTrx,
                'amount' => $sumAmount,
            ];
        }

        // Hitung persentase bar
        foreach ($branchComparisons as &$bc) {
            $bc['percent'] = $maxBranchIncome > 0 ? max(10, min(100, round(($bc['amount'] / $maxBranchIncome) * 100))) : 0;
        }

        // 3. Rekap Jenis Transaksi
        $types = ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'];
        $typeSummaries = [];

        foreach ($types as $type) {
            $tQuery = (clone $query)->where('type', $type);
            $typeSummaries[$type] = [
                'count' => (clone $tQuery)->count(),
                'amount' => (clone $tQuery)->sum('amount'),
            ];
        }

        // 4. Transaksi Terkini
        $recentTransactions = (clone $query)->latest('transaction_date')->latest('id')->take(6)->get();

        return view('dashboard', compact(
            'totalIncome',
            'totalTransactions',
            'totalQty',
            'activeBranchesCount',
            'allBranches',
            'branchComparisons',
            'typeSummaries',
            'recentTransactions',
            'selectedBranchId'
        ));
    }
}
