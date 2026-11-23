# Dokumentasi Lengkap API KLINIKU (Backend)

Dokumentasi resmi antarmuka pemrograman aplikasi (API) untuk sistem backend **KLINIKU**. Seluruh endpoint dirancang menggunakan arsitektur RESTful JSON dan ditujukan untuk integrasi dengan antarmuka pengguna frontend (Web, Mobile, atau Kiosk).

Sistem backend ini mengimplementasikan alur lengkap pendaftaran pasien baru, penerbitan Nomor Rekam Medis otomatis, login aman dengan proteksi **Google reCAPTCHA v2**, verifikasi dua langkah melalui **Email OTP 6-Digit (SMTP Gmail / Google Workspace)**, serta penerbitan token sesi berbasis **JSON Web Token (JWT)**.

---

## Daftar Isi
1. [Informasi Umum & Konvensi API](#1-informasi-umum--konvensi-api)
   - [Base URL & Alias](#base-url--alias)
   - [Header Standar Request](#header-standar-request)
   - [Standar HTTP Status Code](#standar-http-status-code)
   - [Arsitektur Keamanan & Autentikasi](#arsitektur-keamanan--autentikasi)
2. [Alur Registrasi Pasien (Patient Registration Flow)](#2-alur-registrasi-pasien-patient-registration-flow)
3. [Alur Login & Verifikasi Email OTP (Two-Factor Authentication)](#3-alur-login--verifikasi-email-otp-two-factor-authentication)
4. [Katalog Endpoint API](#4-katalog-endpoint-api)
   - [4.1 Autentikasi & Akun](#41-autentikasi--akun)
     - [`POST /api/v1/register` — Registrasi Pasien Baru](#411-registrasi-pasien-baru)
     - [`POST /api/v1/check-nik` — Cek Validitas & Ketersediaan NIK](#412-cek-validitas--ketersediaan-nik)
     - [`POST /api/v1/login` — Login Pasien & Kirim OTP ke Email](#413-login-pasien--kirim-otp-ke-email)
     - [`POST /api/v1/verify-otp` — Verifikasi Kode OTP 6-Digit & Terbit JWT](#414-verifikasi-kode-otp-6-digit--terbit-jwt)
     - [`POST /api/v1/resend-otp` — Kirim Ulang Kode OTP ke Email](#415-kirim-ulang-kode-otp-ke-email)
     - [`GET /api/v1/me` — Profil Pengguna & Rekam Medis (Protected)](#416-profil-pengguna--rekam-medis-protected)
     - [`POST /api/v1/logout` — Logout Pengguna (Protected)](#417-logout-pengguna-protected)
   - [4.2 Data Master & Penunjang](#42-data-master--penunjang)
     - [`GET /api/v1/polis` — Master Data Poliklinik](#421-master-data-poliklinik)
5. [Struktur Basis Data & Model Entitas](#5-struktur-basis-data--model-entitas)
6. [Panduan Pengujian via cURL (End-to-End Workflow)](#6-panduan-pengujian-via-curl-end-to-end-workflow)
7. [Pengujian Otomatis (PHPUnit Test Suite)](#7-pengujian-otomatis-phpunit-test-suite)

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

Untuk endpoint terproteksi autentikasi (misal `/me` dan `/logout`):
```http
Authorization: Bearer <JWT_TOKEN>
```

### Standar HTTP Status Code
| Kode Status | Deskripsi | Skenario Penggunaan |
| :--- | :--- | :--- |
| `200 OK` | Berhasil | Request sukses (login start, profil query, verify OTP sukses). |
| `201 Created` | Berhasil Dibuat | Akun pasien baru dan Nomor Rekam Medis (RM) berhasil diterbitkan. |
| `401 Unauthorized` | Tidak Terotentikasi | NIK/Password salah atau token JWT tidak disertakan/tidak valid. |
| `403 Forbidden` | Akses Ditolak | Akun berstatus non-aktif atau hak akses peran tidak mencukupi. |
| `404 Not Found` | Tidak Ditemukan | Data pengguna atau sesi tidak ditemukan. |
| `409 Conflict` | Konflik Data | NIK sudah terdaftar saat pemeriksaan live validation. |
| `410 Gone` | Kedaluwarsa | Sesi verifikasi email OTP telah kedaluwarsa (TTL 5 menit habis). |
| `422 Unprocessable Content` | Validasi Gagal | Format input tidak valid, reCAPTCHA bot token salah, atau kode OTP salah. |
| `429 Too Many Requests` | Rate Limit Terlampaui | Terlalu banyak percobaan login/request dalam periode 1 menit (terproteksi Throttling). |
| `500 Server Error` | Gangguan Server | Kegagalan socket/koneksi server SMTP mail. |

### Arsitektur Keamanan & Proteksi Siber
Sistem KLINIKU mengimplementasikan 5 pilar keamanan standar industri:

1. **Password Security (Keamanan Kata Sandi)**:
   - Seluruh kata sandi pengguna di-hash menggunakan algoritma **Bcrypt** (`PASSWORD_BCRYPT` dengan cost/rounds 12).
   - Kata sandi **TIDAK PERNAH disimpan atau ditransmisikan dalam bentuk plaintext**.
   - Model `User` menyembunyikan atribut `user_password` (`$hidden = ['user_password']`) sehingga tidak pernah bocor ke respons JSON atau serialisasi array.
   - Pengecekan kata sandi pada saat login dilakukan menggunakan `Hash::check()` yang aman terhadap serangan *timing attack*.

2. **Google reCAPTCHA v2 (Proteksi Bot & Brute Force)**:
   - Diterapkan pada endpoint login (`/api/v1/login`) untuk mencegah serangan credential stuffing dan bot otomatis.
   - Token verifikasi dikirimkan ke backend melalui field `recaptcha_token` dan divalidasi ke Google Siteverify API.
   - Kredensial aktif:
     - **Site Key**: `6LfEiisuAAAAAM1dgo05Kv4IqPwYUF5NTWbDUN9V`
     - **Secret Key**: `6LfEiisuAAAAAEPuGqfaxFfGOtUGHNdZq0djWVkd`
   - Token uji coba lingkungan lokal: `test-valid-token`. Input token yang salah/kedaluwarsa akan menghasilkan error `422 Unprocessable Content`.

3. **One-Time Password (OTP) 2-Factor Authentication**:
   - Kode OTP 6 digit acak aman (`random_int(100000, 999999)`) dikirimkan via SMTP Google Workspace (`smtp.gmail.com:587 TLS`).
   - **Batas Waktu (Time-Bound)**: Sesi OTP disimpan pada cache dengan masa berlaku (TTL) **300 detik (5 menit)**. Melebihi waktu ini, sistem mengembalikan `410 Gone`.
   - **Sekali Pakai (Single-Use Guarantee)**: Sesi OTP langsung dihapus dari cache (`Cache::forget`) segera setelah verifikasi pertama berhasil. Kode tidak dapat digunakan ulang (mencegah *replay attack*).
   - **Brute-Force Lockout**: Jika kode salah dimasukkan sebanyak **5 kali**, sesi OTP langsung dimusnahkan (`status: 'locked'`) dan pengguna diwajibkan login ulang.

4. **SQL Injection Prevention (Pencegahan SQLi)**:
   - Seluruh operasi basis data menggunakan Laravel Eloquent ORM dan PDO Query Builder dengan **Prepared Statements / Parameterized Queries**.
   - Input pengguna selalu diperlakukan sebagai nilai parameter harfiah (literal value binding), bukan bagian dari sintaks query SQL yang dapat dieksekusi.
   - Telah diuji secara ketat terhadap payload SQL injection klasik dan modern (`' OR '1'='1`, `'; DROP TABLE user; --`, `' UNION SELECT ... --`) pada field `nik`, `password`, `check-nik`, dan data registrasi.

5. **Rate Limiting (Throttling Permintaan)**:
   - Pembatasan jumlah request diterapkan pada seluruh rute API untuk mencegah Denial of Service (DoS) dan serangan brute-force:
     - **Login (`throttle:login`)**: Maksimum **5 percobaan per menit** per NIK + IP. Percobaan ke-6 mengembalikan status `HTTP 429 Too Many Requests`.
     - **Verifikasi OTP (`throttle:otp`)**: Maksimum **5 percobaan per menit** per IP.
     - **Kirim Ulang OTP (`throttle:otp_resend`)**: Maksimum **3 permintaan per menit** per IP.
     - **Global API (`throttle:api`)**: Maksimum **60 request per menit** per IP/User.
   - Header respons menyertakan `X-RateLimit-Limit`, `X-RateLimit-Remaining`, dan `Retry-After`.

6. **JSON Web Token (JWT)**:
   - Diterbitkan menggunakan algoritma `HS256` setelah verifikasi OTP email berhasil.
   - Masa berlaku default: 24 jam (`JWT_TTL=86400`).
   - Payload memuat: `user_id`, `nik`, `role` (`pasien`/`dokter`/`staf`), dan `nama`.

---

## 2. Alur Registrasi Pasien (Patient Registration Flow)

```
[Mulai] Pasien membuka halaman Pendaftaran KLINIKU
   │
   ▼
[Input Formulir] Pasien mengisi: NIK (16 digit), Nama, Email (wajib), No. WhatsApp, Password, Alamat
   │
   ▼
[Kirim Data] POST /api/v1/register
   │
   ├──► [Validasi Gagal / NIK atau Email Duplikat?]
   │        │
   │        └──► HTTP 422: Tampilkan pesan error spesifik -> Pasien memperbaiki input
   │
   └──► [Validasi Berhasil]
            │
            ├──► Hash password dengan Bcrypt
            ├──► Decode NIK: Otomatis mendeteksi Jenis Kelamin & Tanggal Lahir jika opsional
            ├──► Generate ID Akun: USR-PAS-XXXX (Sequential, DB Row Lock)
            ├──► Terbitkan Nomor Rekam Medis: RM-YYYY-XXXX (Sequential per tahun berjalan)
            ├──► Simpan data ke tabel 'user' dan 'pasien' dalam DB Transaction
            │
            └──► HTTP 201 Created: Berikan notifikasi sukses & arahkan pasien ke halaman Login
```

---

## 3. Alur Login & Verifikasi Email OTP (Two-Factor Authentication)

```mermaid
sequenceDiagram
    autonumber
    actor Pasien as Pasien (Frontend)
    participant API as Backend KLINIKU
    participant Recaptcha as Google reCAPTCHA
    participant SMTP as Google SMTP (smtp.gmail.com)
    participant Inbox as Kotak Masuk Email Pasien

    Note over Pasien,API: Tahap 1: Submit Kredensial & reCAPTCHA
    Pasien->>API: POST /api/v1/login (nik, password, recaptcha_token)
    API->>Recaptcha: Verifikasi token reCAPTCHA
    alt reCAPTCHA Tidak Valid
        Recaptcha-->>API: Gagal
        API-->>Pasien: HTTP 422 (reCAPTCHA token tidak valid)
    else reCAPTCHA Valid
        Recaptcha-->>API: Sukses
        API->>API: Cocokkan NIK & Hash Password
        alt Kredensial Salah
            API-->>Pasien: HTTP 401 (NIK atau kata sandi tidak sesuai)
        else Kredensial Benar
            API->>API: Generate 6 Digit OTP & Simpan Sesi (TTL: 5 Menit)
            API->>SMTP: Kirim Email Berisi Kode OTP (OtpMail template)
            SMTP-->>Inbox: Email diterima pasien
            API-->>Pasien: HTTP 200 (step="otp_verification", otp_session_id, masked_email)
        end
    end

    Note over Pasien,API: Tahap 2: Input & Verifikasi Kode OTP
    Pasien->>Inbox: Buka email & salin 6 digit kode OTP
    Pasien->>API: POST /api/v1/verify-otp (otp_session_id, otp_code)
    alt Kode OTP Salah
        API-->>Pasien: HTTP 422 (Kode OTP salah)
    else Sesi Kedaluwarsa
        API-->>Pasien: HTTP 410 (Sesi kedaluwarsa, silakan login ulang)
    else Kode OTP Benar
        API->>API: Terbitkan JWT Bearer Token & Ambil Profil Pasien
        API-->>Pasien: HTTP 200 OK (JWT Token, User & Dashboard Profile Data)
    end

    Note over Pasien,API: Tahap 3: Akses Dashboard Terproteksi
    Pasien->>API: GET /api/v1/me (Header: Authorization: Bearer <token>)
    API-->>Pasien: HTTP 200 OK (Data Rekam Medis & Profil Lengkap)
```

---

## 4. Katalog Endpoint API

### 4.1 Autentikasi & Akun

#### 4.1.1 Registrasi Pasien Baru
Mendaftarkan akun pasien baru, menerbitkan ID Pengguna (`user_id`), dan mencetak Nomor Rekam Medis (`pasien_norm`) secara otomatis.

- **URL**: `/api/v1/register` *(Alias: `/api/v1/auth/register`, `/api/register`)*
- **Method**: `POST`
- **Autentikasi**: Tidak diperlukan (Publik)

##### Parameter Request Body
| Field | Tipe Data | Status | Aturan & Keterangan |
| :--- | :--- | :--- | :--- |
| `nik` | String | **Wajib** | 16 digit angka (`regex:/^[0-9]{16}$/`), unik dalam tabel `user`. |
| `nama` | String | **Wajib** | Nama lengkap pasien (2 - 150 karakter). |
| `email` | String | **Wajib** | Alamat email aktif untuk menerima kode OTP (format email valid, unik di `user_email`). |
| `nowa` | String | **Wajib** | Nomor WhatsApp aktif (10 - 15 digit angka, awalan `08`, `62`, atau `+62`). |
| `password` | String | **Wajib** | Kata sandi akun (minimal 6 karakter). |
| `password_confirmation` | String | Opsional | Jika dikirim, nilainya harus sama dengan `password`. |
| `alamat` | String | **Wajib** | Alamat domisili lengkap pasien (3 - 255 karakter). |
| `jeniskelamin` | String | Opsional | Nilai: `Laki-laki` atau `Perempuan`. *Jika tidak diisi, otomatis dideteksi dari NIK.* |
| `tgllahir` | String (Date) | Opsional | Format `YYYY-MM-DD`. *Jika tidak diisi, otomatis dideteksi dari NIK.* |
| `nobpjs` | String | Opsional | Nomor kartu BPJS / KIS (13 digit angka, unik). |
| `goldarah` | String | Opsional | Nilai: `A`, `B`, `AB`, atau `O`. |
| `alergiobat` | String | Opsional | Riwayat alergi obat pasien (maksimal 255 karakter). |
| `poli_id` | Integer | Opsional | ID Poliklinik tujuan utama (1-5). |

##### Contoh Request
```json
{
  "nik": "3372010101950001",
  "nama": "Budi Hartono",
  "email": "budi.hartono@example.com",
  "nowa": "081234567890",
  "password": "Password123!",
  "alamat": "Jl. Melati No. 45, Jebres, Surakarta",
  "goldarah": "O",
  "poli_id": 1
}
```

##### Contoh Response Sukses (`201 Created`)
```json
{
  "success": true,
  "message": "Pendaftaran akun pasien berhasil! Nomor Rekam Medis (RM) telah dibuat. Silakan melanjutkan ke halaman login.",
  "data": {
    "user_id": "USR-PAS-0101",
    "user_role": "pasien",
    "user_nama": "Budi Hartono",
    "user_nik": "3372010101950001",
    "user_email": "budi.hartono@example.com",
    "user_nowa": "081234567890",
    "pasien_norm": "RM-2026-0101",
    "pasien_jeniskelamin": "Laki-laki",
    "pasien_tgllahir": "1995-01-01",
    "pasien_alamat": "Jl. Melati No. 45, Jebres, Surakarta",
    "pasien_nobpjs": null,
    "pasien_goldarah": "O",
    "pasien_alergiobat": null,
    "poli_nama": "Poli Umum",
    "created_at": "2026-11-22T04:44:54.000000Z"
  },
  "next_step": {
    "step": 4,
    "action": "login",
    "endpoint": "/api/v1/login"
  }
}
```

---

#### 4.1.2 Cek Validitas & Ketersediaan NIK
Endpoint live-validation untuk form registrasi sebelum submit penuh. Mendeteksi validitas 16 digit, mengecek duplikasi di database, serta mengekstrak informasi bawaan NIK (jenis kelamin, tanggal lahir, dan usia).

- **URL**: `/api/v1/check-nik` *(Alias: `/api/check-nik`)*
- **Method**: `POST` atau `GET`
- **Autentikasi**: Tidak diperlukan (Publik)

##### Request Parameter
- Jika via `POST` (Body JSON): `{"nik": "3372010101950001"}`
- Jika via `GET` (Query URL): `/api/v1/check-nik?nik=3372010101950001`

##### Response NIK Tersedia (`200 OK`)
```json
{
  "success": true,
  "available": true,
  "message": "NIK valid dan belum terdaftar. Anda dapat melanjutkan pendaftaran.",
  "nik_info": {
    "valid": true,
    "gender": "Laki-laki",
    "birth_date": "1995-01-01",
    "age": 31
  }
}
```

##### Response NIK Sudah Terdaftar (`409 Conflict`)
```json
{
  "success": false,
  "available": false,
  "message": "NIK sudah terdaftar dalam sistem KLINIKU. Silakan langsung login dengan NIK Anda."
}
```

---

#### 4.1.3 Login Pasien & Kirim OTP ke Email
Memverifikasi NIK, kata sandi, dan token Google reCAPTCHA v2. Jika valid, sistem menghasilkan 6 digit kode OTP dan mengirimkannya ke email terdaftar pasien via SMTP Gmail.

- **URL**: `/api/v1/login` *(Alias: `/api/v1/auth/login`, `/api/login`)*
- **Method**: `POST`
- **Autentikasi**: Tidak diperlukan (Publik)

##### Parameter Request Body
| Field | Tipe Data | Status | Keterangan |
| :--- | :--- | :--- | :--- |
| `nik` | String | **Wajib** | 16 digit NIK terdaftar. |
| `password` | String | **Wajib** | Kata sandi akun pasien. |
| `recaptcha_token` | String | **Wajib** | Token respon dari widget Google reCAPTCHA v2 ("Saya bukan robot"). |

##### Contoh Request
```json
{
  "nik": "3372020912254658",
  "password": "Pasien123!",
  "recaptcha_token": "test-valid-token"
}
```

##### Contoh Response Sukses (`200 OK` — Menunggu Verifikasi OTP)
```json
{
  "success": true,
  "message": "Kredensial dan reCAPTCHA valid. Kode OTP 6 digit telah dikirimkan ke email l***i@student.uns.ac.id.",
  "step": "otp_verification",
  "data": {
    "otp_session_id": "a6b4c1d2-7e8f-49a0-bc12-34567890abcd",
    "request_id": "a6b4c1d2-7e8f-49a0-bc12-34567890abcd",
    "channel": "email",
    "masked_email": "l***i@student.uns.ac.id",
    "expires_in": 300,
    "action_guide": "Buka kotak masuk atau folder spam email l***i@student.uns.ac.id, salin 6 digit kode OTP, lalu verifikasi pada endpoint /api/v1/verify-otp."
  }
}
```

##### Contoh Response Kredensial Salah (`401 Unauthorized`)
```json
{
  "success": false,
  "message": "Login gagal. NIK atau kata sandi yang Anda masukkan tidak sesuai."
}
```

##### Contoh Response reCAPTCHA Tidak Valid (`422 Unprocessable Content`)
```json
{
  "success": false,
  "message": "Verifikasi bot reCAPTCHA tidak valid atau telah kedaluwarsa.",
  "errors": {
    "recaptcha_token": [
      "Verifikasi bot reCAPTCHA tidak valid atau telah kedaluwarsa."
    ]
  }
}
```

---

#### 4.1.4 Verifikasi Kode OTP 6-Digit & Terbit JWT
Memverifikasi 6 digit kode OTP yang diterima melalui email. Jika valid, sistem langsung menerbitkan JWT Bearer Token dan data profil lengkap pengguna.

- **URL**: `/api/v1/verify-otp` *(Alias: `/api/v1/auth/verify-otp`, `/api/verify-otp`)*
- **Method**: `POST`
- **Autentikasi**: Tidak diperlukan (Publik — diverifikasi via `otp_session_id` dan `otp_code`)

##### Parameter Request Body
| Field | Tipe Data | Status | Keterangan |
| :--- | :--- | :--- | :--- |
| `otp_session_id` | String | **Wajib** | ID sesi OTP yang diperoleh dari respons endpoint login (alias: `request_id`). |
| `otp_code` | String | **Wajib** | 6 digit kode OTP yang diterima dari email (alias: `code`). |

##### Contoh Request
```json
{
  "otp_session_id": "a6b4c1d2-7e8f-49a0-bc12-34567890abcd",
  "otp_code": "847291"
}
```

##### Contoh Response Berhasil Terverifikasi (`200 OK` — Terbit JWT)
```json
{
  "success": true,
  "message": "Verifikasi OTP email berhasil! Selamat datang di Portal KLINIKU.",
  "data": {
    "status": "verified",
    "user": {
      "user_id": "USR-PAS-0001",
      "user_role": "pasien",
      "user_nama": "Yusuf Hidayat",
      "user_nik": "3372020912254658",
      "user_nowa": "081323756669",
      "user_email": "lintarkertapati@student.uns.ac.id",
      "user_status": "aktif",
      "profile": {
        "norm": "RM-2026-0001",
        "jeniskelamin": "Laki-laki",
        "tgllahir": "2025-12-09",
        "alamat": "Jl. Ki Hajar Dewantara No. 23, Palur, Karanganyar",
        "nobpjs": "0001136505587",
        "goldarah": "O",
        "alergiobat": null,
        "poli": "Poli KIA / Anak"
      }
    },
    "authorization": {
      "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJLTElOSUtVIiwic3ViIjoiVVNSLVBBUy0wMDAxIiwidXNlcl9pZCI6IlVTUi1QQVMtMDAwMSIsIm5payI6IjMzNzIwMjA5MTIyNTQ2NTgiLCJyb2xlIjoicGFzaWVuIiwibmFtYSI6Ill1c3VmIEhpZGF5YXQiLCJpYXQiOjE3OTUzMjcwMzksImV4cCI6MTc5NTQxMzQzOX0.LrQIE-4GdNyVLVLWJKw2YYOvI_RNqgxyFvzNJhy0bu0",
      "type": "Bearer",
      "expires_in": 86400
    }
  }
}
```

##### Contoh Response Kode OTP Salah (`422 Unprocessable Content`)
```json
{
  "success": false,
  "status": "invalid_code",
  "message": "Kode OTP yang Anda masukkan salah. Silakan periksa kembali email Anda."
}
```

##### Contoh Response Sesi Kedaluwarsa (`410 Gone`)
```json
{
  "success": false,
  "status": "expired",
  "message": "Sesi verifikasi OTP tidak ditemukan atau telah kedaluwarsa. Silakan lakukan login ulang."
}
```

---

#### 4.1.5 Kirim Ulang Kode OTP ke Email
Mengirimkan ulang kode OTP 6 digit baru ke alamat email pasien jika kode sebelumnya belum diterima atau masa berlakunya telah habis.

- **URL**: `/api/v1/resend-otp` *(Alias: `/api/v1/auth/resend-otp`, `/api/resend-otp`)*
- **Method**: `POST`
- **Autentikasi**: Tidak diperlukan (Publik)

##### Parameter Request Body
| Field | Tipe Data | Status | Keterangan |
| :--- | :--- | :--- | :--- |
| `otp_session_id` | String | **Wajib** | `otp_session_id` (atau `request_id`) dari sesi sebelumnya. |

##### Contoh Request
```json
{
  "otp_session_id": "a6b4c1d2-7e8f-49a0-bc12-34567890abcd"
}
```

##### Contoh Response Sukses (`200 OK`)
```json
{
  "success": true,
  "message": "Kode OTP baru berhasil dikirimkan ke email l***i@student.uns.ac.id.",
  "data": {
    "otp_session_id": "f8e7d6c5-4b3a-2109-8765-fedcba098765",
    "request_id": "f8e7d6c5-4b3a-2109-8765-fedcba098765",
    "channel": "email",
    "masked_email": "l***i@student.uns.ac.id",
    "expires_in": 300
  }
}
```

---

#### 4.1.6 Profil Pengguna & Rekam Medis (Protected)
Mengambil informasi lengkap profil pengguna dan rekam medis pasien yang sedang login untuk ditampilkan pada Dashboard Pasien.

- **URL**: `/api/v1/me` *(Alias: `/api/v1/auth/me`, `/api/me`)*
- **Method**: `GET`
- **Autentikasi**: **Wajib Bearer Token** (`Authorization: Bearer <TOKEN>`)

##### Contoh Header
```http
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Accept: application/json
```

##### Contoh Response Sukses (`200 OK`)
```json
{
  "success": true,
  "data": {
    "user_id": "USR-PAS-0001",
    "user_role": "pasien",
    "user_nama": "Yusuf Hidayat",
    "user_nik": "3372020912254658",
    "user_nowa": "081323756669",
    "user_email": "lintarkertapati@student.uns.ac.id",
    "user_status": "aktif",
    "profile": {
      "norm": "RM-2026-0001",
      "jeniskelamin": "Laki-laki",
      "tgllahir": "2025-12-09",
      "alamat": "Jl. Ki Hajar Dewantara No. 23, Palur, Karanganyar",
      "nobpjs": "0001136505587",
      "goldarah": "O",
      "alergiobat": null,
      "poli": "Poli KIA / Anak"
    }
  }
}
```

---

#### 4.1.7 Logout Pengguna (Protected)
Mengakhiri sesi pengguna yang sedang aktif di backend.

- **URL**: `/api/v1/logout` *(Alias: `/api/v1/auth/logout`, `/api/logout`)*
- **Method**: `POST`
- **Autentikasi**: **Wajib Bearer Token** (`Authorization: Bearer <TOKEN>`)

##### Contoh Response Sukses (`200 OK`)
```json
{
  "success": true,
  "message": "Logout berhasil. Sesi Anda telah diakhiri."
}
```

---

### 4.2 Data Master & Penunjang

#### 4.2.1 Master Data Poliklinik
Mengambil daftar poliklinik aktif untuk opsi pilihan poli rujukan/tujuan pada dropdown form registrasi.

- **URL**: `/api/v1/polis` *(Alias: `/api/polis`)*
- **Method**: `GET`
- **Autentikasi**: Tidak diperlukan (Publik)

---

## 5. Struktur Basis Data & Model Entitas

Data autentikasi dan rekam medis dikelola dengan integritas referensial ketat pada MySQL 8.4:

### 1. Tabel `user` (Tabel Master Akun)
| Kolom | Tipe Data | Constraint | Keterangan |
| :--- | :--- | :--- | :--- |
| `user_id` | `VARCHAR(30)` | **PRIMARY KEY** | ID unik terstruktur (`USR-PAS-XXXX`, `USR-DOK-XXXX`, `USR-STF-XXXX`). |
| `user_role` | `ENUM('dokter','staf','pasien')` | NOT NULL | Peran pengguna dalam ekosistem klinik. |
| `user_nama` | `VARCHAR(150)` | NOT NULL | Nama lengkap pengguna. |
| `user_nik` | `CHAR(16)` | **UNIQUE**, NOT NULL | 16 digit NIK KTP. |
| `user_nowa` | `VARCHAR(20)` | NOT NULL | Nomor WhatsApp aktif. |
| `user_email` | `VARCHAR(100)` | NOT NULL, **UNIQUE** | Alamat email aktif untuk menerima OTP. |
| `user_password` | `VARCHAR(255)` | NOT NULL | Hash kata sandi terenkripsi Bcrypt. |
| `user_status` | `ENUM('aktif','nonaktif')` | DEFAULT `'aktif'` | Status keaktifan akun. |
| `user_createdat` | `DATETIME` | NOT NULL | Waktu pembuatan akun. |
| `user_updatedat` | `DATETIME` | NULL | Waktu pembaruan akun. |

### 2. Tabel `pasien` (Tabel Profil Pasien & Rekam Medis)
| Kolom | Tipe Data | Constraint | Keterangan |
| :--- | :--- | :--- | :--- |
| `pasien_user_id` | `VARCHAR(30)` | **PRIMARY KEY**, FK | Merujuk ke `user.user_id` (`ON DELETE CASCADE`). |
| `pasien_role` | `ENUM('pasien')` | CHECK | Wajib bernilai `'pasien'`. |
| `pasien_norm` | `VARCHAR(30)` | **UNIQUE**, NOT NULL | Nomor Rekam Medis terstruktur (`RM-YYYY-XXXX`). |
| `pasien_jeniskelamin` | `ENUM('Laki-laki','Perempuan')` | NOT NULL | Jenis kelamin pasien. |
| `pasien_tgllahir` | `DATE` | NOT NULL | Tanggal lahir pasien. |
| `pasien_alamat` | `TEXT` | NOT NULL | Alamat domisili lengkap. |
| `pasien_nobpjs` | `VARCHAR(30)` | **UNIQUE**, NULL | Nomor kartu BPJS / KIS (13 digit). |
| `pasien_goldarah` | `ENUM('A','B','AB','O')` | NULL | Golongan darah. |
| `pasien_alergiobat` | `TEXT` | NULL | Riwayat alergi obat. |
| `pasien_poli_id` | `INT` | FK, NULL | Merujuk ke `poli.poli_id`. |

---

## 6. Panduan Pengujian via cURL (End-to-End Workflow)

Berikut adalah urutan perintah cURL lengkap untuk menguji keseluruhan alur mulai dari pengecekan NIK hingga logout:

### 1. Cek Ketersediaan NIK
```bash
curl -s -X POST http://localhost:8080/api/v1/check-nik \
  -H "Content-Type: application/json" \
  -d '{"nik": "3372010101950001"}'
```

### 2. Pendaftaran Pasien Baru
```bash
curl -s -X POST http://localhost:8080/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "nik": "3372010101950001",
    "nama": "Budi Hartono",
    "email": "budi.hartono@example.com",
    "nowa": "081234567890",
    "password": "Password123!",
    "alamat": "Jl. Melati No. 45, Jebres, Surakarta",
    "goldarah": "O",
    "poli_id": 1
  }'
```

### 3. Login Pasien (Kirim OTP ke Email)
```bash
curl -s -X POST http://localhost:8080/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "nik": "3372020912254658",
    "password": "Pasien123!",
    "recaptcha_token": "test-valid-token"
  }'
```
*Salin nilai `data.otp_session_id` dari respon di atas.*

### 4. Verifikasi Kode OTP Email (Mendapatkan JWT Bearer Token)
```bash
curl -s -X POST http://localhost:8080/api/v1/verify-otp \
  -H "Content-Type: application/json" \
  -d '{
    "otp_session_id": "<MASUKKAN_OTP_SESSION_ID_DARI_STEP_3>",
    "otp_code": "847291"
  }'
```
*Salin nilai `data.authorization.token` dari respon di atas.*

### 5. Akses Profil Pasien (Dashboard /me)
```bash
curl -s -X GET http://localhost:8080/api/v1/me \
  -H "Authorization: Bearer <MASUKKAN_JWT_TOKEN>"
```

### 6. Logout Pengguna
```bash
curl -s -X POST http://localhost:8080/api/v1/logout \
  -H "Authorization: Bearer <MASUKKAN_JWT_TOKEN>"
```

---

## 7. Pengujian Otomatis (PHPUnit Test Suite)

Untuk menjalankan seluruh test suite otomatis:

```bash
docker compose exec app php artisan test
```

Untuk menjalankan kelompok test secara terpisah:
```bash
# Pengujian autentikasi, login, Email OTP SMTP, & JWT
docker compose exec app php artisan test --filter AuthLoginTest

# Pengujian pendaftaran pasien, auto-decode NIK, validasi email & nomor RM
docker compose exec app php artisan test --filter PatientRegistrationTest

# Pengujian 5 Pilar Keamanan (Bcrypt, reCAPTCHA, Single-Use OTP, SQLi, Rate Limiting)
docker compose exec app php artisan test --filter SecurityFeaturesTest
```
