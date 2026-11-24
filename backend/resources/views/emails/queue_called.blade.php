<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panggilan Antrean KLINIKU</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0fdf4;
            color: #1f2937;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #bbf7d0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #16a34a, #15803d);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            letter-spacing: 1px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.95;
        }
        .callout-box {
            background-color: #fef2f2;
            border: 2px dashed #ef4444;
            border-radius: 12px;
            text-align: center;
            padding: 20px;
            margin: 24px;
        }
        .queue-number {
            font-size: 44px;
            font-weight: 800;
            color: #dc2626;
            margin: 8px 0;
            letter-spacing: 2px;
        }
        .callout-title {
            font-size: 15px;
            font-weight: 600;
            color: #991b1b;
            text-transform: uppercase;
        }
        .content {
            padding: 0 24px 24px;
        }
        .detail-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #64748b;
        }
        .value {
            font-weight: 600;
            color: #0f172a;
        }
        .instructions {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 14px;
            border-radius: 6px;
            font-size: 13px;
            color: #1e3a8a;
            line-height: 1.5;
        }
        .footer {
            background: #f8fafc;
            text-align: center;
            padding: 16px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>KLINIKU</h1>
            <p>Sistem Antrean & Layanan Poliklinik Terpadu</p>
        </div>

        <div class="callout-box">
            <div class="callout-title">GIRILAN ANDA SEDANG DIPANGGIL!</div>
            <div class="queue-number">{{ $antrean->antrean_nomor }}</div>
            <p style="margin: 0; font-size: 14px; color: #7f1d1d; font-weight: 500;">
                Panggilan ke-{{ $antrean->antrean_panggilan_ke }} • Segera masuki ruang periksa
            </p>
        </div>

        <div class="content">
            <div class="detail-card">
                <div class="detail-row">
                    <span class="label">Nama Pasien</span>
                    <span class="value">{{ $antrean->pasien->user->user_nama ?? 'Pasien' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Poliklinik Tujuan</span>
                    <span class="value">{{ $antrean->poli->poli_nama ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Dokter Pemeriksa</span>
                    <span class="value">{{ $antrean->dokter->user->user_nama ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Waktu Panggilan</span>
                    <span class="value">{{ optional($antrean->antrean_waktu_panggil)->format('H:i:s') ?? date('H:i:s') }} WIB</span>
                </div>
            </div>

            <div class="instructions">
                <strong>Instruksi Penting:</strong>
                <ul style="margin: 6px 0 0; padding-left: 18px;">
                    <li>Silakan langsung menuju ke pintu poliklinik <strong>{{ $antrean->poli->poli_nama ?? 'Poliklinik' }}</strong>.</li>
                    <li>Tunjukkan E-Tiket atau sebutkan nomor antrean Anda kepada dokter/perawat.</li>
                    <li>Jika Anda tidak hadir dalam 3 kali panggilan, nomor antrean Anda akan dilewati oleh sistem.</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            &copy; 2026 KLINIKU — Pengganti Notifikasi WhatsApp melalui Layanan SMTP Email Resmi.
        </div>
    </div>
</body>
</html>
