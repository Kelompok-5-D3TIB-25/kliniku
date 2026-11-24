<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Tiket Janji Temu KLINIKU</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 20px; color: #1e293b; }
        .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 28px 24px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 6px 0 0; font-size: 13px; opacity: 0.9; }
        .content { padding: 28px 24px; }
        .ticket-box { background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0; }
        .ticket-label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .ticket-number { font-size: 38px; font-weight: 900; color: #059669; margin: 8px 0; font-family: monospace; letter-spacing: 2px; }
        .qr-box { display: inline-block; background: #0f172a; color: #38bdf8; padding: 6px 14px; border-radius: 6px; font-family: monospace; font-size: 13px; font-weight: bold; margin-top: 6px; }
        .detail-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
        .detail-table td { padding: 8px 4px; border-bottom: 1px solid #f1f5f9; }
        .detail-table td.label { color: #64748b; font-weight: 600; width: 38%; }
        .detail-table td.value { color: #0f172a; font-weight: 700; }
        .alert-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 6px; font-size: 12px; color: #1e40af; margin-top: 20px; }
        .footer { background: #f8fafc; padding: 16px 24px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>🏥 KLINIKU — E-Tiket Janji Temu</h1>
            <p>Konfirmasi Pemesanan Antrean Rawat Jalan Online</p>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $antrean->pasien->user->user_nama ?? 'Pasien' }}</strong>,</p>
            <p>Pemesanan janji temu Anda telah berhasil dikonfirmasi oleh sistem KLINIKU. Simpan tiket ini untuk verifikasi saat tiba di klinik.</p>
            
            <div class="ticket-box">
                <div class="ticket-label">Nomor Antrean Anda</div>
                <div class="ticket-number">{{ $antrean->antrean_nomor }}</div>
                <div>Kode Verifikasi QR Check-in:</div>
                <div class="qr-box">{{ $antrean->antrean_qr_code }}</div>
            </div>

            <table class="detail-table">
                <tr>
                    <td class="label">Nomor Rekam Medis:</td>
                    <td class="value">{{ $antrean->pasien->pasien_norm ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Poliklinik Tujuan:</td>
                    <td class="value">{{ $antrean->poli->poli_nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Dokter Pemeriksa:</td>
                    <td class="value">{{ $antrean->dokter->user->user_nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Kunjungan:</td>
                    <td class="value">{{ \Carbon\Carbon::parse($antrean->antrean_tanggal)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Jam Praktik Dokter:</td>
                    <td class="value">
                        @if($antrean->jadwal)
                            {{ substr($antrean->jadwal->jadwal_jammulai, 0, 5) }} - {{ substr($antrean->jadwal->jadwal_jamselesai, 0, 5) }} WIB
                        @else
                            Sesuai Jadwal Poli
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Status Tiket:</td>
                    <td class="value" style="color: #0284c7;">{{ strtoupper(str_replace('_', ' ', $antrean->antrean_status)) }}</td>
                </tr>
            </table>

            <div class="alert-box">
                <strong>💡 Petunjuk Kedatangan:</strong>
                <ol style="margin: 6px 0 0; padding-left: 18px; line-height: 1.5;">
                    <li>Datanglah setidaknya 15 menit sebelum jam praktik dimulai.</li>
                    <li>Tunjukkan kode QR di atas kepada staf loket klinik untuk melakukan <strong>Check-in</strong> kedatangan.</li>
                    <li>Pantau giliran antrean Anda secara langsung di menu <em>Live Queue Tracker</em> pada situs web KLINIKU.</li>
                </ol>
            </div>
        </div>
        <div class="footer">
            Klinik Pratama Medika — Sistem Antrean & Janji Temu Mandiri KLINIKU<br>
            Surakarta, Jawa Tengah. Email otomatis, mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
