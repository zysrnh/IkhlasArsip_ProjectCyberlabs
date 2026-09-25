<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyKitchenReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DailyExpenseController extends Controller
{
    /**
     * Tampilkan halaman rekapitulasi belanja harian cabang.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = DailyKitchenReport::with(['branch', 'user'])->where('total_expense', '>', 0);

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
                $q->where('expense_notes', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('branch', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->orderBy('report_date', 'asc')->orderBy('id', 'asc');
                break;
            case 'terbesar':
                $query->orderBy('total_expense', 'desc');
                break;
            case 'terkecil':
                $query->orderBy('total_expense', 'asc');
                break;
            case 'terbaru':
            default:
                $query->orderBy('report_date', 'desc')->orderBy('id', 'desc');
                break;
        }

        // Cabang untuk filter & form input
        if ($user->isSuperAdmin()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->isKepalaCabang() || $user->isViewer()) {
            $branches = $user->getAccessibleBranches();
        } elseif ($user->branch_id) {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();
        }

        // Hitung Statistik Dinamis
        $stats = [
            'total_records' => (clone $query)->count(),
            'total_raw' => (float) (clone $query)->sum('expense_raw_material'),
            'total_non_raw' => (float) (clone $query)->sum('expense_non_raw_material'),
            'total_personal' => (float) (clone $query)->sum('expense_personal'),
            'grand_total_expense' => (float) (clone $query)->sum('total_expense'),
        ];

        $expenses = $query->paginate(15)->withQueryString();

        return view('expenses.index', compact('expenses', 'branches', 'stats', 'selectedBranchId'));
    }

    /**
     * Simpan / Perbarui data belanja harian secara mandiri dari halaman belanja.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer()) {
            return redirect()->back()->with('error', 'Akun Viewer tidak memiliki izin mencatat belanja.');
        }

        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        $cleanRawExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_raw_material', '0'));
        $cleanNonRawExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_non_raw_material', '0'));
        $cleanPersonalExpense = (float) str_replace(['.', ','], ['', '.'], $request->input('expense_personal', '0'));

        $request->merge([
            'expense_raw_material' => $cleanRawExpense,
            'expense_non_raw_material' => $cleanNonRawExpense,
            'expense_personal' => $cleanPersonalExpense,
        ]);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'report_date' => 'required|date',
            'expense_raw_material' => 'nullable|numeric|min:0',
            'expense_non_raw_material' => 'nullable|numeric|min:0',
            'expense_personal' => 'nullable|numeric|min:0',
            'expense_notes' => 'nullable|string|max:1000',
        ], [
            'branch_id.required' => 'Cabang wajib dipilih.',
            'report_date.required' => 'Tanggal belanja wajib diisi.',
        ]);

        $totalExpense = $cleanRawExpense + $cleanNonRawExpense + $cleanPersonalExpense;

        if ($totalExpense <= 0) {
            return redirect()->back()->withInput()->with('error', 'Total belanja tidak boleh Rp 0.');
        }

        $report = DailyKitchenReport::firstOrNew([
            'branch_id' => $validated['branch_id'],
            'report_date' => $validated['report_date'],
        ]);

        if (!$report->exists) {
            $report->user_id = auth()->id();
            $report->grand_total_sales = 0;
            $report->total_remaining_sellable = 0;
            $report->total_wasted_food = 0;
            $report->cash_income = 0;
            $report->qris_income = 0;
            $report->online_food_income = 0;
            $report->total_omset = 0;
            $report->difference_amount = 0;
            $report->status = 'completed';
        }

        $report->expense_raw_material = $cleanRawExpense;
        $report->expense_non_raw_material = $cleanNonRawExpense;
        $report->expense_personal = $cleanPersonalExpense;
        $report->total_expense = $totalExpense;
        $report->net_cash_income = $report->total_omset - $totalExpense;
        if (isset($validated['expense_notes'])) {
            $report->expense_notes = strip_tags(trim($validated['expense_notes']));
        }
        $report->save();

        return redirect()->route('daily-expenses.index')
            ->with('success', 'Data belanja harian tanggal ' . Carbon::parse($validated['report_date'])->translatedFormat('d F Y') . ' berhasil disimpan.');
    }

    /**
     * Export PDF Rekapitulasi Belanja Harian Cabang (A4 Landscape)
     */
    public function exportPdf(Request $request): Response
    {
        $user = auth()->user();
        $query = DailyKitchenReport::with(['branch', 'user'])->where('total_expense', '>', 0);

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
                $q->where('expense_notes', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('branch', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $query->orderBy('report_date', 'desc')->orderBy('id', 'desc');

        $expenses = $query->get();

        // Hitung Statistik
        $stats = [
            'total_records' => $expenses->count(),
            'total_raw' => (float) $expenses->sum('expense_raw_material'),
            'total_non_raw' => (float) $expenses->sum('expense_non_raw_material'),
            'total_personal' => (float) $expenses->sum('expense_personal'),
            'grand_total_expense' => (float) $expenses->sum('total_expense'),
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

        $pdf = Pdf::loadView('expenses.pdf', compact('expenses', 'stats', 'branchLabel', 'dateRangeLabel', 'user'))
            ->setPaper('a4', 'landscape');

        $fileName = 'Laporan_Belanja_Harian_' . str_replace(' ', '_', $branchLabel) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Hapus / Reset data belanja harian pada laporan dapur tertentu.
     */
    public function destroy($id): RedirectResponse
    {
        $user = auth()->user();
        if ($user->isViewer()) {
            return redirect()->back()->with('error', 'Akun Viewer tidak memiliki izin menghapus belanja.');
        }

        $report = DailyKitchenReport::findOrFail($id);

        if (($user->isAdminCabang() || $user->isAdminDapur()) && $user->branch_id && $report->branch_id != $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses menghapus data belanja cabang lain.');
        } elseif ($user->isKepalaCabang()) {
            if ($user->managedBranches()->count() > 0 && !in_array($report->branch_id, $user->managedBranches()->pluck('branches.id')->toArray())) {
                abort(403, 'Akses ditolak.');
            }
        }

        $dateFormatted = $report->report_date ? $report->report_date->translatedFormat('d F Y') : '-';

        // Jika laporan dapur ini murni hanya catatan belanja (tanpa item masakan dan omzet = 0), hapus record
        if ($report->items()->count() === 0 && (float)$report->total_omset == 0 && (float)$report->grand_total_sales == 0) {
            $report->delete();
        } else {
            // Jika ada laporan masakan / omzet, cukup kosongkan nilai belanja
            $report->expense_raw_material = 0;
            $report->expense_non_raw_material = 0;
            $report->expense_personal = 0;
            $report->total_expense = 0;
            $report->expense_notes = null;
            $report->net_cash_income = $report->total_omset;
            $report->save();
        }

        return redirect()->route('daily-expenses.index')
            ->with('success', "Catatan belanja harian tanggal {$dateFormatted} berhasil dihapus.");
    }
}
