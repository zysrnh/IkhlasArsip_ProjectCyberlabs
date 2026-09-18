<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Summary Transaksi & Keuangan Cabang</title>
    <style>
        @page {
            margin: 12mm 12mm 12mm 12mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 9px;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0B192C;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .title {
            font-size: 15px;
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
        
        /* Filter Information Meta Bar */
        .filter-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 10px;
            padding: 6px 10px;
            font-size: 8.5px;
        }
        .filter-label {
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
        }
        .filter-val {
            color: #0f172a;
            font-weight: bold;
        }

        /* 5 Metric Summary Cards */
        .stat-grid {
            width: 100%;
            margin-bottom: 12px;
            border-spacing: 6px 0;
            margin-left: -6px;
            margin-right: -6px;
        }
        .stat-box {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .stat-title {
            font-size: 7.5px;
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

        /* Main Data Table */
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
            font-size: 8px;
            padding: 6px 4px;
            border: 1px solid #0B192C;
            text-align: center;
        }
        .data-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
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
            padding: 6px 4px;
            font-size: 9px;
            font-weight: bold;
        }

        /* Signature Section */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 30%;
            text-align: center;
            font-size: 8.5px;
        }
        .sig-line {
            margin-top: 45px;
            border-top: 1px solid #0f172a;
            font-weight: bold;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- Header Perusahaan & Laporan -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="title">Summary Transaksi & Keuangan Cabang</div>
                <div class="subtitle">Sistem Manajemen Arsip Penjualan, Belanja Operasional, & Setoran Kas — Ikhlas Solusi</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-size: 8px; color: #64748b;">Waktu Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
                <div style="font-size: 8.5px; font-weight: bold; color: #0B192C;">Dicetak Oleh: {{ auth()->user()->name }}</div>
            </td>
        </tr>
    </table>

    <!-- Parameter Filter Aktif -->
    <table class="filter-table">
        <tr>
            <td style="width: 25%;">
                <div class="filter-label">Cabang:</div>
                <div class="filter-val">{{ $branchLabel ?? 'Semua Cabang' }}</div>
            </td>
            <td style="width: 25%;">
                <div class="filter-label">Periode Bulan:</div>
                <div class="filter-val">
                    @if($request->filled('month'))
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $request->month)->translatedFormat('F Y') }}
                    @else
                        Semua Periode
                    @endif
                </div>
            </td>
            <td style="width: 28%;">
                <div class="filter-label">Rentang Tanggal:</div>
                <div class="filter-val">
                    @if($request->filled('date_from') && $request->filled('date_to'))
                        {{ \Carbon\Carbon::parse($request->date_from)->translatedFormat('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($request->date_to)->translatedFormat('d/m/Y') }}
                    @elseif($request->filled('date_from'))
                        Mulai {{ \Carbon\Carbon::parse($request->date_from)->translatedFormat('d/m/Y') }}
                    @elseif($request->filled('date_to'))
                        Sampai {{ \Carbon\Carbon::parse($request->date_to)->translatedFormat('d/m/Y') }}
                    @else
                        Keseluruhan
                    @endif
                </div>
            </td>
            <td style="width: 22%; text-align: right;">
                <div class="filter-label">Total Rekap:</div>
                <div class="filter-val">{{ number_format($stats['total_records'] ?? count($reports)) }} Hari Laporan</div>
            </td>
        </tr>
    </table>

    <!-- 5 Ringkasan Indikator Keuangan -->
    <table class="stat-grid">
        <tr>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">1. Pendapatan Cash (Laci)</div>
                <div class="stat-val" style="color: #0f172a;">Rp {{ number_format($stats['total_cash_income'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">2. QRIS / Transfer Bank</div>
                <div class="stat-val" style="color: #0f766e;">Rp {{ number_format($stats['total_qris_income'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">3. Online Food</div>
                <div class="stat-val" style="color: #475569;">Rp {{ number_format($stats['total_online_income'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%;">
                <div class="stat-title">4. Total Pengeluaran Belanja</div>
                <div class="stat-val" style="color: #b91c1c;">Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}</div>
            </td>
            <td class="stat-box" style="width: 20%; background-color: #f0fdf4; border-color: #86efac;">
                <div class="stat-title" style="color: #166534;">5. Sisa Kas Setoran Bersih</div>
                <div class="stat-val" style="color: #15803d;">Rp {{ number_format($stats['total_net_cash_income'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rekapitulasi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 14%;">Cabang</th>
                <th style="width: 12%;">Cash (Laci)</th>
                <th style="width: 12%;">QRIS / Bank</th>
                <th style="width: 11%;">Online Food</th>
                <th style="width: 12%;">Total Omset</th>
                <th style="width: 12%;">Total Belanja</th>
                <th style="width: 13%;">Sisa Kas Setoran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $r)
                @php
                    $netCash = $r->cash_income - ($r->total_expense ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center font-bold">{{ $r->report_date->translatedFormat('d M Y') }}</td>
                    <td class="font-bold">{{ $r->branch->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($r->cash_income, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #0f766e;">Rp {{ number_format($r->qris_income, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #475569;">Rp {{ number_format($r->online_food_income, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #047857;">Rp {{ number_format($r->total_omset, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b91c1c;">Rp {{ number_format($r->total_expense ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #0A97B0;">Rp {{ number_format($netCash, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data summary transaksi yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <td colspan="3" class="text-right" style="padding-right: 8px; text-transform: uppercase;">GRAND TOTAL:</td>
                <td class="text-right">Rp {{ number_format($stats['total_cash_income'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #5eead4;">Rp {{ number_format($stats['total_qris_income'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #cbd5e1;">Rp {{ number_format($stats['total_online_income'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #86efac;">Rp {{ number_format($stats['total_omset'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #fca5a5;">Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #67e8f9;">Rp {{ number_format($stats['total_net_cash_income'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan / Otorisasi -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div>Dibuat Oleh,</div>
                <div class="sig-line">{{ auth()->user()->name }}<br><span style="font-size: 7.5px; font-weight: normal; color: #64748b;">( {{ ucfirst(auth()->user()->role) }} )</span></div>
            </td>
            <td style="width: 40%;"></td>
            <td class="sig-box">
                <div>Mengetahui / Disetujui,</div>
                <div class="sig-line">Pimpinan / Finance<br><span style="font-size: 7.5px; font-weight: normal; color: #64748b;">( Management Pusat )</span></div>
            </td>
        </tr>
    </table>

</body>
</html>
