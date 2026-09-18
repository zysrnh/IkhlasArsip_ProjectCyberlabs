<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Summary Transaksi & Keuangan Cabang</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10px;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0B192C;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #0B192C;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9px;
            color: #64748b;
        }
        .filter-badge {
            background-color: #f1f5f9;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #334155;
            display: inline-block;
            margin-bottom: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .data-table th {
            background-color: #0B192C;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 6px 4px;
            border: 1px solid #0B192C;
            text-align: center;
        }
        .data-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .tfoot-total {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .tfoot-total td {
            border: 1px solid #94a3b8;
            padding: 6px 4px;
            font-size: 9px;
        }
        .stat-grid {
            width: 100%;
            margin-bottom: 12px;
        }
        .stat-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            text-align: center;
        }
        .stat-title {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .stat-val {
            font-size: 11px;
            font-weight: bold;
            color: #0B192C;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                <div class="title">Summary Transaksi & Keuangan Cabang</div>
                <div class="subtitle">Sistem Manajemen Penjualan & Belanja Operasional Ikhlas Solusi</div>
            </td>
            <td style="text-align: right;">
                <div style="font-size: 9px; color: #64748b;">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
                <div style="font-size: 9px; font-weight: bold; color: #0B192C;">Oleh: {{ auth()->user()->name }}</div>
            </td>
        </tr>
    </table>

    <!-- Quick Stat Summary Row -->
    <table class="stat-grid" style="border-spacing: 6px 0; margin-left: -6px; margin-right: -6px;">
        <tr>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">Total Cash (Laci)</div>
                <div class="stat-val">Rp {{ number_format($stats['total_cash_income'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">Total QRIS / Transfer</div>
                <div class="stat-val">Rp {{ number_format($stats['total_qris_income'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">Total Online Food</div>
                <div class="stat-val">Rp {{ number_format($stats['total_online_income'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">Total Pengeluaran Belanja</div>
                <div class="stat-val" style="color: #b91c1c;">Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">Sisa Setoran Bersih</div>
                <div class="stat-val" style="color: #047857;">Rp {{ number_format($stats['total_net_cash_income'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Table of Records -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 14%;">Cabang</th>
                <th style="width: 12%;">Pendapatan Cash</th>
                <th style="width: 12%;">Pendapatan QRIS</th>
                <th style="width: 11%;">Online Food</th>
                <th style="width: 12%;">Total Omset</th>
                <th style="width: 12%;">Total Belanja</th>
                <th style="width: 13%;">Sisa Kas Setoran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $r)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $r->report_date->translatedFormat('d M Y') }}</td>
                    <td class="font-bold">{{ $r->branch->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($r->cash_income, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($r->qris_income, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($r->online_food_income, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #047857;">Rp {{ number_format($r->total_omset, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b91c1c;">Rp {{ number_format($r->total_expense ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #0A97B0;">Rp {{ number_format($r->cash_income - ($r->total_expense ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data transaksi atau laporan dapur pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="3" class="text-right" style="padding-right: 8px;">GRAND TOTAL:</td>
                <td class="text-right">Rp {{ number_format($stats['total_cash_income'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['total_qris_income'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['total_online_income'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #047857;">Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #b91c1c;">Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #0A97B0;">Rp {{ number_format($stats['total_net_cash_income'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
