# Panduan Teknis Pencegahan SQL Injection (SQLi) — KLINIKU

Dokumen ini disusun khusus bagi Database Administrator (DBA), Backend Engineer, dan Application Security Auditor untuk memahami prinsip pertahanan terhadap serangan **SQL Injection (SQLi)** pada sistem **KLINIKU**.

---

## 1. Ringkasan Eksekutif & Latar Belakang

SQL Injection menduduki peringkat teratas dalam **OWASP Top 10: Injection (A03:2021)**. Kerentanan ini terjadi apabila data yang dikirimkan oleh pengguna (*untrusted user input*) digabungkan langsung (*string concatenation / interpolation*) ke dalam sintaks query SQL tanpa proses sanitasi atau parameterisasi.

### Dampak Serangan SQL Injection Jika Tidak Dicegah:
1. **Authentication Bypass**: Penyerang dapat masuk sebagai pengguna lain tanpa kata sandi (misal menggunakan klausa `' OR '1'='1`).
2. **Data Leakage & Exfiltration**: Penyerang mengekstraksi seluruh rekam medis pasien menggunakan teknik `UNION SELECT`.
3. **Data Tampering & Destruction**: Penyerang menghapus atau mengubah data menggunakan *piggybacked queries* (misal `'; DROP TABLE user; --`).

Sistem KLINIKU dirancang dengan prinsip **Zero Trust to User Input** menggunakan **PDO Prepared Statements (Parameterized Queries)** secara menyeluruh pada lapisan Eloquent ORM.

---

## 2. Arsitektur & Cara Kerja Prepared Statements

Perbedaan fundamental antara query rentan (*Vulnerable Query*) dan query berpengaman (*Prepared Statement*) di KLINIKU:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ ❌ PENDEKATAN RENTAN (String Concatenation):                                 │
│ "SELECT * FROM user WHERE user_nik = '" . $nik . "'"                        │
│ Input penyerang: "' OR '1'='1"                                              │
│ Query hasil: SELECT * FROM user WHERE user_nik = '' OR '1'='1' (BOBOL!)     │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│  PENDEKATAN AMAN KLINIKU (PDO Prepared Statements / Parameterized Queries): │
│ Template Query: SELECT * FROM user WHERE user_nik = ?                       │
│ Parameter Binding: ? = "' OR '1'='1" (Diperlakukan sebagai DATA TEKS BIASA) │
│ Query hasil: Mencari NIK yang isi teksnya memang "' OR '1'='1" (AMAN!)      │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Mekanisme 2 Fase Eksekusi Database MySQL:
1. **Fase 1: Kompilasi Struktur SQL (Prepare)**
   Server MySQL menerima template query SQL dengan placeholder parameter (`?`). Server menyusun *Abstract Syntax Tree (AST)* dan rencana eksekusi (*Execution Plan*) query. Pada tahap ini, struktur dan logika query telah **terkunci permanen**.
2. **Fase 2: Pengiriman Data (Execute & Bind)**
   Server MySQL menerima data input pengguna secara terpisah melalui protokol biner. Karakter berbahaya seperti tanda petik (`'`), titik koma (`;`), strip ganda (`--`), atau tanda pagar (`#`) **tidak akan pernah dapat mengubah struktur pohon AST logika SQL**.

---

## 3. Berkas dan Potongan Kode Terkait

### 3.1 Pencarian Pengguna saat Login — [LoginController.php](file:///root/Melissa/kliniku/backend/app/Http/Controllers/Api/LoginController.php)
Pada baris 54-56, pemanggilan Eloquent ORM secara otomatis menggunakan parameter binding:
```php
// File: backend/app/Http/Controllers/Api/LoginController.php (Baris 54-56)

/** @var User|null $user */
$user = User::with(['pasien.poli', 'dokter.poli', 'staf'])
    ->where('user_nik', $validated['nik']) // <-- PDO Parameter Binding: WHERE user_nik = ?
    ->first();
```
*Di balik layar, PDO mengirimkan template `SELECT * FROM user WHERE user_nik = ? LIMIT 1` dan mengikat `$validated['nik']` sebagai parameter teks.*

