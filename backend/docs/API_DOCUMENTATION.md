# Dokumentasi Lengkap API KLINIKU (Backend REST API)

Dokumentasi resmi antarmuka pemrograman aplikasi (API) untuk sistem backend **KLINIKU v1.0** berdasarkan Dokumen Perancangan Perangkat Lunak (DPPL) Kelompok 5 D3 Teknik Informatika SV UNS. Seluruh endpoint dirancang menggunakan arsitektur RESTful JSON dan ditujukan untuk integrasi dengan antarmuka pengguna frontend (Web Pasien, Antarmuka Admin Loket, Portal Dokter Poli, dan Display TV Ruang Tunggu).

Sistem backend ini mengimplementasikan seluruh 9 User Story utama (US-01 s/d US-09), arsitektur keamanan tingkat tinggi (bcrypt, Google reCAPTCHA v2, SMTP Email OTP, enkripsi simetris **AES-256** untuk rekam medis sesuai UU PDP No. 27/2022, rate limiting, dan RBAC), serta penggantian seluruh saluran notifikasi WhatsApp menjadi **SMTP Email Resmi** (`smtp.gmail.com:587 TLS`).

---

## Daftar Isi
1. [Informasi Umum & Konvensi API](#1-informasi-umum--konvensi-api)
   - [Base URL & Alias](#base-url--alias)
   - [Header Standar Request](#header-standar-request)
   - [Standar HTTP Status Code](#standar-http-status-code)
   - [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
2. [Matriks Endpoint & Pemetaan User Story (DPPL)](#2-matriks-endpoint--pemetaan-user-story-dppl)
3. [Katalog Endpoint Lengkap](#3-katalog-endpoint-lengkap)
   - [3.1 Autentikasi & Akun (US-01)](#31-autentikasi--akun-us-01)
   - [3.2 Pemesanan Antrean Online & Quota Engine (US-03)](#32-pemesanan-antrean-online--quota-engine-us-03)
   - [3.3 Live Queue Tracker & Monitor TV Ruang Tunggu (US-02)](#33-live-queue-tracker--monitor-tv-ruang-tunggu-us-02)
   - [3.4 Admin Loket: QR Check-in & Walk-in Registration (US-08)](#34-admin-loket-qr-check-in--walk-in-registration-us-08)
   - [3.5 Admin Loket: Pemanggilan Antrean Multi-Channel & TTS (US-04)](#35-admin-loket-pemanggilan-antrean-multi-channel--tts-us-04)
   - [3.6 Dokter Poli & Rekam Medis Terenkripsi AES-256 (US-05)](#36-dokter-poli--rekam-medis-terenkripsi-aes-256-us-05)
   - [3.7 Pasien: Riwayat Rekam Medis Mandiri (US-07)](#37-pasien-riwayat-rekam-medis-mandiri-us-07)
   - [3.8 Admin: Pengelolaan Jadwal Praktik & Kuota Dokter (US-06)](#38-admin-pengelolaan-jadwal-praktik--kuota-dokter-us-06)
   - [3.9 Admin: Rekapitulasi Laporan Kunjungan & Export CSV (US-09)](#39-admin-rekapitulasi-laporan-kunjungan--export-csv-us-09)
4. [Arsitektur Keamanan & Enkripsi Data](#4-arsitektur-keamanan--enkripsi-data)
5. [Pengujian Otomatis (PHPUnit Test Suite)](#5-pengujian-otomatis-phpunit-test-suite)

---

## 1. Informasi Umum & Konvensi API

### Base URL & Alias
- **Base URL Utama (v1)**: `http://localhost:8080/api/v1`
- **Alias Kompatibilitas**: `http://localhost:8080/api` (contoh: `/api/login` mengarah ke `/api/v1/login`)
- **Format Data**: JSON (`application/json`)
- **Karakter Set**: `UTF-8`

### Header Standar Request
Untuk semua endpoint berbasis `POST`, `PUT`, dan `PATCH`:
```http
Content-Type: application/json
Accept: application/json
```

Untuk endpoint terproteksi autentikasi (Protected via JWT):
```http
Authorization: Bearer <JWT_TOKEN>
```

### Standar HTTP Status Code
| Kode Status | Deskripsi | Skenario Penggunaan |
| :--- | :--- | :--- |
| `200 OK` | Berhasil | Request sukses (baca data, update status, panggilan antrean). |
| `201 Created` | Berhasil Dibuat | Entitas baru berhasil dibuat (registrasi, booking, rekam medis, jadwal). |
| `400 Bad Request` | Permintaan Salah | Parameter request tidak lengkap atau tidak dapat diproses. |
| `401 Unauthorized` | Belum Login / Token Invalid | Token JWT tidak disertakan, kedaluwarsa, atau tanda tangan tidak valid. |
| `403 Forbidden` | Akses Ditolak (RBAC) | Pengguna tidak memiliki hak akses/peran yang sesuai untuk rute tersebut. |
| `404 Not Found` | Tidak Ditemukan | ID antrean, dokter, jadwal, atau rekam medis tidak ditemukan. |
| `410 Gone` | Sesi Kedaluwarsa | Sesi OTP email telah melewati batas waktu (5 menit). |
| `422 Unprocessable Entity` | Validasi Gagal | Kuota penuh, format input salah, atau aturan bisnis terlanggar. |
| `429 Too Many Requests` | Rate Limit Terlampaui | Melebihi batas request throttle per menit. |
| `500 Server Error` | Kesalahan Internal Server | Terjadi pengecualian database atau kegagalan sistem internal. |

### Role-Based Access Control (RBAC)
Sistem menggunakan 3 aktor peran utama:
1. `pasien`: Pasien terdaftar (`USR-PAS-XXXX`). Memiliki akses booking online, jadwal saya, e-tiket, live tracker, dan riwayat rekam medis pribadi.
2. `dokter`: Dokter poliklinik (`USR-DOC-XXXX`). Memiliki akses daftar pasien hari ini, riwayat medis pasien, dan input rekam medis AES-256.
3. `staf`: Petugas loket / administrasi (`USR-ADM-XXXX`). Memiliki akses scan QR check-in, pendaftaran walk-in, pemanggilan antrean, manajemen jadwal & kuota dokter, dan rekapitulasi laporan kunjungan.

---

## 2. Matriks Endpoint & Pemetaan User Story (DPPL)

| User Story | Modul / Fitur | Method | Endpoint URL | Akses Peran |
| :--- | :--- | :--- | :--- | :--- |
| **US-01** | Registrasi Pasien Baru | `POST` | `/api/v1/register` | Publik |
| **US-01** | Cek Duplikasi NIK | `POST` | `/api/v1/check-nik` | Publik |
| **US-01** | Login Pasien & Kirim Email OTP | `POST` | `/api/v1/login` | Publik (Throttle) |
| **US-01** | Verifikasi Email OTP 6-Digit | `POST` | `/api/v1/verify-otp` | Publik (Throttle) |
| **US-01** | Kirim Ulang Email OTP | `POST` | `/api/v1/resend-otp` | Publik (Throttle) |
| **US-01** | Profil Pengguna Login | `GET` | `/api/v1/me` | Authenticated |
| **US-01** | Logout Pengguna | `POST` | `/api/v1/logout` | Authenticated |
| **US-03** | Daftar Dokter & Jadwal Praktik | `GET` | `/api/v1/booking/dokter` | Publik / Pasien |
| **US-03** | Cek Kuota Dokter H-7 s/d Hari H | `POST` | `/api/v1/booking/cek-kuota` | Publik / Pasien |
| **US-03** | Buat Pemesanan Antrean Online | `POST` | `/api/v1/booking` | `pasien` |
| **US-03** | Detail E-Tiket QR Code | `GET` | `/api/v1/booking/{id}/tiket` | Authenticated |
| **US-03** | Batalkan Pemesanan Antrean | `POST` | `/api/v1/booking/{id}/batal` | `pasien` |
| **US-02** | Jadwal Saya (Antrean Pasien) | `GET` | `/api/v1/antrean/saya` | `pasien` |
| **US-02** | Live Tracking Real-Time Antrean | `GET` | `/api/v1/antrean/live/{id}` | Publik / Pasien |
| **US-02** | Daftar Antrean Poli Hari Ini | `GET` | `/api/v1/antrean/poli/{poli_id}/hari-ini` | Publik |
| **US-02** | Display Monitor TV & Audio TTS | `GET` | `/api/v1/display/tv` | Publik |
| **US-08** | Verifikasi QR Check-in Loket | `POST` | `/api/v1/loket/checkin-qr` | `staf` |
| **US-08** | Pendaftaran Pasien Walk-in | `POST` | `/api/v1/loket/pendaftaran-walkin` | `staf` |
| **US-08** | Monitoring Antrean Loket Hari Ini | `GET` | `/api/v1/loket/antrean-hari-ini` | `staf` |
| **US-04** | Panggil Antrean (TV TTS + Email) | `POST` | `/api/v1/loket/antrean/{id}/panggil` | `staf` |
| **US-04** | Panggil Ulang Antrean (Recall) | `POST` | `/api/v1/loket/antrean/{id}/panggil-ulang` | `staf` |
| **US-04** | Lewati Antrean (Skip) | `POST` | `/api/v1/loket/antrean/{id}/lewati` | `staf` |
| **US-04** | Update Status Sedang Diperiksa | `POST` | `/api/v1/loket/antrean/{id}/layani` | `staf` |
| **US-05** | Antrean Pasien Dokter Hari Ini | `GET` | `/api/v1/dokter/antrean-hari-ini` | `dokter` |
| **US-05** | Riwayat Medis Pasien (Decrypted) | `GET` | `/api/v1/dokter/pasien/{id}/rekam-medis` | `dokter` |
| **US-05** | Simpan Rekam Medis (AES-256) | `POST` | `/api/v1/dokter/rekam-medis` | `dokter` |
| **US-07** | Riwayat Medis Mandiri Pasien | `GET` | `/api/v1/pasien/riwayat-medis` | `pasien` |
| **US-07** | Detail Rekam Medis Pribadi | `GET` | `/api/v1/pasien/riwayat-medis/{id}` | `pasien` |
| **US-06** | Lihat Jadwal & Kuota Dokter | `GET` | `/api/v1/admin/jadwal` | `staf` |
| **US-06** | Tambah Jadwal Praktik Dokter | `POST` | `/api/v1/admin/jadwal` | `staf` |
| **US-06** | Update Jadwal, Kuota & Cuti | `PUT` | `/api/v1/admin/jadwal/{id}` | `staf` |
| **US-06** | Hapus Jadwal Praktik Dokter | `DELETE` | `/api/v1/admin/jadwal/{id}` | `staf` |
| **US-09** | Rekapitulasi Laporan Kunjungan | `GET` | `/api/v1/admin/laporan-kunjungan` | `staf` |
| **US-09** | Export Laporan Kunjungan (CSV) | `GET` | `/api/v1/admin/laporan-kunjungan/export` | `staf` |

---

## 3. Katalog Endpoint Lengkap

### 3.1 Autentikasi & Akun (US-01)

#### 3.1.1 Registrasi Pasien Baru
- **Endpoint**: `POST /api/v1/register`
- **Akses**: Publik
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
  "alamat": "Jl. Slamet Riyadi No. 45, Solo",
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

#### 3.1.2 Login Pasien (Google reCAPTCHA v2 & Kirim Email OTP)
- **Endpoint**: `POST /api/v1/login`
- **Akses**: Publik (Throttle: 5 perc/menit)
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

#### 3.1.3 Verifikasi Email OTP & Penerbitan JWT
- **Endpoint**: `POST /api/v1/verify-otp`
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

---

### 3.2 Pemesanan Antrean Online & Quota Engine (US-03)

#### 3.2.1 Cek Kuota Dokter H-7 s/d Hari H
- **Endpoint**: `POST /api/v1/booking/cek-kuota`
- **Akses**: Publik / Pasien
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

#### 3.2.2 Pemesanan Antrean Online Baru
- **Endpoint**: `POST /api/v1/booking`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Payload Request**:
```json
{
  "dokter_user_id": "USR-DOC-001",
  "tanggal": "2026-11-26",
  "keluhan": "Batuk dan demam tinggi sejak 2 hari yang lalu"
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
    "poli": {
      "poli_id": 1,
      "poli_nama": "Poli Umum"
    },
    "dokter": {
      "dokter_user_id": "USR-DOC-001",
      "nama": "dr. Andi Pratama, Sp.PD",
      "spesialisasi": "Penyakit Dalam"
    },
    "jadwal": {
      "jam_mulai": "08:00:00",
      "jam_selesai": "12:00:00"
    },
    "catatan": "Batuk dan demam tinggi sejak 2 hari yang lalu"
  }
}
```

#### 3.2.3 Detail E-Tiket QR Code
- **Endpoint**: `GET /api/v1/booking/{id}/tiket`
- **Akses**: `Bearer Token`

#### 3.2.4 Batalkan Pemesanan Antrean
- **Endpoint**: `POST /api/v1/booking/{id}/batal`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Catatan**: Hanya antrean dengan status `menunggu_checkin` yang dapat dibatalkan.

---

### 3.3 Live Queue Tracker & Monitor TV Ruang Tunggu (US-02)

#### 3.3.1 Jadwal Saya (Antrean Pasien)
- **Endpoint**: `GET /api/v1/antrean/saya?tipe=aktif`
- **Akses**: `Bearer Token` (`role:pasien`)
- **Query Params**: `tipe` (`aktif`, `riwayat`, `semua`)

#### 3.3.2 Live Queue Tracker Real-Time
- **Endpoint**: `GET /api/v1/antrean/live/{antrean_id}`
- **Akses**: Publik / Pasien
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
      "poli": {
        "poli_id": 1,
        "poli_nama": "Poli Umum"
      },
      "dokter": {
        "dokter_user_id": "USR-DOC-001",
        "nama": "dr. Andi Pratama, Sp.PD"
      }
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

#### 3.3.3 TV Display Monitor & Text-to-Speech (TTS) Ruang Tunggu
- **Endpoint**: `GET /api/v1/display/tv`
- **Akses**: Publik
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

#### 3.4.1 QR Check-in di Loket Fisik
- **Endpoint**: `POST /api/v1/loket/checkin-qr`
- **Akses**: `Bearer Token` (`role:staf`)
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
    "antrean_status": "menunggu",
    "waktu_checkin": "08:15:20",
    "pasien": {
      "nama": "Budi Santoso",
      "norm": "RM-2026-0002",
      "nik": "3372020912950001"
    },
    "poli": {
      "poli_id": 1,
      "poli_nama": "Poli Umum"
    }
  }
}
```

#### 3.4.2 Pendaftaran Walk-in Pasien Langsung
- **Endpoint**: `POST /api/v1/loket/pendaftaran-walkin`
- **Akses**: `Bearer Token` (`role:staf`)
- **Payload Request (Pasien Baru)**:
```json
{
  "nik": "3372021504900003",
  "nama": "Siti Rahmawati",
  "nowa": "081299887755",
  "email": "siti.rahma@gmail.com",
  "jeniskelamin": "Perempuan",
  "tgllahir": "1990-04-15",
  "alamat": "Jl. Adi Sucipto No. 12, Solo",
  "dokter_user_id": "USR-DOC-001",
  "keluhan": "Sakit kepala berdenyut"
}
```
- **Response Berhasil (201 Created)**:
Mengembalikan detail tiket untuk langsung dicetak pada printer termal di loket fisik.

---

### 3.5 Admin Loket: Pemanggilan Antrean Multi-Channel & TTS (US-04)

#### 3.5.1 Panggil Antrean Pasien
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/panggil`
- **Akses**: `Bearer Token` (`role:staf`)
- **Efek Samping**:
  1. Status antrean berubah menjadi `dipanggil`.
  2. Suara TTS diperbarui pada Monitor TV Ruang Tunggu.
  3. Mengirimkan **SMTP Email Notifikasi** ke email pasien secara otomatis (menggantikan notifikasi WhatsApp).

#### 3.5.2 Panggil Ulang (Recall)
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/panggil-ulang`
- **Akses**: `Bearer Token` (`role:staf`)

#### 3.5.3 Lewati Antrean (Skip)
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/lewati`
- **Akses**: `Bearer Token` (`role:staf`)

#### 3.5.4 Layani Antrean (Dokter Mulai Memeriksa)
- **Endpoint**: `POST /api/v1/loket/antrean/{id}/layani`
- **Akses**: `Bearer Token` (`role:staf`)

---

### 3.6 Dokter Poli & Rekam Medis Terenkripsi AES-256 (US-05)

#### 3.6.1 Antrean Dokter Hari Ini
- **Endpoint**: `GET /api/v1/dokter/antrean-hari-ini`
- **Akses**: `Bearer Token` (`role:dokter`)

#### 3.6.2 Riwayat Rekam Medis Pasien
- **Endpoint**: `GET /api/v1/dokter/pasien/{pasien_user_id}/rekam-medis`
- **Akses**: `Bearer Token` (`role:dokter`)

#### 3.6.3 Simpan Pemeriksaan & Enkripsi Rekam Medis AES-256
- **Endpoint**: `POST /api/v1/dokter/rekam-medis`
- **Akses**: `Bearer Token` (`role:dokter`)
- **Payload Request**:
```json
{
  "antrean_id": 42,
  "keluhan": "Demam tinggi 38.5 C disertai batuk berdahak kuning kehijauan",
  "diagnosa": "J06.9 - Acute upper respiratory infection, unspecified",
  "tanda_vital": "TD: 120/80 mmHg, Nadi: 84 bpm, Suhu: 38.5 C, RR: 20 x/m",
  "tindakan": "Pemeriksaan fisis toraks, pemberian kompres hangat",
  "resep_obat": "Amoxicillin 500mg 3x1 (5 hari), Paracetamol 500mg 3x1 (prn demam)"
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
    "tanggal": "2026-11-26",
    "diagnosa": "J06.9 - Acute upper respiratory infection, unspecified",
    "waktu_selesai": "09:35:40"
  }
}
```

---

### 3.7 Pasien: Riwayat Rekam Medis Mandiri (US-07)

#### 3.7.1 Riwayat Medis Pribadi
- **Endpoint**: `GET /api/v1/pasien/riwayat-medis`
- **Akses**: `Bearer Token` (`role:pasien`)

#### 3.7.2 Detail Berkas Rekam Medis Pribadi
- **Endpoint**: `GET /api/v1/pasien/riwayat-medis/{id}`
- **Akses**: `Bearer Token` (`role:pasien`)

---

### 3.8 Admin: Pengelolaan Jadwal Praktik & Kuota Dokter (US-06)

#### 3.8.1 Lihat Jadwal & Kuota Praktik
- **Endpoint**: `GET /api/v1/admin/jadwal`
- **Akses**: `Bearer Token` (`role:staf`)
- **Filter**: `?poli_id=1&hari=1&status=aktif`

#### 3.8.2 Tambah Jadwal Praktik Dokter
- **Endpoint**: `POST /api/v1/admin/jadwal`
- **Akses**: `Bearer Token` (`role:staf`)
- **Payload Request**:
```json
{
  "dokter_user_id": "USR-DOC-001",
  "hari": 6,
  "jam_mulai": "08:00",
  "jam_selesai": "12:00",
  "kuota": 25,
  "status": "aktif"
}
```

#### 3.8.3 Perbarui Jadwal / Cuti Dokter
- **Endpoint**: `PUT /api/v1/admin/jadwal/{id}`
- **Akses**: `Bearer Token` (`role:staf`)
- **Payload Request**:
```json
{
  "kuota": 30,
  "status": "cuti"
}
```

#### 3.8.4 Hapus Jadwal Praktik
- **Endpoint**: `DELETE /api/v1/admin/jadwal/{id}`
- **Akses**: `Bearer Token` (`role:staf`)

---

### 3.9 Admin: Rekapitulasi Laporan Kunjungan & Export CSV (US-09)

#### 3.9.1 Rekapitulasi Laporan Kunjungan
- **Endpoint**: `GET /api/v1/admin/laporan-kunjungan?tanggal_mulai=2026-11-01&tanggal_selesai=2026-11-24`
- **Akses**: `Bearer Token` (`role:staf`)
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
      "per_poli": { "Poli Umum": 78, "Poli Gigi & Mulut": 34, "Poli KIA": 30 }
    },
    "daftar_kunjungan": [...]
  }
}
```

#### 3.9.2 Export Rekapitulasi Format CSV
- **Endpoint**: `GET /api/v1/admin/laporan-kunjungan/export?tanggal_mulai=2026-11-01&tanggal_selesai=2026-11-24`
- **Akses**: `Bearer Token` (`role:staf`)
- **Format Download**: File CSV kompatibel Microsoft Excel dengan UTF-8 BOM encoding.

---

## 4. Arsitektur Keamanan & Enkripsi Data

1. **Password Hashing (bcrypt)**:
   - Password pengguna di-hash menggunakan algoritma **bcrypt** dengan work factor standar industri (12 rounds).
   - Atribut `user_password` dilindungi atribut `$hidden` pada model Eloquent sehingga tidak pernah bocor dalam serialisasi JSON.

2. **Google reCAPTCHA v2**:
   - Mencegah serangan *credential stuffing* dan *bot abuse* pada rute login.

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

## 5. Pengujian Otomatis (PHPUnit Test Suite)

Seluruh endpoint dan aturan bisnis telah diuji secara menyeluruh menggunakan automated feature tests dengan status **100% PASS**:
- `Tests\Feature\AuthLoginTest` (13 tests)
- `Tests\Feature\BookingTest` (5 tests)
- `Tests\Feature\LoketTest` (5 tests)
- `Tests\Feature\DokterPoliAndRekamMedisTest` (2 tests)
- `Tests\Feature\AdminJadwalAndLaporanTest` (4 tests)
- `Tests\Feature\QueueTrackerTest` (4 tests)
- `Tests\Feature\PatientRegistrationTest` (6 tests)
- `Tests\Feature\SecurityFeaturesTest` (13 tests)
- `Tests\Unit\ExampleTest` & `Tests\Feature\ExampleTest` (2 tests)

**Total Test Suite Result**:
```bash
Tests:    54 passed (543 assertions)
Duration: ~7 seconds
```
