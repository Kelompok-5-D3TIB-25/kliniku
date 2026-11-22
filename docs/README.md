# Dokumentasi Teknis KLINIKU

Folder ini memuat seluruh dokumentasi teknis, referensi integrasi API pihak ketiga, dan spesifikasi arsitektur backend untuk sistem **KLINIKU**.

## Daftar Dokumen

1. [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)  
   Dokumentasi lengkap seluruh endpoint RESTful API backend KLINIKU (v1). Mencakup alur registrasi akun pasien baru, auto-decode NIK & nomor Rekam Medis (RM), alur login dengan Google reCAPTCHA v2, verifikasi dua langkah WhatsApp Tap-to-Verify, pengelolaan sesi JWT, skema request/response JSON, struktur tabel basis data, panduan pengujian cURL, serta test suite otomatis.

2. [Dokumentasi API FlowKirim.md](./Dokumentasi%20API%20FlowKirim.md)  
   Dokumentasi referensi teknis layanan pihak ketiga FlowKirim Verify API. Mencakup metode pengiriman OTP, spesifikasi `whatsapp_tap` (Tap-to-Verify via WhatsApp deep link), alur polling status verifikasi, dan penanganan QR Code pada perangkat desktop.
