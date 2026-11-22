# Panduan Lengkap Deployment KLINIKU ke InfinityFree

Panduan teknis dan operasional untuk melakukan deployment sistem backend **KLINIKU** (Laravel 11/13 + MySQL) dan antarmuka web ke layanan shared hosting **InfinityFree** tanpa akses SSH/CLI.

---

## 1. Arsitektur Struktur Folder di Server Hosting

InfinityFree menyediakan akses FTP dengan direktori root `/`. Folder publik yang dapat diakses oleh browser dari seluruh dunia adalah `/htdocs`.

Untuk menjaga keamanan kode sumber, file konfigurasi `.env`, dan database dump agar **tidak bocor ke publik**, struktur diatur dengan memisahkan folder privat dan folder publik:

```text
/ (FTP Root InfinityFree)
│
├── backend/                       <-- FOLDER PRIVAT (Di luar htdocs, TIDAK BISA diakses publik)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   └── kliniku_dump.sql       <-- File backup database untuk diimpor
│   ├── routes/
│   ├── storage/                   <-- Wajib CHMOD 775/777
│   ├── vendor/                    <-- Dependensi Composer produksi
│   └── .env                       <-- Konfigurasi database & kredensial API
│
└── htdocs/                        <-- FOLDER PUBLIK WEB ROOT (Bisa diakses publik)
    ├── .htaccess                  <-- Mengatur routing /api/ ke index.php dan / ke index.html
    ├── index.php                  <-- Jembatan publik yang memanggil ../backend/
    └── index.html                 <-- Halaman GUI Pengujian API (atau hasil build Vue nantinya)
```

---

## 2. Checklist Persiapan di Komputer Lokal

Sebelum meng-upload ke hosting, siapkan berkas di komputer lokal:

### A. Database Dump SQL
File dump database MySQL yang sudah berisi seluruh tabel, relasi, dan 110 akun pengguna seeder awal telah siap di:
```text
backend/database/kliniku_dump.sql
```

### B. Siapkan Folder `vendor` untuk Produksi
Jalankan perintah ini di dalam folder `backend/` komputer Anda:
```bash
cd backend
composer install --optimize-autoloader --no-dev
```
*(Ini akan membersihkan paket pengujian dev dan mengoptimalkan autoloader class PHP).*

---

## 3. Langkah Demi Langkah Setup di InfinityFree

