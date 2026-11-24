# Dokumentasi Lengkap API KLINIKU (Backend REST API)

Dokumentasi resmi antarmuka pemrograman aplikasi (API) untuk sistem backend **KLINIKU v1.0** berdasarkan Dokumen Perancangan Perangkat Lunak (DPPL) Kelompok 5 D3 Teknik Informatika SV UNS. Seluruh endpoint dirancang menggunakan arsitektur RESTful JSON dan ditujukan untuk integrasi dengan aplikasi frontend (Portal Pasien, Antarmuka Admin Loket, Portal Dokter Poliklinik, dan Monitor Display TV Ruang Tunggu).

Sistem backend ini mengimplementasikan seluruh 9 User Story utama (US-01 s/d US-09), arsitektur keamanan tingkat tinggi (bcrypt, Google reCAPTCHA v2, SMTP Email OTP, enkripsi simetris **AES-256** untuk rekam medis sesuai amanat UU PDP No. 27/2022, rate limiting, dan RBAC), serta penggantian seluruh saluran notifikasi WhatsApp menjadi **SMTP Email Resmi** (`lintarkertapati@student.uns.ac.id` via `smtp.gmail.com:587 TLS`).

---

## Daftar Isi
1. [Informasi Umum & Konvensi API](#1-informasi-umum--konvensi-api)
   - [Base URL & Format Data](#base-url--format-data)
   - [Header Standar Request](#header-standar-request)
   - [Standar HTTP Status Code](#standar-http-status-code)
   - [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
   - [Daftar Poliklinik & Prefix Nomor Antrean](#daftar-poliklinik--prefix-nomor-antrean)
2. [Matriks Endpoint & Pemetaan User Story (DPPL)](#2-matriks-endpoint--pemetaan-user-story-dppl)
3. [Katalog Endpoint Lengkap](#3-katalog-endpoint-lengkap)
   - [3.1 Autentikasi & Akun Pengguna (US-01)](#31-autentikasi--akun-pengguna-us-01)
     - [`POST /api/v1/register` — Registrasi Pasien Baru](#311-registrasi-pasien-baru)
     - [`POST /api/v1/check-nik` — Cek Validitas & Ketersediaan NIK](#312-cek-validitas--ketersediaan-nik)
     - [`GET /api/v1/polis` — Master Data Poliklinik](#313-master-data-poliklinik)
     - [`POST /api/v1/login` — Login Pasien (reCAPTCHA v2 + Kirim OTP Email)](#314-login-pasien-recaptcha-v2--kirim-otp-email)
     - [`POST /api/v1/verify-otp` — Verifikasi Kode OTP 6-Digit & Terbit JWT](#315-verifikasi-kode-otp-6-digit--terbit-jwt)
     - [`POST /api/v1/resend-otp` — Kirim Ulang Kode OTP ke Email](#316-kirim-ulang-kode-otp-ke-email)
     - [`GET /api/v1/me` — Profil Pengguna Login (Protected)](#317-profil-pengguna-login-protected)
     - [`POST /api/v1/logout` — Logout Pengguna (Protected)](#318-logout-pengguna-protected)
   - [3.2 Pemesanan Antrean Online & Quota Engine (US-03)](#32-pemesanan-antrean-online--quota-engine-us-03)
     - [`GET /api/v1/booking/dokter` — Daftar Dokter & Jadwal Praktik](#321-daftar-dokter--jadwal-praktik)
     - [`POST /api/v1/booking/cek-kuota` — Cek Kuota Dokter H-7 s/d Hari H](#322-cek-kuota-dokter-h-7-sd-hari-h)
     - [`POST /api/v1/booking` — Buat Pemesanan Antrean Online](#323-buat-pemesanan-antrean-online)
     - [`GET /api/v1/booking/{id}/tiket` — Detail E-Tiket & QR Code](#324-detail-e-tiket--qr-code)
     - [`POST /api/v1/booking/{id}/batal` — Batalkan Pemesanan Antrean](#325-batalkan-pemesanan-antrean)
   - [3.3 Live Queue Tracker & Monitor TV Ruang Tunggu (US-02)](#33-live-queue-tracker--monitor-tv-ruang-tunggu-us-02)
     - [`GET /api/v1/antrean/saya` — Jadwal Saya (Antrean Milik Pasien)](#331-jadwal-saya-antrean-milik-pasien)
     - [`GET /api/v1/antrean/live/{id}` — Live Tracking Real-Time Antrean](#332-live-tracking-real-time-antrean)
     - [`GET /api/v1/antrean/poli/{poli_id}/hari-ini` — Antrean Poli Hari Ini](#333-antrean-poli-hari-ini)
     - [`GET /api/v1/display/tv` — Display Monitor TV & Audio TTS](#334-display-monitor-tv--audio-tts)
   - [3.4 Admin Loket: QR Check-in & Walk-in Registration (US-08)](#34-admin-loket-qr-check-in--walk-in-registration-us-08)
     - [`POST /api/v1/loket/checkin-qr` — Scan QR Check-in Pasien di Loket](#341-scan-qr-check-in-pasien-di-loket)
     - [`POST /api/v1/loket/pendaftaran-walkin` — Pendaftaran Walk-in & Tiket Termal](#342-pendaftaran-walk-in--tiket-termal)
     - [`GET /api/v1/loket/antrean-hari-ini` — Monitoring Antrean Loket Hari Ini](#343-monitoring-antrean-loket-hari-ini)
   - [3.5 Admin Loket: Pemanggilan Antrean Multi-Channel & TTS (US-04)](#35-admin-loket-pemanggilan-antrean-multi-channel--tts-us-04)
     - [`POST /api/v1/loket/antrean/{id}/panggil` — Panggil Antrean (TV TTS + Notif Email)](#351-panggil-antrean-tv-tts--notif-email)
     - [`POST /api/v1/loket/antrean/{id}/panggil-ulang` — Panggil Ulang (Recall)](#352-panggil-ulang-recall)
     - [`POST /api/v1/loket/antrean/{id}/lewati` — Lewati Antrean (Skip)](#353-lewati-antrean-skip)
     - [`POST /api/v1/loket/antrean/{id}/layani` — Update Status Sedang Diperiksa](#354-update-status-sedang-diperiksa)
   - [3.6 Dokter Poliklinik: Antrean & Rekam Medis AES-256 (US-05)](#36-dokter-poliklinik-antrean--rekam-medis-aes-256-us-05)
     - [`GET /api/v1/dokter/antrean-hari-ini` — Daftar Pasien Dokter Hari Ini](#361-daftar-pasien-dokter-hari-ini)
     - [`GET /api/v1/dokter/pasien/{pasien_user_id}/rekam-medis` — Riwayat Pasien (AES-256 Decrypted)](#362-riwayat-pasien-aes-256-decrypted)
     - [`POST /api/v1/dokter/rekam-medis` — Simpan Pemeriksaan (AES-256 Encrypted)](#363-simpan-pemeriksaan-aes-256-encrypted)
   - [3.7 Pasien: Portal Mandiri Rekam Medis Pribadi (US-07)](#37-pasien-portal-mandiri-rekam-medis-pribadi-us-07)
     - [`GET /api/v1/pasien/riwayat-medis` — Daftar Riwayat Rekam Medis Pribadi](#371-daftar-riwayat-rekam-medis-pribadi)
     - [`GET /api/v1/pasien/riwayat-medis/{id}` — Detail Berkas Rekam Medis Pribadi](#372-detail-berkas-rekam-medis-pribadi)
   - [3.8 Admin Loket: Pengelolaan Jadwal Praktik & Kuota Dokter (US-06)](#38-admin-loket-pengelolaan-jadwal-praktik--kuota-dokter-us-06)
     - [`GET /api/v1/admin/jadwal` — Daftar Jadwal Praktik & Kuota Dokter](#381-daftar-jadwal-praktik--kuota-dokter)
     - [`POST /api/v1/admin/jadwal` — Tambah Jadwal Praktik Baru](#382-tambah-jadwal-praktik-baru)
     - [`PUT /api/v1/admin/jadwal/{id}` — Update Jadwal, Kuota & Cuti Dokter](#383-update-jadwal-kuota--cuti-dokter)
     - [`DELETE /api/v1/admin/jadwal/{id}` — Hapus Jadwal Praktik Dokter](#384-hapus-jadwal-praktik-dokter)
   - [3.9 Admin Loket: Rekapitulasi Laporan Kunjungan & Export CSV (US-09)](#39-admin-loket-rekapitulasi-laporan-kunjungan--export-csv-us-09)
     - [`GET /api/v1/admin/laporan-kunjungan` — Agregasi Metrik & Rekap Kunjungan](#391-agregasi-metrik--rekap-kunjungan)
     - [`GET /api/v1/admin/laporan-kunjungan/export` — Download Laporan Format CSV](#392-download-laporan-format-csv)
4. [Arsitektur Keamanan & Enkripsi Data](#4-arsitektur-keamanan--enkripsi-data)
5. [Alur Integrasi Frontend (End-to-End Workflows)](#5-alur-integrasi-frontend-end-to-end-workflows)
6. [Pengujian Otomatis & Verifikasi Sistem (PHPUnit)](#6-pengujian-otomatis--verifikasi-sistem-phpunit)

---

## 1. Informasi Umum & Konvensi API

### Base URL & Format Data
- **Base URL Utama (v1)**: `http://localhost:8080/api/v1`
- **Alias Kompatibilitas**: `http://localhost:8080/api` (contoh: `/api/login` mengarah ke handler yang sama dengan `/api/v1/login`)
- **Format Pertukaran Data**: JSON (`application/json`)
- **Karakter Set**: `UTF-8`

### Header Standar Request
Untuk semua endpoint berbasis `POST`, `PUT`, `PATCH`, dan `DELETE`:
```http
Content-Type: application/json
Accept: application/json
```

Untuk endpoint terproteksi autentikasi (Protected via JWT):
```http
Authorization: Bearer <JWT_TOKEN>
```

### Standar HTTP Status Code
| Kode Status | Keterangan | Skenario Penggunaan |
| :--- | :--- | :--- |
| `200 OK` | Berhasil | Permintaan berhasil diproses (baca data, update status, pemanggilan). |
| `201 Created` | Berhasil Dibuat | Entitas baru berhasil dibuat (registrasi, antrean baru, rekam medis, jadwal). |
| `400 Bad Request` | Permintaan Tidak Valid | Parameter query atau format payload tidak sesuai standar. |
| `401 Unauthorized` | Belum Login / Token Invalid | Token JWT tidak disertakan, kedaluwarsa, atau tanda tangan tidak valid. |
| `403 Forbidden` | Akses Ditolak (RBAC) | Peran pengguna tidak diizinkan mengakses resource (misal: pasien mengakses loket). |
| `404 Not Found` | Tidak Ditemukan | ID antrean, pasien, dokter, atau jadwal tidak ada di basis data. |
| `410 Gone` | Sesi Kedaluwarsa | Sesi verifikasi OTP email telah melewati batas waktu (5 menit). |
| `422 Unprocessable Entity` | Validasi Gagal | Kuota penuh, format input keliru, atau pelanggaran aturan bisnis klinik. |
| `429 Too Many Requests` | Rate Limit Terlampaui | Melebihi ambang batas pemanggilan endpoint per menit (Throttle). |
| `500 Server Error` | Kesalahan Internal Server | Terjadi pengecualian database atau kegagalan sistem internal. |

### Role-Based Access Control (RBAC)
Sistem KLINIKU membagi hak akses ke dalam 3 peran (*roles*):
1. **`pasien`** (Format ID: `USR-PAS-XXXX`):
   - Akses: Cek kuota, booking online, melihat e-tiket, pembatalan antrean online, tracking live antrean pribadi, melihat riwayat rekam medis pribadi.
2. **`dokter`** (Format ID: `USR-DOC-XXXX`):
   - Akses: Melihat antrean pasien pada polikliniknya hari ini, melihat riwayat rekam medis pasien terdahulu, menyimpan hasil pemeriksaan (EMR AES-256).
3. **`staf`** (Format ID: `USR-ADM-XXXX`):
   - Akses: Scan QR check-in, pendaftaran walk-in, pemanggilan antrean (TV TTS + email), recall, skip, layani, kelola jadwal & kuota dokter, serta laporan kunjungan & ekspor CSV.

### Daftar Poliklinik & Prefix Nomor Antrean
Alokasi kode antrean menggunakan prefix poliklinik diikuti 3 digit nomor urut harian:
| ID Poli | Nama Poliklinik | Prefix Nomor | Format Contoh |
| :--- | :--- | :--- | :--- |
| `1` | Poli Umum | `UM` | `UM-001`, `UM-002`, ... |
| `2` | Poli Geriatri | `GER` | `GER-001`, `GER-002`, ... |
| `3` | Poli KIA (Kesehatan Ibu & Anak) | `KIA` | `KIA-001`, `KIA-002`, ... |
| `4` | Poli Gigi & Mulut | `GIG` | `GIG-001`, `GIG-002`, ... |
| `5` | Poli Kandungan | `KND` | `KND-001`, `KND-002`, ... |

---

## 2. Matriks Endpoint & Pemetaan User Story (DPPL)

| User Story | Modul / Fitur | Method | Endpoint URL | Akses Peran |
| :--- | :--- | :--- | :--- | :--- |
| **US-01** | Registrasi Pasien Baru | `POST` | `/api/v1/register` | Publik |
| **US-01** | Cek Duplikasi NIK | `POST` | `/api/v1/check-nik` | Publik |
| **US-01** | Master Data Poliklinik | `GET` | `/api/v1/polis` | Publik |
| **US-01** | Login Pasien (reCAPTCHA + Kirim OTP Email) | `POST` | `/api/v1/login` | Publik (Throttle: 5/mnt) |
| **US-01** | Verifikasi Email OTP 6-Digit & Terbit JWT | `POST` | `/api/v1/verify-otp` | Publik (Throttle: 10/mnt) |
| **US-01** | Kirim Ulang Email OTP | `POST` | `/api/v1/resend-otp` | Publik (Throttle: 3/mnt) |
| **US-01** | Profil Pengguna Login | `GET` | `/api/v1/me` | Authenticated |
| **US-01** | Logout Pengguna | `POST` | `/api/v1/logout` | Authenticated |
| **US-03** | Daftar Dokter & Jadwal Praktik | `GET` | `/api/v1/booking/dokter` | Publik / Pasien |
| **US-03** | Cek Kuota Dokter H-7 s/d Hari H | `POST` | `/api/v1/booking/cek-kuota` | Publik / Pasien |
| **US-03** | Buat Pemesanan Antrean Online | `POST` | `/api/v1/booking` | `role:pasien` |
| **US-03** | Detail E-Tiket & QR Code | `GET` | `/api/v1/booking/{id}/tiket` | Authenticated |
| **US-03** | Batalkan Pemesanan Antrean | `POST` | `/api/v1/booking/{id}/batal` | `role:pasien` |
| **US-02** | Jadwal Saya (Antrean Milik Pasien) | `GET` | `/api/v1/antrean/saya` | `role:pasien` |
| **US-02** | Live Tracking Real-Time Antrean | `GET` | `/api/v1/antrean/live/{id}` | Publik / Pasien |
| **US-02** | Antrean Poli Hari Ini | `GET` | `/api/v1/antrean/poli/{id}/hari-ini` | Publik |
| **US-02** | Display Monitor TV & Audio TTS | `GET` | `/api/v1/display/tv` | Publik |
| **US-08** | Scan QR Check-in Pasien di Loket | `POST` | `/api/v1/loket/checkin-qr` | `role:staf` |
| **US-08** | Pendaftaran Pasien Walk-in (Tiket Termal) | `POST` | `/api/v1/loket/pendaftaran-walkin` | `role:staf` |
| **US-08** | Monitoring Antrean Loket Hari Ini | `GET` | `/api/v1/loket/antrean-hari-ini` | `role:staf` |
| **US-04** | Panggil Antrean (TV TTS + Notif Email) | `POST` | `/api/v1/loket/antrean/{id}/panggil` | `role:staf` |
| **US-04** | Panggil Ulang Antrean (Recall) | `POST` | `/api/v1/loket/antrean/{id}/panggil-ulang` | `role:staf` |
| **US-04** | Lewati Antrean Pasien (Skip) | `POST` | `/api/v1/loket/antrean/{id}/lewati` | `role:staf` |
| **US-04** | Update Status Sedang Diperiksa | `POST` | `/api/v1/loket/antrean/{id}/layani` | `role:staf` |
| **US-05** | Antrean Pasien Dokter Hari Ini | `GET` | `/api/v1/dokter/antrean-hari-ini` | `role:dokter` |
| **US-05** | Riwayat Medis Pasien (Decrypted AES-256) | `GET` | `/api/v1/dokter/pasien/{id}/rekam-medis` | `role:dokter` |
| **US-05** | Simpan Pemeriksaan EMR (Encrypted AES-256) | `POST` | `/api/v1/dokter/rekam-medis` | `role:dokter` |
| **US-07** | Riwayat Rekam Medis Pribadi Pasien | `GET` | `/api/v1/pasien/riwayat-medis` | `role:pasien` |
| **US-07** | Detail Berkas Rekam Medis Pribadi | `GET` | `/api/v1/pasien/riwayat-medis/{id}` | `role:pasien` |
| **US-06** | Lihat Jadwal & Kuota Praktik Dokter | `GET` | `/api/v1/admin/jadwal` | `role:staf` |
| **US-06** | Tambah Jadwal Praktik Dokter | `POST` | `/api/v1/admin/jadwal` | `role:staf` |
| **US-06** | Update Jadwal, Kuota & Status Cuti | `PUT` | `/api/v1/admin/jadwal/{id}` | `role:staf` |
| **US-06** | Hapus Jadwal Praktik Dokter | `DELETE` | `/api/v1/admin/jadwal/{id}` | `role:staf` |
| **US-09** | Rekapitulasi Laporan Kunjungan | `GET` | `/api/v1/admin/laporan-kunjungan` | `role:staf` |
| **US-09** | Export Rekapitulasi Format CSV | `GET` | `/api/v1/admin/laporan-kunjungan/export` | `role:staf` |

---

## 3. Katalog Endpoint Lengkap

### 3.1 Autentikasi & Akun Pengguna (US-01)

#### 3.1.1 Registrasi Pasien Baru
- **Endpoint**: `POST /api/v1/register`
- **Akses**: Publik
- **Deskripsi**: Mendaftarkan akun pasien baru, auto-generate Nomor Rekam Medis (`RM-YYYY-XXXX`), dan auto-derive jenis kelamin serta tanggal lahir dari NIK jika tidak disertakan.
- **Payload Request**:
```json
{
  "nik": "3372020912950001",
  "nama": "Budi Santoso",
  "nowa": "081234567890",
  "email": "budi.santoso@gmail.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "jeniskelamin": "Laki-laki",
  "tgllahir": "1995-12-09",
  "alamat": "Jl. Slamet Riyadi No. 45, Surakarta",
  "nobpjs": "0001234567891",
  "goldarah": "O",
  "alergiobat": "Tidak ada",
  "poli_id": 1
}
```
- **Response Berhasil (201 Created)**:
```json
{
  "success": true,
  "message": "Registrasi pasien berhasil. Silakan login untuk melakukan pemesanan antrean.",
  "data": {
    "user_id": "USR-PAS-0002",
    "norm": "RM-2026-0002",
    "nama": "Budi Santoso",
    "email": "budi.santoso@gmail.com"
  }
}
```
- **Response Error (422 Unprocessable Entity)**:
```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "nik": ["NIK sudah terdaftar dalam sistem."],
    "password": ["Konfirmasi password tidak cocok."]
  }
}
```

#### 3.1.2 Cek Validitas & Ketersediaan NIK
- **Endpoint**: `POST /api/v1/check-nik`
- **Akses**: Publik
- **Payload Request**:
```json
{
  "nik": "3372020912950001"
}
```
- **Response Berhasil (200 OK)**:
```json
{
  "success": true,
  "valid": true,
  "available": true,
  "message": "NIK valid dan tersedia untuk pendaftaran.",
  "details": {
    "gender": "Laki-laki",
    "birth_date": "1995-12-09",
    "age": 30
  }
}
```

#### 3.1.3 Master Data Poliklinik
- **Endpoint**: `GET /api/v1/polis`
- **Akses**: Publik
- **Response Berhasil (200 OK)**:
```json
{
  "success": true,
  "data": [
    { "poli_id": 1, "poli_nama": "Poli Umum", "poli_keterangan": null },
    { "poli_id": 2, "poli_nama": "Poli Geriatri", "poli_keterangan": null },
    { "poli_id": 3, "poli_nama": "Poli KIA", "poli_keterangan": null },
    { "poli_id": 4, "poli_nama": "Poli Gigi & Mulut", "poli_keterangan": null },
    { "poli_id": 5, "poli_nama": "Poli Kandungan", "poli_keterangan": null }
  ]
}
```

#### 3.1.4 Login Pasien (reCAPTCHA v2 & Kirim OTP Email)
- **Endpoint**: `POST /api/v1/login`
- **Akses**: Publik (Throttle: 5 request/menit)
- **Deskripsi**: Verifikasi NIK, password bcrypt, dan Google reCAPTCHA v2. Jika valid, sistem membangkitkan kode OTP 6-digit dan mengirimkannya ke email terdaftar pasien via SMTP Gmail TLS.
- **Payload Request**:
```json
{
  "nik": "3372020912950001",
  "password": "Password123!",
  "recaptcha_token": "TOKEN_RECAPTCHA_DARI_FRONTEND"
}
```
- **Response Berhasil (200 OK)**:
```json
{
  "success": true,
  "message": "Kredensial valid. Kode OTP 6-digit telah dikirimkan ke email Anda (bu***@gmail.com).",
  "data": {
    "otp_session_id": "otp_6f9a8b1c2d3e4f...",
    "email_masked": "bu***@gmail.com",
    "expires_in_seconds": 300
  }
}
```
- **Response Error (422 Unprocessable Entity)**:
```json
{
  "success": false,
  "errors": {
    "recaptcha_token": ["Verifikasi bot reCAPTCHA tidak valid atau telah kedaluwarsa."]
  }
}
```

#### 3.1.5 Verifikasi Kode OTP 6-Digit & Terbit JWT
- **Endpoint**: `POST /api/v1/verify-otp`
- **Akses**: Publik (Throttle: 10 request/menit)
- **Deskripsi**: Memvalidasi kode OTP 6-digit yang dikirimkan ke email. Sesi berlaku selama 5 menit dan terkunci otomatis jika terjadi 5 kali kesalahan input berturut-turut.
- **Payload Request**:
```json
{
  "otp_session_id": "otp_6f9a8b1c2d3e4f...",
  "otp_code": "824195"
}
```
- **Response Berhasil (200 OK)**:
```json
{
  "success": true,
  "message": "Verifikasi OTP berhasil. Selamat datang di KLINIKU.",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "user": {
      "user_id": "USR-PAS-0002",
      "user_role": "pasien",
      "user_nama": "Budi Santoso",
      "user_email": "budi.santoso@gmail.com",
      "norm": "RM-2026-0002"
    }
  }
}
```
- **Response Error (410 Gone - Sesi Kedaluwarsa)**:
```json
{
  "success": false,
  "message": "Sesi OTP telah kedaluwarsa. Silakan lakukan login ulang atau minta kirim ulang kode."
}
```

#### 3.1.6 Kirim Ulang Kode OTP ke Email
- **Endpoint**: `POST /api/v1/resend-otp`
- **Akses**: Publik (Throttle: 3 request/menit)
- **Payload Request**:
```json
{
  "otp_session_id": "otp_6f9a8b1c2d3e4f..."
}
```
- **Response Berhasil (200 OK)**:
```json
{
  "success": true,
  "message": "Kode OTP baru telah berhasil dikirim ulang ke email Anda.",
  "data": {
    "otp_session_id": "otp_7a8b9c0d1e...",
    "expires_in_seconds": 300
  }
}
```

#### 3.1.7 Profil Pengguna Login (Protected)
- **Endpoint**: `GET /api/v1/me`
- **Akses**: `Bearer Token` (Semua Role)
- **Header**: `Authorization: Bearer <JWT_TOKEN>`
- **Response Berhasil (200 OK)**:
```json
{
  "success": true,
  "data": {
    "user_id": "USR-PAS-0002",
    "user_role": "pasien",
    "user_nama": "Budi Santoso",
    "user_nik": "3372020912950001",
    "user_email": "budi.santoso@gmail.com",
    "user_nowa": "081234567890",
    "profile": {
      "norm": "RM-2026-0002",
      "jeniskelamin": "Laki-laki",
      "tgllahir": "1995-12-09",
      "alamat": "Jl. Slamet Riyadi No. 45, Surakarta",
      "nobpjs": "0001234567891",
      "goldarah": "O"
    }
  }
}
```

#### 3.1.8 Logout Pengguna (Protected)
- **Endpoint**: `POST /api/v1/logout`
- **Akses**: `Bearer Token`
- **Response Berhasil (200 OK)**:
```json
{
  "success": true,
  "message": "Berhasil logout. Sesi Anda telah diakhiri."
}
```

---

### 3.2 Pemesanan Antrean Online & Quota Engine (US-03)

#### 3.2.1 Daftar Dokter & Jadwal Praktik
- **Endpoint**: `GET /api/v1/booking/dokter`
- **Akses**: Publik / Pasien
- **Query Params**: `?poli_id=1` *(opsional, filter poliklinik)*
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": [
    {
      "dokter_user_id": "USR-DOC-001",
      "nama": "dr. Andi Pratama, Sp.PD",
      "spesialisasi": "Penyakit Dalam",
      "sip": "503/SIP.01/DKS/2023",
      "kuota_harian": 25,
      "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
      "jadwal": [
        {
          "jadwal_id": 1,
          "hari_angka": 1,
          "hari_nama": "Senin",
          "jam_mulai": "08:00:00",
          "jam_selesai": "12:00:00",
          "kuota": 25,
          "status": "aktif"
        }
      ]
    }
  ]
}
```

#### 3.2.2 Cek Kuota Dokter H-7 s/d Hari H
- **Endpoint**: `POST /api/v1/booking/cek-kuota`
- **Akses**: Publik / Pasien
- **Deskripsi**: Memeriksa ketersediaan kuota dokter pada tanggal periksa. Validasi membatasi pemesanan hanya boleh dilakukan maksimal H-7 (7 hari ke depan) s/d Hari H.
- **Payload Request**:
```json
{
  "dokter_user_id": "USR-DOC-001",
  "tanggal": "2026-11-26"
}
```
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "tanggal": "2026-11-26",
    "available": true,
    "max_quota": 25,
    "booked": 8,
    "remaining": 17,
    "message": "Kuota periksa tersedia (17 slot tersisa dari kuota 25).",
    "dokter": {
      "dokter_user_id": "USR-DOC-001",
      "nama": "dr. Andi Pratama, Sp.PD",
      "spesialisasi": "Penyakit Dalam",
      "poli": "Poli Umum"
    },
    "jadwal": {
      "jadwal_id": 4,
      "jam_mulai": "08:00:00",
      "jam_selesai": "12:00:00",
      "status": "aktif"
    }
  }
}
```
- **Response Error (422 Unprocessable Entity - Tanggal di Luar Rentang)**:
```json
{
  "status": "error",
  "message": "Pemesanan online hanya dapat dilakukan maksimal H-7 (7 hari ke depan)."
}
```

#### 3.2.3 Buat Pemesanan Antrean Online
- **Endpoint**: `POST /api/v1/booking`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Deskripsi**: Mengunci slot kuota dokter, membangkitkan nomor antrean atomik, menerbitkan token QR Code e-tiket (`KLN-QR-XXXXX`), status awal `menunggu_checkin`, dan mengirimkan E-Tiket ke email pasien via SMTP.
- **Payload Request**:
```json
{
  "dokter_user_id": "USR-DOC-001",
  "tanggal": "2026-11-26",
  "keluhan": "Demam dan batuk berdahak 3 hari"
}
```
- **Response Berhasil (201 Created)**:
```json
{
  "status": "success",
  "message": "Pemesanan antrean berhasil! E-tiket telah dibuat dan dikirim ke email Anda.",
  "data": {
    "antrean_id": 42,
    "antrean_nomor": "UM-009",
    "antrean_angka": 9,
    "antrean_tanggal": "2026-11-26",
    "antrean_status": "menunggu_checkin",
    "antrean_qr_code": "KLN-QR-A9F7B12C84E6D012",
    "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
    "dokter": {
      "dokter_user_id": "USR-DOC-001",
      "nama": "dr. Andi Pratama, Sp.PD",
      "spesialisasi": "Penyakit Dalam"
    },
    "jadwal": { "jam_mulai": "08:00:00", "jam_selesai": "12:00:00" },
    "catatan": "Demam dan batuk berdahak 3 hari"
  }
}
```

#### 3.2.4 Detail E-Tiket & QR Code
- **Endpoint**: `GET /api/v1/booking/{id}/tiket`
- **Akses**: `Bearer Token` (Pemilik tiket / Staf)
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "antrean_id": 42,
    "antrean_nomor": "UM-009",
    "antrean_angka": 9,
    "antrean_tanggal": "2026-11-26",
    "antrean_status": "menunggu_checkin",
    "antrean_qr_code": "KLN-QR-A9F7B12C84E6D012",
    "antrean_jenis": "online",
    "waktu_checkin": null,
    "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
    "dokter": { "nama": "dr. Andi Pratama, Sp.PD", "spesialisasi": "Penyakit Dalam" },
    "pasien": { "nama": "Budi Santoso", "nik": "3372020912950001", "norm": "RM-2026-0002" }
  }
}
```

#### 3.2.5 Batalkan Pemesanan Antrean
- **Endpoint**: `POST /api/v1/booking/{id}/batal`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Aturan**: Hanya tiket berstatus `menunggu_checkin` yang dapat dibatalkan. Setelah dibatalkan, slot kuota dokter otomatis kembali terbuka.
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "message": "Pemesanan antrean UM-009 berhasil dibatalkan.",
  "data": {
    "antrean_id": 42,
    "antrean_status": "batal"
  }
}
```

---

### 3.3 Live Queue Tracker & Monitor TV Ruang Tunggu (US-02)

#### 3.3.1 Jadwal Saya (Antrean Milik Pasien)
- **Endpoint**: `GET /api/v1/antrean/saya`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Query Params**: `?tipe=aktif` *(pilihan: `aktif`, `riwayat`, `semua`, default: `semua`)*
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": [
    {
      "antrean_id": 42,
      "antrean_nomor": "UM-009",
      "antrean_angka": 9,
      "antrean_tanggal": "2026-11-26",
      "antrean_status": "menunggu_checkin",
      "antrean_jenis": "online",
      "antrean_qr_code": "KLN-QR-A9F7B12C84E6D012",
      "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
      "dokter": { "nama": "dr. Andi Pratama, Sp.PD" }
    }
  ]
}
```

#### 3.3.2 Live Tracking Real-Time Antrean
- **Endpoint**: `GET /api/v1/antrean/live/{antrean_id}`
- **Akses**: Publik / Pasien
- **Deskripsi**: Menghitung secara real-time posisi nomor antrean yang sedang dilayani, sisa pasien di depan, total pasien menunggu di poli, dan estimasi waktu tunggu (10 menit/pasien).
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "tiket": {
      "antrean_id": 42,
      "antrean_nomor": "UM-009",
      "antrean_angka": 9,
      "antrean_tanggal": "2026-11-26",
      "antrean_status": "menunggu",
      "antrean_jenis": "online",
      "antrean_qr_code": "KLN-QR-A9F7B12C84E6D012",
      "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
      "dokter": { "nama": "dr. Andi Pratama, Sp.PD" }
    },
    "live_status": {
      "sedang_dilayani": {
        "antrean_id": 38,
        "antrean_nomor": "UM-005",
        "antrean_angka": 5,
        "antrean_status": "sedang_diperiksa",
        "waktu_panggil": "09:15:30"
      },
      "sisa_antrean_di_depan": 3,
      "total_menunggu_poli": 7,
      "total_selesai_poli": 4,
      "estimasi_waktu_tunggu_menit": 30,
      "pesan": "Masih terdapat 3 pasien di depan Anda. Harap menunggu panggilan."
    }
  }
}
```

#### 3.3.3 Antrean Poli Hari Ini
- **Endpoint**: `GET /api/v1/antrean/poli/{poli_id}/hari-ini`
- **Akses**: Publik
- **Query Params**: `?tanggal=2026-11-26` *(opsional, default: hari ini)*
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
    "tanggal": "2026-11-26",
    "total_antrean": 12,
    "antrean": [
      {
        "antrean_id": 35,
        "antrean_nomor": "UM-001",
        "antrean_angka": 1,
        "antrean_status": "selesai",
        "pasien_nama": "Ahmad Dani"
      }
    ]
  }
}
```

#### 3.3.4 Display Monitor TV & Audio TTS
- **Endpoint**: `GET /api/v1/display/tv`
- **Akses**: Publik
- **Deskripsi**: Khusus untuk antarmuka TV monitor ruang tunggu. Menyediakan status nomor yang dipanggil per poliklinik dan string teks sintesis suara Text-to-Speech (TTS) untuk memicu chime panggilan otomatis di frontend.
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "tanggal": "2026-11-24",
    "timestamp": "2026-11-24T10:45:00+07:00",
    "display_poli": [
      {
        "poli_id": 1,
        "poli_nama": "Poli Umum",
        "nomor_dipanggil": "UM-006",
        "antrean_id": 39,
        "status": "dipanggil",
        "panggilan_ke": 1,
        "waktu_panggil": "09:40:15",
        "teks_tts": "Nomor antrean UM-006, silakan menuju ke Poli Umum.",
        "sisa_antrean": 5,
        "total_antrean": 12
      }
    ],
    "panggilan_terbaru": {
      "antrean_id": 39,
      "antrean_nomor": "UM-006",
      "poli_nama": "Poli Umum",
      "teks_panggilan": "Nomor antrean UM-006, silakan menuju Poli Umum.",
      "waktu_panggil": "2026-11-24 09:40:15"
    }
  }
}
```

---

### 3.4 Admin Loket: QR Check-in & Walk-in Registration (US-08)

#### 3.4.1 Scan QR Check-in Pasien di Loket
- **Endpoint**: `POST /api/v1/loket/checkin-qr`
- **Akses**: `Bearer Token` (`role:staf`)
- **Deskripsi**: Scan QR Code tiket online setibanya pasien di klinik. Mengubah status tiket dari `menunggu_checkin` menjadi `menunggu` dan mencatat `antrean_waktu_checkin`. Validasi menolak jika tanggal periksa bukan hari ini atau sudah check-in sebelumnya.
- **Payload Request**:
```json
{
  "qr_code": "KLN-QR-A9F7B12C84E6D012"
}
```
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "message": "Check-in berhasil untuk antrean UM-009. Pasien telah masuk dalam daftar tunggu pemeriksaan.",
  "data": {
    "antrean_id": 42,
    "antrean_nomor": "UM-009",
    "antrean_angka": 9,
    "antrean_status": "menunggu",
    "waktu_checkin": "08:15:20",
    "pasien": {
      "nama": "Budi Santoso",
      "norm": "RM-2026-0002",
      "nik": "3372020912950001"
    },
    "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
    "dokter": { "nama": "dr. Andi Pratama, Sp.PD" }
  }
}
```

#### 3.4.2 Pendaftaran Walk-in & Tiket Termal
- **Endpoint**: `POST /api/v1/loket/pendaftaran-walkin`
- **Akses**: `Bearer Token` (`role:staf`)
- **Deskripsi**: Pendaftaran pasien yang datang langsung ke loket. Dapat mendaftarkan pasien lama (via `pasien_user_id`) atau pasien baru langsung di loket. Status antrean langsung menjadi `menunggu` (tanpa perlu check-in lagi) dan mengembalikan data cetak tiket fisik termal.
- **Payload Request (Pasien Baru)**:
```json
{
  "nik": "3372021504900003",
  "nama": "Siti Rahmawati",
  "nowa": "081299887755",
  "email": "siti.rahma@gmail.com",
  "jeniskelamin": "Perempuan",
  "tgllahir": "1990-04-15",
  "alamat": "Jl. Adi Sucipto No. 12, Surakarta",
  "dokter_user_id": "USR-DOC-001",
  "keluhan": "Sakit kepala berdenyut"
}
```
- **Response Berhasil (201 Created)**:
```json
{
  "status": "success",
  "message": "Pendaftaran walk-in berhasil. Nomor antrean UM-010 siap dicetak pada printer termal.",
  "data": {
    "antrean_id": 43,
    "antrean_nomor": "UM-010",
    "antrean_angka": 10,
    "antrean_tanggal": "2026-11-24",
    "antrean_status": "menunggu",
    "antrean_jenis": "walkin",
    "antrean_qr_code": "KLN-QR-B8C2D3E4F5A60123",
    "waktu_cetak": "2026-11-24 08:30:00",
    "pasien": {
      "user_id": "USR-PAS-0003",
      "nama": "Siti Rahmawati",
      "norm": "RM-2026-0003"
    },
    "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
    "dokter": { "nama": "dr. Andi Pratama, Sp.PD" }
  }
}
```

#### 3.4.3 Monitoring Antrean Loket Hari Ini
- **Endpoint**: `GET /api/v1/loket/antrean-hari-ini`
- **Akses**: `Bearer Token` (`role:staf`)
- **Query Params**: `?poli_id=1&status=menunggu` *(opsional)*
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "tanggal": "2026-11-24",
    "total": 15,
    "antrean": [
      {
        "antrean_id": 42,
        "antrean_nomor": "UM-009",
        "antrean_angka": 9,
        "antrean_status": "menunggu",
        "antrean_jenis": "online",
        "antrean_panggilan_ke": 0,
        "waktu_checkin": "08:15:20",
        "pasien": { "nama": "Budi Santoso", "norm": "RM-2026-0002" }
      }
    ]
  }
}
```

---

### 3.5 Admin Loket: Pemanggilan Antrean Multi-Channel & TTS (US-04)

#### 3.5.1 Panggil Antrean (TV TTS + Notif Email)
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/panggil`
- **Akses**: `Bearer Token` (`role:staf`)
- **Efek Sistem**:
  1. Status antrean berubah menjadi `dipanggil`.
  2. Nilai `antrean_panggilan_ke` bertambah 1.
  3. Memperbarui string TTS audio pada endpoint TV monitor.
  4. Mengirimkan **Email Panggilan via SMTP** ke email pasien (`QueueCalledMail`) berisi nomor antrean, nama poli, dan nama dokter (menggantikan saluran WhatsApp).
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "message": "Antrean UM-009 berhasil dipanggil (panggilan ke-1).",
  "data": {
    "antrean_id": 42,
    "antrean_nomor": "UM-009",
    "antrean_status": "dipanggil",
    "antrean_panggilan_ke": 1,
    "waktu_panggil": "08:45:10",
    "teks_tts": "Nomor antrean UM-009, silakan menuju Poli Umum.",
    "email_terkirim": true
  }
}
```

#### 3.5.2 Panggil Ulang (Recall)
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/panggil-ulang`
- **Akses**: `Bearer Token` (`role:staf`)
- **Deskripsi**: Memanggil ulang antrean tanpa mereset nomor urut, meng-update `waktu_panggil`, dan mengirimkan email notifikasi ulang.
- **Response Berhasil (200 OK)**: Format sama dengan 3.5.1 dengan `antrean_panggilan_ke: 2`.

#### 3.5.3 Lewati Antrean (Skip)
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/lewati`
- **Akses**: `Bearer Token` (`role:staf`)
- **Deskripsi**: Menandai antrean dilewati jika pasien tidak hadir setelah dipanggil berulang kali. Status menjadi `dilewati`.
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "message": "Antrean UM-009 telah dilewati.",
  "data": {
    "antrean_id": 42,
    "antrean_nomor": "UM-009",
    "antrean_status": "dilewati"
  }
}
```

#### 3.5.4 Update Status Sedang Diperiksa
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/layani`
- **Akses**: `Bearer Token` (`role:staf`)
- **Deskripsi**: Mengubah status antrean menjadi `sedang_diperiksa` saat pasien memasuki ruang konsultasi dokter.
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "message": "Antrean UM-009 sedang dalam pemeriksaan dokter.",
  "data": {
    "antrean_id": 42,
    "antrean_nomor": "UM-009",
    "antrean_status": "sedang_diperiksa"
  }
}
```

---

### 3.6 Dokter Poliklinik: Antrean & Rekam Medis AES-256 (US-05)

#### 3.6.1 Daftar Pasien Dokter Hari Ini
- **Endpoint**: `GET /api/v1/dokter/antrean-hari-ini`
- **Akses**: `Bearer Token` (`role:dokter`)
- **Query Params**: `?status=menunggu` *(opsional)*
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "dokter": {
      "dokter_user_id": "USR-DOC-001",
      "nama": "dr. Andi Pratama, Sp.PD",
      "spesialisasi": "Penyakit Dalam",
      "poli": "Poli Umum"
    },
    "tanggal": "2026-11-24",
    "total": 6,
    "antrean": [
      {
        "antrean_id": 42,
        "antrean_nomor": "UM-009",
        "antrean_angka": 9,
        "antrean_status": "sedang_diperiksa",
        "catatan_keluhan_awal": "Demam dan batuk berdahak 3 hari",
        "pasien": {
          "user_id": "USR-PAS-0002",
          "nama": "Budi Santoso",
          "norm": "RM-2026-0002",
          "jenis_kelamin": "Laki-laki",
          "gol_darah": "O",
          "alergi_obat": "Tidak ada"
        }
      }
    ]
  }
}
```

#### 3.6.2 Riwayat Pasien (AES-256 Decrypted)
- **Endpoint**: `GET /api/v1/dokter/pasien/{pasien_user_id}/rekam-medis`
- **Akses**: `Bearer Token` (`role:dokter`)
- **Deskripsi**: Menampilkan rekam medis terdahulu milik pasien tertentu. Nilai pada field terenkripsi didekripsi secara transparan dari cipher AES-256.
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "pasien": {
      "user_id": "USR-PAS-0002",
      "nama": "Budi Santoso",
      "norm": "RM-2026-0002",
      "nik": "3372020912950001"
    },
    "total_riwayat": 2,
    "riwayat": [
      {
        "rekam_medis_id": 14,
        "tanggal": "2026-10-15",
        "dokter_nama": "dr. Andi Pratama, Sp.PD",
        "poli_nama": "Poli Umum",
        "keluhan": "Gastritis kronis kambuh",
        "diagnosa": "K29.7 - Gastritis, unspecified",
        "tanda_vital": "TD: 110/70 mmHg, Suhu: 36.6 C",
        "tindakan": "Pemeriksaan palpasi abdomen",
        "resep_obat": "Omeprazole 20mg 2x1, Antasida syrup 3x1 cth"
      }
    ]
  }
}
```

#### 3.6.3 Simpan Pemeriksaan (AES-256 Encrypted)
- **Endpoint**: `POST /api/v1/dokter/rekam-medis`
- **Akses**: `Bearer Token` (`role:dokter`)
- **Deskripsi**: Menyimpan hasil pemeriksaan dokter, mengenkripsi field klinis dengan **AES-256-CBC**, serta mengupdate status antrean menjadi `selesai`.
- **Payload Request**:
```json
{
  "antrean_id": 42,
  "keluhan": "Demam tinggi 38.5 C disertai batuk berdahak kuning kehijauan",
  "diagnosa": "J06.9 - Acute upper respiratory infection, unspecified",
  "tanda_vital": "TD: 120/80 mmHg, Nadi: 84 bpm, Suhu: 38.5 C, RR: 20 x/m",
  "tindakan": "Pemeriksaan fisis toraks, edukasi istirahat cukup",
  "resep_obat": "Amoxicillin 500mg 3x1 (5 hari), Paracetamol 500mg 3x1"
}
```
- **Response Berhasil (201 Created)**:
```json
{
  "status": "success",
  "message": "Pemeriksaan pasien Budi Santoso selesai. Rekam medis berhasil dienkripsi dan disimpan.",
  "data": {
    "rekam_medis_id": 15,
    "antrean_nomor": "UM-009",
    "antrean_status": "selesai",
    "tanggal": "2026-11-24",
    "pasien_nama": "Budi Santoso",
    "dokter_nama": "dr. Andi Pratama, Sp.PD",
    "poli_nama": "Poli Umum",
    "diagnosa": "J06.9 - Acute upper respiratory infection, unspecified",
    "waktu_selesai": "09:15:00"
  }
}
```

---

### 3.7 Pasien: Portal Mandiri Rekam Medis Pribadi (US-07)

#### 3.7.1 Daftar Riwayat Rekam Medis Pribadi
- **Endpoint**: `GET /api/v1/pasien/riwayat-medis`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Deskripsi**: Pasien dapat melihat riwayat kunjungan dan hasil diagnosa masa lalu miliknya sendiri secara aman dan terdekripsi.
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "total": 1,
    "riwayat": [
      {
        "rekam_medis_id": 15,
        "tanggal": "2026-11-24",
        "poli": { "poli_id": 1, "poli_nama": "Poli Umum" },
        "dokter": { "nama": "dr. Andi Pratama, Sp.PD", "spesialisasi": "Penyakit Dalam" },
        "keluhan": "Demam tinggi 38.5 C disertai batuk berdahak",
        "diagnosa": "J06.9 - Acute upper respiratory infection, unspecified",
        "tanda_vital": "TD: 120/80 mmHg, Nadi: 84 bpm, Suhu: 38.5 C",
        "tindakan": "Pemeriksaan fisis toraks, edukasi istirahat cukup",
        "resep_obat": "Amoxicillin 500mg 3x1, Paracetamol 500mg 3x1",
        "waktu_pemeriksaan": "2026-11-24 09:15:00"
      }
    ]
  }
}
```

#### 3.7.2 Detail Berkas Rekam Medis Pribadi
- **Endpoint**: `GET /api/v1/pasien/riwayat-medis/{id}`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Aturan Otorisasi**: Pasien dilarang membuka rekam medis milik pasien lain (HTTP 403 Forbidden).

---

### 3.8 Admin Loket: Pengelolaan Jadwal Praktik & Kuota Dokter (US-06)

#### 3.8.1 Daftar Jadwal Praktik & Kuota Dokter
- **Endpoint**: `GET /api/v1/admin/jadwal`
- **Akses**: `Bearer Token` (`role:staf`)
- **Query Params**: `?poli_id=1&hari=1&status=aktif` *(opsional)*
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "total": 5,
    "jadwal": [
      {
        "jadwal_id": 1,
        "dokter": {
          "dokter_user_id": "USR-DOC-001",
          "nama": "dr. Andi Pratama, Sp.PD",
          "poli": "Poli Umum"
        },
        "hari_angka": 1,
        "hari_nama": "Senin",
        "jam_mulai": "08:00:00",
        "jam_selesai": "12:00:00",
        "kuota": 25,
        "status": "aktif"
      }
    ]
  }
}
```

#### 3.8.2 Tambah Jadwal Praktik Baru
- **Endpoint**: `POST /api/v1/admin/jadwal`
- **Akses**: `Bearer Token` (`role:staf`)
- **Payload Request**:
```json
{
  "dokter_user_id": "USR-DOC-001",
  "hari": 6,
  "jam_mulai": "09:00",
  "jam_selesai": "13:00",
  "kuota": 30,
  "status": "aktif"
}
```
- **Response Berhasil (201 Created)**:
```json
{
  "status": "success",
  "message": "Jadwal praktik dokter berhasil ditambahkan.",
  "data": {
    "jadwal_id": 6,
    "dokter_nama": "dr. Andi Pratama, Sp.PD",
    "hari": "Sabtu",
    "jam_mulai": "09:00",
    "jam_selesai": "13:00",
    "kuota": 30,
    "status": "aktif"
  }
}
```

#### 3.8.3 Update Jadwal, Kuota & Cuti Dokter
- **Endpoint**: `PUT /api/v1/admin/jadwal/{id}`
- **Akses**: `Bearer Token` (`role:staf`)
- **Payload Request**:
```json
{
  "kuota": 35,
  "status": "cuti"
}
```
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "message": "Jadwal praktik dan kuota dokter berhasil diperbarui.",
  "data": {
    "jadwal_id": 6,
    "kuota": 35,
    "status": "cuti"
  }
}
```

#### 3.8.4 Hapus Jadwal Praktik Dokter
- **Endpoint**: `DELETE /api/v1/admin/jadwal/{id}`
- **Akses**: `Bearer Token` (`role:staf`)
- **Aturan**: Jadwal tidak dapat dihapus jika masih terdapat antrean aktif pada jadwal tersebut. Admin disarankan mengubah status menjadi `tutup` atau `cuti`.
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "message": "Jadwal praktik berhasil dihapus."
}
```

---

### 3.9 Admin Loket: Rekapitulasi Laporan Kunjungan & Export CSV (US-09)

#### 3.9.1 Agregasi Metrik & Rekap Kunjungan
- **Endpoint**: `GET /api/v1/admin/laporan-kunjungan`
- **Akses**: `Bearer Token` (`role:staf`)
- **Query Params**:
  - `tanggal_mulai` *(default: awal bulan berjalan)*
  - `tanggal_selesai` *(default: hari ini)*
  - `poli_id` *(opsional)*
  - `jenis` *(opsional: `online` / `walkin`)*
  - `status` *(opsional: `selesai`, `batal`, `dilewati`)*
- **Response Berhasil (200 OK)**:
```json
{
  "status": "success",
  "data": {
    "periode": {
      "tanggal_mulai": "2026-11-01",
      "tanggal_selesai": "2026-11-24"
    },
    "ringkasan": {
      "total_kunjungan": 142,
      "kunjungan_selesai": 128,
      "kunjungan_dilewati": 8,
      "kunjungan_batal": 6,
      "rata_rata_per_hari": 17.8
    },
    "statistik": {
      "per_status": { "selesai": 128, "dilewati": 8, "batal": 6 },
      "per_jenis": { "online": 95, "walkin": 47 },
      "per_poli": { "Poli Umum": 78, "Poli Gigi & Mulut": 34, "Poli KIA": 30 },
      "per_tanggal": { "2026-11-24": 15, "2026-11-23": 22 }
    },
    "daftar_kunjungan": [
      {
        "antrean_id": 42,
        "tanggal": "2026-11-24",
        "nomor_antrean": "UM-009",
        "jenis": "online",
        "status": "selesai",
        "norm": "RM-2026-0002",
        "nama_pasien": "Budi Santoso",
        "poli": "Poli Umum",
        "dokter": "dr. Andi Pratama, Sp.PD",
        "waktu_checkin": "08:15:20",
        "waktu_panggil": "08:45:10",
        "waktu_selesai": "09:15:00"
      }
    ]
  }
}
```

#### 3.9.2 Download Laporan Format CSV
- **Endpoint**: `GET /api/v1/admin/laporan-kunjungan/export`
- **Akses**: `Bearer Token` (`role:staf`)
- **Query Params**: `?tanggal_mulai=2026-11-01&tanggal_selesai=2026-11-24`
- **Headers Respon**:
  - `Content-Type: text/csv; charset=UTF-8`
  - `Content-Disposition: attachment; filename="rekap-kunjungan-kliniku-2026-11-01-sd-2026-11-24.csv"`
- **Format File**: Kompatibel Microsoft Excel dengan UTF-8 Byte Order Mark (BOM). Kolom meliputi: `ID Antrean`, `Tanggal`, `Nomor Antrean`, `Jenis Pendaftaran`, `No RM`, `NIK`, `Nama Pasien`, `Poliklinik`, `Dokter Pemeriksa`, `Status`, `Waktu Check-in`, `Waktu Panggil`, `Waktu Selesai`, `Catatan / Keluhan`.

---

## 4. Arsitektur Keamanan & Enkripsi Data

1. **Password Hashing (bcrypt)**:
   - Password pengguna di-hash menggunakan algoritma **bcrypt** dengan work factor standar industri (12 rounds).
   - Atribut `user_password` dilindungi atribut `$hidden` pada model Eloquent sehingga tidak pernah bocor dalam serialisasi JSON.

2. **Google reCAPTCHA v2**:
   - Memvalidasi respons bot pada endpoint `/login` via Google reCAPTCHA API untuk mencegah serangan *credential stuffing*.

3. **Email OTP 6-Digit (Two-Factor Authentication)**:
   - Kode OTP bersifat *single-use* (hanya bisa dipakai satu kali).
   - Masa berlaku 5 menit dengan *sliding expiration*.
   - Proteksi *brute-force*: maksimal 5 kali percobaan salah, setelah itu sesi dikunci permanen.
   - Pengiriman menggunakan protokol **SMTP Gmail TLS** (`smtp.gmail.com:587`).

4. **Enkripsi Simetris AES-256 (Mandat UU PDP No. 27/2022)**:
   - Rekam medis pasien (`keluhan`, `diagnosa`, `tanda_vital`, `tindakan`, `resep_obat`) disimpan dalam bentuk ciphertext di database menggunakan cipher **AES-256-CBC**.
   - Dekripsi hanya dilakukan secara transparan pada layer model Eloquent saat diakses oleh pengguna yang berhak.

5. **SQL Injection Prevention**:
   - Seluruh query menggunakan parameterized prepared statements via PDO / Laravel Eloquent ORM.

6. **Rate Limiting (Throttle Middleware)**:
   - `/api/v1/login`: 5 request / menit.
   - `/api/v1/verify-otp`: 10 request / menit.
   - `/api/v1/resend-otp`: 3 request / menit.
   - Endpoint umum: 60 request / menit.

---

## 5. Alur Integrasi Frontend (End-to-End Workflows)

### Alur 1: Pasien (Booking Online & Tracking)
```text
1. Frontend memanggil GET /api/v1/booking/dokter untuk menampilkan daftar poli & jadwal.
2. Pasien memilih dokter & tanggal, lalu Frontend memanggil POST /api/v1/booking/cek-kuota.
3. Pasien konfirmasi booking -> Frontend kirim POST /api/v1/booking dengan Authorization Header.
4. Pasien menerima respon berisi e-tiket QR code + salinan tiket masuk ke Email SMTP pasien.
5. Pada hari H, Pasien membuka menu "Jadwal Saya" (GET /api/v1/antrean/saya) dan memantau antrean via live tracking (GET /api/v1/antrean/live/{id}).
```

### Alur 2: Loket Fisik (Check-in & Pemanggilan Antrean)
```text
1. Pasien datang menunjukkan QR e-tiket -> Petugas Loket scan QR via POST /api/v1/loket/checkin-qr.
2. Status antrean berubah jadi 'menunggu'. Pasien duduk di ruang tunggu.
3. Petugas memanggil pasien berikutnya -> POST /api/v1/loket/antrean/{id}/panggil.
4. Sistem otomatis:
   a. Mengirim notifikasi panggilan ke email pasien via SMTP Gmail.
   b. Memperbarui Display TV Ruang Tunggu (GET /api/v1/display/tv) beserta teks audio TTS.
5. Jika pasien tidak hadir -> Petugas menekan 'Lewati' (POST /api/v1/loket/antrean/{id}/lewati).
6. Jika pasien hadir -> Petugas menekan 'Layani' (POST /api/v1/loket/antrean/{id}/layani).
```

### Alur 3: Dokter Poliklinik (Pemeriksaan & EMR)
```text
1. Dokter login ke portal dokter -> GET /api/v1/dokter/antrean-hari-ini.
2. Dokter membuka riwayat medis masa lalu pasien -> GET /api/v1/dokter/pasien/{id}/rekam-medis (terdekripsi otomatis).
3. Setelah memeriksa, Dokter menginput diagnosa, vital signs, resep obat -> POST /api/v1/dokter/rekam-medis.
4. Sistem mengenkripsi rekam medis dengan AES-256, menyimpannya ke database, dan menandai antrean 'selesai'.
```

---

## 6. Pengujian Otomatis & Verifikasi Sistem (PHPUnit)

Seluruh 54 test otomatis telah terverifikasi dengan hasil **100% PASS**:

```bash
docker exec kliniku php artisan test
```

**Ringkasan Hasil Uji**:
```text
PASS Tests\Unit\ExampleTest
PASS Tests\Feature\AdminJadwalAndLaporanTest   (4 tests)
PASS Tests\Feature\AuthLoginTest              (13 tests)
PASS Tests\Feature\BookingTest                (5 tests)
PASS Tests\Feature\DokterPoliAndRekamMedisTest (2 tests - verifikasi raw DB terenkripsi AES-256)
PASS Tests\Feature\ExampleTest                (1 test)
PASS Tests\Feature\LoketTest                  (5 tests)
PASS Tests\Feature\PatientRegistrationTest    (6 tests)
PASS Tests\Feature\QueueTrackerTest           (4 tests)
PASS Tests\Feature\SecurityFeaturesTest       (13 tests)

Tests:    54 passed (543 assertions)
Duration: ~7.5 detik (100% PASS, 0 Failed)
```
