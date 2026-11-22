# Dokumentasi Lengkap API KLINIKU (Backend)

Dokumentasi resmi antarmuka pemrograman aplikasi (API) untuk sistem backend **KLINIKU**. Seluruh endpoint dirancang menggunakan arsitektur RESTful JSON dan ditujukan untuk integrasi dengan antarmuka pengguna frontend (Web, Mobile, atau Kiosk).

Sistem backend ini mengimplementasikan alur lengkap pendaftaran pasien baru, penerbitan Nomor Rekam Medis otomatis, login aman dengan proteksi **Google reCAPTCHA v2**, verifikasi dua langkah melalui **FlowKirim WhatsApp Tap-to-Verify (`whatsapp_tap`)**, serta penerbitan token sesi berbasis **JSON Web Token (JWT)**.

---

## Daftar Isi
1. [Informasi Umum & Konvensi API](#1-informasi-umum--konvensi-api)
   - [Base URL & Alias](#base-url--alias)
   - [Header Standar Request](#header-standar-request)
   - [Standar HTTP Status Code](#standar-http-status-code)
   - [Arsitektur Keamanan & Autentikasi](#arsitektur-keamanan--autentikasi)
2. [Alur Registrasi Pasien (Patient Registration Flow)](#2-alur-registrasi-pasien-patient-registration-flow)
3. [Alur Login & Verifikasi WhatsApp (WhatsApp Tap-to-Verify Flow)](#3-alur-login--verifikasi-whatsapp-whatsapp-tap-to-verify-flow)
4. [Katalog Endpoint API](#4-katalog-endpoint-api)
   - [4.1 Autentikasi & Akun](#41-autentikasi--akun)
     - [`POST /api/v1/register` — Registrasi Pasien Baru](#411-registrasi-pasien-baru)
     - [`POST /api/v1/check-nik` — Cek Validitas & Ketersediaan NIK](#412-cek-validitas--ketersediaan-nik)
     - [`POST /api/v1/login` — Login Pasien & Start Verifikasi WhatsApp](#413-login-pasien--start-verifikasi-whatsapp)
     - [`POST /api/v1/verify-otp` — Verifikasi & Polling Status WhatsApp Tap](#414-verifikasi--polling-status-whatsapp-tap)
     - [`POST /api/v1/resend-otp` — Kirim Ulang Verifikasi WhatsApp](#415-kirim-ulang-verifikasi-whatsapp)
     - [`GET /api/v1/me` — Profil Pengguna & Rekam Medis (Protected)](#416-profil-pengguna--rekam-medis-protected)
     - [`POST /api/v1/logout` — Logout Pengguna (Protected)](#417-logout-pengguna-protected)
   - [4.2 Data Master & Penunjang](#42-data-master--penunjang)
     - [`GET /api/v1/polis` — Master Data Poliklinik](#421-master-data-poliklinik)
5. [Struktur Basis Data & Model Entitas](#5-struktur-basis-data--model-entitas)
6. [Panduan Pengujian via cURL (End-to-End Workflow)](#6-panduan-pengujian-via-curl-end-to-end-workflow)
7. [Pengujian Otomatis (PHPUnit / Pest Test Suite)](#7-pengujian-otomatis-phpunit--pest-test-suite)

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
| `200 OK` | Berhasil | Request sukses (login start, query profile, verified OTP). |
| `201 Created` | Berhasil Dibuat | Akun pasien baru dan Nomor Rekam Medis (RM) berhasil diterbitkan. |
| `202 Accepted` | Diterima / Dalam Proses | Polling WhatsApp Tap masih menunggu pengiriman pesan oleh pengguna (`pending`). |
| `401 Unauthorized` | Tidak Terotentikasi | NIK/Password salah atau token JWT tidak disertakan/tidak valid. |
| `403 Forbidden` | Akses Ditolak | Akun berstatus non-aktif atau hak akses peran tidak mencukupi. |
| `404 Not Found` | Tidak Ditemukan | Data pengguna atau resource tidak ditemukan. |
| `409 Conflict` | Konflik Data | NIK sudah terdaftar saat pemeriksaan live validation. |
| `410 Gone` | Kedaluwarsa | Sesi verifikasi WhatsApp / token OTP telah kedaluwarsa (TTL habis). |
| `422 Unprocessable Content` | Validasi Gagal | Format input tidak valid, reCAPTCHA bot token salah, atau duplikasi field unik. |
| `502 Bad Gateway` | Gateway Error | Server verifikasi pihak ketiga (FlowKirim) mengembalikan kegagalan pemrosesan. |

### Arsitektur Keamanan & Autentikasi
1. **Google reCAPTCHA v2 ("Saya bukan robot")**:
   - Widget frontend memverifikasi bahwa pengisi form login adalah manusia.
   - Token verifikasi dikirimkan ke backend melalui field `recaptcha_token` dan divalidasi ke Google Siteverify API.
   - Kredensial aktif:
     - **Site Key**: `6LfEiisuAAAAAM1dgo05Kv4IqPwYUF5NTWbDUN9V`
     - **Secret Key**: `6LfEiisuAAAAAEPuGqfaxFfGOtUGHNdZq0djWVkd`
   - *Testing Token*: Token `test-valid-token` atau `passthrough-token` dapat digunakan pada lingkungan local/testing.

2. **FlowKirim WhatsApp Tap-to-Verify (`whatsapp_tap`)**:
   - Metode verifikasi tanpa mengharuskan pengguna mengetikkan kode OTP manual secara rumit.
   - Backend memanggil endpoint FlowKirim `/start` untuk menghasilkan deep-link WhatsApp (`wa_deeplink`).
   - Pengguna cukup menekan tombol di antarmuka frontend, WhatsApp otomatis terbuka dengan pesan verifikasi, lalu pengguna menekan tombol "Kirim".
   - Frontend melakukan polling ke backend (`/verify-otp`) hingga status berubah menjadi `verified`.
   - **Handling Zero Balance / Insufficient Balance**:
     - Jika saldo FlowKirim kosong (HTTP 402 `INSUFFICIENT_BALANCE`), backend di lingkungan `local` / `testing` secara otomatis mengaktifkan mode simulasi verifikasi (`is_simulation: true`) sehingga pengujian dan pengembangan frontend tidak terhambat. Begitu akun FlowKirim diisi saldo (top-up), pengiriman WhatsApp langsung berjalan secara live tanpa perubahan kode.

3. **JSON Web Token (JWT)**:
   - Diterbitkan menggunakan algoritma `HS256` setelah verifikasi WhatsApp berhasil.
   - Masa berlaku default: 24 jam (`JWT_TTL=86400`).
   - Payload memuat: `user_id`, `nik`, `role` (`pasien`/`dokter`/`staf`), dan `nama`.

---

## 2. Alur Registrasi Pasien (Patient Registration Flow)

```
[Mulai] Pasien membuka halaman Pendaftaran KLINIKU
   │
   ▼
[Input Formulir] Pasien mengisi: NIK (16 digit), Nama, No. WhatsApp, Password, Alamat
   │
   ▼
[Kirim Data] POST /api/v1/register
   │
   ├──► [Validasi Gagal / NIK Duplikat?]
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

## 3. Alur Login & Verifikasi WhatsApp (WhatsApp Tap-to-Verify Flow)

```mermaid
sequenceDiagram
    autonumber
    actor Pasien as Pasien (Frontend)
    participant API as Backend KLINIKU
    participant Recaptcha as Google reCAPTCHA
    participant FlowKirim as FlowKirim API
    participant WA as WhatsApp App

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
            API->>FlowKirim: POST /start (phone, channel="whatsapp_tap")
            FlowKirim-->>API: 200 OK (request_id, wa_deeplink, expires_in)
            API-->>Pasien: HTTP 200 (step="otp_verification", request_id, wa_deeplink)
        end
    end

    Note over Pasien,WA: Tahap 2: Pasien Mengirim Pesan Verifikasi WhatsApp
    Pasien->>WA: Buka wa_deeplink (Mobile Tap / Scan QR di Desktop)
    WA->>FlowKirim: Pasien menekan "Kirim" pesan verifikasi dari nomor terdaftarnya

    Note over Pasien,API: Tahap 3: Polling Status & Terbit JWT Token
    loop Polling Status Verifikasi (Setiap 2-3 detik)
        Pasien->>API: POST /api/v1/verify-otp (request_id)
        API->>FlowKirim: GET /{request_id}
        alt Status Masih Pending
            FlowKirim-->>API: status: "pending"
            API-->>Pasien: HTTP 202 Accepted (Menunggu verifikasi WhatsApp)
        else Status Verified
            FlowKirim-->>API: status: "verified"
            API->>API: Terbitkan JWT Bearer Token & Ambil Profil Pasien
            API-->>Pasien: HTTP 200 OK (JWT Token, User & Patient Dashboard Data)
        end
    end

    Note over Pasien,API: Tahap 4: Akses Dashboard Terproteksi
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

##### Contoh Response Validasi Gagal (`422 Unprocessable Content`)
```json
{
  "success": false,
  "message": "Validasi format atau duplikasi data gagal. Silakan periksa kembali formulir pendaftaran.",
  "errors": {
    "nik": ["NIK sudah terdaftar dalam sistem KLINIKU. Silakan periksa kembali atau gunakan opsi Login."]
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

#### 4.1.3 Login Pasien & Start Verifikasi WhatsApp
Memverifikasi NIK, kata sandi, dan token Google reCAPTCHA v2. Jika valid, sistem memanggil layanan FlowKirim dengan channel `whatsapp_tap` dan mengembalikan tautan deep-link WhatsApp (`wa_deeplink`) serta `request_id`.

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

##### Contoh Response Sukses (`200 OK` — Menunggu Verifikasi WhatsApp)
```json
{
  "success": true,
  "message": "Kredensial dan reCAPTCHA valid. Silakan selesaikan verifikasi via WhatsApp untuk masuk.",
  "step": "otp_verification",
  "data": {
    "request_id": "7b1c4a9e-11df-4b2a-8f90-8c290192e2a1",
    "channel": "whatsapp_tap",
    "masked_phone": "*********6669",
    "wa_deeplink": "https://wa.me/6285113736221?text=VERIFY-AB12CD34EF",
    "wa_number": "6285113736221",
    "expires_in": 300,
    "action_guide": "Buka tautan wa_deeplink di WhatsApp lalu tekan Kirim. Kemudian periksa status dengan memanggil endpoint /api/v1/verify-otp."
  }
}
```

> **Catatan Alur Frontend**:
> - Di perangkat smartphone: Frontend dapat menyediakan tombol "Verifikasi via WhatsApp" yang langsung membuka `wa_deeplink`.
> - Di desktop / PC: Frontend dapat merender nilai `wa_deeplink` menjadi QR Code agar dapat di-scan menggunakan aplikasi kamera / WhatsApp di HP pengguna.

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
  "message": "Verifikasi reCAPTCHA gagal. Pastikan Anda telah mencentang widget \"Saya bukan robot\" dengan benar.",
  "errors": {
    "recaptcha_token": [
      "Verifikasi bot reCAPTCHA tidak valid atau telah kedaluwarsa."
    ]
  }
}
```

---

#### 4.1.4 Verifikasi & Polling Status WhatsApp Tap
Digunakan oleh frontend untuk memvalidasi apakah pengguna sudah menekan tombol "Kirim" di WhatsApp melalui `wa_deeplink`. Endpoint ini juga mendukung verifikasi manual jika parameter `code` disertakan.

- **URL**: `/api/v1/verify-otp` *(Alias: `/api/v1/auth/verify-otp`, `/api/verify-otp`)*
- **Method**: `POST`
- **Autentikasi**: Tidak diperlukan (Publik — diverifikasi via `request_id`)

##### Parameter Request Body
| Field | Tipe Data | Status | Keterangan |
| :--- | :--- | :--- | :--- |
| `request_id` | String | **Wajib** | Nilai `request_id` yang diterima dari respons endpoint login. |
| `code` | String | Opsional | Kode verifikasi manual 4–8 digit (hanya digunakan jika bukan metode tap). |

##### Contoh Request
```json
{
  "request_id": "7b1c4a9e-11df-4b2a-8f90-8c290192e2a1"
}
```

##### Contoh Response Status Pending (`202 Accepted`)
Dikembalikan saat pengguna belum mengirim pesan WhatsApp:
```json
{
  "success": false,
  "status": "pending",
  "message": "Menunggu verifikasi WhatsApp. Silakan buka tautan wa_deeplink dan kirim pesan verifikasi."
}
```

##### Contoh Response Berhasil Terverifikasi (`200 OK` — Terbit JWT)
Dikembalikan saat pesan WhatsApp telah diterima FlowKirim:
```json
{
  "success": true,
  "message": "Verifikasi WhatsApp berhasil! Selamat datang di Portal KLINIKU.",
  "data": {
    "status": "verified",
    "user": {
      "user_id": "USR-PAS-0001",
      "user_role": "pasien",
      "user_nama": "Yusuf Hidayat",
      "user_nik": "3372020912254658",
      "user_nowa": "081323756669",
      "user_email": null,
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

##### Contoh Response Sesi Kedaluwarsa (`410 Gone`)
```json
{
  "success": false,
  "status": "expired",
  "message": "Sesi verifikasi tidak ditemukan atau telah kedaluwarsa. Silakan lakukan login ulang."
}
```

---

#### 4.1.5 Kirim Ulang Verifikasi WhatsApp
Memperbarui sesi verifikasi dan menghasilkan deep-link WhatsApp baru apabila batas waktu (`ttl`) sesi sebelumnya hampir habis atau kedaluwarsa.

- **URL**: `/api/v1/resend-otp` *(Alias: `/api/v1/auth/resend-otp`, `/api/resend-otp`)*
- **Method**: `POST`
- **Autentikasi**: Tidak diperlukan (Publik)

##### Parameter Request Body
| Field | Tipe Data | Status | Keterangan |
| :--- | :--- | :--- | :--- |
| `request_id` | String | **Wajib** | `request_id` dari sesi login sebelumnya. |

##### Contoh Request
```json
{
  "request_id": "7b1c4a9e-11df-4b2a-8f90-8c290192e2a1"
}
```

##### Contoh Response Sukses (`200 OK`)
```json
{
  "success": true,
  "message": "Permintaan verifikasi WhatsApp baru berhasil dibuat.",
  "data": {
    "request_id": "8c2d5b0f-22ea-4c3b-9g01-9d300203f3b2",
    "channel": "whatsapp_tap",
    "masked_phone": "*********6669",
    "wa_deeplink": "https://wa.me/6285113736221?text=VERIFY-CD34EF56GH",
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
    "user_email": null,
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

##### Contoh Response Token Tidak Disertakan / Tidak Valid (`401 Unauthorized`)
```json
{
  "success": false,
  "message": "Token autentikasi tidak ditemukan. Harap sertakan Authorization: Bearer <token> pada header."
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

##### Contoh Response Sukses (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "poli_id": 1,
      "poli_nama": "Poli Umum",
      "poli_keterangan": null
    },
    {
      "poli_id": 2,
      "poli_nama": "Poli Umum & Geriatri (Lansia)",
      "poli_keterangan": null
    },
    {
      "poli_id": 3,
      "poli_nama": "Poli KIA / Anak",
      "poli_keterangan": null
    },
    {
      "poli_id": 4,
      "poli_nama": "Poli Gigi & Mulut",
      "poli_keterangan": null
    },
    {
      "poli_id": 5,
      "poli_nama": "Poli Kandungan & Kebidanan",
      "poli_keterangan": null
    }
  ]
}
```

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
| `user_email` | `VARCHAR(100)` | NULL | Alamat email (hanya untuk dokter/staf). |
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
    "nowa": "081234567890",
    "password": "Password123!",
    "alamat": "Jl. Melati No. 45, Jebres, Surakarta",
    "goldarah": "O",
    "poli_id": 1
  }'
```

### 3. Login Pasien (Mendapatkan Sesi WhatsApp Tap)
```bash
curl -s -X POST http://localhost:8080/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "nik": "3372020912254658",
    "password": "Pasien123!",
    "recaptcha_token": "test-valid-token"
  }'
```
*Salin nilai `data.request_id` dari respon di atas.*

### 4. Verifikasi WhatsApp Tap (Mendapatkan JWT Bearer Token)
```bash
curl -s -X POST http://localhost:8080/api/v1/verify-otp \
  -H "Content-Type: application/json" \
  -d '{
    "request_id": "<MASUKKAN_REQUEST_ID_DARI_STEP_3>"
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

## 7. Pengujian Otomatis (PHPUnit / Pest Test Suite)

Untuk menjalankan seluruh test suite otomatis yang mencakup validasi input, kegagalan kredensial, proteksi bot reCAPTCHA, inisiasi FlowKirim WhatsApp Tap, penerbitan JWT, serta otorisasi rute:

```bash
docker compose exec app php artisan test
```

Untuk menjalankan kelompok test secara terpisah:
```bash
# Pengujian autentikasi, login, FlowKirim WhatsApp, & JWT
docker compose exec app php artisan test --filter AuthLoginTest

# Pengujian pendaftaran pasien, auto-decode NIK, & pembuatan nomor RM
docker compose exec app php artisan test --filter PatientRegistrationTest
```
