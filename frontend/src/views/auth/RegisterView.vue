<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import logoKliniku from '@/assets/logo-kliniku.png'

const router = useRouter()

// State Form Registrasi
const nik = ref('')
const namaLengkap = ref('')
const email = ref('')
const tanggalLahir = ref('')
const alamat = ref('')
const password = ref('')
const konfirmasiPassword = ref('')
const setuju = ref(false)

// State UI
const showPassword = ref(false)
const showKonfirmasiPassword = ref(false)
const errorMessage = ref('')
const logoLoadError = ref(false)
const dateInputRef = ref(null)

// Reset pesan error setiap user mengetik atau berinteraksi
const clearError = () => {
  if (errorMessage.value) {
    errorMessage.value = ''
  }
}

// Format input NIK (hanya angka, maks 16 digit)
const handleNikInput = (event) => {
  const numericOnly = event.target.value.replace(/\D/g, '').slice(0, 16)
  nik.value = numericOnly
  event.target.value = numericOnly
  clearError()
}

// Buka date picker saat tombol/area kalender diklik
const openDatePicker = () => {
  if (dateInputRef.value) {
    if (typeof dateInputRef.value.showPicker === 'function') {
      dateInputRef.value.showPicker()
    } else {
      dateInputRef.value.focus()
    }
  }
}

