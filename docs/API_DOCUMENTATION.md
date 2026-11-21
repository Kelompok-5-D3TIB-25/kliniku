# Dokumentasi API KLINIKU (Backend)

Dokumentasi resmi antarmuka pemrograman aplikasi (API) untuk sistem backend **KLINIKU**. Seluruh endpoint dirancang menggunakan standar RESTful JSON dan ditujukan untuk integrasi dengan aplikasi frontend (Web / Mobile).

---

## 1. Informasi Umum

- **Base URL Pengembangan**: `http://localhost:8080/api/v1` (tersedia juga alias di `http://localhost:8080/api`)
- **Format Pertukaran Data**: `application/json`
- **Encoding Karakter**: `UTF-8`

### Header Standar Request
Semua request berbasis `POST` / `PUT` / `PATCH` wajib menyertakan header berikut:
```http
Content-Type: application/json
Accept: application/json
```

### Standar HTTP Status Code
| Kode Status | Keterangan |
| :--- | :--- |
| `200 OK` | Permintaan berhasil diproses. |
| `201 Created` | Data baru berhasil dibuat (misal: akun pasien & nomor RM berhasil diterbitkan). |
| `400 Bad Request` | Permintaan tidak sesuai format yang diharapkan. |
| `409 Conflict` | Konflik data, misalnya NIK sudah terdaftar dalam sistem. |
| `422 Unprocessable Content` | Validasi input gagal atau terdapat duplikasi field unik. |
| `500 Internal Server Error` | Terjadi kegagalan pemrosesan pada internal server / database. |

---

## 2. Alur Registrasi Pasien (Patient Registration Flow)

```
[Mulai]
   │
   ▼
[Pasien mengisi formulir pendaftaran]
(NIK 16 digit, Nama, No. WhatsApp, Password, Alamat)
   │
   ▼
[Kirim request ke POST /api/v1/register]
   │
   ├─► Validasi Gagal / NIK Duplikat?
   │      │
   │      └─► Return HTTP 422 (Pesan error spesifik) -> Pasien memperbaiki form
   │
   └─► Validasi Sukses?
          │
          ├─► Hash Password dengan Bcrypt
          ├─► Decode NIK (Otomatis deteksi Jenis Kelamin & Tanggal Lahir jika tidak diisi)
          ├─► Generate User ID berurutan: USR-PAS-XXXX
          ├─► Terbitkan Nomor Rekam Medis: RM-YYYY-XXXX
          ├─► Simpan ke tabel 'user' & 'pasien' dalam DB Transaction
          │
          └─► Return HTTP 201 Created -> Lanjut ke Tahap Login (Step 4)
```

---

## 3. Rincian Endpoint

### 3.1 Pendaftaran Akun Pasien Baru
Mendaftarkan akun pasien baru, menerbitkan ID Pengguna (`user_id`), dan mencetak Nomor Rekam Medis (`pasien_norm`) secara otomatis.

- **URL**: `/api/v1/register` *(Alias: `/api/v1/auth/register`, `/api/register`)*
- **Method**: `POST`
- **Autentikasi**: Tidak diperlukan (Public)

#### Parameter Request Body
| Field | Tipe Data | Wajib/Opsional | Aturan & Keterangan |
| :--- | :--- | :--- | :--- |
| `nik` | String | **Wajib** | Tepat 16 digit angka (`regex:/^[0-9]{16}$/`), unik dalam tabel `user`. |
| `nama` | String | **Wajib** | Nama lengkap pasien (2 - 150 karakter). |
| `nowa` | String | **Wajib** | Nomor WhatsApp aktif (10 - 15 digit angka, awalan `08`, `62`, atau `+62`). |
| `password` | String | **Wajib** | Kata sandi akun (minimal 6 karakter). |
| `password_confirmation` | String | Opsional | Jika dikirim, nilainya harus sama dengan `password`. |
| `alamat` | String | **Wajib** | Alamat domisili lengkap pasien (3 - 255 karakter). |
| `jeniskelamin` | String | Opsional | Nilai: `Laki-laki` atau `Perempuan`. *Jika tidak diisi, otomatis dideteksi dari NIK.* |
| `tgllahir` | String (Date) | Opsional | Format `YYYY-MM-DD`. *Jika tidak diisi, otomatis dideteksi dari NIK.* |
| `nobpjs` | String | Opsional | Nomor kartu BPJS / KIS (tepat 13 digit angka, unik). |
| `goldarah` | String | Opsional | Nilai: `A`, `B`, `AB`, atau `O`. |
| `alergiobat` | String | Opsional | Riwayat alergi obat (maks. 255 karakter). |
| `poli_id` | Integer | Opsional | ID Poliklinik rujukan/tujuan utama (1-5). |

