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
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

        // Ambil semua jenis transaksi yang ada di database + default 4 jenis utama
        $defaultTypes = ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'];
        $dbTypes = Transaction::distinct()->pluck('type')->filter()->toArray();
        $allTransactionTypes = array_values(array_unique(array_merge($defaultTypes, $dbTypes)));

        // Generate next code preview yang dijamin unik
        $nextCode = $this->generateUniqueTransactionCode();

        return view('transactions.index', compact(
            'transactions',
            'branches',
            'totalAmount',
            'totalCount',
            'selectedBranchId',
            'nextCode',
            'allTransactionTypes'
        ));
    }

    /**
     * Generator Kode Transaksi Unik Anti-Duplikasi
     */
    private function generateUniqueTransactionCode(?int &$runningNumber = null): string
    {
        if ($runningNumber === null) {
            $maxCodeNum = 0;
            $allCodes = Transaction::withTrashed()->pluck('code');
            foreach ($allCodes as $c) {
                if (preg_match('/TRX-(\d+)/i', (string) $c, $matches)) {
                    $num = intval($matches[1]);
                    if ($num > $maxCodeNum) {
                        $maxCodeNum = $num;
                    }
                }
            }
            $runningNumber = $maxCodeNum + 1;
        }

        do {
            $code = 'TRX-' . str_pad($runningNumber, 3, '0', STR_PAD_LEFT);
            $exists = Transaction::withTrashed()->where('code', $code)->exists();
            $runningNumber++;
        } while ($exists);

        return $code;
    }

    /**
     * Otorisasi Hak Akses Tulis (Viewer hanya diizinkan membaca data & unduh laporan)
     */
    private function authorizeWriteAccess(): void
    {
        if (auth()->check() && auth()->user()->isViewer()) {
            abort(403, 'Akses Ditolak: Akun dengan role Viewer hanya memiliki izin untuk memantau data dan mengunduh laporan.');
        }
    }

    /**
     * Simpan Transaksi Baru (Manual Form)
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeWriteAccess();

        $user = auth()->user();

        // Admin cabang hanya bisa input untuk cabangnya sendiri
        $branchId = $user->isAdminCabang() ? $user->branch_id : $request->branch_id;

        // Bersihkan pemisah ribuan titik sebelum validasi
        if ($request->filled('amount')) {
            $cleanedAmount = preg_replace('/[^0-9\-]/', '', (string) $request->amount);
            $request->merge(['amount' => $cleanedAmount]);
        }

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:transactions,code'],
            'branch_id' => ['required', 'exists:branches,id'],
            'transaction_date' => ['required', 'date'],
            'type' => ['required', 'string', 'max:100'],
            'customer_name' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'type.required' => 'Jenis transaksi wajib diisi/dipilih.',
            'customer_name.required' => 'Nama customer / tujuan wajib diisi.',
            'qty.required' => 'Qty wajib diisi.',
            'amount.required' => 'Nominal jumlah wajib diisi.',
        ]);

        // Auto-generate code jika kosong
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateUniqueTransactionCode();
        }

        $validated['branch_id'] = $branchId;
        $validated['user_id'] = $user->id;

        // Jika jenis retur, pastikan amount bertanda minus jika diisi positif
        if (stripos($validated['type'], 'retur') !== false && $validated['amount'] > 0) {
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
        $this->authorizeWriteAccess();

        $user = auth()->user();

        // Otorisasi: Admin cabang hanya boleh edit transaksi di cabangnya
        if ($user->isAdminCabang() && $transaction->branch_id !== $user->branch_id) {
            abort(403, 'Anda tidak memiliki izin mengubah data transaksi cabang lain.');
        }

        $branchId = $user->isAdminCabang() ? $user->branch_id : $request->branch_id;

        // Bersihkan pemisah ribuan titik sebelum validasi
        if ($request->filled('amount')) {
            $cleanedAmount = preg_replace('/[^0-9\-]/', '', (string) $request->amount);
            $request->merge(['amount' => $cleanedAmount]);
        }

        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'type' => ['required', 'string', 'max:100'],
            'customer_name' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($user->canAccessAllBranches()) {
            $validated['branch_id'] = $branchId;
        }

        // Jika jenis retur, pastikan amount bertanda minus
        if (stripos($validated['type'], 'retur') !== false && $validated['amount'] > 0) {
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
        $this->authorizeWriteAccess();

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
        $this->authorizeWriteAccess();

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

        // Resolve logo dengan multi-fallback path untuk shared hosting
        $logoBase64 = null;
        $logoCandidates = [
            public_path('images/logo.png'),
            base_path('public/images/logo.png'),
            base_path('public_html/images/logo.png'),
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/images/logo.png' : null,
            base_path('../public_html/images/logo.png'),
        ];

        foreach ($logoCandidates as $candidate) {
            if ($candidate && file_exists($candidate)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($candidate));
                break;
            }
        }

        $pdf = Pdf::loadView('transactions.pdf', [
            'transactions' => $transactions,
            'selectedBranch' => $selectedBranch,
            'totalAmount' => $totalAmount,
            'totalQty' => $totalQty,
            'printedBy' => $user->name,
            'printedAt' => now()->translatedFormat('d F Y, H:i'),
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'portrait');

        $fileName = 'Laporan_Transaksi_' . ($selectedBranch ? str_replace(' ', '_', $selectedBranch->name) : 'Semua_Cabang') . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Export Laporan Excel (.xlsx) Resmi & Rapi Sesuai Filter
     */
     public function exportExcel(Request $request): BinaryFileResponse
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

         $spreadsheet = new Spreadsheet();
         $sheet = $spreadsheet->getActiveSheet();
         $sheet->setTitle('Laporan Transaksi');
         $sheet->setShowGridLines(true);

         // 1. Spacing & KOP Header Banner
         $sheet->getRowDimension(1)->setRowHeight(12);

         // Judul Utama (Baris 2 & 3)
         $sheet->setCellValue('A2', 'IKHLAS SOLUSI — SALES & ARCHIVING MANAGEMENT');
         $sheet->mergeCells('A2:I2');
         $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('FFFFFF');
         $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
         $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
         $sheet->getRowDimension(2)->setRowHeight(30);

         $sheet->setCellValue('A3', 'LAPORAN RESUME DATA TRANSAKSI RESMI');
         $sheet->mergeCells('A3:I3');
         $sheet->getStyle('A3')->getFont()->setSize(9.5)->setBold(true)->getColor()->setRGB('FFFFFF');
         $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0A97B0');
         $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
         $sheet->getRowDimension(3)->setRowHeight(20);

         $sheet->getRowDimension(4)->setRowHeight(10);

         // 2. Metadata Info Card (Baris 5 - 7)
         $periodeText = ($request->filled('date_from') || $request->filled('date_to')) 
             ? (($request->date_from ?: 'Awal') . ' s/d ' . ($request->date_to ?: 'Sekarang'))
             : 'Semua Periode';

         // Baris 5
         $sheet->setCellValue('A5', 'Unit Cabang');
         $sheet->mergeCells('A5:B5');
         $sheet->setCellValue('C5', ': ' . $branchTitle);
         $sheet->mergeCells('C5:E5');

         $sheet->setCellValue('F5', 'Dicetak Oleh');
         $sheet->mergeCells('F5:G5');
         $sheet->setCellValue('H5', ': ' . $user->name);
         $sheet->mergeCells('H5:I5');

         // Baris 6
         $sheet->setCellValue('A6', 'Periode');
         $sheet->mergeCells('A6:B6');
         $sheet->setCellValue('C6', ': ' . $periodeText);
         $sheet->mergeCells('C6:E6');

         $sheet->setCellValue('F6', 'Waktu Cetak');
         $sheet->mergeCells('F6:G6');
         $sheet->setCellValue('H6', ': ' . now()->translatedFormat('d F Y, H:i') . ' WIB');
         $sheet->mergeCells('H6:I6');

         // Baris 7
         $sheet->setCellValue('A7', 'Kode Dokumen');
         $sheet->mergeCells('A7:B7');
         $sheet->setCellValue('C7', ': DOC-TRX-' . date('Ymd'));
         $sheet->mergeCells('C7:E7');

         $sheet->setCellValue('F7', 'Status Dokumen');
         $sheet->mergeCells('F7:G7');
         $sheet->setCellValue('H7', ': Terverifikasi Sistem');
         $sheet->mergeCells('H7:I7');

         $sheet->getStyle('A5:I7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
         $sheet->getStyle('A5:B7')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('475569');
         $sheet->getStyle('F5:G7')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('475569');
         $sheet->getStyle('C5:E7')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('0F172A');
         $sheet->getStyle('H5:I7')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('0F172A');
         $sheet->getStyle('H7')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('0A97B0');
         $sheet->getStyle('A5:I7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
         $sheet->getStyle('A5:I7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
         $sheet->getRowDimension(5)->setRowHeight(19);
         $sheet->getRowDimension(6)->setRowHeight(19);
         $sheet->getRowDimension(7)->setRowHeight(19);

         $sheet->getRowDimension(8)->setRowHeight(10);

         // 3. KPI Summary Cards (Baris 9 - 10)
         // Card 1: Total Transaksi
         $sheet->setCellValue('A9', 'TOTAL TRANSAKSI');
         $sheet->mergeCells('A9:C9');
         $sheet->setCellValue('A10', count($transactions) . ' Entri Data');
         $sheet->mergeCells('A10:C10');

         // Card 2: Total Volume Penjualan
         $sheet->setCellValue('D9', 'TOTAL VOLUME PENJUALAN');
         $sheet->mergeCells('D9:F9');
         $sheet->setCellValue('D10', number_format($totalQty, 0, ',', '.') . ' Unit Barang');
         $sheet->mergeCells('D10:F10');

         // Card 3: Total Akumulasi Nominal
         $sheet->setCellValue('G9', 'TOTAL AKUMULASI NOMINAL');
         $sheet->mergeCells('G9:I9');
         $sheet->setCellValue('G10', 'Rp ' . number_format($totalAmount, 0, ',', '.'));
         $sheet->mergeCells('G10:I10');

         $sheet->getStyle('A9:I10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
         $sheet->getStyle('A9:F9')->getFont()->setSize(7.5)->setBold(true)->getColor()->setRGB('64748B');
         $sheet->getStyle('G9:I9')->getFont()->setSize(7.5)->setBold(true)->getColor()->setRGB('0A97B0');
         $sheet->getStyle('A10:F10')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB('0F172A');
         $sheet->getStyle('G10:I10')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('0A97B0');

         $sheet->getStyle('A9:I10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
         $sheet->getStyle('A9:C10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
         $sheet->getStyle('D9:F10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
         $sheet->getStyle('G9:I10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
         $sheet->getStyle('G9:I10')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('0A97B0');

         $sheet->getRowDimension(9)->setRowHeight(16);
         $sheet->getRowDimension(10)->setRowHeight(24);

         $sheet->getRowDimension(11)->setRowHeight(12);

         // 4. Table Column Headers (Baris 12)
         $columns = ['NO', 'ID', 'TANGGAL', 'CABANG', 'JENIS', 'CUSTOMER', 'DESKRIPSI', 'QTY', 'NOMINAL (RP)'];
         $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

         for ($i = 0; $i < count($columns); $i++) {
             $cellRef = $colLetters[$i] . '12';
             $sheet->setCellValue($cellRef, $columns[$i]);
         }

         $sheet->getStyle('A12:I12')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
         $sheet->getStyle('A12:I12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
         $sheet->getStyle('A12:I12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
         $sheet->getRowDimension(12)->setRowHeight(26);

         // 5. Data Rows (Baris 13+)
         $currencyFormat = '"Rp"\ #,##0;[Red]"-Rp"\ #,##0;"Rp"\ 0';
         $startRow = 13;
         $currentRow = $startRow;
         $no = 1;

         foreach ($transactions as $trx) {
             $sheet->setCellValue("A{$currentRow}", $no);
             $sheet->setCellValue("B{$currentRow}", $trx->code);
             $sheet->setCellValue("C{$currentRow}", $trx->transaction_date ? $trx->transaction_date->format('Y-m-d') : '-');
             $sheet->setCellValue("D{$currentRow}", $trx->branch->name ?? '-');
             $sheet->setCellValue("E{$currentRow}", $trx->type);
             $sheet->setCellValue("F{$currentRow}", $trx->customer_name);
             $sheet->setCellValue("G{$currentRow}", $trx->notes ?: '-');
             $sheet->setCellValue("H{$currentRow}", $trx->qty);
             $sheet->setCellValue("I{$currentRow}", $trx->amount);

             $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
             $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
             $sheet->getStyle("B{$currentRow}")->getFont()->setBold(true)->setSize(8.5);
             $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
             $sheet->getStyle("C{$currentRow}")->getFont()->setSize(8.5);
             $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
             $sheet->getStyle("D{$currentRow}")->getFont()->setSize(8.5);
             $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
             $sheet->getStyle("E{$currentRow}")->getFont()->setBold(true)->setSize(8.5);
             $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
             $sheet->getStyle("F{$currentRow}")->getFont()->setBold(true)->setSize(8.5);
             $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
             $sheet->getStyle("G{$currentRow}")->getFont()->setSize(8.5);
             $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
             $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true)->setSize(8.5);
             $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
             $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
             $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode($currencyFormat);
             $sheet->getStyle("I{$currentRow}")->getFont()->setBold(true)->setSize(8.5);

             $sheet->getRowDimension($currentRow)->setRowHeight(21);

             // Alternating row background
             if ($currentRow % 2 == 1) {
                 $sheet->getStyle("A{$currentRow}:I{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
             }

             $currentRow++;
             $no++;
         }

         // 6. Total Row
         $sheet->setCellValue("A{$currentRow}", 'TOTAL KESELURUHAN :');
         $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
         $sheet->setCellValue("H{$currentRow}", $totalQty);
         $sheet->setCellValue("I{$currentRow}", $totalAmount);

         $sheet->getStyle("A{$currentRow}:I{$currentRow}")->getFont()->setBold(true)->setSize(9.5);
         $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER)->setIndent(1);
         $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
         $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
         $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
         $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode($currencyFormat);
         $sheet->getStyle("A{$currentRow}:I{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
         $sheet->getRowDimension($currentRow)->setRowHeight(25);

         // Borders untuk tabel data
         $sheet->getStyle("A12:I{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
         $sheet->getStyle("A{$currentRow}:I{$currentRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setRGB('0F172A');
         $sheet->getStyle("A{$currentRow}:I{$currentRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK)->getColor()->setRGB('0F172A');

         // 7. Footer Catatan & Pengesahan
         $footerRow = $currentRow + 2;
         $sheet->setCellValue("A{$footerRow}", 'Catatan Sistem:');
         $sheet->getStyle("A{$footerRow}")->getFont()->setBold(true)->setSize(8)->getColor()->setRGB('64748B');
         $sheet->setCellValue("A" . ($footerRow + 1), 'Dokumen ini dicetak otomatis dari sistem arsip transaksi resmi Ikhlas Solusi. Seluruh nominal dan jumlah data telah tervalidasi.');
         $sheet->mergeCells("A" . ($footerRow + 1) . ":E" . ($footerRow + 2));
         $sheet->getStyle("A" . ($footerRow + 1))->getFont()->setSize(7.5)->getColor()->setRGB('94A3B8');
         $sheet->getStyle("A" . ($footerRow + 1))->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

         // Tanda Tangan Kanan
         $sheet->setCellValue("G{$footerRow}", 'Disahkan di Bandung, ' . now()->translatedFormat('d F Y'));
         $sheet->mergeCells("G{$footerRow}:I{$footerRow}");
         $sheet->getStyle("G{$footerRow}")->getFont()->setSize(8)->getColor()->setRGB('64748B');
         $sheet->getStyle("G{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

         $sheet->setCellValue("G" . ($footerRow + 1), ($selectedBranch ? 'Kepala Cabang ' . $selectedBranch->name : 'Penanggung Jawab Operasional'));
         $sheet->mergeCells("G" . ($footerRow + 1) . ":I" . ($footerRow + 1));
         $sheet->getStyle("G" . ($footerRow + 1))->getFont()->setBold(true)->setSize(8.5)->getColor()->setRGB('0F172A');
         $sheet->getStyle("G" . ($footerRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

         $sigRow = $footerRow + 4;
         $sheet->setCellValue("G{$sigRow}", $user->name);
         $sheet->mergeCells("G{$sigRow}:I{$sigRow}");
         $sheet->getStyle("G{$sigRow}")->getFont()->setBold(true)->setUnderline(true)->setSize(9)->getColor()->setRGB('0F172A');
         $sheet->getStyle("G{$sigRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

         $sheet->setCellValue("G" . ($sigRow + 1), 'Ikhlas Solusi Sales & Archiving Management');
         $sheet->mergeCells("G" . ($sigRow + 1) . ":I" . ($sigRow + 1));
         $sheet->getStyle("G" . ($sigRow + 1))->getFont()->setSize(7.5)->getColor()->setRGB('64748B');
         $sheet->getStyle("G" . ($sigRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

         // 8. Auto-width Column Dimensions yang Proporsional
         $sheet->getColumnDimension('A')->setWidth(6);
         $sheet->getColumnDimension('B')->setWidth(14);
         $sheet->getColumnDimension('C')->setWidth(14);
         $sheet->getColumnDimension('D')->setWidth(18);
         $sheet->getColumnDimension('E')->setWidth(18);
         $sheet->getColumnDimension('F')->setWidth(24);
         $sheet->getColumnDimension('G')->setWidth(30);
         $sheet->getColumnDimension('H')->setWidth(12);
         $sheet->getColumnDimension('I')->setWidth(22);

         $tempFile = tempnam(sys_get_temp_dir(), 'export_trx_');
         $writer = new Xlsx($spreadsheet);
         $writer->save($tempFile);

         $headers = [
             'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
             'Pragma' => 'no-cache',
             'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
             'Expires' => '0',
         ];

         return response()->download($tempFile, $fileName, $headers)->deleteFileAfterSend(true);
     }

     /**
      * Download Template Resmi Native XLSX (.xlsx) dengan Format Rupiah Otomatis
      */
     public function downloadTemplate(): BinaryFileResponse
     {
         $fileName = 'template_import_transaksi_ikhlas.xlsx';

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
             '3. Jenis Transaksi: Pilih dari dropdown atau ketik jenis kustom bebas (Contoh: Penjualan Tunai, BAA, Operasional, dsb.).',
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

         // Data Validation: Dropdown Saran untuk Kolom C (Jenis Transaksi) Baris 12 - 500
         $validationJenis = $sheet->getCell('C12')->getDataValidation();
         $validationJenis->setType(DataValidation::TYPE_LIST);
         $validationJenis->setErrorStyle(DataValidation::STYLE_INFORMATION);
         $validationJenis->setAllowBlank(true);
         $validationJenis->setShowInputMessage(true);
         $validationJenis->setShowErrorMessage(false);
         $validationJenis->setShowDropDown(true);
         $validationJenis->setPromptTitle('Pilih / Ketik Jenis Transaksi');
         $validationJenis->setPrompt('Pilih opsi atau ketik jenis transaksi baru langsung di kolom ini.');
         $validationJenis->setFormula1('"Penjualan Tunai,Penjualan Kredit,Retur Penjualan,Transfer Cabang"');

         // Dropdown List untuk Kolom B (Cabang)
         $activeBranchNames = Branch::where('status', 'active')->pluck('name')->implode(',');
         if (!empty($activeBranchNames)) {
             $validationCabang = $sheet->getCell('B12')->getDataValidation();
             $validationCabang->setType(DataValidation::TYPE_LIST);
             $validationCabang->setErrorStyle(DataValidation::STYLE_INFORMATION);
             $validationCabang->setShowDropDown(true);
             $validationCabang->setFormula1('"' . $activeBranchNames . '"');
         }

         for ($row = 12; $row <= 500; $row++) {
             $sheet->getCell("C{$row}")->setDataValidation(clone $validationJenis);
             if (!empty($activeBranchNames)) {
                 $sheet->getCell("B{$row}")->setDataValidation(clone $validationCabang);
             }
         }

         // Auto-width kolom
         $sheet->getColumnDimension('A')->setWidth(16);
         $sheet->getColumnDimension('B')->setWidth(20);
         $sheet->getColumnDimension('C')->setWidth(22);
         $sheet->getColumnDimension('D')->setWidth(35);
         $sheet->getColumnDimension('E')->setWidth(25);
         $sheet->getColumnDimension('F')->setWidth(12);
         $sheet->getColumnDimension('G')->setWidth(24);

         $tempFile = tempnam(sys_get_temp_dir(), 'tpl_trx_');
         $writer = new Xlsx($spreadsheet);
         $writer->save($tempFile);

         $headers = [
             'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
             'Pragma' => 'no-cache',
             'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
             'Expires' => '0',
         ];

         return response()->download($tempFile, $fileName, $headers)->deleteFileAfterSend(true);
     }

    /**
     * Import Data Transaksi dari Berkas Excel (.xlsx, .xls binary/html, .csv) Menggunakan PhpSpreadsheet Engine
     */
    public function importExcel(Request $request): RedirectResponse
    {
        $this->authorizeWriteAccess();

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

        // Inisialisasi running number awal dari angka tertinggi kode TRX
        $maxCodeNum = 0;
        $allCodes = Transaction::withTrashed()->pluck('code');
        foreach ($allCodes as $c) {
            if (preg_match('/TRX-(\d+)/i', (string) $c, $matches)) {
                $num = intval($matches[1]);
                if ($num > $maxCodeNum) {
                    $maxCodeNum = $num;
                }
            }
        }
        $runningNumber = $maxCodeNum + 1;

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

            // Deteksi Header Murni: Tanggal & Cabang (Bukan baris petunjuk)
            if (stripos($col0, 'Tanggal') !== false && stripos($col1, 'Cabang') !== false && !str_starts_with($col0, '1.')) {
                $isHeaderPassed = true;
                continue;
            }

            // Abaikan baris sebelum header (judul atau box petunjuk)
            if (!$isHeaderPassed || stripos($col0, 'TEMPLATE') !== false || stripos($col0, 'PETUNJUK') !== false || preg_match('/^\d+\.\s/', $col0)) {
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

            // 3. Jenis Transaksi (Pakai input asli dari berkas atau fallback ke Penjualan Tunai jika kosong)
            $matchedType = !empty($col2) ? $col2 : 'Penjualan Tunai';

            // 4. QTY & Notes & Customer
            $qtyRaw = intval(preg_replace('/[^0-9]/', '', $col5));
            $qty = max(1, $qtyRaw);
            $notes = $col3;
            $customer = $col4;

            // 5. Normalisasi Nominal Jumlah (mendukung format "Rp 16.700.000", "(Rp 3,900,000)", "-Rp 3.900.000")
            $isNegative = str_contains($col6, '-') || (str_starts_with($col6, '(') && str_ends_with($col6, ')'));
            $cleanAmountStr = str_ireplace(['Rp', ' ', '.', ',', '(', ')'], '', $col6);
            $amountVal = floatval($cleanAmountStr);

            if ($isNegative || stripos($matchedType, 'retur') !== false) {
                $amount = -abs($amountVal);
            } else {
                $amount = abs($amountVal);
            }

            $code = $this->generateUniqueTransactionCode($runningNumber);

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
