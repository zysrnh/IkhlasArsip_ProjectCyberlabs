<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Rincian Cost Cabang & Belanja Harian</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 9.5px;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0B192C;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .title {
            font-size: 15px;
            font-weight: 900;
            color: #0B192C;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        
        .meta-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 12px;
            padding: 8px 12px;
            font-size: 9px;
        }
        .meta-label {
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
        }
        .meta-val {
            color: #0f172a;
            font-weight: bold;
            font-size: 10px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 900;
            color: #0B192C;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #0B192C;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 6px 8px;
            border: 1px solid #0B192C;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .total-box {
            width: 100%;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 15px;
        }
        .grand-total-highlight {
            font-size: 14px;
            font-weight: 900;
            color: #007A78;
        }

        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 35%;
            text-align: center;
            font-size: 9px;
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

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 65%;">
                <div class="title">Rincian Cost Cabang & Belanja Operasional</div>
                <div class="subtitle">Laporan Resmi Pengeluaran Periode {{ $monthlyCost->period_name }}</div>
            </td>
            <td style="width: 35%; text-align: right;">
                <div style="font-weight: 900; font-size: 14px; color: #007A78;">IKHLAS SOLUSI</div>
                <div style="font-size: 8px; color: #64748b;">Dicetak: {{ date('d/m/Y H:i') }} WIB</div>
                <div style="font-size: 8px; color: #64748b;">Oleh: {{ $user->name }}</div>
            </td>
        </tr>
    </table>

    <!-- Meta Info -->
    <table class="meta-table">
        <tr>
            <td style="width: 35%;">
                <span class="meta-label">Cabang:</span><br>
                <span class="meta-val">{{ $monthlyCost->branch->name ?? '-' }}</span>
            </td>
            <td style="width: 35%;">
                <span class="meta-label">Periode:</span><br>
                <span class="meta-val">{{ $monthlyCost->period_name }}</span>
            </td>
            <td style="width: 30%;">
                <span class="meta-label">Dicatat Oleh:</span><br>
                <span class="meta-val">{{ $monthlyCost->user->name ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <!-- Section 1: Biaya Tetap Bulanan -->
    <div class="section-title">1. Rincian Biaya Tetap Bulanan (Fixed Operational)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">No</th>
                <th style="width: 52%;" class="text-left">Komponen Biaya</th>
                <th style="width: 40%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Sewa Tempat / Ruko Cabang</td>
                <td class="text-right">{{ number_format($monthlyCost->rent_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Internet / Wifi</td>
                <td class="text-right">{{ number_format($monthlyCost->wifi_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Iuran Kebersihan / Sampah</td>
                <td class="text-right">{{ number_format($monthlyCost->trash_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Air & Listrik</td>
                <td class="text-right">{{ number_format($monthlyCost->utilities_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Netflix / Hiburan Display</td>
                <td class="text-right">{{ number_format($monthlyCost->netflix_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>IPL (Iuran Pengelolaan Lingkungan)</td>
                <td class="text-right">{{ number_format($monthlyCost->ipl_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td>Gaji Karyawan / Staf Cabang</td>
                <td class="text-right">{{ number_format($monthlyCost->salary_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">8</td>
                <td>Biaya Tetap Lainnya</td>
                <td class="text-right">{{ number_format($monthlyCost->other_cost, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #0B192C; color: #ffffff; font-weight: bold;">
                <td colspan="2" class="text-center" style="border: 1px solid #0B192C;">SUBTOTAL BIAYA BULANAN (FIXED)</td>
                <td class="text-right" style="border: 1px solid #0B192C; font-size: 10px;">Rp {{ number_format($monthlyCost->total_monthly_cost, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2: Rekapitulasi Belanja Harian -->
    <div class="section-title">2. Rekapitulasi Belanja Harian Dapur (Bulan Terpilih)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">No</th>
                <th style="width: 52%;" class="text-left">Kategori Belanja Harian</th>
                <th style="width: 40%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Belanja Bahan Baku (Ayam, Bebek, Bumbu, dll)</td>
                <td class="text-right">{{ number_format($monthlyCost->daily_expense_raw, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Belanja Non-Bahan Baku (Gas, Minyak, Kresek, dll)</td>
                <td class="text-right">{{ number_format($monthlyCost->daily_expense_non_raw, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Kebutuhan Pribadi / Operasional Harian Staf Dapur</td>
                <td class="text-right">{{ number_format($monthlyCost->daily_expense_personal, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #b45309; color: #ffffff; font-weight: bold;">
                <td colspan="2" class="text-center" style="border: 1px solid #b45309;">SUBTOTAL BELANJA HARIAN ({{ $monthlyCost->daily_expense_days }} Hari)</td>
                <td class="text-right" style="border: 1px solid #b45309; font-size: 10px;">Rp {{ number_format($monthlyCost->daily_expense_total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Grand Total Box -->
    <table class="total-box">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div style="font-size: 8px; color: #64748b; font-weight: bold; text-transform: uppercase;">AKUMULASI SELURUH BIAYA OPERASIONAL CABANG</div>
                <div style="font-size: 11px; font-weight: bold; color: #0f172a;">GRAND TOTAL PENGELUARAN CABANG</div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: middle;">
                <div class="grand-total-highlight">Rp {{ number_format($monthlyCost->grand_total_cost, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    @if(!empty($monthlyCost->notes))
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 10px; margin-bottom: 12px; font-size: 8.5px;">
        <span style="font-weight: bold; color: #334155;">Catatan Tambahan:</span><br>
        <span style="color: #475569;">{{ $monthlyCost->notes }}</span>
    </div>
    @endif

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div>Dibuat / Dicatat Oleh:</div>
                <div class="sig-line">{{ $monthlyCost->user->name ?? $user->name }}<br><span style="font-size: 8px; font-weight: normal; color: #64748b;">Admin Cabang</span></div>
            </td>
            <td style="width: 30%;"></td>
            <td class="sig-box">
                <div>Mengetahui & Menyetujui:</div>
                <div class="sig-line">Kepala Cabang / Manajemen<br><span style="font-size: 8px; font-weight: normal; color: #64748b;">Ikhlas Solusi</span></div>
            </td>
        </tr>
    </table>

</body>
</html>
