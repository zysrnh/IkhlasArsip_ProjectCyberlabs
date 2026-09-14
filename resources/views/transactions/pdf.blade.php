<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Resume Data Transaksi — Ikhlas Solusi</title>
    <style>
        @page {
            margin: 1.2cm 1.4cm 1.2cm 1.4cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 9pt;
            line-height: 1.35;
        }

        /* Header KOP Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .kop-logo-box {
            width: 46px;
            height: 46px;
            background-color: #0A97B0;
            color: #ffffff;
            font-size: 16pt;
            font-weight: 900;
            text-align: center;
            line-height: 46px;
            border-radius: 6px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .company-sub {
            font-size: 8pt;
            color: #64748b;
            line-height: 1.3;
        }
        .company-tag {
            font-size: 7.5pt;
            color: #0A97B0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Judul & Info Card */
        .report-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-title {
            font-size: 12pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .report-subtitle {
            font-size: 8.5pt;
            color: #475569;
        }

        /* Summary KPI Cards */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 7px 10px;
        }
        .kpi-label {
            font-size: 7pt;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 9.5pt;
            font-weight: 800;
            color: #0f172a;
        }
        .kpi-value-accent {
            color: #0A97B0;
        }

        /* Tabel Data Transaksi */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .data-table thead th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 5px;
            border: 1px solid #0f172a;
            vertical-align: middle;
        }
        .data-table tbody td {
            padding: 5.5px 5px;
            font-size: 8pt;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Badges & Alignments */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        .type-badge {
            font-size: 7pt;
            font-weight: 800;
            padding: 2px 5px;
            border-radius: 4px;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-tunai { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-kredit { background-color: #ecfeff; color: #0e7490; border: 1px solid #a5f3fc; }
        .badge-retur { background-color: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .badge-transfer { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-custom { background-color: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }

        .amount-pos { color: #0f172a; font-weight: 800; }
        .amount-neg { color: #b91c1c; font-weight: 800; }

        /* Tanda Tangan & Footer */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
            font-size: 8.5pt;
        }
        .sig-space {
            height: 52px;
        }
        .sig-name {
            font-weight: 800;
            font-size: 9.5pt;
            color: #0f172a;
            text-decoration: underline;
        }
        .sig-role {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .footer-note {
            margin-top: 18px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
            font-size: 7pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

@php
    if (empty($logoBase64)) {
        $candidatePaths = [
            public_path('images/logo.png'),
            base_path('public/images/logo.png'),
            base_path('public_html/images/logo.png'),
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/images/logo.png' : null,
            base_path('../public_html/images/logo.png'),
        ];
        foreach ($candidatePaths as $cPath) {
            if ($cPath && file_exists($cPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($cPath));
                break;
            }
        }
    }
@endphp

    <!-- KOP Surat Resmi -->
    <table class="kop-table">
        <tr>
            <td style="width: 52px; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" width="46" height="46" style="width: 46px; height: 46px; object-fit: contain; border-radius: 6px;">
                @else
                    <div class="kop-logo-box">IS</div>
                @endif
            </td>
            <td style="padding-left: 10px; vertical-align: middle;">
                <div class="company-name">IKHLAS SOLUSI</div>
                <div class="company-sub">
                    Sistem Manajemen & Resume Penjualan Terpusat &bull;
                    <strong>{{ $selectedBranch ? $selectedBranch->name : 'Seluruh Unit Outlet Cabang' }}</strong>
                </div>
                <div class="company-tag">
                    Layanan Laporan Digital &bull; Dokumen Resmi Terverifikasi Sistem
                </div>
            </td>
            <td style="text-align: right; vertical-align: middle; width: 160px;">
                <div style="font-size: 7.5pt; color: #64748b;">Kode Dokumen:</div>
                <div style="font-size: 8.5pt; font-weight: 800; font-family: monospace; color: #0f172a;">DOC-TRX-{{ date('Ymd') }}</div>
            </td>
        </tr>
    </table>

    <!-- Judul Laporan -->
    <div class="report-header">
        <div class="report-title">Laporan Resume Data Transaksi</div>
        <div class="report-subtitle">
            Unit Cabang: <strong>{{ $selectedBranch ? $selectedBranch->name : 'Semua Cabang (Konsolidasi Global)' }}</strong>
            @if($dateFrom || $dateTo)
                &bull; Periode: <strong>{{ $dateFrom ?: 'Awal' }}</strong> s/d <strong>{{ $dateTo ?: 'Sekarang' }}</strong>
            @else
                &bull; Periode: <strong>Semua Tanggal</strong>
            @endif
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <table class="kpi-table">
        <tr>
            <td style="width: 32%; padding-right: 5px;">
                <div class="kpi-card">
                    <div class="kpi-label">TOTAL TRANSAKSI</div>
                    <div class="kpi-value">{{ count($transactions) }} <span style="font-size: 7.5pt; font-weight: normal; color: #64748b;">Entri Data</span></div>
                </div>
            </td>
            <td style="width: 32%; padding-right: 5px; padding-left: 5px;">
                <div class="kpi-card">
                    <div class="kpi-label">TOTAL VOLUME PENJUALAN</div>
                    <div class="kpi-value">{{ number_format($totalQty, 0, ',', '.') }} <span style="font-size: 7.5pt; font-weight: normal; color: #64748b;">Unit Barang</span></div>
                </div>
            </td>
            <td style="width: 36%; padding-left: 5px;">
                <div class="kpi-card" style="border-left: 3px solid #0A97B0;">
                    <div class="kpi-label">TOTAL AKUMULASI NOMINAL</div>
                    <div class="kpi-value kpi-value-accent">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Details Baris Singkat -->
    <table style="width: 100%; margin-bottom: 8px; font-size: 7.5pt; color: #64748b;">
        <tr>
            <td style="width: 50%;">Dicetak Oleh: <strong style="color: #0f172a;">{{ $printedBy }}</strong></td>
            <td style="width: 50%; text-align: right;">Waktu Cetak: <strong style="color: #0f172a;">{{ $printedAt }} WIB</strong></td>
        </tr>
    </table>

    <!-- Tabel Data Transaksi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22px;" class="text-center">No</th>
                <th style="width: 55px;" class="text-center">ID</th>
                <th style="width: 60px;" class="text-center">Tanggal</th>
                <th style="width: 75px;">Cabang</th>
                <th style="width: 85px;" class="text-center">Jenis</th>
                <th>Customer / Deskripsi</th>
                <th style="width: 32px;" class="text-center">Qty</th>
                <th style="width: 85px;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $idx => $trx)
                @php
                    $tLower = strtolower($trx->type);
                    if (str_contains($tLower, 'tunai') || str_contains($tLower, 'cash')) {
                        $badgeClass = 'badge-tunai';
                    } elseif (str_contains($tLower, 'kredit') || str_contains($tLower, 'tempo')) {
                        $badgeClass = 'badge-kredit';
                    } elseif (str_contains($tLower, 'retur')) {
                        $badgeClass = 'badge-retur';
                    } elseif (str_contains($tLower, 'transfer')) {
                        $badgeClass = 'badge-transfer';
                    } else {
                        $badgeClass = 'badge-custom';
                    }
                @endphp
                <tr>
                    <td class="text-center" style="color: #64748b;">{{ $idx + 1 }}</td>
                    <td class="text-center font-mono font-bold" style="color: #334155;">{{ $trx->code }}</td>
                    <td class="text-center" style="color: #475569; white-space: nowrap;">
                        {{ $trx->transaction_date ? $trx->transaction_date->format('d/m/Y') : '-' }}
                    </td>
                    <td style="font-weight: 700; color: #1e293b;">{{ $trx->branch->name ?? '-' }}</td>
                    <td class="text-center">
                        <span class="type-badge {{ $badgeClass }}">{{ $trx->type }}</span>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: #0f172a;">{{ $trx->customer_name }}</div>
                        @if($trx->notes)
                            <div style="color: #64748b; font-size: 7pt; margin-top: 1px;">{{ $trx->notes }}</div>
                        @endif
                    </td>
                    <td class="text-center font-bold font-mono">{{ $trx->qty }}</td>
                    <td class="text-right font-mono {{ $trx->amount < 0 ? 'amount-neg' : 'amount-pos' }}" style="white-space: nowrap;">
                        {{ $trx->amount < 0 ? '-Rp ' . number_format(abs($trx->amount), 0, ',', '.') : 'Rp ' . number_format($trx->amount, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 24px; color: #94a3b8; font-style: italic;">
                        Tidak ada transaksi yang tercatat pada kriteria filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: 800; border-top: 2px solid #0f172a;">
                <td colspan="6" class="text-right" style="padding: 7px 6px; font-size: 8pt; color: #0f172a;">
                    TOTAL KESELURUHAN :
                </td>
                <td class="text-center font-mono" style="padding: 7px 4px; font-size: 8.5pt; color: #0f172a;">
                    {{ number_format($totalQty, 0, ',', '.') }}
                </td>
                <td class="text-right font-mono" style="padding: 7px 6px; font-size: 9pt; color: #0f172a; border-bottom: 3px double #0f172a; white-space: nowrap;">
                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan Pengesahan -->
    <table class="signature-table">
        <tr>
            <td style="width: 58%; vertical-align: bottom;">
                <div style="font-size: 7.5pt; color: #64748b; line-height: 1.4;">
                    <strong>Catatan Sistem:</strong><br>
                    Dokumen ini dicetak langsung dari platform arsip transaksi resmi Ikhlas Solusi.<br>
                    Data telah divalidasi dan dicocokkan dengan basis data pusat.
                </div>
            </td>
            <td style="width: 42%;" class="sig-box">
                <div style="color: #475569; font-size: 8pt; margin-bottom: 2px;">
                    Disahkan di Bandung, {{ now()->translatedFormat('d F Y') }}
                </div>
                <div style="font-weight: 800; color: #0f172a;">
                    {{ $selectedBranch ? 'Kepala Cabang ' . $selectedBranch->name : 'Penanggung Jawab Operasional' }}
                </div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $printedBy }}</div>
                <div class="sig-role">Ikhlas Solusi Sales & Archiving Management</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Ikhlas Solusi Archiving System &bull; Dokumen Rahasia Perusahaan &bull; Halaman 1 dari 1
    </div>

</body>
</html>