---

#### Contoh Request (Minimal)
```json
{
  "nik": "3372010101950001",
  "nama": "Budi Hartono",
  "nowa": "081234567890",
  "password": "Password123!",
  "alamat": "Jl. Melati No. 45, Jebres, Surakarta"
}
```

#### Contoh Request (Lengkap)
```json
{
  "nik": "3372085005990002",
  "nama": "Siti Nurhaliza",
  "nowa": "081298765432",
  "password": "PasienSecret123!",
  "password_confirmation": "PasienSecret123!",
  "alamat": "Jl. Kemuning No. 15, Jebres, Surakarta",
  "jeniskelamin": "Perempuan",
  "tgllahir": "1999-05-10",
  "nobpjs": "0001234567890",
  "goldarah": "B",
  "alergiobat": "Amoksisilin",
  "poli_id": 3
}
```

---

#### Contoh Response Sukses (`201 Created`)
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
    "pasien_goldarah": null,
    "pasien_alergiobat": null,
    "poli_nama": null,
    "created_at": "2026-11-21T11:44:54.000000Z"
  },
  "next_step": {
    "step": 4,
    "action": "login",
    "endpoint": "/api/v1/login"
  }
}
```

---

#### Contoh Response Validasi Gagal (`422 Unprocessable Content`)
```json
{
  "success": false,
  "message": "Validasi format atau duplikasi data gagal. Silakan periksa kembali formulir pendaftaran.",
  "errors": {
    "nik": [
      "NIK (Nomor Induk Kependudukan) wajib diisi."
    ],
    "nama": [
      "Nama lengkap wajib diisi."
    ],
    "nowa": [
      "Format nomor WhatsApp tidak valid. Gunakan format yang valid seperti 081234567890."
    ],
    "password": [
      "Password minimal terdiri dari 6 karakter."
    ],
    "alamat": [
      "Alamat domisili wajib diisi."
    ]
  }
}
```

#### Contoh Response Duplikasi NIK (`422 Unprocessable Content`)
```json
{
  "success": false,
  "message": "Validasi format atau duplikasi data gagal. Silakan periksa kembali formulir pendaftaran.",
  "errors": {
    "nik": [
      "NIK sudah terdaftar dalam sistem KLINIKU. Silakan periksa kembali atau gunakan opsi Login."
    ]
  }
}
```

---

### 3.2 Cek Validitas & Ketersediaan NIK (Live Validation)
Endpoint pembantu untuk form frontend dalam memeriksa apakah format 16 digit NIK valid dan apakah NIK tersebut sudah pernah terdaftar sebelum form di-submit secara penuh.

- **URL**: `/api/v1/check-nik` *(Mendukung `POST` & `GET`)*
- **Method**: `POST` atau `GET`
- **Autentikasi**: Tidak diperlukan (Public)

#### Request Parameter
- Jika via `POST` (JSON Body): `{"nik": "3372010101900001"}`
- Jika via `GET` (Query String): `/api/v1/check-nik?nik=3372010101900001`

---

#### Response NIK Tersedia & Valid (`200 OK`)
Sistem otomatis mendekode NIK untuk mengetahui jenis kelamin, tanggal lahir, dan estimasi usia pasien:
```json
{
  "success": true,
  "available": true,
  "message": "NIK valid dan belum terdaftar. Anda dapat melanjutkan pendaftaran.",
  "nik_info": {
    "valid": true,
    "gender": "Laki-laki",
    "birth_date": "1990-01-01",
    "age": 36
  }
}
```

#### Response NIK Sudah Terdaftar (`409 Conflict`)
```json
{
  "success": false,
  "available": false,
  "message": "NIK sudah terdaftar dalam sistem KLINIKU. Silakan langsung login dengan NIK Anda."
}
```

#### Response Format NIK Salah (`422 Unprocessable Content`)
```json
{
  "success": false,
  "available": false,
  "message": "NIK harus berupa 16 digit angka.",
  "errors": {
    "nik": [
      "NIK harus berupa 16 digit angka."
    ]
  }
}
```

---

### 3.3 Daftar Poliklinik (Master Data)
Mengambil daftar master poliklinik yang tersedia di klinik untuk opsi pilihan poliklinik tujuan pada formulir registrasi.

- **URL**: `/api/v1/polis` *(Alias: `/api/polis`)*
- **Method**: `GET`
- **Autentikasi**: Tidak diperlukan (Public)

#### Contoh Response (`200 OK`)
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

## 4. Struktur Database & Model Entitas

Data registrasi pasien disimpan ke dalam dua tabel dengan relasi `1-to-1` terikat integritas referensial:

### Tabel `user` (Tabel Akun Master)
Menyimpan data identitas umum untuk semua peran (dokter, staf, pasien):
- `user_id`: ID unik berformat `USR-PAS-XXXX` (Primary Key).
- `user_role`: ENUM (`'dokter'`, `'staf'`, `'pasien'`).
- `user_nama`: Nama lengkap pasien.
- `user_nik`: NIK 16 digit (Unique Key).
- `user_nowa`: Nomor WhatsApp aktif.
- `user_email`: Bernilai `NULL` untuk pasien.
- `user_password`: Hash kata sandi terenkripsi (Bcrypt).
- `user_status`: Status akun (`'aktif'` / `'nonaktif'`).
- `user_createdat` / `user_updatedat`: Timestamp pencatatan.

### Tabel `pasien` (Tabel Profil Pasien)
Menyimpan profil medis khusus pasien:
- `pasien_user_id`: Foreign key merujuk ke `user(user_id, user_role)` dengan `ON DELETE CASCADE`.
- `pasien_role`: Wajib bernilai `'pasien'` (diverifikasi oleh constraint `CHECK (pasien_role = 'pasien')`).
- `pasien_norm`: Nomor Rekam Medis unik berformat `RM-YYYY-XXXX` (Unique Key).
- `pasien_jeniskelamin`: ENUM (`'Laki-laki'`, `'Perempuan'`).
- `pasien_tgllahir`: Tanggal lahir (DATE).
- `pasien_alamat`: Alamat domisili.
- `pasien_nobpjs`: Nomor BPJS 13 digit (Unique Key, nullable).
- `pasien_goldarah`: Golongan darah (`'A'`, `'B'`, `'AB'`, `'O'`, nullable).
- `pasien_alergiobat`: Catatan alergi obat (nullable).
- `pasien_poli_id`: Foreign key ke tabel `poli(poli_id)` (nullable).

---

## 5. Contoh Pengujian Endpoint via cURL

### 1. Cek Ketersediaan NIK
```bash
curl -X POST http://localhost:8080/api/v1/check-nik \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"nik": "3372010101900001"}'
```

### 2. Registrasi Pasien Baru
```bash
curl -X POST http://localhost:8080/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nik": "3372010101900001",
    "nama": "Ahmad Fauzi",
    "nowa": "081234567890",
    "password": "Password123!",
    "alamat": "Jl. Slamet Riyadi No. 100, Surakarta",
    "goldarah": "O",
    "poli_id": 1
  }'
```

### 3. Mengambil Daftar Poliklinik
```bash
curl -X GET http://localhost:8080/api/v1/polis \
  -H "Accept: application/json"
```

---

## 6. Pengujian Otomatis (Unit / Feature Test)

Untuk menjalankan seluruh rangkaian pengujian otomatis pada endpoint registrasi pasien, jalankan perintah berikut:

```bash
docker compose exec app php artisan test --filter PatientRegistrationTest
```
