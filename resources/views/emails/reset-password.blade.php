<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi</title>
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
            padding: 12px 24px;
            border-radius: 4px;
        }
        .notice-box {
            border-left: 3px solid #f59e0b;
            background-color: #fffbeb;
            padding: 12px 16px;
            font-size: 12px;
            line-height: 1.5;
            color: #92400e;
            margin-top: 24px;
        }
        .alternative-link {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            margin-top: 20px;
            word-break: break-all;
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
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda pada sistem <strong>{{ config('app.name', 'Ikhlas Arsip') }}</strong>. Klik tombol di bawah ini untuk membuat kata sandi baru:
                            </div>

                            <div class="button-area">
                                <a href="{{ $resetUrl }}" class="btn-action" target="_blank">
                                    Atur Ulang Kata Sandi
                                </a>
                            </div>

                            <div class="notice-box">
                                <strong>Penting:</strong> Tautan pengaturan ulang kata sandi ini hanya berlaku selama <strong>60 menit</strong>. Jika Anda tidak pernah meminta reset kata sandi, abaikan email ini dan akun Anda akan tetap aman.
                            </div>

                            <div class="alternative-link">
                                Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut pada peramban web Anda:<br>
                                <a href="{{ $resetUrl }}" style="color: #0A97B0;">{{ $resetUrl }}</a>
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