### Langkah 1: Buat Database MySQL di VistaPanel
1. Login ke akun [InfinityFree](https://app.infinityfree.com) dan buka **Control Panel (VistaPanel)**.
2. Cari dan klik menu **"MySQL Databases"**.
3. Buat database baru dengan nama, misal: `kliniku` (InfinityFree akan menambahkan prefix akun otomatis, misal: `epiz_12345678_kliniku`).
4. Catat detail kredensial yang muncul di halaman tersebut:
   * **MySQL Hostname**: contoh `sql200.infinityfree.com`
   * **MySQL Database Name**: contoh `epiz_12345678_kliniku`
   * **MySQL Username**: contoh `epiz_12345678`
   * **MySQL Password**: kata sandi akun hosting Anda
   * **MySQL Port**: `3306`

### Langkah 2: Impor Database via phpMyAdmin
1. Kembali ke Control Panel, klik menu **phpMyAdmin**.
2. Klik tombol **Connect** pada database yang baru dibuat.
3. Di phpMyAdmin, pilih database Anda di sidebar kiri.
4. Klik tab **Import** pada menu atas.
5. Klik **Choose File** -> pilih file `backend/database/kliniku_dump.sql` dari komputer Anda.
6. Gulir ke bawah dan klik tombol **Import / Go**.
7. Pastikan seluruh tabel (`user`, `pasien`, `dokter`, `staf`, `poli`, `jadwal`, `cache`, `migrations`) berhasil terbuat.

---

### Langkah 3: Upload Berkas melalui FTP (FileZilla)
Gunakan aplikasi FTP seperti **FileZilla** untuk mengunggah berkas:
* **Host**: `ftpupload.net` (atau sesuai detail di dashboard InfinityFree)
* **Username**: `epiz_12345678`
* **Password**: Kata sandi hosting Anda
* **Port**: `21`

#### A. Upload Folder `backend/`:
1. Pada sisi server (Remote Site), pastikan Anda berada di direktori root `/`.
2. Buat folder baru bernama `backend` sejajar dengan folder `htdocs`.
3. Upload seluruh isi folder `backend/` dari komputer Anda ke dalam `/backend/` di server.

#### B. Upload Folder `frontend/` ke `/htdocs/`:
1. Di sisi server, masuk ke dalam folder `/htdocs/`.
2. Hapus file bawaan default jika ada (seperti `default.html`).
3. Upload seluruh isi folder `frontend/` lokal Anda ke dalam `/htdocs/`:
   * `frontend/.htaccess` $\rightarrow$ upload ke `/htdocs/.htaccess`
   * `frontend/index.php` $\rightarrow$ upload ke `/htdocs/index.php`
   * `frontend/index.html` $\rightarrow$ upload ke `/htdocs/index.html`

---

### Langkah 4: Konfigurasi File `.env` di Server
Buka file `/backend/.env` di server (via FileZilla -> View/Edit atau File Manager) dan sesuaikan:

```dotenv
APP_NAME=KLINIKU
APP_ENV=production
APP_KEY=base64:u3wR6d42z2c1Fq7gYQxWqVp9d1eK5L3n6z4v6m2Q8M=
APP_DEBUG=false
APP_URL=http://namadomainanda.infinityfreeapp.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=sql200.infinityfree.com
DB_PORT=3306
DB_DATABASE=epiz_12345678_kliniku
DB_USERNAME=epiz_12345678
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
1. Klik kanan folder `/backend/storage` -> pilih **File permissions...**.
2. Masukkan Numeric value: **775** (atau **777** jika 775 terkendala akses) dan centang **Recurse into subdirectories**.
3. Lakukan hal yang sama untuk folder `/backend/bootstrap/cache`.

---

## 4. Pengujian Live Melalui Browser

Buka domain Anda di web browser:
```text
http://namadomainanda.infinityfreeapp.com/
```

### Mengapa Halaman GUI Test Client Berfungsi Bebas Anti-Bot?
1. Saat Anda membuka halaman di atas melalui browser asli, InfinityFree otomatis menjalankan script pengecekan browser (`aes.js`) dan menerbitkan cookie `__test`.
2. Halaman [index.html](file:///root/Melissa/kliniku/frontend/index.html) dimuat dengan UI responsif.
3. Seluruh panggilan API dari tombol di halaman tersebut berjalan ke path yang sama (`/api/v1/...`), sehingga cookie `__test` **otomatis terkirim** dan request ke Laravel **tidak diblokir**!

### Fitur yang Dapat Diuji Langsung dari Halaman:
1. **Cek Koneksi Server**: Menguji koneksi database dengan mengambil data master poliklinik (`GET /api/v1/polis`).
2. **Validasi & Ekstraksi NIK**: Mengetikkan 16 digit NIK untuk melihat decode jenis kelamin, tanggal lahir, dan ketersediaan NIK di database.
3. **Pendaftaran Pasien**: Mengisi formulir pasien baru dan mencetak nomor Rekam Medis (`RM-YYYY-XXXX`).
4. **Login & WhatsApp Tap**:
   - Memverifikasi NIK + Kata Sandi + centang Google reCAPTCHA v2.
   - Mengembalikan link **"Buka WhatsApp (Tap to Verify)"**.
   - Tombol **"Cek Status Verifikasi"** yang mem-poll status ke server hingga terbit JWT Bearer token.
5. **Akses Profil Pasien (/me)**: Menampilkan kartu rekam medis pasien terotentikasi.
6. **Logout**: Menghapus sesi JWT.
7. **JSON Inspector**: Memperlihatkan secara live HTTP status code dan payload JSON yang dikembalikan oleh backend.

---

## 5. Transisi Saat Frontend Vue Selesai Dibuat

Ketika aplikasi frontend Vue Anda sudah selesai dikembangkan di komputer lokal:
1. Jalankan `npm run build` di folder proyek Vue.
2. Hasil build akan berada di folder `dist/` (berupa `index.html` dan folder `assets/`).
3. Upload seluruh isi folder `dist/` ke dalam `/htdocs/` di InfinityFree (menggantikan `index.html` penguji API sementara).
4. Pastikan file `.htaccess` dan `index.php` tetap berada di `/htdocs/`.
5. Aplikasi Vue Anda langsung aktif dan terhubung sempurna ke API backend Laravel!
