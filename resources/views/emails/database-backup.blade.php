<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Backup Database SQL</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f8;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        .container {
            max-width: 580px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .header {
            padding: 24px 32px;
            background-color: #0B192C;
            border-bottom: 3px solid #0A97B0;
        }
        .brand-title {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .brand-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }
        .content {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
        }
        .intro-text {
            font-size: 13.5px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #f8fafc;
            margin-bottom: 24px;
        }
        .info-table td {
            padding: 10px 16px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-label {
            width: 38%;
            font-weight: 600;
            color: #64748b;
        }
        .info-val {
            width: 62%;
            font-weight: 700;
            color: #0f172a;
        }
        .file-badge {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff;
            font-family: Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 4px;
        }
        .notice-box {
            background-color: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 0 4px 4px 0;
            font-size: 12.5px;
            color: #1e40af;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .footer {
            padding: 20px 32px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <div class="container">
                    
                    <!-- Header -->
                    <div class="header">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td>
                                    <h1 class="brand-title">IKHLAS SOLUSI</h1>
                                    <div class="brand-sub">Sistem Manajemen & Arsip Transaksi Terpusat</div>
                                </td>
                                <td align="right" style="vertical-align: middle;">
                                    <span style="font-size: 10px; font-weight: 800; color: #0A97B0; text-transform: uppercase; letter-spacing: 0.5px;">
                                        DATABASE BACKUP
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <div class="greeting">Halo {{ $recipientName }},</div>
                        <div class="intro-text">
                            Berikut terlampir salinan berkas cadangan database (SQL Dump) resmi dari sistem <strong>Ikhlas Solusi</strong>. Berkas ini mencakup seluruh struktur skema tabel, data transaksi, dan master data terkait.
                        </div>

                        <!-- Ringkasan Berkas -->
                        <table class="info-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="info-label">Nama Berkas</td>
                                <td class="info-val">
                                    <span class="file-badge">{{ $fileName }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label">Ukuran Berkas</td>
                                <td class="info-val">{{ $fileSizeFormatted }}</td>
                            </tr>
                            <tr>
                                <td class="info-label">Waktu Pembuatan</td>
                                <td class="info-val">{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
                            </tr>
                            <tr>
                                <td class="info-label">Format Berkas</td>
                                <td class="info-val">Standard MySQL Dump (.sql)</td>
                            </tr>
                            @if(!empty($notes))
                                <tr>
                                    <td class="info-label">Catatan</td>
                                    <td class="info-val" style="font-weight: normal; color: #475569;">{{ $notes }}</td>
                                </tr>
                            @endif
                        </table>

                        <div class="notice-box">
                            <strong>Perhatian Keamanan:</strong><br>
                            Berkas cadangan SQL ini memuat data rahasia perusahaan. Harap simpan dan amankan berkas ini hanya pada perangkat penyimpanan yang terotorisasi.
                        </div>

                        <div style="font-size: 12px; color: #64748b;">
                            Untuk memulihkan (restore) data, Anda dapat mengimpor file SQL terlampir melalui phpMyAdmin, MySQL Workbench, atau terminal CLI database server.
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <div>Email ini dikirim otomatis oleh Sistem Arsip & Penjualan <strong>Ikhlas Solusi</strong>.</div>
                        <div style="margin-top: 4px; color: #94a3b8;">&copy; {{ date('Y') }} Ikhlas Solusi. Seluruh hak cipta dilindungi.</div>
                    </div>

                </div>
            </td>
        </tr>
    </table>

</body>
</html>
