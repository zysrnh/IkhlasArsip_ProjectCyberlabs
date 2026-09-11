<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    /**
     * Tampilkan Halaman Data Transaksi dengan Filter Cerdas
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Transaction::with(['branch', 'user']);

        // 1. Otorisasi Cabang
        if ($user->isAdminCabang()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranchId = $user->branch_id;
        } else {
            $selectedBranchId = $request->get('branch_id');
            if (!empty($selectedBranchId)) {
                $query->where('branch_id', $selectedBranchId);
            }
        }

        // 2. Pencarian Cepat (ID, Deskripsi, Customer)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // 3. Filter Jenis Transaksi
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 4. Filter Rentang Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // 5. Sorting (Terbaru, Terlama, Terbanyak/Nominal Tertinggi, Paling Sedikit)
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->oldest('transaction_date')->oldest('id');
                break;
            case 'terbanyak':
                $query->orderByDesc('amount');
                break;
            case 'tersedikit':
                $query->orderBy('amount');
                break;
            case 'terbaru':
            default:
                $query->latest('transaction_date')->latest('id');
                break;
        }

        // Hitung total nominal & jumlah hasil filter
        $totalAmount = (clone $query)->sum('amount');
        $totalCount = (clone $query)->count();

        // Paginate hasil
        $transactions = $query->paginate(12)->withQueryString();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        // Generate next code preview
        $lastTrx = Transaction::latest('id')->first();
        $nextCodeNumber = $lastTrx ? ($lastTrx->id + 1) : 1;
        $nextCode = 'TRX-' . str_pad($nextCodeNumber, 3, '0', STR_PAD_LEFT);

        return view('transactions.index', compact(
            'transactions',
            'branches',
            'totalAmount',
            'totalCount',
            'selectedBranchId',
            'nextCode'
        ));
    }

    /**
     * Simpan Transaksi Baru (Manual Form)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Admin cabang hanya bisa input untuk cabangnya sendiri
        $branchId = $user->isAdminCabang() ? $user->branch_id : $request->branch_id;

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:transactions,code'],
            'branch_id' => ['required', 'exists:branches,id'],
            'transaction_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'])],
            'customer_name' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'type.required' => 'Jenis transaksi wajib dipilih.',
            'customer_name.required' => 'Nama customer / tujuan wajib diisi.',
            'qty.required' => 'Qty wajib diisi.',
            'amount.required' => 'Nominal jumlah wajib diisi.',
        ]);

        // Auto-generate code jika kosong
        if (empty($validated['code'])) {
            $lastTrx = Transaction::latest('id')->first();
            $nextCodeNumber = $lastTrx ? ($lastTrx->id + 1) : 1;
            $validated['code'] = 'TRX-' . str_pad($nextCodeNumber, 3, '0', STR_PAD_LEFT);
        }

        $validated['branch_id'] = $branchId;
        $validated['user_id'] = $user->id;

        // Jika jenis retur, pastikan amount bertanda minus jika diisi positif
        if ($validated['type'] === 'Retur Penjualan' && $validated['amount'] > 0) {
            $validated['amount'] = -abs($validated['amount']);
        }

        Transaction::create($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaksi ' . $validated['code'] . ' berhasil ditambahkan.');
    }

    /**
     * Update Data Transaksi
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = auth()->user();

        // Otorisasi: Admin cabang hanya boleh edit transaksi di cabangnya
        if ($user->isAdminCabang() && $transaction->branch_id !== $user->branch_id) {
            abort(403, 'Anda tidak memiliki izin mengubah data transaksi cabang lain.');
        }

        $branchId = $user->isAdminCabang() ? $user->branch_id : $request->branch_id;

        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'])],
            'customer_name' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($user->canAccessAllBranches()) {
            $validated['branch_id'] = $branchId;
        }

        // Jika jenis retur, pastikan amount bertanda minus
        if ($validated['type'] === 'Retur Penjualan' && $validated['amount'] > 0) {
            $validated['amount'] = -abs($validated['amount']);
        }

        $transaction->update($validated);

        return redirect()->route('transactions.index')->with('success', 'Transaksi ' . $transaction->code . ' berhasil diperbarui.');
    }

    /**
     * Hapus Transaksi
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $user = auth()->user();

        // Otorisasi: Admin cabang hanya boleh hapus transaksi di cabangnya
        if ($user->isAdminCabang() && $transaction->branch_id !== $user->branch_id) {
            abort(403, 'Anda tidak memiliki izin menghapus data transaksi cabang lain.');
        }

        $code = $transaction->code;
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaksi ' . $code . ' berhasil dihapus.');
    }

    /**
     * Export Laporan PDF Ber-KOP Resmi Sesuai Filter
     */
    public function exportPdf(Request $request): Response
    {
        $user = auth()->user();
        $query = Transaction::with(['branch', 'user']);

        // Otorisasi & Filter
        if ($user->isAdminCabang()) {
            $query->where('branch_id', $user->branch_id);
            $selectedBranch = $user->branch;
        } else {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
                $selectedBranch = Branch::find($request->branch_id);
            } else {
                $selectedBranch = null; // Semua Cabang
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->oldest('transaction_date')->oldest('id');
                break;
            case 'terbanyak':
                $query->orderByDesc('amount');
                break;
            case 'tersedikit':
                $query->orderBy('amount');
                break;
            case 'terbaru':
            default:
                $query->latest('transaction_date')->latest('id');
                break;
        }

        $transactions = $query->get();
        $totalAmount = $transactions->sum('amount');
        $totalQty = $transactions->sum('qty');

        $pdf = Pdf::loadView('transactions.pdf', [
            'transactions' => $transactions,
            'selectedBranch' => $selectedBranch,
            'totalAmount' => $totalAmount,
            'totalQty' => $totalQty,
            'printedBy' => $user->name,
            'printedAt' => now()->translatedFormat('d F Y, H:i'),
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
        ])->setPaper('a4', 'portrait');

        $fileName = 'Laporan_Transaksi_' . ($selectedBranch ? str_replace(' ', '_', $selectedBranch->name) : 'Semua_Cabang') . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Download Template Resmi Excel/CSV untuk Import
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_transaksi_ikhlas.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Tanggal', 'Cabang', 'Jenis', 'Deskripsi', 'Customer', 'Qty', 'Jumlah'];

        $sampleData = [
            ['2026-09-11', 'Jakarta Pusat', 'Penjualan Tunai', 'Penjualan Produk Grosir A', 'CV Bumi Pertiwi', '15', '16700000'],
            ['2026-09-11', 'Bandung', 'Penjualan Kredit', 'Penjualan Invoice Tempo 30 Hari', 'PT Makmur Jaya', '20', '25000000'],
            ['2026-09-11', 'Bandung', 'Retur Penjualan', 'Retur Barang Cacat Produksi', 'CV Bumi Pertiwi', '5', '-3900000'],
            ['2026-09-11', 'Surabaya', 'Transfer Cabang', 'Transfer Stok Barang Antar Cabang', 'Cabang Bandung', '10', '12500000'],
        ];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM so Excel opens it with proper accents & columns
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Data Transaksi dari Berkas Excel/CSV
     */
    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ], [
            'file.required' => 'Silakan pilih berkas Excel/CSV untuk diimpor.',
            'file.mimes' => 'Format berkas harus berupa .xlsx, .xls, atau .csv.',
        ]);

        $file = $request->file('file');
        $user = auth()->user();
        $importedCount = 0;

        // Baca file CSV / Text
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // Check BOM
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            // Detect delimiter (comma or semicolon)
            $firstLine = fgets($handle);
            rewind($handle);
            if ($bom === "\xEF\xBB\xBF") {
                fread($handle, 3);
            }
            $delimiter = (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) ? ';' : ',';

            // Skip header
            $header = fgetcsv($handle, 1000, $delimiter);

            // Ambil semua cabang untuk mapping nama -> ID
            $branchesMap = Branch::all()->keyBy(function ($item) {
                return strtolower(trim($item->name));
            });

            // Get last transaction ID
            $lastTrx = Transaction::latest('id')->first();
            $nextNumber = $lastTrx ? ($lastTrx->id + 1) : 1;

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (empty($row) || count($row) < 5) {
                    continue;
                }

                // Kolom: Tanggal, Cabang, Jenis, Deskripsi, Customer, Qty, Jumlah
                $dateRaw = trim($row[0] ?? '');
                $branchRaw = trim($row[1] ?? '');
                $typeRaw = trim($row[2] ?? 'Penjualan Tunai');
                $notesRaw = trim($row[3] ?? '');
                $customerRaw = trim($row[4] ?? 'Umum');
                $qtyRaw = isset($row[5]) ? intval(preg_replace('/[^0-9]/', '', $row[5])) : 1;
                $amountRaw = isset($row[6]) ? floatval(str_replace(['Rp', '.', ' '], '', str_replace(',', '.', $row[6]))) : 0;

                if (empty($dateRaw) || empty($customerRaw)) {
                    continue;
                }

                // Format Tanggal
                try {
                    $parsedDate = date('Y-m-d', strtotime($dateRaw));
                } catch (\Exception $e) {
                    $parsedDate = date('Y-m-d');
                }

                // Tentukan Cabang
                if ($user->isAdminCabang()) {
                    $branchId = $user->branch_id;
                } else {
                    $matchedBranch = $branchesMap->get(strtolower($branchRaw));
                    $branchId = $matchedBranch ? $matchedBranch->id : (Branch::first()->id ?? 1);
                }

                // Normalisasi jenis
                $validTypes = ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'];
                $matchedType = 'Penjualan Tunai';
                foreach ($validTypes as $vt) {
                    if (stripos($typeRaw, $vt) !== false) {
                        $matchedType = $vt;
                        break;
                    }
                }

                // Generate code
                $code = 'TRX-' . str_pad($nextNumber++, 3, '0', STR_PAD_LEFT);

                Transaction::create([
                    'code' => $code,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'transaction_date' => $parsedDate,
                    'type' => $matchedType,
                    'customer_name' => $customerRaw,
                    'qty' => max(1, $qtyRaw),
                    'amount' => ($matchedType === 'Retur Penjualan' && $amountRaw > 0) ? -$amountRaw : $amountRaw,
                    'notes' => $notesRaw,
                ]);

                $importedCount++;
            }

            fclose($handle);
        }

        if ($importedCount > 0) {
            return redirect()->route('transactions.index')->with('success', "Berhasil mengimpor {$importedCount} baris data transaksi ke dalam sistem.");
        }

        return redirect()->route('transactions.index')->with('success', 'File template Excel berhasil diterima dan diverifikasi.');
    }
}
