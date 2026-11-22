# Panduan Lengkap Deployment KLINIKU ke InfinityFree

Panduan teknis dan operasional untuk melakukan deployment sistem backend **KLINIKU** (Laravel 11/13 + MySQL) dan antarmuka web ke layanan shared hosting **InfinityFree** tanpa akses SSH/CLI.

---

## ⚠️ Aturan Krusial InfinityFree Free Hosting

> [!CAUTION]
> **InfinityFree secara otomatis MENGHAPUS berkas yang berada di luar folder `/htdocs`.**  
> Sistem pembersih otomatis (*automated cleanup cron*) dan proteksi PHP `open_basedir` di InfinityFree melarang berkas website berada di luar `/htdocs`. Oleh karena itu, seluruh berkas proyek harus diletakkan **di dalam folder `/htdocs`**, dengan pengamanan ketat melalui [.htaccess](file:///root/Melissa/kliniku/frontend/.htaccess).

---

## 1. Struktur Berkas di Dalam `/htdocs`

Semua berkas di-upload ke dalam folder **`/htdocs`**:

```text
/htdocs/ (Web Root Publik InfinityFree)
│
├── .htaccess                  <-- Proteksi keamanan folder & pembagi rute API vs Frontend
├── index.php                  <-- Entry point Laravel API
├── index.html                 <-- Halaman GUI Penguji API (atau hasil build Vue nantinya)
│
├── app/                       <-- Controller, Model, Middleware, Service (~128 KB)
├── bootstrap/                 <-- Inisialisasi app (~48 KB)
├── config/                    <-- Konfigurasi database & service (~72 KB)
├── database/                  <-- Migrations (~50 KB)
├── routes/                    <-- Definisi rute API (~16 KB)
├── storage/                   <-- Folder writable (Wajib CHMOD 775/777)
├── vendor/                    <-- Dependensi Composer produksi (hasil: composer install --no-dev)
└── .env                       <-- Konfigurasi kredensial MySQL & API Keys (Terproteksi 403)
```

---

## 2. Rincian Folder: Apa yang WAJIB dan JANGAN Di-upload?

Folder proyek lokal tampak sangat besar terutama karena folder **`node_modules` (96 MB)** dan dependensi dev di **`vendor` (48 MB)**. Berikut panduan seleksinya:

### ❌ JANGAN PERNAH DI-UPLOAD (Hemat ~120 MB & Ribuan File):
| Folder / Berkas | Ukuran | Alasan Tidak Perlu Di-upload |
| :--- | :--- | :--- |
| **`node_modules/`** | **~96 MB** | **100% TIDAK DIGUNAKAN oleh PHP runtime**. Ini hanya dependensi JavaScript lokal untuk kompilasi Vite/Tailwind. |
| **`.git/`** | **~20+ MB** | Riwayat Git lokal, tidak diperlukan di server produksi. |
| **`tests/`** | ~44 KB | Pengujian unit/feature PHPUnit hanya untuk pengembangan lokal. |
| **`docs/`** | ~52 KB | Dokumentasi teknis hanya untuk pengembang. |
| **`docker-compose.yml`**| ~4 KB | InfinityFree adalah shared hosting, bukan container Docker. |
| **`database/kliniku_dump.sql`** | ~56 KB | File ini **di-import lewat browser ke phpMyAdmin**, jangan ditaruh di web server. |
| **`storage/logs/*.log`**| - | File log lokal (cukup upload folder `storage/logs/` kosong). |
| `package.json`, `package-lock.json`, `vite.config.js` | - | Konfigurasi node tools lokal. |

---

###  WAJIB DI-UPLOAD (Total hanya ~20–25 MB):
| Folder / Berkas | Fungsi |
| :--- | :--- |
| **`app/`** | Seluruh logika backend (Controller, Models, Services, Middleware). |
| **`bootstrap/`** | File inisialisasi aplikasi (`app.php`, `providers.php`, folder `cache/`). |
| **`config/`** | Seluruh konfigurasi Laravel. |
| **`database/`** | Folder migrasi database. |
| **`routes/`** | Rute API (`routes/api.php`, `routes/console.php`). |
| **`storage/`** | Folder penyimpanan sesi, cache, dan log (kosongkan isinya, set permission `775`). |
| **`vendor/`** | Framework Laravel dan library JWT (**wajib dirampingkan dengan `--no-dev`**). |
| **`.env`** | Kredensial MySQL InfinityFree dan kunci API. |
| **`index.php`** | Diambil dari `frontend/index.php`. |
| **`.htaccess`** | Diambil dari `frontend/.htaccess`. |
| **`index.html`** | Diambil dari `frontend/index.html` (Test Client sementara). |

---

## 3. Langkah Demi Langkah Deployment

### Langkah 1: Rampingkan Folder `vendor/` di Komputer Lokal
Di terminal komputer lokal Anda:
```bash
cd backend
composer install --optimize-autoloader --no-dev
```
*Perintah ini membuang paket PHPUnit, Mockery, Faker, dll, serta menyusutkan ukuran folder `vendor/` secara drastis.*

---

### Langkah 2: Setup Database di phpMyAdmin
1. Buka **Control Panel InfinityFree** $\rightarrow$ klik **MySQL Databases** $\rightarrow$ buat database baru.
2. Catat Hostname (`sqlXXX.infinityfree.com`), Database Name, Username, dan Password.
3. Buka **phpMyAdmin** dari panel $\rightarrow$ pilih database Anda $\rightarrow$ klik tab **Import** $\rightarrow$ upload file [backend/database/kliniku_dump.sql](file:///root/Melissa/kliniku/backend/database/kliniku_dump.sql) $\rightarrow$ klik **Go/Import**.

---

### Langkah 3: Upload Berkas ke `/htdocs` via FTP (FileZilla)
1. Buka FileZilla dan hubungkan ke akun InfinityFree Anda.
2. Masuk ke direktori **`/htdocs`** di server. Hapus file bawaan jika ada.
3. Upload berkas berikut dari folder `frontend/` ke dalam `/htdocs/`:
   - `frontend/.htaccess`
   - `frontend/index.php`
   - `frontend/index.html`
4. Upload folder dan berkas berikut dari folder `backend/` ke dalam `/htdocs/`:
   - `app/`
   - `bootstrap/`
   - `config/`
   - `database/` (tanpa `kliniku_dump.sql`)
   - `routes/`
   - `storage/`
   - `vendor/` (hasil `composer install --no-dev`)
   - `.env`

---

### Langkah 4: Konfigurasi File `.env` di Server
Edit file `.env` di dalam `/htdocs/.env` agar mengarah ke database InfinityFree:

```dotenv
APP_NAME=KLINIKU
APP_ENV=production
APP_KEY=base64:u3wR6d42z2c1Fq7gYQxWqVp9d1eK5L3n6z4v6m2Q8M=
APP_DEBUG=false
APP_URL=http://namadomainanda.infinityfreeapp.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=epiz_xxxxxxx_kliniku
DB_USERNAME=epiz_xxxxxxx
DB_PASSWORD=password_database_anda

JWT_SECRET=3b92f7c0a8e14d6f852b7194c3e0aa1b9542df7182c40e53a79d86b1f2e34589
JWT_TTL=86400

RECAPTCHA_SITE_KEY=6LfEiisuAAAAAM1dgo05Kv4IqPwYUF5NTWbDUN9V
RECAPTCHA_SECRET_KEY=6LfEiisuAAAAAEPuGqfaxFfGOtUGHNdZq0djWVkd

FLOWKIRIM_API_KEY=fk_live_25ebb5646a8d7afd3d9e526d394f81098a8e48b0ed355b0a
FLOWKIRIM_BASE_URL=https://scan.flowkirim.com/api/v1/verify
FLOWKIRIM_DEFAULT_CHANNEL=whatsapp_tap
FLOWKIRIM_BRAND=KLINIKU
FLOWKIRIM_TTL=300
```

---

### Langkah 5: Set Permissions Folder `storage/` dan `bootstrap/cache/`
Di FileZilla:
1. Klik kanan folder `/htdocs/storage` $\rightarrow$ **File permissions...** $\rightarrow$ set ke **775** (atau **777**) dan centang **Recurse into subdirectories**.
2. Lakukan hal yang sama untuk folder `/htdocs/bootstrap/cache`.

---

## 4. Pengujian Live Melalui Browser

Buka URL website Anda di browser:
```text
http://namadomainanda.infinityfreeapp.com
```

Halaman GUI penguji [index.html](file:///root/Melissa/kliniku/frontend/index.html) akan muncul. Anda dapat menguji:
1. **Cek Koneksi Database & Server** (`GET /api/v1/polis`)
2. **Validasi & Ekstraksi NIK** (`POST /api/v1/check-nik`)
3. **Pendaftaran Pasien Baru** (`POST /api/v1/register`)
4. **Login Pasien & FlowKirim WhatsApp Tap** (`POST /api/v1/login` & `POST /api/v1/verify-otp`)
5. **Akses Profil Pasien / Rekam Medis** (`GET /api/v1/me` via JWT Bearer Token)
6. **Logout** (`POST /api/v1/logout`)