### 3.2 Pendaftaran Pasien & Profil — [RegisterController.php](file:///root/Melissa/kliniku/backend/app/Http/Controllers/Api/RegisterController.php)
Pada baris 66-89, pembuatan akun baru menggunakan metode mass-assignment Eloquent dalam transaksi ACID:
```php
// File: backend/app/Http/Controllers/Api/RegisterController.php (Baris 66-76)

$user = User::create([
    'user_id' => $userId,
    'user_role' => 'pasien',
    'user_nama' => trim($validated['nama']),
    'user_nik' => $validated['nik'],
    'user_nowa' => $nowa,
    'user_email' => strtolower(trim($validated['email'])),
    'user_password' => Hash::make($validated['password']),
    'user_status' => 'aktif',
]);
```
*Seluruh nilai array di-bind secara aman ke statement `INSERT INTO user (...) VALUES (?, ?, ?, ...)`.*

### 3.3 Validasi Format Ketat (Defense-in-Depth) — [RegisterPatientRequest.php](file:///root/Melissa/kliniku/backend/app/Http/Requests/RegisterPatientRequest.php) & [LoginRequest.php](file:///root/Melissa/kliniku/backend/app/Http/Requests/LoginRequest.php)
Selain prepared statements, sistem menerapkan lapisan pertahanan awal (*Defense in Depth*) melalui validasi regex ketat:
```php
// File: backend/app/Http/Requests/LoginRequest.php (Baris 28)
'nik' => ['required', 'string', 'regex:/^[0-9]{16}$/'],
```
*Input NIK yang mengandung karakter injeksi seperti `'`, space, atau `--` akan langsung ditolak oleh validator sebelum query database dieksekusi.*

---

## 4. Rute API dan Uji Ketahanan Serangan (Attack Scenarios)

### 4.1 Uji Serangan Tautologi pada Endpoint Login (`POST /api/v1/login`)
- **Tujuan Penyerang**: Mencoba login tanpa mengetahui NIK atau kata sandi yang sah.
- **Payload Uji**:
  ```json
  {
    "nik": "' OR '1'='1",
    "password": "' OR '1'='1",
    "recaptcha_token": "test-valid-token"
  }
  ```
- **Hasil Respons Sistem (`422 Unprocessable Content`)**:
  ```json
  {
    "message": "NIK harus terdiri dari tepat 16 digit angka.",
    "errors": {
      "nik": ["NIK harus terdiri dari tepat 16 digit angka."]
    }
  }
  ```
  *Bahkan jika validasi dilewati, Eloquent akan mencari NIK literal `"' OR '1'='1"` yang tidak akan menghasilkan record apa pun.*

### 4.2 Uji Serangan Destruktif (*Piggybacked Query*) pada Endpoint Cek NIK (`POST /api/v1/check-nik`)
- **Tujuan Penyerang**: Mencoba menghapus tabel database melalui injeksi multi-query.
- **Payload Uji**:
  ```json
  {
    "nik": "'; DROP TABLE pasien; --"
  }
  ```
- **Hasil Respons Sistem (`422 Unprocessable Content`)**:
  Permintaan ditolak seketika oleh validator. Tabel `pasien` tetap 100% utuh tanpa kerusakan skema apa pun.

### 4.3 Uji Serangan Injeksi pada Kolom Teks Bebas (`POST /api/v1/register`)
- **Tujuan Penyerang**: Memasukkan perintah SQL pada kolom nama atau alamat pasien.
- **Payload Uji**:
  ```json
  {
    "nik": "3372091508980077",
    "nama": "Budi Hartono'; DROP TABLE user; --",
    "email": "sqli.test@kliniku.id",
    "nowa": "081299998888",
    "password": "SafePassword123!",
    "alamat": "Jl. Raya No. 1' OR 1=1; --"
  }
  ```
