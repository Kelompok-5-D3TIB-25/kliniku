# Dokumentasi API FlowKirim Verify

## Pengenalan
Verifikasi nomor pengguna Anda dengan 2 langkah: **start** (kirim OTP) lalu **check** (cek kode). FlowKirim yang mengurus pengiriman & channel — Anda cukup memanggil API. Anda tidak perlu punya WhatsApp Business Account atau membuat template apa pun — pengirim, template, dan pengiriman OTP sepenuhnya ditangani FlowKirim. 

Cukup kirim nomor tujuan + (opsional) kode kustom. Pada *missed-call*, sistem menelepon nomor pengguna; kode = 4 digit terakhir nomor penelepon yang pengguna masukkan.

---

## Autentikasi
Setiap request wajib menyertakan header `Bearer` dengan API key scope `verify`:

```http
Authorization: Bearer <API_KEY>
```

> **Catatan:** Buat/kelola key di halaman **API Key Saya**. Key hanya tampil sekali.

---

## Endpoint Utama

### 1. Mulai Verifikasi — `POST /start`

**Parameter Body:**

| Field | Wajib | Keterangan |
| :--- | :--- | :--- |
| `phone` | ya* | Nomor tujuan (format lokal `08xx` atau E.164 `62xx`). *\*Tidak wajib bila `channel: "email"`.* |
| `email` | ya** | *\*\*Wajib bila `channel: "email"`* — alamat email tujuan OTP. |
| `channel` | tidak | `auto` (default, coba missed-call lalu WhatsApp) / `missedcall` / `whatsapp` / `whatsapp_tap` (Tap-to-Verify) / `email`. |
| `brand` | tidak | Nama brand Anda (maks 40 karakter). |
| `code_length` | tidak | `4–8` (default `4`). |
| `ttl` | tidak | Masa berlaku dalam detik (`60–900`, default `300`). |

**Contoh Request:**
```bash
curl -X POST https://scan.flowkirim.com/api/v1/verify/start \
  -H "Authorization: Bearer <API_KEY>" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: <uuid-opsional>" \
  -d '{"phone":"087864205391","channel":"auto"}'
```

**Contoh Respons:**
```json
{
  "data": {
    "request_id": "391df788-...-c08c0a",
    "channel": "missedcall",
    "expires_in": 300,
    "masked_phone": "*********5391"
  }
}
```
> *`Idempotency-Key` (opsional) mencegah dobel-kirim saat retry.*

---

### 2. Cek Kode — `POST /check`

**Parameter Body:**

| Field | Wajib | Keterangan |
| :--- | :--- | :--- |
| `request_id` | ya | Dari respons `/start`. |
| `code` | ya | Kode yang dimasukkan pengguna. |

**Contoh Request:**
```bash
curl -X POST https://scan.flowkirim.com/api/v1/verify/check \
  -H "Authorization: Bearer <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"request_id":"391df788-...","code":"1234"}'
```

**Contoh Respons:**
```json
{
  "data": {
    "status": "verified"
  }
}
```

---

### 3. Status — `GET /{request_id}`
Mengembalikan status terkini dari proses verifikasi:
* `pending`
* `verified`
* `expired`
* `max_attempts`

---

## Metode Verifikasi Khusus

### Tap-to-Verify — `channel: "whatsapp_tap"` ✨
Verifikasi nomor tanpa pengguna mengetik OTP. Pada `/start` dengan channel `whatsapp_tap`, respons berisi `wa_deeplink` — tautan WhatsApp berisi pesan ter-isi otomatis.

**Alur Kerja:**
1. Panggil `/start` dengan `channel: "whatsapp_tap"` $\rightarrow$ dapatkan `wa_deeplink`.
2. Tampilkan tombol **"Verifikasi via WhatsApp"** yang membuka `wa_deeplink`.
3. Pengguna menekan **Kirim** di WhatsApp (tanpa mengetik apa pun).
4. Poll `GET /{request_id}` hingga status menjadi `verified`. Tidak ada langkah `/check` dan tidak ada kode.

**Contoh Request:**
```bash
curl -X POST https://scan.flowkirim.com/api/v1/verify/start \
  -H "Authorization: Bearer <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"phone":"087864205391","channel":"whatsapp_tap"}'
```

