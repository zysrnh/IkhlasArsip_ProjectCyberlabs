<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Transaksi</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.3;
        }
        .kop-table {
            width: 100%;
            border-bottom: 3px double #0B192C;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .kop-logo {
            width: 50px;
            height: 50px;
            background-color: #0A97B0;
            color: #ffffff;
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
            line-height: 50px;
        }
        .kop-text {
            padding-left: 12px;
        }
        .company-name {
            font-size: 15pt;
            font-weight: bold;
            color: #0B192C;
            letter-spacing: 0.5px;
        }
        .company-sub {
            font-size: 9pt;
            color: #64748b;
            margin-top: 2px;
        }
        .report-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
            color: #0B192C;
        }
        .report-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 15px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #0B192C;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 6px;
            border: 1px solid #0B192C;
        }
        .data-table td {
            padding: 6px;
            font-size: 8.5pt;
            border: 1px solid #cbd5e1;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .badge {
            font-size: 7.5pt;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .total-box {
            width: 100%;
            margin-top: 10px;
            border-top: 2px solid #0B192C;
            padding-top: 8px;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

@php
    $logoPath = public_path('images/logo.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp

    <!-- KOP Surat Resmi -->
    <table class="kop-table">
        <tr>
            <td style="width: 55px; vertical-align: middle;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 50px; height: 50px; object-fit: contain;">
                @else
                    <div class="kop-logo">IS</div>
                @endif
            </td>
            <td class="kop-text">
                <div class="company-name">IKHLAS SOLUSI</div>
                <div class="company-sub">
                    Sistem Manajemen & Resume Penjualan Terpusat &bull;
                    {{ $selectedBranch ? $selectedBranch->name : 'Seluruh Unit Outlet Cabang' }}
                </div>
                <div class="company-sub" style="font-size: 8pt; margin-top: 2px;">
                    Layanan Laporan Digital &bull; Dokumen Resmi Terverifikasi Sistem
                </div>
            </td>
        </tr>
    </table>

    <!-- Judul Laporan -->
    <div class="report-title">Laporan Resume Data Transaksi</div>
    <div class="report-subtitle">
        Cabang: <strong>{{ $selectedBranch ? $selectedBranch->name : 'Semua Cabang (Global)' }}</strong>
        @if($dateFrom || $dateTo)
            &bull; Periode: {{ $dateFrom ?: '-' }} s/d {{ $dateTo ?: '-' }}
        @endif
    </div>

    <!-- Informasi Cetak -->
    <table class="meta-table">
        <tr>
            <td style="width: 50%;">Dicetak Oleh: <strong>{{ $printedBy }}</strong></td>
            <td style="width: 50%; text-align: right;">Waktu Cetak: <strong>{{ $printedAt }} WIB</strong></td>
        </tr>
        <tr>
            <td>Total Entri Data: <strong>{{ count($transactions) }} Transaksi</strong></td>
            <td style="text-align: right;">Total QTY: <strong>{{ $totalQty }} Unit</strong></td>
        </tr>
    </table>

    <!-- Tabel Data Transaksi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 55px;">ID</th>
                <th style="width: 65px;">Tanggal</th>
                <th style="width: 80px;">Cabang</th>
                <th style="width: 90px;">Jenis</th>
                <th>Customer / Deskripsi</th>
                <th style="width: 35px;" class="text-center">QTY</th>
                <th style="width: 90px;" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $idx => $trx)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold" style="font-family: monospace;">{{ $trx->code }}</td>
                    <td>{{ $trx->transaction_date ? $trx->transaction_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $trx->branch->name ?? '-' }}</td>
                    <td>{{ $trx->type }}</td>
                    <td>
                        <strong>{{ $trx->customer_name }}</strong>
                        @if($trx->notes)
                            <br><span style="color: #64748b; font-size: 7.5pt;">{{ $trx->notes }}</span>
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ $trx->qty }}</td>
                    <td class="text-right font-bold" style="{{ $trx->amount < 0 ? 'color: #dc2626;' : '' }}">
                        {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data transaksi.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="6" class="text-right" style="padding: 8px;">TOTAL KESELURUHAN:</td>
                <td class="text-center" style="padding: 8px;">{{ $totalQty }}</td>
                <td class="text-right" style="padding: 8px; color: #0f172a; font-size: 9.5pt;">
                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan Pengesahan -->
    <table class="signature-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center;">
                <div style="font-size: 9pt; margin-bottom: 60px;">
                    Disahkan pada {{ date('d F Y') }}<br>
                    <strong>Kepala Cabang / Penanggung Jawab</strong>
                </div>
                <div style="font-weight: bold; text-decoration: underline; font-size: 10pt;">
                    {{ $printedBy }}
                </div>
                <div style="font-size: 8.5pt; color: #64748b;">
                    Ikhlas Solusi Sales Management
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
