<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Masakan & Omzet Dapur Harian</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8px;
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
            border-spacing: 5px 0;
            margin-left: -5px;
            margin-right: -5px;
        }
        .stat-box {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 6px;
            text-align: center;
        }
        .stat-title {
            font-size: 6.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .stat-val {
            font-size: 10px;
            font-weight: 900;
            color: #0B192C;
        }
        .stat-val-teal {
            font-size: 10px;
            font-weight: 900;
            color: #007A78;
        }
        .stat-val-rose {
            font-size: 10px;
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
            font-size: 7px;
            padding: 5px 3px;
            border: 1px solid #0B192C;
            text-align: center;
        }
        .data-table td {
            padding: 4px 3px;
            border: 1px solid #cbd5e1;
            font-size: 7.5px;
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
            padding: 5px 3px;
            font-size: 8px;
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
                <div class="title">Laporan Rekapitulasi Dapur & Omzet Harian</div>
                <div class="subtitle">Pencatatan Porsi Masak, Sisa Lauk, Omzet Kasir, dan Belanja Operasional Dapur</div>
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

    <!-- KPI Summary Cards -->
    <table class="stat-table">
        <tr>
            <td class="stat-box" style="width: 16.6%;">
                <div class="stat-title">TOTAL LAPORAN</div>
                <div class="stat-val">{{ number_format($stats['total_reports']) }} Hari</div>
            </td>
            <td class="stat-box" style="width: 16.6%;">
                <div class="stat-title">NILAI MASAKAN</div>
                <div class="stat-val">Rp {{ number_format($stats['grand_total_sales'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 16.6%;">
                <div class="stat-title">TOTAL OMZET KASIR</div>
                <div class="stat-val-teal">Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 16.6%;">
                <div class="stat-title">BELANJA HARIAN</div>
                <div class="stat-val-rose">Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 16.6%;">
                <div class="stat-title">NET CASH DINIKMATI</div>
                <div class="stat-val-teal">Rp {{ number_format($stats['total_net_cash'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 17%;">
                <div class="stat-title">SISA / TERBUANG</div>
                <div class="stat-val">{{ $stats['total_remaining_sellable'] }} / {{ $stats['total_wasted_food'] }} pcs</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 12%;">Cabang</th>
                <th style="width: 10%;">Nilai Masakan</th>
                <th style="width: 8%;">Kas Tunai</th>
                <th style="width: 8%;">QRIS</th>
                <th style="width: 8%;">Online Food</th>
                <th style="width: 10%;">Total Omzet</th>
                <th style="width: 9%;">Belanja Harian</th>
                <th style="width: 9%;">Net Cash</th>
                <th style="width: 8%;">Selisih Kas</th>
                <th style="width: 7%;">PJ Dapur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $report->report_date ? $report->report_date->format('d/m/Y') : '-' }}</td>
                    <td class="font-bold">{{ $report->branch->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($report->grand_total_sales, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($report->cash_income, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($report->qris_income, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($report->online_food_income, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #007A78;">{{ number_format($report->total_omset, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #e11d48;">{{ number_format($report->total_expense, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($report->net_cash_income, 0, ',', '.') }}</td>
                    <td class="text-right {{ $report->difference_amount < 0 ? 'font-bold' : '' }}" style="{{ $report->difference_amount < 0 ? 'color: #e11d48;' : '' }}">
                        {{ number_format($report->difference_amount, 0, ',', '.') }}
                    </td>
                    <td>{{ $report->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data laporan dapur pada filter periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($reports->count() > 0)
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="3" class="text-center">TOTAL REKAPITULASI</td>
                <td class="text-right">{{ number_format($stats['grand_total_sales'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($reports->sum('cash_income'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($reports->sum('qris_income'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($reports->sum('online_food_income'), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stats['total_net_cash'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($stats['total_difference'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div>Penanggung Jawab Dapur:</div>
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
