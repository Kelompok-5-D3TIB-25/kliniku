<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kode OTP Verifikasi KLINIKU</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background-color: #f8fafc;
      margin: 0;
      padding: 0;
      color: #1e293b;
    }
    .container {
      max-width: 560px;
      margin: 30px auto;
      background: #ffffff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
      border: 1px solid #e2e8f0;
    }
    .header {
      background: #059669;
      color: #ffffff;
      padding: 24px 30px;
      text-align: center;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
      letter-spacing: -0.5px;
    }
    .header p {
      margin: 4px 0 0;
      font-size: 13px;
      color: #d1fae5;
    }
    .content {
      padding: 32px 30px;
    }
    .greeting {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 12px;
      color: #0f172a;
    }
    .message {
      font-size: 14px;
      line-height: 1.6;
      color: #475569;
      margin-bottom: 24px;
    }
    .otp-box {
      background: #ecfdf5;
      border: 2px dashed #059669;
      border-radius: 10px;
      padding: 18px 20px;
      text-align: center;
      margin: 24px 0;
    }
    .otp-code {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: 8px;
      color: #065f46;
      font-family: Consolas, 'Liberation Mono', Menlo, monospace;
    }
    .otp-expiry {
      font-size: 12px;
      color: #047857;
      margin-top: 6px;
      font-weight: 500;
    }
    .warning {
      background: #fffbeb;
      border-left: 4px solid #f59e0b;
      padding: 12px 16px;
      font-size: 12px;
      color: #92400e;
      border-radius: 4px;
      margin-bottom: 24px;
      line-height: 1.5;
    }
    .footer {
      background: #f1f5f9;
      padding: 20px 30px;
      text-align: center;
      font-size: 12px;
      color: #64748b;
      border-top: 1px solid #e2e8f0;
    }
    .footer a {
      color: #059669;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🏥 KLINIKU</h1>
      <p>Sistem Layanan Kesehatan Terintegrasi</p>
    </div>

    <div class="content">
      <div class="greeting">Halo, {{ $userName }}!</div>
      
      <p class="message">
        Kami menerima permintaan masuk (*login*) ke akun KLINIKU Anda. Silakan gunakan kode One-Time Password (OTP) 6 digit di bawah ini untuk memverifikasi identitas Anda:
      </p>

      <div class="otp-box">
        <div class="otp-code">{{ $otpCode }}</div>
        <div class="otp-expiry">⏳ Berlaku selama {{ $expiresInMinutes }} menit</div>
      </div>

      <div class="warning">
        <strong>PENTING:</strong> Jangan berikan kode OTP ini kepada siapa pun, termasuk pihak yang mengatasnamakan staf atau dokter KLINIKU. Jika Anda tidak merasa melakukan permintaan ini, segera amankan kata sandi akun Anda.
      </div>

      <p class="message" style="margin-bottom: 0;">
        Terima kasih telah mempercayakan layanan kesehatan Anda bersama <strong>KLINIKU</strong>.
      </p>
    </div>

    <div class="footer">
      &copy; {{ date('Y') }} KLINIKU Healthcare System. Hak cipta dilindungi.<br>
      Email ini dikirim otomatis oleh sistem, mohon untuk tidak membalas email ini.
    </div>
  </div>
</body>
</html>