// Submit Registrasi
const handleSubmit = () => {
  // 1. Cek jika ada field wajib yang masih kosong, NIK < 16 digit, atau checkbox belum dicentang
  if (
    !nik.value ||
    nik.value.length < 16 ||
    !namaLengkap.value.trim() ||
    !email.value.trim() ||
    !tanggalLahir.value ||
    !alamat.value.trim() ||
    !password.value ||
    !konfirmasiPassword.value ||
    !setuju.value
  ) {
    errorMessage.value = 'Data Belum Lengkap!'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  // Validasi format email sederhana
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email.value.trim())) {
    errorMessage.value = 'Format email tidak valid!'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  // 2. Cek kesesuaian kata sandi
  if (password.value !== konfirmasiPassword.value) {
    errorMessage.value = 'Konfirmasi kata sandi tidak cocok!'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  // 3. Cek panjang kata sandi minimal 8 karakter
  if (password.value.length < 8) {
    errorMessage.value = 'Kata sandi minimal 8 karakter!'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  // Validasi Berhasil - Simpan ke localStorage (kliniku_users)
  try {
    const existingUsers = JSON.parse(localStorage.getItem('kliniku_users') || '[]')
    const newUser = {
      nik: nik.value,
      namaLengkap: namaLengkap.value.trim(),
      email: email.value.trim(),
      tanggalLahir: tanggalLahir.value,
      alamat: alamat.value.trim(),
      password: password.value
    }
    const index = existingUsers.findIndex(u => u.nik === nik.value)
    if (index >= 0) {
      existingUsers[index] = newUser
    } else {
      existingUsers.push(newUser)
    }
    localStorage.setItem('kliniku_users', JSON.stringify(existingUsers))
  } catch (err) {
    console.error('Gagal menyimpan user ke localStorage:', err)
  }

  errorMessage.value = ''
  // Pendaftaran berhasil, set session flag dan arahkan ke halaman verifikasi OTP
  try {
    sessionStorage.setItem('kliniku_register_success', 'true')
  } catch (e) {
    // ignore
  }

  router.push({
    path: '/verifikasi-otp',
    query: { email: email.value.trim() }
  })
}
</script>

<template>
  <main class="bg-[#FFF7FA] min-h-screen w-full flex justify-center items-start p-[24px_20px_36px] box-border">
    <div class="w-full max-w-[420px] flex flex-col items-center">
      <!-- 1. Header Sambutan -->
      <header class="flex flex-col items-center text-center mb-[22px] w-full">
        <!-- Logo KLINIKU -->
        <div class="flex justify-center items-center mb-4">
          <img
            v-if="!logoLoadError"
            :src="logoKliniku"
            alt="Logo KLINIKU"
            class="w-[105px] max-w-[115px] h-auto object-contain block"
            @error="logoLoadError = true"
          />

          <!-- Fallback SVG jika file logo tidak ditemukan / gagal dimuat -->
          <div v-else class="w-20 h-20 flex items-center justify-center" aria-label="Logo KLINIKU">
            <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
              <rect width="100" height="100" rx="24" fill="#F8D6E6" />
              <path
                d="M50 20v60M20 50h60"
                stroke="#6D314F"
                stroke-width="12"
                stroke-linecap="round"
              />
              <path
                d="M30 50h12l5-12 7 24 6-12h10"
                stroke="#FFFFFF"
                stroke-width="4"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
        </div>

        <h1 class="text-[#1F1A1D] text-[24px] font-extrabold leading-[1.25] mb-[10px] tracking-[-0.3px] max-[360px]:text-[21px]">Daftar Akun</h1>
        <p class="text-[#2D2428] text-[13.5px] leading-[1.5] max-w-[320px] m-0">
          Lengkapi data diri Anda di bawah ini untuk mengakses antrean digital &amp; rekam medis elektronik Anda dengan aman.
        </p>
      </header>

      <!-- 2. Banner Peringatan Error (Muncul Kondisional) -->
      <Transition
        enter-active-class="transition-all duration-250 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        leave-active-class="transition-all duration-250 ease-in"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="errorMessage" class="w-full bg-[#FFB4B4] border border-[#991B1B] rounded-[10px] p-[11px_14px] flex items-center gap-[9px] text-[#DC2626] mb-4 box-border" role="alert">
          <!-- Inline SVG Icon Lingkaran Tanda Seru Merah -->
          <svg
            class="w-[19px] h-[19px] shrink-0"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.4"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <span class="text-[13.5px] font-bold tracking-[0.2px]">{{ errorMessage }}</span>
        </div>
      </Transition>

      <!-- 3. Kartu Form Registrasi -->
      <div class="bg-white rounded-[18px] shadow-[0_4px_20px_rgba(109,49,79,0.05),0_1px_3px_rgba(0,0,0,0.03)] p-[22px_20px] w-full box-border mb-[18px] max-[360px]:p-[18px_14px]">
        <form @submit.prevent="handleSubmit" novalidate>
          <!-- Field 1: NIK -->
          <div class="mb-[17px]">
            <div class="flex justify-between items-center mb-2">
              <label for="reg-nik" class="flex items-center gap-[7px] text-[#2D2428] text-[13px] font-bold cursor-pointer max-[360px]:text-[12px]">
                <!-- Inline SVG ID Card -->
                <svg
                  class="w-4 h-4 shrink-0"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="#6D314F"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <rect x="2" y="4" width="20" height="16" rx="3" />
                  <circle cx="8" cy="10" r="2.2" />
                  <line x1="14" y1="9" x2="18" y2="9" />
                  <line x1="14" y1="13" x2="18" y2="13" />
                  <line x1="6" y1="16" x2="10" y2="16" />
                </svg>
                <span>NIK (Nomor Induk Kependudukan)<span class="text-[#DC2626] ml-0.5 font-bold">*</span></span>
              </label>
              <span class="text-[12px] text-[#8E8489] font-medium">16 digit</span>
            </div>

            <input
              id="reg-nik"
              type="text"
              inputmode="numeric"
              maxlength="16"
              :value="nik"
              @input="handleNikInput"
              placeholder="317xxxxxxxxxxxxxxx"
              autocomplete="off"
              class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] px-[14px] py-[12.5px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
            />
          </div>

          <!-- Field 2: Nama Lengkap -->
          <div class="mb-[17px]">
            <label for="reg-nama" class="flex items-center gap-[7px] text-[#2D2428] text-[13px] font-bold mb-2 cursor-pointer max-[360px]:text-[12px]">
              <!-- Inline SVG User -->
              <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="#6D314F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <span>Nama Lengkap (Sesuai KTP)<span class="text-[#DC2626] ml-0.5 font-bold">*</span></span>
            </label>

            <input
              id="reg-nama"
              type="text"
              v-model="namaLengkap"
              @input="clearError"
              placeholder="Contoh: Rahmawati Soebandono"
              autocomplete="name"
              class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] px-[14px] py-[12.5px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
            />
          </div>

          <!-- Field 3: Email Aktif -->
          <div class="mb-[17px]">
            <label for="reg-email" class="flex items-center gap-[7px] text-[#2D2428] text-[13px] font-bold mb-2 cursor-pointer max-[360px]:text-[12px]">
              <!-- Inline SVG Email -->
              <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="#6D314F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
              </svg>
              <span>Email Aktif <span class="text-[#DC2626] ml-0.5 font-bold">*</span></span>
            </label>

            <input
              id="reg-email"
              type="email"
              v-model="email"
              @input="clearError"
              placeholder="Contoh: rahmawati@gmail.com"
              autocomplete="email"
              class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] px-[14px] py-[12.5px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
            />
          </div>

          <!-- Field 4: Tanggal Lahir -->
          <div class="mb-[17px]">
            <label for="reg-tgl" class="flex items-center gap-[7px] text-[#2D2428] text-[13px] font-bold mb-2 cursor-pointer max-[360px]:text-[12px]">
              <!-- Inline SVG Calendar -->
              <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="#6D314F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
              <span>Tanggal Lahir <span class="text-[#DC2626] ml-0.5 font-bold">*</span></span>
            </label>

            <div class="relative flex items-center w-full">
              <input
                ref="dateInputRef"
                id="reg-tgl"
                type="date"
                v-model="tanggalLahir"
                @input="clearError"
                class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] pl-[14px] pr-[42px] py-[12.5px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7] cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:right-0 [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:h-full [&::-webkit-calendar-picker-indicator]:cursor-pointer"
                placeholder="dd/mm/yyyy"
              />
              <button
                type="button"
                class="absolute right-3 bg-transparent border-0 p-0 flex items-center justify-center cursor-pointer pointer-events-none"
                @click="openDatePicker"
                aria-label="Pilih Tanggal Lahir"
              >
                <!-- SVG Kalender di sisi kanan -->
                <svg
                  class="w-[18px] h-[18px]"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="#6D314F"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                  <line x1="16" y1="2" x2="16" y2="6" />
                  <line x1="8" y1="2" x2="8" y2="6" />
                  <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Field 5: Alamat Domisili Lengkap -->
          <div class="mb-[17px]">
            <label for="reg-alamat" class="flex items-center gap-[7px] text-[#2D2428] text-[13px] font-bold mb-2 cursor-pointer max-[360px]:text-[12px]">
              <!-- Inline SVG Location Pin -->
              <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="#6D314F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              <span>Alamat Domisili Lengkap <span class="text-[#DC2626] ml-0.5 font-bold">*</span></span>
            </label>

            <textarea
              id="reg-alamat"
              v-model="alamat"
              @input="clearError"
              rows="3"
              placeholder="Contoh: Jl. Sudirman No. 45, RT 04 / RW 02, Jakarta Selatan"
              class="w-full min-h-[98px] max-h-[160px] resize-y bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] p-[12px_14px] text-[13.5px] text-[#2D2428] outline-none box-border leading-[1.45] transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
            ></textarea>
          </div>

          <!-- Field 6: Kata Sandi -->
          <div class="mb-[17px]">
            <label for="reg-password" class="flex items-center gap-[7px] text-[#2D2428] text-[13px] font-bold mb-2 cursor-pointer max-[360px]:text-[12px]">
              <!-- Inline SVG Lock -->
              <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="#6D314F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <span>Kata Sandi <span class="text-[#DC2626] ml-0.5 font-bold">*</span></span>
            </label>

            <div class="relative flex items-center w-full">
              <input
                id="reg-password"
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                @input="clearError"
                placeholder="Buat kata sandi yang aman"
                autocomplete="new-password"
                class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] pl-[14px] pr-[44px] py-[12.5px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
              />

              <!-- Tombol Mata Toggle Password -->
              <button
                type="button"
                class="absolute right-3 bg-transparent border-0 p-1 flex items-center justify-center cursor-pointer text-[#8E8489] rounded-md hover:text-[#6D314F] transition-colors duration-150"
                :aria-label="showPassword ? 'Sembunyikan Kata Sandi' : 'Tampilkan Kata Sandi'"
                @click="showPassword = !showPassword"
              >
                <svg
                  v-if="!showPassword"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="w-[18px] h-[18px]"
                >
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg
                  v-else
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="w-[18px] h-[18px]"
                >
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                  <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Field 7: Konfirmasi Kata Sandi -->
          <div class="mb-[17px]">
            <label for="reg-konfirmasi-password" class="flex items-center gap-[7px] text-[#2D2428] text-[13px] font-bold mb-2 cursor-pointer max-[360px]:text-[12px]">
              <!-- Inline SVG Lock -->
              <svg
                class="w-4 h-4 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="#6D314F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <span>Konfirmasi Kata Sandi <span class="text-[#DC2626] ml-0.5 font-bold">*</span></span>
            </label>

            <div class="relative flex items-center w-full">
              <input
                id="reg-konfirmasi-password"
                :type="showKonfirmasiPassword ? 'text' : 'password'"
                v-model="konfirmasiPassword"
                @input="clearError"
                placeholder="Ulangi kata sandi"
                autocomplete="new-password"
                class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] pl-[14px] pr-[44px] py-[12.5px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
              />

              <!-- Tombol Mata Toggle Konfirmasi Password -->
              <button
                type="button"
                class="absolute right-3 bg-transparent border-0 p-1 flex items-center justify-center cursor-pointer text-[#8E8489] rounded-md hover:text-[#6D314F] transition-colors duration-150"
                :aria-label="showKonfirmasiPassword ? 'Sembunyikan Konfirmasi Kata Sandi' : 'Tampilkan Konfirmasi Kata Sandi'"
                @click="showKonfirmasiPassword = !showKonfirmasiPassword"
              >
                <svg
                  v-if="!showKonfirmasiPassword"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="w-[18px] h-[18px]"
                >
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg
                  v-else
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="w-[18px] h-[18px]"
                >
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                  <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Checkbox Persetujuan -->
          <label class="flex items-start gap-[10px] my-[18px] mb-[22px] cursor-pointer select-none">
            <input
              type="checkbox"
              v-model="setuju"
              @change="clearError"
              class="w-[18px] h-[18px] accent-[#6D314F] cursor-pointer mt-0.5 shrink-0 rounded"
            />
            <span class="text-[12px] leading-[1.5] text-[#6D314F] font-medium">
              Saya menyetujui sinkronisasi data riwayat rekam medis dengan sistem pelayanan terpadu KLINIKU.
            </span>
          </label>

          <!-- Tombol Submit Daftar Sekarang -->
          <button type="submit" class="w-full bg-[#6D314F] text-white border-0 rounded-[10px] p-[14px_18px] text-[15px] font-bold cursor-pointer flex items-center justify-center gap-2 transition-all duration-200 hover:bg-[#55233C] active:scale-[0.985] group">
            <span>Daftar Sekarang</span>
            <!-- Inline SVG Arrow Right -->
            <svg
              class="w-[17px] h-[17px] transition-transform duration-200 group-hover:translate-x-[3px]"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </button>
        </form>

        <!-- Link Kembali ke Halaman Masuk -->
        <p class="mt-[18px] text-center text-[13px] text-[#2D2428] flex justify-center gap-[5px]">
          <span>Sudah punya akun?</span>
          <RouterLink to="/login" class="text-[#6D314F] font-bold no-underline hover:underline transition-all duration-150">
            Masuk di sini
          </RouterLink>
        </p>
      </div>

      <!-- 4. Kartu Bantuan -->
      <aside class="w-full bg-[#F8D0E3] rounded-[14px] p-[13px_18px] flex items-center gap-[14px] box-border">
        <div class="w-[42px] h-[42px] rounded-full bg-white flex items-center justify-center shrink-0 shadow-[0_2px_6px_rgba(109,49,79,0.08)]">
          <!-- Inline SVG Customer Service Headset -->
          <svg
            class="w-[21px] h-[21px]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#6D314F"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
            <path d="M16 19a4 4 0 0 1-4 4h-1" />
          </svg>
        </div>

        <div class="flex flex-col gap-[2px]">
          <span class="text-[12px] font-bold text-[#2D2428] leading-[1.2]">Butuh Bantuan Pendaftaran?</span>
          <a href="tel:085951564958" class="text-[16.5px] font-extrabold text-[#6D314F] tracking-[0.5px] no-underline leading-[1.2] transition-opacity duration-150 hover:opacity-85">0800-1-KLINIK</a>
        </div>
      </aside>
    </div>
  </main>
</template>
