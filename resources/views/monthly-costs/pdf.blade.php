<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Cost Cabang & Belanja Operasional</title>
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
        
        /* Filter Information Bar */
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

        /* 3 Summary Stat Cards */
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
        .stat-val-harian {
            font-size: 11px;
            font-weight: 900;
            color: #b45309;
        }
        .stat-val-grand {
            font-size: 11px;
            font-weight: 900;
            color: #007A78;
        }

        /* Data Table */
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

        /* Signature Section */
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

    <!-- Header Perusahaan & Laporan -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="title">Laporan Cost Cabang & Belanja Operasional</div>
                <div class="subtitle">Rekapitulasi Biaya Tetap Bulanan dan Belanja Harian Cabang</div>
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
            <td style="width: 33%;">
                <span class="filter-label">Cabang:</span><br>
                <span class="filter-val">{{ $branchLabel }}</span>
            </td>
            <td style="width: 33%;">
                <span class="filter-label">Periode Bulan:</span><br>
                <span class="filter-val">{{ $monthLabel }}</span>
            </td>
            <td style="width: 34%;">
                <span class="filter-label">Periode Tahun:</span><br>
                <span class="filter-val">{{ $yearLabel }}</span>
            </td>
        </tr>
    </table>

    <!-- 3 Summary Stat Cards -->
    <table class="stat-table">
        <tr>
            <td class="stat-box" style="width: 33.33%;">
                <div class="stat-title">TOTAL BIAYA BULANAN (FIXED)</div>
                <div class="stat-val">Rp {{ number_format($statsMonthlyTotal, 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 33.33%;">
                <div class="stat-title">TOTAL BELANJA HARIAN DAPUR</div>
                <div class="stat-val-harian">Rp {{ number_format($statsDailyTotal, 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 33.33%;">
                <div class="stat-title">GRAND TOTAL PENGELUARAN</div>
                <div class="stat-val-grand">Rp {{ number_format($statsGrandTotal, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rekap Cost Cabang -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 11%;">Cabang</th>
                <th style="width: 9%;">Periode</th>
                <th style="width: 7%;">Sewa</th>
                <th style="width: 6%;">Wifi</th>
                <th style="width: 6%;">Sampah</th>
                <th style="width: 7%;">Air/Listrik</th>
                <th style="width: 6%;">Netflix</th>
                <th style="width: 6%;">IPL</th>
                <th style="width: 8%;">Gaji</th>
                <th style="width: 6%;">Lainnya</th>
                <th style="width: 9%;">Total Bulanan</th>
                <th style="width: 8%;">Belanja Harian</th>
                <th style="width: 9%;">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumRent = 0; $sumWifi = 0; $sumTrash = 0; $sumUtils = 0;
                $sumNetflix = 0; $sumIpl = 0; $sumSalary = 0; $sumOther = 0;
                $sumMonthly = 0; $sumDaily = 0; $sumGrand = 0;
            @endphp

            @forelse($costs as $index => $cost)
                @php
                    $sumRent += $cost->rent_cost;
                    $sumWifi += $cost->wifi_cost;
                    $sumTrash += $cost->trash_cost;
                    $sumUtils += $cost->utilities_cost;
                    $sumNetflix += $cost->netflix_cost;
                    $sumIpl += $cost->ipl_cost;
                    $sumSalary += $cost->salary_cost;
                    $sumOther += $cost->other_cost;
                    $sumMonthly += $cost->total_monthly_cost;
                    $sumDaily += $cost->daily_expense_total;
                    $sumGrand += $cost->grand_total_cost;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $cost->branch->name ?? '-' }}</td>
                    <td class="text-center">{{ $cost->period_name }}</td>
                    <td class="text-right">{{ $cost->rent_cost > 0 ? number_format($cost->rent_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $cost->wifi_cost > 0 ? number_format($cost->wifi_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $cost->trash_cost > 0 ? number_format($cost->trash_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $cost->utilities_cost > 0 ? number_format($cost->utilities_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $cost->netflix_cost > 0 ? number_format($cost->netflix_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $cost->ipl_cost > 0 ? number_format($cost->ipl_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $cost->salary_cost > 0 ? number_format($cost->salary_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $cost->other_cost > 0 ? number_format($cost->other_cost, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold">{{ number_format($cost->total_monthly_cost, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b45309;">{{ number_format($cost->daily_expense_total, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #007A78;">{{ number_format($cost->grand_total_cost, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data cost cabang pada filter periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($costs->count() > 0)
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="3" class="text-center">TOTAL REKAP</td>
                <td class="text-right">{{ number_format($sumRent, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumWifi, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumTrash, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumUtils, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumNetflix, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumIpl, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumSalary, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumOther, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumMonthly, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumDaily, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sumGrand, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Signature Section -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div>Dibuat / Dicatat Oleh:</div>
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
