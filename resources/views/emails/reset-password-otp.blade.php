<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Reset Kata Sandi - SIMPEL DBI</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .email-container {
            max-width: 480px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            padding: 32px 24px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.06);
        }
        .badge-icon {
            width: 48px;
            height: 48px;
            background-color: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
            margin: 0 0 8px 0;
        }
        .header-desc {
            font-size: 13px;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
            margin: 0 0 24px 0;
        }
        .otp-box {
            background-color: #f8fafc;
            border: 1px dashed #94a3b8;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 24px;
        }
        .otp-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #0f172a;
        }
        .btn-action {
            display: block;
            width: 100%;
            background-color: #0f172a;
            color: #ffffff !important;
            text-align: center;
            padding: 12px 0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .footer-note {
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header-title">Reset Kata Sandi SIMPEL DBI</div>
        <p class="header-desc">
            Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda (<strong>{{ $email }}</strong>). Silakan gunakan kode OTP 6 digit di bawah ini untuk melanjutkan.
        </p>

        <div class="otp-box">
            <div class="otp-label">Kode Verifikasi OTP Anda</div>
            <div class="otp-code">{{ $otpCode }}</div>
        </div>

        <a href="{{ $verifyUrl }}" class="btn-action">Verifikasi & Atur Ulang Kata Sandi</a>

        <p class="footer-note">
            Kode ini berlaku selama 10 menit. Jika Anda tidak merasa meminta reset kata sandi, harap abaikan pesan email ini.
        </p>
    </div>
</body>
</html>
