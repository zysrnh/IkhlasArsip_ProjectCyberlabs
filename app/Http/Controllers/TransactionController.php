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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $lastTrx = Transaction::withTrashed()->latest('id')->first();
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
            $lastTrx = Transaction::withTrashed()->latest('id')->first();
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
     * Hapus Transaksi (Soft Delete)
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

        return redirect()->route('transactions.index')->with('success', 'Transaksi ' . $code . ' berhasil dipindahkan ke tempat sampah.');
    }

    /**
     * Hapus Massal Transaksi (Bulk Soft-Delete)
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->route('transactions.index')->with('error', 'Tidak ada transaksi yang dipilih untuk dihapus.');
        }

        $query = Transaction::whereIn('id', $ids);

        // Jika admin cabang, batasi hanya transaksi di cabangnya
        if ($user->isAdminCabang()) {
            $query->where('branch_id', $user->branch_id);
        }

        $count = $query->count();
        $query->delete(); // Soft delete

        return redirect()->route('transactions.index')->with('success', "{$count} transaksi berhasil dipindahkan ke tempat sampah.");
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
     * Export Laporan Excel (.xlsx) Resmi & Rapi Sesuai Filter
     */
    public function exportExcel(Request $request): StreamedResponse
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

        $branchTitle = $selectedBranch ? $selectedBranch->name : 'Semua Cabang';
        $fileName = 'Laporan_Transaksi_' . ($selectedBranch ? str_replace(' ', '_', $selectedBranch->name) : 'Semua_Cabang') . '_' . date('Ymd_His') . '.xlsx';

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions, $branchTitle, $totalAmount, $totalQty, $user, $request) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Transaksi');

            // 1. Header KOP Laporan
            $sheet->setCellValue('A1', 'IKHLAS SOLUSI — SALES & ARCHIVING MANAGEMENT');
            $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('0B192C');

            $sheet->setCellValue('A2', 'LAPORAN DATA RESUME TRANSAKSI');
            $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB('0A97B0');

            // 2. Metadata Info
            $periodeText = ($request->filled('date_from') || $request->filled('date_to')) 
                ? (($request->date_from ?: 'Awal') . ' s/d ' . ($request->date_to ?: 'Sekarang'))
                : 'Semua Periode';
            
            $sheet->setCellValue('A4', 'Cabang');
            $sheet->setCellValue('B4', ': ' . $branchTitle);
            $sheet->setCellValue('D4', 'Dicetak Oleh');
            $sheet->setCellValue('E4', ': ' . $user->name);

            $sheet->setCellValue('A5', 'Periode');
            $sheet->setCellValue('B5', ': ' . $periodeText);
            $sheet->setCellValue('D5', 'Waktu Cetak');
            $sheet->setCellValue('E5', ': ' . now()->translatedFormat('d F Y, H:i'));

            $sheet->getStyle('A4:A5')->getFont()->setBold(true)->getColor()->setRGB('475569');
            $sheet->getStyle('D4:D5')->getFont()->setBold(true)->getColor()->setRGB('475569');

            // 3. Table Headers
            $columns = ['ID', 'Tanggal', 'Cabang', 'Jenis', 'Deskripsi', 'Customer', 'Qty', 'Jumlah'];
            $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

            for ($i = 0; $i < count($columns); $i++) {
                $cellRef = $colLetters[$i] . '7';
                $sheet->setCellValue($cellRef, $columns[$i]);
            }

            $sheet->getStyle('A7:H7')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A7:H7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0B192C');
            $sheet->getStyle('A7:H7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(7)->setRowHeight(28);

            // 4. Data Rows
            $currencyFormat = '"Rp"\ #,##0;[Red]"-Rp"\ #,##0;"Rp"\ 0';
            $startRow = 8;
            $currentRow = $startRow;

            foreach ($transactions as $trx) {
                $sheet->setCellValue("A{$currentRow}", $trx->code);
                $sheet->setCellValue("B{$currentRow}", $trx->transaction_date ? $trx->transaction_date->format('Y-m-d') : '-');
                $sheet->setCellValue("C{$currentRow}", $trx->branch->name ?? '-');
                $sheet->setCellValue("D{$currentRow}", $trx->type);
                $sheet->setCellValue("E{$currentRow}", $trx->notes ?: '-');
                $sheet->setCellValue("F{$currentRow}", $trx->customer_name);
                $sheet->setCellValue("G{$currentRow}", $trx->qty);
                $sheet->setCellValue("H{$currentRow}", $trx->amount);

                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode($currencyFormat);

                // Alternating row background
                if ($currentRow % 2 == 1) {
                    $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                }

                $currentRow++;
            }

            // 5. Total Row
            $sheet->setCellValue("A{$currentRow}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
            $sheet->setCellValue("G{$currentRow}", $totalQty);
            $sheet->setCellValue("H{$currentRow}", $totalAmount);

            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
            $sheet->getRowDimension($currentRow)->setRowHeight(24);

            // 6. Borders
            $sheet->getStyle("A7:H{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

            // 7. Auto-width Column Dimensions
            $sheet->getColumnDimension('A')->setWidth(14);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(22);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(22);

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Template Resmi Native XLSX (.xlsx) dengan Format Rupiah Otomatis
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_import_transaksi_ikhlas.xlsx"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Transaksi');

            // 1. Header Judul
            $sheet->setCellValue('A1', 'TEMPLATE RESUME TRANSAKSI — IKHLAS SOLUSI');
            $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('0B192C');

            $sheet->setCellValue('A2', 'Silakan isi data transaksi mulai baris ke-12. Jangan mengubah susunan nama kolom pada baris ke-11.');
            $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

            // 2. Petunjuk Pengisian di Atas (Baris 4 - 9)
            $sheet->setCellValue('A4', 'PETUNJUK PENGISIAN IMPORT TRANSAKSI:');
            $sheet->mergeCells('A4:G4');
            $sheet->getStyle('A4')->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('92400E');
            $sheet->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');

            $guideLines = [
                '1. Tanggal: Gunakan format standar YYYY-MM-DD (Contoh: 2026-09-11).',
                '2. Cabang: Diisi nama cabang resmi (Contoh: Jakarta Pusat, Bandung, Surabaya).',
                '3. Jenis Transaksi: Pilih salah satu dari: [Penjualan Tunai, Penjualan Kredit, Retur Penjualan, Transfer Cabang].',
                '4. Customer: Diisi nama customer / pembeli / cabang tujuan transfer.',
                '5. Qty: Jumlah kuantitas unit barang (Angka bulat).',
                '6. Jumlah: Cukup ketik angkanya saja (misal: 16700000), Excel akan otomatis memformat menjadi Rp 16.700.000. Untuk Retur Penjualan boleh diberi tanda minus (-).',
            ];

            for ($g = 0; $g < count($guideLines); $g++) {
                $rowNum = 5 + $g;
                $sheet->setCellValue("A{$rowNum}", $guideLines[$g]);
                $sheet->mergeCells("A{$rowNum}:G{$rowNum}");
                $sheet->getStyle("A{$rowNum}")->getFont()->setSize(9)->getColor()->setRGB('78350F');
                $sheet->getStyle("A{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFBEB');
            }

            // Border untuk box petunjuk
            $sheet->getStyle('A4:G10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('FCD34D');

            // 3. Table Header Kolom (Baris 11)
            $columns = ['Tanggal', 'Cabang', 'Jenis', 'Deskripsi', 'Customer', 'Qty', 'Jumlah'];
            $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

            for ($i = 0; $i < count($columns); $i++) {
                $cellRef = $colLetters[$i] . '11';
                $sheet->setCellValue($cellRef, $columns[$i]);
            }

            $sheet->getStyle('A11:G11')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A11:G11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0B192C');
            $sheet->getStyle('A11:G11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(11)->setRowHeight(28);

            // 4. Sample Data Rows (Baris 12 - 15)
            $sampleData = [
                ['2026-09-11', 'Jakarta Pusat', 'Penjualan Tunai', 'Penjualan Produk Grosir A', 'CV Bumi Pertiwi', 15, 16700000],
                ['2026-09-11', 'Bandung', 'Penjualan Kredit', 'Penjualan Invoice Tempo 30 Hari', 'PT Makmur Jaya', 20, 25000000],
                ['2026-09-11', 'Bandung', 'Retur Penjualan', 'Retur Barang Cacat Produksi', 'CV Bumi Pertiwi', 5, -3900000],
                ['2026-09-11', 'Surabaya', 'Transfer Cabang', 'Transfer Stok Barang Antar Cabang', 'Cabang Bandung', 10, 12500000],
            ];

            $currencyFormat = '"Rp"\ #,##0;[Red]"-Rp"\ #,##0;"Rp"\ 0';

            foreach ($sampleData as $idx => $row) {
                $rowIdx = 12 + $idx;
                $sheet->setCellValue("A{$rowIdx}", $row[0]);
                $sheet->setCellValue("B{$rowIdx}", $row[1]);
                $sheet->setCellValue("C{$rowIdx}", $row[2]);
                $sheet->setCellValue("D{$rowIdx}", $row[3]);
                $sheet->setCellValue("E{$rowIdx}", $row[4]);
                $sheet->setCellValue("F{$rowIdx}", $row[5]);
                $sheet->setCellValue("G{$rowIdx}", $row[6]);

                $sheet->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G{$rowIdx}")->getNumberFormat()->setFormatCode($currencyFormat);
            }

            // Set Format Otomatis Rp untuk 1000 baris ke bawah pada kolom G
            $sheet->getStyle('G12:G1000')->getNumberFormat()->setFormatCode($currencyFormat);
            $sheet->getStyle('A12:A1000')->getNumberFormat()->setFormatCode('@');
            $sheet->getStyle('F12:F1000')->getNumberFormat()->setFormatCode('#,##0');

            // Borders untuk tabel sample
            $sheet->getStyle('A11:G15')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

            // Auto-width kolom
            $sheet->getColumnDimension('A')->setWidth(16);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(22);
            $sheet->getColumnDimension('D')->setWidth(35);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(24);

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Data Transaksi dari Berkas Excel (.xlsx, .xls binary/html, .csv) Menggunakan PhpSpreadsheet Engine
     */
    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ], [
            'file.required' => 'Silakan pilih berkas Excel/CSV untuk diimpor.',
        ]);

        $file = $request->file('file');
        $user = auth()->user();
        $importedCount = 0;
        $realPath = $file->getRealPath();

        try {
            // Muat file menggunakan IOFactory (Mendukung .xlsx, .xls binary BIFF8, HTML .xls, CSV, dll.)
            $spreadsheet = IOFactory::load($realPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rawRows = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return redirect()->route('transactions.index')->with('error', 'Gagal membaca berkas Excel: ' . $e->getMessage());
        }

        // Ambil semua cabang untuk mapping nama -> ID
        $branchesMap = Branch::all()->keyBy(function ($item) {
            return strtolower(trim($item->name));
        });

        // Ambil ID transaksi terakhir
        $lastTrx = Transaction::withTrashed()->latest('id')->first();
        $nextNumber = $lastTrx ? ($lastTrx->id + 1) : 1;

        $isHeaderPassed = false;

        foreach ($rawRows as $cells) {
            if (empty($cells) || count($cells) < 4) {
                continue;
            }

            // Normalisasi array
            $col0 = trim((string)($cells[0] ?? ''));
            $col1 = trim((string)($cells[1] ?? ''));
            $col2 = trim((string)($cells[2] ?? ''));
            $col3 = trim((string)($cells[3] ?? ''));
            $col4 = trim((string)($cells[4] ?? ''));
            $col5 = trim((string)($cells[5] ?? ''));
            $col6 = trim((string)($cells[6] ?? ''));

            // Deteksi Header: Tanggal & Cabang
            if (stripos($col0, 'Tanggal') !== false && stripos($col1, 'Cabang') !== false) {
                $isHeaderPassed = true;
                continue;
            }

            // Abaikan baris sebelum header (judul atau box petunjuk)
            if (!$isHeaderPassed || stripos($col0, 'TEMPLATE') !== false || stripos($col0, 'PETUNJUK') !== false) {
                continue;
            }

            // Jika baris kosong
            if (empty($col0) || empty($col4)) {
                continue;
            }

            // 1. Parsing Tanggal
            $dateRaw = $col0;
            if (is_numeric($dateRaw) && intval($dateRaw) > 30000) {
                $unixTimestamp = ($dateRaw - 25569) * 86400;
                $parsedDate = gmdate('Y-m-d', $unixTimestamp);
            } else {
                try {
                    $parsedDate = date('Y-m-d', strtotime($dateRaw));
                } catch (\Exception $e) {
                    $parsedDate = date('Y-m-d');
                }
            }

            // 2. Tentukan Cabang
            if ($user->isAdminCabang()) {
                $branchId = $user->branch_id;
            } else {
                $matchedBranch = $branchesMap->get(strtolower($col1));
                $branchId = $matchedBranch ? $matchedBranch->id : (Branch::first()->id ?? 1);
            }

            // 3. Normalisasi Jenis Transaksi
            $validTypes = ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'];
            $matchedType = 'Penjualan Tunai';
            foreach ($validTypes as $vt) {
                if (stripos($col2, $vt) !== false) {
                    $matchedType = $vt;
                    break;
                }
            }

            // 4. QTY & Notes & Customer
            $qtyRaw = intval(preg_replace('/[^0-9]/', '', $col5));
            $qty = max(1, $qtyRaw);
            $notes = $col3;
            $customer = $col4;

            // 5. Normalisasi Nominal Jumlah (mendukung format "Rp 16.700.000", "(Rp 3,900,000)", "-Rp 3.900.000")
            $isNegative = str_contains($col6, '-') || (str_starts_with($col6, '(') && str_ends_with($col6, ')'));
            $cleanAmountStr = str_ireplace(['Rp', ' ', '.', ',', '(', ')'], '', $col6);
            $amountVal = floatval($cleanAmountStr);

            if ($isNegative || $matchedType === 'Retur Penjualan') {
                $amount = -abs($amountVal);
            } else {
                $amount = abs($amountVal);
            }

            $code = 'TRX-' . str_pad($nextNumber++, 3, '0', STR_PAD_LEFT);

            Transaction::create([
                'code' => $code,
                'branch_id' => $branchId,
                'user_id' => $user->id,
                'transaction_date' => $parsedDate,
                'type' => $matchedType,
                'customer_name' => $customer,
                'qty' => $qty,
                'amount' => $amount,
                'notes' => $notes,
            ]);

            $importedCount++;
        }

        if ($importedCount > 0) {
            return redirect()->route('transactions.index')->with('success', "Berhasil mengimpor {$importedCount} baris data transaksi ke dalam sistem.");
        }

        return redirect()->route('transactions.index')->with('success', 'File template Excel berhasil diterima dan diverifikasi.');
    }
}
