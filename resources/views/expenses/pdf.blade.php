<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Belanja Harian Cabang</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8.5px;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0B192C;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .title {
            font-size: 14px;
            font-weight: 900;
            color: #0B192C;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }
        
        .filter-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 8px;
            padding: 5px 8px;
            font-size: 8px;
        }
        .filter-label {
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
        }
        .filter-val {
            color: #0f172a;
            font-weight: bold;
        }

        .stat-table {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-left: -6px;
            margin-right: -6px;
        }
        .stat-box {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
        }
        .stat-title {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .stat-val {
            font-size: 11px;
            font-weight: 900;
            color: #0B192C;
        }
        .stat-val-rose {
            font-size: 11px;
            font-weight: 900;
            color: #e11d48;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .data-table th {
            background-color: #0B192C;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 5px 4px;
            border: 1px solid #0B192C;
            text-align: center;
        }
        .data-table td {
            padding: 4px 4px;
            border: 1px solid #cbd5e1;
            font-size: 8px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .tfoot-total {
            background-color: #0B192C;
            color: #ffffff;
            font-weight: bold;
        }
        .tfoot-total td {
            border: 1px solid #0B192C;
            padding: 5px 4px;
            font-size: 8.5px;
            font-weight: bold;
        }

        .signature-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 30%;
            text-align: center;
            font-size: 8px;
        }
        .sig-line {
            margin-top: 35px;
            border-top: 1px solid #0f172a;
            font-weight: bold;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="title">Laporan Rekapitulasi Belanja Harian Cabang</div>
                <div class="subtitle">Bahan Baku Pasar, Operasional Non-Bahan Baku, dan Kebutuhan Pribadi Cabang</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-weight: 900; font-size: 13px; color: #007A78;">IKHLAS SOLUSI</div>
                <div style="font-size: 7.5px; color: #64748b;">Dicetak: {{ date('d/m/Y H:i') }} WIB</div>
                <div style="font-size: 7.5px; color: #64748b;">Oleh: {{ $user->name }} ({{ $user->role_name }})</div>
            </td>
        </tr>
    </table>

    <!-- Filter Meta Bar -->
    <table class="filter-table">
        <tr>
            <td style="width: 50%;">
                <span class="filter-label">Cabang:</span><br>
                <span class="filter-val">{{ $branchLabel }}</span>
            </td>
            <td style="width: 50%;">
                <span class="filter-label">Rentang Periode Tanggal:</span><br>
                <span class="filter-val">{{ $dateRangeLabel }}</span>
            </td>
        </tr>
    </table>

    <!-- 4 KPI Cards -->
    <table class="stat-table">
        <tr>
            <td class="stat-box" style="width: 25%;">
                <div class="stat-title">BAHAN BAKU PASAR</div>
                <div class="stat-val">Rp {{ number_format($stats['total_raw'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 25%;">
                <div class="stat-title">NON-BAHAN BAKU</div>
                <div class="stat-val">Rp {{ number_format($stats['total_non_raw'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 25%;">
                <div class="stat-title">KEBUTUHAN PRIBADI</div>
                <div class="stat-val">Rp {{ number_format($stats['total_personal'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 25%;">
                <div class="stat-title">GRAND TOTAL BELANJA ({{ $stats['total_records'] }} HARI)</div>
                <div class="stat-val-rose">Rp {{ number_format($stats['grand_total_expense'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 14%;">Cabang</th>
                <th style="width: 12%;">Bahan Baku</th>
                <th style="width: 12%;">Non-Bahan Baku</th>
                <th style="width: 12%;">Pribadi</th>
                <th style="width: 13%;">Total Belanja</th>
                <th style="width: 13%;">Dicatat Oleh</th>
                <th style="width: 10%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $index => $expense)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $expense->report_date ? $expense->report_date->format('d/m/Y') : '-' }}</td>
                    <td class="font-bold">{{ $expense->branch->name ?? '-' }}</td>
                    <td class="text-right">{{ $expense->expense_raw_material > 0 ? number_format($expense->expense_raw_material, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $expense->expense_non_raw_material > 0 ? number_format($expense->expense_non_raw_material, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $expense->expense_personal > 0 ? number_format($expense->expense_personal, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold" style="color: #e11d48;">Rp {{ number_format($expense->total_expense, 0, ',', '.') }}</td>
                    <td>{{ $expense->user->name ?? '-' }}</td>
                    <td style="font-size: 7.5px; color: #475569;">{{ $expense->expense_notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data belanja harian pada filter periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($expenses->count() > 0)
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="3" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-right">Rp {{ number_format($stats['total_raw'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['total_non_raw'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['total_personal'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['grand_total_expense'], 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div>Dicatat Oleh:</div>
                <div class="sig-line">{{ $user->name }}<br><span style="font-size: 7.5px; font-weight: normal; color: #64748b;">{{ $user->role_name }}</span></div>
            </td>
            <td style="width: 40%;"></td>
            <td class="sig-box">
                <div>Mengetahui & Menyetujui:</div>
                <div class="sig-line">Kepala Cabang / Manajemen<br><span style="font-size: 7.5px; font-weight: normal; color: #64748b;">Ikhlas Solusi</span></div>
            </td>
        </tr>
    </table>

</body>
</html>
