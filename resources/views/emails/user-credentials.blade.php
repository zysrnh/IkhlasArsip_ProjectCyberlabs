<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Akun Anda</title>
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
            max-width: 560px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .header {
            padding: 24px 32px;
            border-bottom: 1px solid #e2e8f0;
        }
        .header img {
            max-height: 40px;
            display: block;
            border: 0;
        }
        .brand-title {
            font-size: 16px;
            font-weight: 700;
            color: #0B192C;
            letter-spacing: -0.2px;
            margin: 0;
        }
        .content {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 24px;
        }
        .cred-table {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            background-color: #f8fafc;
            margin-bottom: 24px;
        }
        .cred-table td {
            padding: 10px 16px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }
        .cred-table tr:last-child td {
            border-bottom: none;
        }
        .cred-label {
            width: 35%;
            font-weight: 600;
            color: #64748b;
        }
        .cred-val {
            width: 65%;
            font-weight: 600;
            color: #0f172a;
        }
        .password-highlight {
            display: inline-block;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 3px;
            font-family: Consolas, 'Liberation Mono', Menlo, Courier, monospace;
            font-size: 13px;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .button-area {
            text-align: left;
            margin-top: 24px;
            margin-bottom: 24px;
        }
        .btn-action {
            display: inline-block;
            background-color: #0A97B0;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 4px;
        }
        .notice-box {
            border-left: 3px solid #0A97B0;
            background-color: #f8fafc;
            padding: 12px 16px;
            font-size: 12px;
            line-height: 1.5;
            color: #475569;
            margin-top: 24px;
        }
        .footer {
            padding: 20px 32px;
            border-top: 1px solid #e2e8f0;
            background-color: #fafafa;
            font-size: 11px;
            line-height: 1.5;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f6f8; padding: 20px 10px;">
        <tr>
            <td align="center">
                <table class="container" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        @if(file_exists(public_path('images/logo.png')))
                                            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo" style="max-height: 36px;">
                                        @else
                                            <h1 class="brand-title">{{ config('app.name', 'Ikhlas Arsip') }}</h1>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content">
                            <div class="greeting">Halo, {{ $user->name }}</div>
                            <div class="intro-text">
                                Akun Anda telah berhasil dibuat pada sistem <strong>{{ config('app.name', 'Ikhlas Arsip') }}</strong>. Berikut adalah informasi kredensial yang dapat digunakan untuk masuk:
                            </div>

                            <!-- Credentials Table -->
                            <table class="cred-table" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="cred-label">Email / Username</td>
                                    <td class="cred-val">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="cred-label">Kata Sandi</td>
                                    <td class="cred-val">
                                        <span class="password-highlight">{{ $plainPassword }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cred-label">Hak Akses (Role)</td>
                                    <td class="cred-val">
                                        @if($user->role === 'superadmin')
                                            Super Administrator
                                        @elseif($user->role === 'kepala_cabang')
                                            Kepala Cabang
                                        @elseif($user->role === 'admin_cabang')
                                            Admin Cabang
                                        @else
                                            Viewer
                                        @endif
                                    </td>
                                </tr>
                                @if($user->branch)
                                    <tr>
                                        <td class="cred-label">Penempatan Cabang</td>
                                        <td class="cred-val">{{ $user->branch->name }}</td>
                                    </tr>
                                @endif
                            </table>

                            <div class="button-area">
                                <a href="{{ route('login') }}" class="btn-action" target="_blank">
                                    Masuk ke Dashboard Sistem
                                </a>
                            </div>

                            <div class="notice-box">
                                <strong>Keamanan Akun:</strong> Harap jaga kerahasiaan informasi akun Anda. Demi keamanan, disarankan untuk memperbarui kata sandi setelah Anda berhasil login melalui menu Pengaturan Profil.
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Ikhlas Arsip') }}. Seluruh hak cipta dilindungi.<br>
                            Email ini dikirimkan secara otomatis oleh sistem. Mohon untuk tidak membalas email ini secara langsung.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
