<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrashController extends Controller
{
    /**
     * Tampilkan Halaman Sampah (Hanya Super Admin)
     */
    public function index(Request $request): View
    {
        $query = Transaction::onlyTrashed()->with(['branch', 'user']);

        // Filter Cabang
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $totalTrashedAmount = (clone $query)->sum('amount');
        $totalTrashedCount = (clone $query)->count();

        $transactions = $query->latest('deleted_at')->paginate(12)->withQueryString();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        $selectedBranchId = $request->get('branch_id');

        return view('trash.index', compact(
            'transactions',
            'branches',
            'totalTrashedAmount',
            'totalTrashedCount',
            'selectedBranchId'
        ));
    }

    /**
     * Pulihkan / Restore Transaksi Satuan
     */
    public function restore(int $id): RedirectResponse
    {
        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $code = $transaction->code;
        $transaction->restore();

        return redirect()->route('trash.index')->with('success', "Transaksi {$code} berhasil dipulihkan.");
    }

    /**
     * Hapus Permanen Transaksi Satuan
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $transaction = Transaction::onlyTrashed()->findOrFail($id);
        $code = $transaction->code;
        $transaction->forceDelete();

        return redirect()->route('trash.index')->with('success', "Transaksi {$code} berhasil dihapus permanen.");
    }

    /**
     * Pulihkan Massal (Bulk Restore)
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->route('trash.index')->with('error', 'Tidak ada transaksi yang dipilih untuk dipulihkan.');
        }

        $count = Transaction::onlyTrashed()->whereIn('id', $ids)->count();
        Transaction::onlyTrashed()->whereIn('id', $ids)->restore();

        return redirect()->route('trash.index')->with('success', "{$count} transaksi berhasil dipulihkan.");
    }

    /**
     * Hapus Permanen Massal (Bulk Force Delete)
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->route('trash.index')->with('error', 'Tidak ada transaksi yang dipilih untuk dihapus permanen.');
        }

        $count = Transaction::onlyTrashed()->whereIn('id', $ids)->count();
        Transaction::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return redirect()->route('trash.index')->with('success', "{$count} transaksi berhasil dihapus secara permanen.");
    }

    /**
     * Kosongkan Seluruh Tempat Sampah
     */
    public function emptyTrash(): RedirectResponse
    {
        $count = Transaction::onlyTrashed()->count();
        Transaction::onlyTrashed()->forceDelete();

        return redirect()->route('trash.index')->with('success', "Tempat sampah berhasil dikosongkan ({$count} transaksi dihapus permanen).");
    }
}
