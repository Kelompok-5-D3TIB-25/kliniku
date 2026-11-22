# KLINIKU — Backend API

Sistem backend antarmuka pemrograman aplikasi (RESTful API) untuk layanan klinik kesehatan **KLINIKU**. Repositori ini didedikasikan secara eksklusif untuk backend layanan dan pengelolaan data.

## Fitur Utama Backend

- **Pendaftaran Pasien Otomatis**:
  - Validasi 16 digit NIK & pengecekan duplikasi realtime.
  - Auto-ekstraksi jenis kelamin & tanggal lahir dari NIK.
  - Penomoran User ID (`USR-PAS-XXXX`) dan Nomor Rekam Medis (`RM-YYYY-XXXX`) berurutan secara otomatis dengan row locking MySQL.
- **Autentikasi Aman & Proteksi Bot**:
  - Proteksi formulir login dengan **Google reCAPTCHA v2** ("Saya bukan robot").
  - Otentikasi dua langkah dengan **FlowKirim WhatsApp Tap-to-Verify (`whatsapp_tap`)** via WhatsApp deep link.
  - Pengelolaan sesi otentikasi berbasis **JSON Web Token (JWT Bearer HS256)**.
- **Arsitektur Database**:
  - MySQL 8.4 dengan integritas referensial dan foreign key cascading.
  - Model data terstandarisasi untuk peran Pasien, Dokter, Staf, dan Poliklinik.

## Dokumentasi Lengkap

Seluruh dokumentasi teknis dan panduan integrasi API tersimpan di direktori [docs/](file:///root/Melissa/kliniku/docs/):

- 📘 [Dokumentasi Lengkap API KLINIKU](file:///root/Melissa/kliniku/docs/API_DOCUMENTATION.md) — Spesifikasi seluruh endpoint API, parameter request, contoh response, struktur tabel, dan panduan cURL.
- 📗 [Dokumentasi API FlowKirim](file:///root/Melissa/kliniku/docs/Dokumentasi%20API%20FlowKirim.md) — Panduan integrasi FlowKirim Verify API dan WhatsApp Tap-to-Verify.

## Menjalankan Lingkungan Pengembangan

1. Pastikan Docker dan Docker Compose telah terpasang.
2. Jalankan container:
   ```bash
   docker compose up -d
   ```
3. Menjalankan pengujian otomatis (PHPUnit / Pest):
   ```bash
   docker compose exec app php artisan test
   ```
