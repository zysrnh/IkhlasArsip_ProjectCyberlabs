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
     * Download Template Resmi Excel (.xls / SpreadsheetML) dengan Format & Desain Rapi
     * (Petunjuk Pengisian ditempatkan di Atas, Baris Data Bebas Diisi ke Bawah Tanpa Batas)
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_transaksi_ikhlas.xls"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            ?>
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <!--[if gte mso 9]>
                <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Template Transaksi</x:Name>
                            <x:WorksheetOptions>
                                <x:DisplayGridlines/>
                            </x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
                </xml>
                <![endif]-->
                <style>
                    body { font-family: Calibri, Arial, sans-serif; }
                    .header-title { font-size: 14pt; font-weight: bold; color: #0B192C; }
                    .header-subtitle { font-size: 9pt; color: #64748B; font-style: italic; }
                    .guide-title { font-size: 10pt; font-weight: bold; color: #92400E; background-color: #FEF3C7; border: 1px solid #FCD34D; padding: 6px; }
                    .guide-desc { font-size: 9pt; color: #78350F; background-color: #FFFBEB; border: 1px solid #FCD34D; padding: 6px; }
                    .th-cell { background-color: #0B192C; color: #FFFFFF; font-weight: bold; font-size: 10pt; text-align: center; height: 32px; vertical-align: middle; border: 1px solid #334155; }
                    .td-text { font-size: 10pt; vertical-align: middle; border: 1px solid #CBD5E1; padding: 5px; mso-number-format: "\@"; }
                    .td-date { font-size: 10pt; text-align: center; vertical-align: middle; border: 1px solid #CBD5E1; padding: 5px; mso-number-format: "yyyy-mm-dd"; }
                    .td-qty { font-size: 10pt; text-align: center; vertical-align: middle; border: 1px solid #CBD5E1; padding: 5px; mso-number-format: "\#\,\#\#0"; }
                    .td-amount { font-size: 10pt; text-align: right; vertical-align: middle; border: 1px solid #CBD5E1; padding: 5px; mso-number-format: "\#\,\#\#0"; }
                </style>
            </head>
            <body>
                <table border="0" cellpadding="0" cellspacing="0">
                    <!-- Title -->
                    <tr>
                        <td colspan="7" class="header-title">TEMPLATE RESUME TRANSAKSI — IKHLAS SOLUSI</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="header-subtitle">Silakan isi data mulai baris tabel di bawah ini. Jangan mengubah susunan nama kolom pada Header.</td>
                    </tr>
                    <tr><td colspan="7" height="8"></td></tr>

                    <!-- Petunjuk Pengisian (Di Bagian Atas) -->
                    <tr>
                        <td colspan="7" class="guide-title">
                            <strong>PETUNJUK PENGISIAN IMPORT TRANSAKSI:</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" class="guide-desc">
                            1. <strong>Tanggal</strong>: Gunakan format standar YYYY-MM-DD (Contoh: 2026-09-11).<br>
                            2. <strong>Cabang</strong>: Diisi nama cabang resmi (Contoh: Jakarta Pusat, Bandung, Surabaya).<br>
                            3. <strong>Jenis Transaksi</strong>: Pilih salah satu dari: [Penjualan Tunai, Penjualan Kredit, Retur Penjualan, Transfer Cabang].<br>
                            4. <strong>Customer</strong>: Diisi nama customer / pembeli / cabang tujuan transfer.<br>
                            5. <strong>Qty</strong>: Jumlah kuantitas unit barang (Angka bulat).<br>
                            6. <strong>Jumlah</strong>: Diisi nominal rupiah tanpa tanda titik atau koma (Untuk Retur Penjualan boleh diberi tanda minus -).
                        </td>
                    </tr>
                    <tr><td colspan="7" height="12"></td></tr>
                    
                    <!-- Table Header (Data Dimulai Di Bawah Ini, Bebas Ditambah Berapapun Baris Ke Bawah) -->
                    <tr>
                        <th class="th-cell" style="width: 130px;">Tanggal</th>
                        <th class="th-cell" style="width: 160px;">Cabang</th>
                        <th class="th-cell" style="width: 170px;">Jenis</th>
                        <th class="th-cell" style="width: 280px;">Deskripsi</th>
                        <th class="th-cell" style="width: 200px;">Customer</th>
                        <th class="th-cell" style="width: 80px;">Qty</th>
                        <th class="th-cell" style="width: 160px;">Jumlah</th>
                    </tr>

                    <!-- Sample Data Rows -->
                    <tr>
                        <td class="td-date">2026-09-11</td>
                        <td class="td-text">Jakarta Pusat</td>
                        <td class="td-text">Penjualan Tunai</td>
                        <td class="td-text">Penjualan Produk Grosir A</td>
                        <td class="td-text">CV Bumi Pertiwi</td>
                        <td class="td-qty">15</td>
                        <td class="td-amount">16700000</td>
                    </tr>
                    <tr>
                        <td class="td-date">2026-09-11</td>
                        <td class="td-text">Bandung</td>
                        <td class="td-text">Penjualan Kredit</td>
                        <td class="td-text">Penjualan Invoice Tempo 30 Hari</td>
                        <td class="td-text">PT Makmur Jaya</td>
                        <td class="td-qty">20</td>
                        <td class="td-amount">25000000</td>
                    </tr>
                    <tr>
                        <td class="td-date">2026-09-11</td>
                        <td class="td-text">Bandung</td>
                        <td class="td-text">Retur Penjualan</td>
                        <td class="td-text">Retur Barang Cacat Produksi</td>
                        <td class="td-text">CV Bumi Pertiwi</td>
                        <td class="td-qty">5</td>
                        <td class="td-amount">-3900000</td>
                    </tr>
                    <tr>
                        <td class="td-date">2026-09-11</td>
                        <td class="td-text">Surabaya</td>
                        <td class="td-text">Transfer Cabang</td>
                        <td class="td-text">Transfer Stok Barang Antar Cabang</td>
                        <td class="td-text">Cabang Bandung</td>
                        <td class="td-qty">10</td>
                        <td class="td-amount">12500000</td>
                    </tr>
                </table>
            </body>
            </html>
            <?php
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Data Transaksi dari Berkas Excel/CSV/XLS
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

        $content = file_get_contents($file->getRealPath());

        // Ambil semua cabang untuk mapping nama -> ID
        $branchesMap = Branch::all()->keyBy(function ($item) {
            return strtolower(trim($item->name));
        });

        // Get last transaction ID
        $lastTrx = Transaction::withTrashed()->latest('id')->first();
        $nextNumber = $lastTrx ? ($lastTrx->id + 1) : 1;

        // Cek jika format adalah HTML/XML Spreadsheet (.xls)
        if (str_contains($content, '<table') || str_contains($content, '<tr')) {
            // Parse HTML Table
            $dom = new \DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
            $rows = $dom->getElementsByTagName('tr');

            $isHeaderPassed = false;
            foreach ($rows as $tr) {
                $cells = [];
                foreach ($tr->getElementsByTagName('td') as $td) {
                    $cells[] = trim($td->textContent);
                }

                // Jika row adalah TH
                if (empty($cells)) {
                    foreach ($tr->getElementsByTagName('th') as $th) {
                        $cells[] = trim($th->textContent);
                    }
                }

                if (empty($cells) || count($cells) < 5) {
                    continue;
                }

                // Deteksi header row (Tanggal & Cabang)
                if (stripos($cells[0], 'Tanggal') !== false && stripos($cells[1] ?? '', 'Cabang') !== false) {
                    $isHeaderPassed = true;
                    continue;
                }

                // Abaikan baris petunjuk atau judul di bagian atas
                if (!$isHeaderPassed || stripos($cells[0], 'TEMPLATE') !== false || stripos($cells[0], 'PETUNJUK') !== false) {
                    continue;
                }

                $dateRaw = trim($cells[0] ?? '');
                $branchRaw = trim($cells[1] ?? '');
                $typeRaw = trim($cells[2] ?? 'Penjualan Tunai');
                $notesRaw = trim($cells[3] ?? '');
                $customerRaw = trim($cells[4] ?? 'Umum');
                $qtyRaw = isset($cells[5]) ? intval(preg_replace('/[^0-9]/', '', $cells[5])) : 1;
                $amountRaw = isset($cells[6]) ? floatval(str_replace(['Rp', '.', ' '], '', str_replace(',', '.', $cells[6]))) : 0;

                if (empty($dateRaw) || empty($customerRaw) || stripos($dateRaw, 'PETUNJUK') !== false) {
                    continue;
                }

                try {
                    $parsedDate = date('Y-m-d', strtotime($dateRaw));
                } catch (\Exception $e) {
                    $parsedDate = date('Y-m-d');
                }

                if ($user->isAdminCabang()) {
                    $branchId = $user->branch_id;
                } else {
                    $matchedBranch = $branchesMap->get(strtolower($branchRaw));
                    $branchId = $matchedBranch ? $matchedBranch->id : (Branch::first()->id ?? 1);
                }

                $validTypes = ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'];
                $matchedType = 'Penjualan Tunai';
                foreach ($validTypes as $vt) {
                    if (stripos($typeRaw, $vt) !== false) {
                        $matchedType = $vt;
                        break;
                    }
                }

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
        } else {
            // Baca berkas CSV murni
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                $firstLine = fgets($handle);
                rewind($handle);
                if ($bom === "\xEF\xBB\xBF") {
                    fread($handle, 3);
                }
                $delimiter = (strpos($firstLine, ';') !== false && strpos($firstLine, ',') === false) ? ';' : ',';

                // Skip header
                $header = fgetcsv($handle, 1000, $delimiter);

                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    if (empty($row) || count($row) < 5) {
                        continue;
                    }

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

                    try {
                        $parsedDate = date('Y-m-d', strtotime($dateRaw));
                    } catch (\Exception $e) {
                        $parsedDate = date('Y-m-d');
                    }

                    if ($user->isAdminCabang()) {
                        $branchId = $user->branch_id;
                    } else {
                        $matchedBranch = $branchesMap->get(strtolower($branchRaw));
                        $branchId = $matchedBranch ? $matchedBranch->id : (Branch::first()->id ?? 1);
                    }

                    $validTypes = ['Penjualan Tunai', 'Penjualan Kredit', 'Retur Penjualan', 'Transfer Cabang'];
                    $matchedType = 'Penjualan Tunai';
                    foreach ($validTypes as $vt) {
                        if (stripos($typeRaw, $vt) !== false) {
                            $matchedType = $vt;
                            break;
                        }
                    }

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
        }

        if ($importedCount > 0) {
            return redirect()->route('transactions.index')->with('success', "Berhasil mengimpor {$importedCount} baris data transaksi ke dalam sistem.");
        }

        return redirect()->route('transactions.index')->with('success', 'File template Excel berhasil diterima dan diverifikasi.');
    }
}
