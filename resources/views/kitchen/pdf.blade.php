<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Masakan Dapur & Penjualan Kasir — {{ $kitchenReport->branch->name ?? 'Cabang' }} — {{ $kitchenReport->report_date->translatedFormat('d M Y') }}</title>
    <style>
        @page {
            margin: 1.0cm 1.2cm 1.0cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8pt;
            line-height: 1.3;
        }

        /* Header KOP Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .kop-logo-box {
            width: 40px;
            height: 40px;
            background-color: #0A97B0;
            color: #ffffff;
            font-size: 14pt;
            font-weight: 900;
            text-align: center;
            line-height: 40px;
            border-radius: 6px;
        }
        .company-name {
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .company-sub {
            font-size: 7.5pt;
            color: #64748b;
            line-height: 1.25;
        }
        .company-tag {
            font-size: 7pt;
            color: #0A97B0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Judul & Info Laporan */
        .report-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 11pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .report-subtitle {
            font-size: 8pt;
            color: #475569;
        }

        /* Summary KPI Cards */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 6px 8px;
        }
        .kpi-label {
            font-size: 6.5pt;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 9pt;
            font-weight: 800;
            color: #0f172a;
        }
        .kpi-value-accent {
            color: #0A97B0;
        }
        .kpi-value-success {
            color: #16a34a;
        }
        .kpi-value-danger {
            color: #e11d48;
        }

        /* Table Data */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 5px 4px;
            border: 1px solid #0f172a;
            font-size: 6.5pt;
        }
        .data-table td {
            padding: 4px 4px;
            border: 1px solid #e2e8f0;
            color: #334155;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tr.total-row {
            background-color: #0f172a !important;
            color: #ffffff !important;
            font-weight: 800;
        }
        .data-table tr.total-row td {
            border: 1px solid #0f172a;
            color: #ffffff;
            padding: 5px 4px;
            font-size: 7.5pt;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .badge-perishable {
            color: #e11d48;
            font-weight: bold;
            font-size: 6pt;
        }
        .badge-normal {
            color: #0A97B0;
            font-size: 6pt;
        }

        /* Notes Box */
        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-title {
            font-size: 7.5pt;
            color: #64748b;
            margin-bottom: 40px;
        }
        .signature-name {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .signature-role {
            font-size: 7pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI -->
    <table class="kop-table">
        <tr>
            <td style="width: 48px; vertical-align: top;">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 40px; height: 40px; object-fit: contain;" alt="Logo">
                @else
                    <div class="kop-logo-box">IS</div>
                @endif
            </td>
            <td style="vertical-align: top; padding-left: 8px;">
                <div class="company-name">IKHLAS SOLUSI</div>
                <div class="company-tag">Sistem Manajemen Operasional & Rekap Dapur Cabang</div>
                <div class="company-sub">Laporan Resmi Pencatatan Porsi Masakan, Sisa Harian & Settlement Omset Kasir</div>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <div style="font-size: 7pt; color: #64748b;">DOKUMEN RESMI</div>
                <div style="font-size: 9pt; font-weight: 800; color: #0f172a;">NO: DKR-{{ str_pad($kitchenReport->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size: 7pt; color: #64748b;">Dicetak: {{ $printedAt }}</div>
            </td>
        </tr>
    </table>

    <!-- JUDUL LAPORAN -->
    <div class="report-header">
        <div class="report-title">LAPORAN HARIAN MASAKAN DAPUR & PENJUALAN KASIR</div>
        <div class="report-subtitle">
            Cabang: <strong>{{ $kitchenReport->branch->name ?? 'Cabang' }}</strong> &nbsp;|&nbsp; 
            Hari / Tanggal: <strong>{{ $kitchenReport->report_date->translatedFormat('l, d F Y') }}</strong> &nbsp;|&nbsp;
            Penginput: <strong>{{ $kitchenReport->user->name ?? 'Sistem' }}</strong>
        </div>
    </div>

    <!-- SUMMARY KPI 4 BOX (SESUAI FORMAT EXCEL) -->
    <table class="kpi-table">
        <tr>
            <td style="width: 20%; padding-right: 4px;">
                <div class="kpi-card">
                    <div class="kpi-label">1. Pendapatan Cash</div>
                    <div class="kpi-value">Rp {{ number_format($kitchenReport->cash_income, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%; padding-right: 4px; padding-left: 4px;">
                <div class="kpi-card">
                    <div class="kpi-label">2. Pendapatan QRIS</div>
                    <div class="kpi-value">Rp {{ number_format($kitchenReport->qris_income, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%; padding-right: 4px; padding-left: 4px;">
                <div class="kpi-card">
                    <div class="kpi-label">3. Pendapatan Online</div>
                    <div class="kpi-value">Rp {{ number_format($kitchenReport->online_food_income, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%; padding-right: 4px; padding-left: 4px;">
                <div class="kpi-card" style="background-color: #f0fdf4; border-color: #bbf7d0;">
                    <div class="kpi-label" style="color: #166534;">4. Total Omset Kasir</div>
                    <div class="kpi-value kpi-value-success">Rp {{ number_format($kitchenReport->total_omset, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 20%; padding-left: 4px;">
                <div class="kpi-card" style="background-color: {{ abs($kitchenReport->difference_amount) < 1 ? '#f0fdf4' : '#fffbeb' }}; border-color: {{ abs($kitchenReport->difference_amount) < 1 ? '#bbf7d0' : '#fde68a' }};">
                    <div class="kpi-label" style="color: {{ abs($kitchenReport->difference_amount) < 1 ? '#166534' : '#92400e' }};">Status Selisih</div>
                    <div class="kpi-value" style="color: {{ abs($kitchenReport->difference_amount) < 1 ? '#16a34a' : '#b45309' }};">
                        @if(abs($kitchenReport->difference_amount) < 1)
                            Cocok (Match)
                        @elseif($kitchenReport->difference_amount > 0)
                            +Rp {{ number_format($kitchenReport->difference_amount, 0, ',', '.') }}
                        @else
                            -Rp {{ number_format(abs($kitchenReport->difference_amount), 0, ',', '.') }}
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TABEL RINCIAN 57 MENU MASAKAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;" class="text-center">No</th>
                <th style="width: 140px;" class="text-left">Nama Masakan</th>
                <th style="width: 40px;" class="text-center">Sifat</th>
                <th style="width: 45px;" class="text-center">Sisa Kemarin</th>
                <th style="width: 45px;" class="text-center">Masak Hari Ini</th>
                <th style="width: 45px;" class="text-center">Total Masakan</th>
                <th style="width: 45px;" class="text-center">Terjual</th>
                <th style="width: 45px;" class="text-center">Sisa Hari Ini</th>
                <th style="width: 55px;" class="text-right">Harga (Rp)</th>
                <th style="width: 65px;" class="text-right">Total Penjualan</th>
                <th style="width: 65px;" class="text-right">Sisa Bisa Dijual</th>
                <th style="width: 60px;" class="text-right">Lauk Terbuang</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kitchenReport->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $item->menu->order_number ?? ($idx + 1) }}</td>
                <td class="font-bold">{{ $item->menu->name ?? 'Menu' }}</td>
                <td class="text-center">
                    @if($item->menu && $item->menu->is_perishable)
                        <span class="badge-perishable">Sayur</span>
                    @else
                        <span class="badge-normal">Lauk</span>
                    @endif
                </td>
                <td class="text-center">{{ $item->yesterday_remaining }}</td>
                <td class="text-center font-bold">{{ $item->cooked_today }}</td>
                <td class="text-center font-bold">{{ $item->total_cooked }}</td>
                <td class="text-center font-bold" style="color: #0A97B0;">{{ $item->sold }}</td>
                <td class="text-center font-bold">{{ $item->remaining }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right font-bold" style="color: #16a34a;">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #0A97B0;">Rp {{ number_format($item->remaining_sellable_amount, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #e11d48;">Rp {{ number_format($item->wasted_food_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right uppercase">GRAND TOTAL:</td>
                <td class="text-center">{{ number_format($kitchenReport->items->sum('cooked_today')) }}</td>
                <td class="text-center">{{ number_format($kitchenReport->items->sum('total_cooked')) }}</td>
                <td class="text-center" style="color: #67e8f9;">{{ number_format($kitchenReport->items->sum('sold')) }}</td>
                <td class="text-center">{{ number_format($kitchenReport->items->sum('remaining')) }}</td>
                <td></td>
                <td class="text-right" style="color: #4ade80;">Rp {{ number_format($kitchenReport->grand_total_sales, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #67e8f9;">Rp {{ number_format($kitchenReport->total_remaining_sellable, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #fda4af;">Rp {{ number_format($kitchenReport->total_wasted_food, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- NOTES -->
    @if($kitchenReport->notes)
    <div class="notes-box">
        <strong>Catatan / Keterangan Operasional Dapur:</strong><br>
        {{ $kitchenReport->notes }}
    </div>
    @endif

    <!-- TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div class="signature-title">Dibuat Oleh (Admin Cabang):</div>
                <div class="signature-name">{{ $kitchenReport->user->name ?? 'Admin Cabang' }}</div>
                <div class="signature-role">{{ $kitchenReport->branch->name ?? 'Cabang' }}</div>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="signature-title">Diketahui / Disetujui Oleh:</div>
                <div class="signature-name">Kepala Cabang / Manager</div>
                <div class="signature-role">Ikhlas Solusi</div>
            </td>
        </tr>
    </table>

</body>
</html>