- **Hasil Respons Sistem (`201 Created`)**:
  Akun berhasil didaftarkan. Di dalam database, teks `"Budi Hartono'; DROP TABLE user; --"` disimpan murni sebagai nama pasien harfiah tanpa mengeksekusi perintah SQL `DROP TABLE`.

---

## 5. Pengujian Otomatis (Automated Testing)

Pengujian ketahanan SQL Injection terdaftar pada [SecurityFeaturesTest.php](file:///root/Melissa/kliniku/backend/tests/Feature/SecurityFeaturesTest.php).

### Skenario Uji 1: Uji Resitensi Injeksi pada Login
```php
public function test_login_resists_sql_injection_payloads(): void
{
    $sqlInjectionPayloads = [
        "' OR '1'='1",
        "3372020912254658' OR '1'='1' --",
        "'; DROP TABLE user; --",
        "' UNION SELECT * FROM user --",
        "1' OR 1=1 #",
        "' OR ''='",
    ];

    foreach ($sqlInjectionPayloads as $payload) {
        $response = $this->postJson('/api/v1/login', [
            'nik' => $payload,
            'password' => $payload,
            'recaptcha_token' => 'test-valid-token',
        ]);

        // Wajib ditolak oleh validasi (422) atau autentikasi (401), tidak boleh 500 error
        $this->assertContains($response->status(), [401, 422]);
        $response->assertJson(['success' => false]);
    }

    // Pastikan tabel user tetap utuh dan data ada
    $this->assertGreaterThan(0, User::count());
}
```

### Skenario Uji 2: Uji Resitensi Injeksi pada Kolom Teks Bebas Registrasi
```php
public function test_registration_resists_sql_injection_in_text_fields(): void
{
    $cleanNik = '3372091508980077';
    User::where('user_nik', $cleanNik)->delete();

    $sqlPayload = "Budi Hartono'; DROP TABLE user; --";

    $response = $this->postJson('/api/v1/register', [
        'nik' => $cleanNik,
        'nama' => $sqlPayload,
        'email' => 'sqli.test@kliniku.id',
        'nowa' => '081299998888',
        'password' => 'SafePassword123!',
        'alamat' => "Jl. Raya No. 1' OR 1=1; --",
    ]);

    $response->assertStatus(201);

    // Verifikasi teks tersimpan secara harfiah tanpa eksekusi SQL
    $createdUser = User::where('user_nik', $cleanNik)->first();
    $this->assertNotNull($createdUser);
    $this->assertEquals($sqlPayload, $createdUser->user_nama);

    // Verifikasi tabel user tetap aman
    $this->assertTrue(DB::getSchemaBuilder()->hasTable('user'));
}
```

---

## 6. Checklist & Standar Pengembang (Developer Rules of Engagement)

Untuk memastikan sistem KLINIKU tetap bebas dari celah SQL Injection di masa mendatang:

1. **Dilarang Keras Menggunakan String Interpolasi pada Raw Query**:
   ```php
   // ❌ SANGAT DILARANG (VULNERABLE):
   DB::select("SELECT * FROM user WHERE user_nik = '$nik'");
   User::whereRaw("user_nama = '$nama'");

   //  WAJIB MENGGUNAKAN BINDING:
   DB::select("SELECT * FROM user WHERE user_nik = ?", [$nik]);
   User::whereRaw("user_nama = ?", [$nama]);
   ```
2. **Gunakan Fitur Native Eloquent**: Selalu utamakan fungsi bawaan Eloquent seperti `where()`, `find()`, `create()`, dan `update()`.
3. **Prinsip Hak Akses Minimal (Principle of Least Privilege)**: Akun database MySQL pada lingkungan produksi shared hosting hanya diberikan hak `SELECT`, `INSERT`, `UPDATE`, `DELETE`, dan dilarang memiliki hak administratif seperti `GRANT OPTION` atau `SUPER`.