**Respons `/start`:**
```json
{
  "data": {
    "request_id": "7b1c...-a2",
    "channel": "whatsapp_tap",
    "expires_in": 300,
    "masked_phone": "*********5391",
    "wa_number": "6285113736221",
    "wa_deeplink": "https://wa.me/6285113736221?text=VERIFY-AB12CD34EF"
  }
}
```

**Respons Polling Status:**
```json
{
  "data": {
    "status": "verified"
  }
}
```

* **Validasi Keamanan:** Terverifikasi hanya jika pengguna mengirim pesan dari nomor yang sama dengan `phone` (bukti kepemilikan nomor).
* **Ketersediaan:** Hanya untuk nomor yang memiliki WhatsApp. Jika tidak menggunakan WhatsApp, gunakan channel lain.
* **Keunggulan:** Lebih mulus (cukup tap + kirim) dan umumnya lebih murah (pesan masuk, bukan template berbayar).

#### QR Code untuk Alur Desktop
Jika halaman verifikasi dibuka di desktop/laptop, tampilkan `wa_deeplink` sebagai QR Code agar pengguna memindainya dengan HP pemilik nomor — WhatsApp akan terbuka di HP tersebut dengan pesan yang sudah terisi. Di perangkat mobile, cukup gunakan tombol tap biasa.

```javascript
// Alur desktop: render wa_deeplink sebagai QR agar pengguna
// men-scan-nya dengan HP pemilik nomor (pengirim harus nomor itu).
import QRCode from "qrcode"; // atau library QR apa pun

const r = await fetch("https://scan.flowkirim.com/api/v1/verify/start", {
  method: "POST",
  headers: { 
    Authorization: "Bearer <API_KEY>", 
    "Content-Type": "application/json" 
  },
  body: JSON.stringify({ phone, channel: "whatsapp_tap" }),
}).then((x) => x.json());

const { request_id, wa_deeplink } = r.data;
const qrDataUrl = await QRCode.toDataURL(wa_deeplink); // -> <img src={qrDataUrl} />

// Di HP: tampilkan wa_deeplink sebagai tombol biasa (tap langsung buka WhatsApp).
// Lalu poll GET /{request_id} sampai "verified".
```

---

### Email OTP — `channel: "email"` ✉️
Kirim OTP ke alamat email, bukan nomor telepon. Gunakan field `email` (bukan `phone`). Verifikasi tetap dilakukan lewat `/check` seperti biasa — kode yang dimasukkan pengguna dibandingkan dengan kode yang dikirim. Cocok sebagai cadangan universal saat nomor tidak memiliki WhatsApp.

**Contoh Request `/start`:**
```bash
curl -X POST https://scan.flowkirim.com/api/v1/verify/start \
  -H "Authorization: Bearer <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"channel":"email","email":"nama@email.com"}'
```

**Contoh Request `/check`:**
```bash
curl -X POST https://scan.flowkirim.com/api/v1/verify/check \
  -H "Authorization: Bearer <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"request_id":"391df788-...","code":"1234"}'
```

---

## Referensi Status & Error

### Status Hasil `/check`
* `verified` — kode benar ✅
* `invalid` — kode salah (masih bisa coba lagi)
* `expired` — OTP kedaluwarsa (minta baru)
* `max_attempts` — percobaan habis (minta baru)

### Format & Kode Error
```json
{
  "error": {
    "code": "RATE_LIMITED",
    "message": "..."
  }
}
```

| Kode Error | HTTP Status |
| :--- | :--- |
| `INSUFFICIENT_BALANCE` | `402` |
| `NOT_FOUND` | `404` |
| `INVALID_REQUEST` | `422` |
| `RESEND_COOLDOWN` / `RATE_LIMITED` | `429` |
| `SEND_FAILED` | `502` |
| `SERVICE_UNAVAILABLE` | `503` |

---

## Catatan Tambahan
* **Billing:** Saldo dipotong per OTP terkirim (prabayar). Top-up tersedia via QRIS.
* **Anti-Abuse:** Terdapat jeda minimal antar-kirim ke nomor yang sama serta batas pengiriman per jam (mencegah *OTP-bombing*).
* **Keamanan:** Kode OTP disimpan dalam bentuk ter-hash (*hashed*) dan tidak pernah dikembalikan oleh API.