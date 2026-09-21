<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DailyKitchenReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    /**
     * Tampilkan Halaman Summary Transaksi & Keuangan Cabang (Sesuai Sheet SUMMARY Excel)
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $selectedBranchId = $request->get('branch_id');
        if (!$user->canAccessAllBranches()) {
            if ($user->isKepalaCabang() || $user->isViewer()) {
                $accessibleIds = $user->getAccessibleBranchIds();
                if ($selectedBranchId && !in_array($selectedBranchId, $accessibleIds)) {
                    $selectedBranchId = null;
                }
            } else {
                $selectedBranchId = $user->branch_id;
            }
        }
        $selectedMonth = $request->get('month');

        $query = $this->buildSummaryQuery($request);

        // Hitung Metrik Agregat Ringkasan (Statistik Dinamis Sesuai Filter)
        $statQuery = clone $query;
        $totalCash = (float) (clone $statQuery)->sum('cash_income');
        $totalExpense = (float) (clone $statQuery)->sum('total_expense');
        $stats = [
            'total_cash_income' => $totalCash,
            'total_qris_income' => (clone $statQuery)->sum('qris_income'),
            'total_online_income' => (clone $statQuery)->sum('online_food_income'),
            'total_omset' => (clone $statQuery)->sum('total_omset'),
            'total_sales' => (clone $statQuery)->sum('grand_total_sales'),
            'total_expense' => $totalExpense,
            'total_raw_expense' => (clone $statQuery)->sum('expense_raw_material'),
            'total_non_raw_expense' => (clone $statQuery)->sum('expense_non_raw_material'),
            'total_personal_expense' => (clone $statQuery)->sum('expense_personal'),
            'total_net_cash_income' => $totalCash - $totalExpense,
            'total_records' => (clone $statQuery)->count(),
        ];

        $summaryReports = $query->paginate(15)->withQueryString();
        $branches = $user->getAccessibleBranches();

        // Ambil Daftar Bulan yang Tersedia di Database untuk Dropdown Cepat
        $availableMonths = DailyKitchenReport::selectRaw("DATE_FORMAT(report_date, '%Y-%m') as ym")
            ->distinct()
            ->orderByDesc('ym')
            ->pluck('ym')
            ->map(function ($ym) {
                $carbon = Carbon::createFromFormat('Y-m', $ym);
                return [
                    'value' => $ym,
                    'label' => $carbon->translatedFormat('F Y')
                ];
            });

        return view('transactions.index', compact(
            'summaryReports',
            'branches',
            'selectedBranchId',
            'selectedMonth',
            'stats',
            'availableMonths'
        ));
    }

    /**
     * Export Excel Rekap Summary Sesuai Format Sheet SUMMARY Excel
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $reports = $this->buildSummaryQuery($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SUMMARY');

        // Header Title
        $sheet->setCellValue('A1', 'SUMMARY TRANSAKSI & KEUANGAN HARIAN CABANG');
        $sheet->setCellValue('A2', 'IKHLAS SOLUSI - SISTEM MANAJEMEN DAPUR & PENJUALAN');
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');

        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('666666');

        // Filter Info
        $sheet->setCellValue('A4', 'Filter Cabang: ' . ($request->filled('branch_id') ? (Branch::find($request->branch_id)?->name ?? 'Semua') : 'Semua Cabang'));
        $sheet->setCellValue('A5', 'Periode: ' . ($request->filled('month') ? Carbon::createFromFormat('Y-m', $request->month)->translatedFormat('F Y') : 'Semua Periode'));
        $sheet->getStyle('A4:A5')->getFont()->setSize(9)->setItalic(true);

        // Table Header
        $headers = [
            'A7' => 'No',
            'B7' => 'Tanggal',
            'C7' => 'Cabang',
            'D7' => 'Pendapatan Cash',
            'E7' => 'Pendapatan QRIS',
            'F7' => 'Online Food',
            'G7' => 'Total Omset',
            'H7' => 'Total Belanja',
            'I7' => 'Sisa Kas Setoran'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B192C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];
        $sheet->getStyle('A7:I7')->applyFromArray($headerStyle);
        $sheet->getRowDimension(7)->setRowHeight(24);

        $row = 8;
        $totalCash = 0;
        $totalQris = 0;
        $totalOnline = 0;
        $totalOmset = 0;
        $totalExpense = 0;
        $totalNetCash = 0;

        foreach ($reports as $index => $r) {
            $netCash = $r->cash_income - ($r->total_expense ?? 0);

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $r->report_date->translatedFormat('d/m/Y'));
            $sheet->setCellValue('C' . $row, $r->branch->name ?? '-');
            $sheet->setCellValue('D' . $row, $r->cash_income);
            $sheet->setCellValue('E' . $row, $r->qris_income);
            $sheet->setCellValue('F' . $row, $r->online_food_income);
            $sheet->setCellValue('G' . $row, $r->total_omset);
            $sheet->setCellValue('H' . $row, $r->total_expense ?? 0);
            $sheet->setCellValue('I' . $row, $netCash);

            $totalCash += $r->cash_income;
            $totalQris += $r->qris_income;
            $totalOnline += $r->online_food_income;
            $totalOmset += $r->total_omset;
            $totalExpense += ($r->total_expense ?? 0);
            $totalNetCash += $netCash;

            $sheet->getStyle('D' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $row . ':C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Grand Total Row
        $sheet->setCellValue('A' . $row, 'TOTAL:');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, $totalCash);
        $sheet->setCellValue('E' . $row, $totalQris);
        $sheet->setCellValue('F' . $row, $totalOnline);
        $sheet->setCellValue('G' . $row, $totalOmset);
        $sheet->setCellValue('H' . $row, $totalExpense);
        $sheet->setCellValue('I' . $row, $totalNetCash);

        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($totalStyle);
        $sheet->getStyle('D' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Summary_Transaksi_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export PDF Rekap Summary Transaksi
     */
    public function exportPdf(Request $request)
    {
        $reports = $this->buildSummaryQuery($request)->get();

        $totalCash = $reports->sum('cash_income');
        $totalExpense = $reports->sum('total_expense');

        $stats = [
            'total_cash_income' => $totalCash,
            'total_qris_income' => $reports->sum('qris_income'),
            'total_online_income' => $reports->sum('online_food_income'),
            'total_omset' => $reports->sum('total_omset'),
            'total_expense' => $totalExpense,
            'total_net_cash_income' => $totalCash - $totalExpense,
            'total_records' => $reports->count(),
        ];

        $user = auth()->user();
        $branchLabel = 'Semua Cabang';
        if ($request->filled('branch_id')) {
            $branchLabel = Branch::find($request->branch_id)?->name ?? 'Cabang ' . $request->branch_id;
        } elseif ($user->isKepalaCabang() || $user->isViewer()) {
            $branchLabel = 'Wilayah Cabang ' . $user->name;
        } elseif (!$user->canAccessAllBranches()) {
            $branchLabel = $user->branch->name ?? 'Cabang Anda';
        }

        $pdf = Pdf::loadView('transactions.pdf', compact('reports', 'stats', 'request', 'branchLabel'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Summary_Transaksi_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Helper Query Builder untuk Rekap Summary Transaksi
     */
    private function buildSummaryQuery(Request $request)
    {
        $user = auth()->user();
        $query = DailyKitchenReport::with(['branch', 'user']);

        // 1. Otorisasi Cabang
        if (!$user->canAccessAllBranches()) {
            if ($user->isKepalaCabang() || $user->isViewer()) {
                $accessibleBranchIds = $user->getAccessibleBranchIds();
                if ($request->filled('branch_id') && in_array($request->branch_id, $accessibleBranchIds)) {
                    $query->where('branch_id', $request->branch_id);
                } else {
                    $query->whereIn('branch_id', $accessibleBranchIds);
                }
            } else {
                $query->where('branch_id', $user->branch_id);
            }
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // 2. Filter Cepat Bulan (Format: YYYY-MM)
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('report_date', $year)->whereMonth('report_date', $month);
        }

        // 3. Filter Rentang Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->date_to);
        }

        // 4. Pencarian Cepat (Cabang, Catatan, Penginput)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('branch', function ($qb) use ($search) {
                    $qb->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('expense_notes', 'like', "%{$search}%");
            });
        }

        // 5. Sorting
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'terlama':
                $query->oldest('report_date')->oldest('id');
                break;
            case 'omset_terbesar':
                $query->orderByDesc('total_omset');
                break;
            case 'omset_terkecil':
                $query->orderBy('total_omset', 'asc');
                break;
            case 'cash_terbesar':
                $query->orderByDesc('cash_income');
                break;
            case 'cash_terkecil':
                $query->orderBy('cash_income', 'asc');
                break;
            case 'belanja_terbesar':
                $query->orderByDesc('total_expense');
                break;
            case 'belanja_terkecil':
                $query->orderBy('total_expense', 'asc');
                break;
            case 'sisa_terbesar':
                $query->orderByRaw('(cash_income - COALESCE(total_expense, 0)) DESC');
                break;
            case 'sisa_terkecil':
                $query->orderByRaw('(cash_income - COALESCE(total_expense, 0)) ASC');
                break;
            case 'terbaru':
            default:
                $query->latest('report_date')->latest('id');
                break;
        }

        return $query;
    }
}
