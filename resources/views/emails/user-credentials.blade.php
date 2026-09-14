<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Akun Anda</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 40px 0;
        }
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 580px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0B192C;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #0A97B0;
            font-size: 11px;
            font-weight: 600;
            margin: 4px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .body-content {
            padding: 35px 40px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .description {
            font-size: 13px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 25px;
        }
        .credential-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .cred-item {
            margin-bottom: 12px;
        }
        .cred-item:last-child {
            margin-bottom: 0;
        }
        .cred-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .cred-value {
            font-size: 14px;
            font-weight: 700;
            color: #0B192C;
            font-family: 'Courier New', Courier, monospace;
            background-color: #ffffff;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: inline-block;
            word-break: break-all;
        }
        .role-badge {
            font-size: 11px;
            font-weight: 700;
            color: #0A97B0;
            background-color: #e0f7fa;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-block;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0 20px 0;
        }
        .btn-primary {
            background-color: #0A97B0;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(10, 151, 176, 0.3);
        }
        .security-notice {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 0 8px 8px 0;
            font-size: 11px;
            color: #92400e;
            line-height: 1.5;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" align="center" cellpadding="0" cellspacing="0">
            <!-- Header -->
            <tr>
                <td class="header">
                    <h1>IKHLAS SOLUSI</h1>
                    <p>Sistem Manajemen & Resume Penjualan</p>
                </td>
            </tr>

            <!-- Body -->
            <tr>
                <td class="body-content">
                    <div class="greeting">Halo, {{ $user->name }}!</div>
                    <div class="description">
                        Akun Anda telah berhasil didaftarkan pada sistem <strong>{{ config('app.name', 'Ikhlas Solusi') }}</strong>. Berikut adalah rincian informasi akun untuk login ke dalam sistem:
                    </div>

                    <div class="credential-box">
                        <div class="cred-item">
                            <div class="cred-label">Alamat Email / Username</div>
                            <div class="cred-value">{{ $user->email }}</div>
                        </div>
                        <div class="cred-item" style="margin-top: 12px;">
                            <div class="cred-label">Kata Sandi (Password)</div>
                            <div class="cred-value">{{ $plainPassword }}</div>
                        </div>
                        <div class="cred-item" style="margin-top: 12px;">
                            <div class="cred-label">Hak Akses (Role)</div>
                            <span class="role-badge">
                                @if($user->role === 'superadmin')
                                    Super Administrator
                                @elseif($user->role === 'kepala_cabang')
                                    Kepala Cabang
                                @elseif($user->role === 'admin_cabang')
                                    Admin Cabang
                                @else
                                    Viewer
                                @endif
                            </span>
                        </div>
                        @if($user->branch)
                            <div class="cred-item" style="margin-top: 12px;">
                                <div class="cred-label">Penempatan Cabang</div>
                                <div style="font-size: 13px; font-weight: 700; color: #334155;">
                                    {{ $user->branch->name }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="btn-container">
                        <a href="{{ route('login') }}" class="btn-primary" target="_blank">
                            Masuk ke Dashboard Sistem &rarr;
                        </a>
                    </div>

                    <div class="security-notice">
                        <strong>Perhatian Keamanan:</strong> Jangan bagikan informasi login ini kepada pihak lain. Anda dapat memperbarui kata sandi kapan saja melalui menu Profil Pengguna di dashboard.
                    </div>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Ikhlas Solusi') }}. Hak Cipta Dilindungi.<br>
                    Email ini dikirimkan secara otomatis oleh sistem, mohon tidak membalas email ini.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
