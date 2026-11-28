<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import logoKliniku from '@/assets/logo-kliniku.png'

const router = useRouter()

// State form
const nik = ref('')
const password = ref('')
const showPassword = ref(false)
const errorMessage = ref('')
const logoLoadError = ref(false)
const showRegisterCompletedToast = ref(false)

onMounted(() => {
  try {
    if (sessionStorage.getItem('kliniku_register_completed')) {
      showRegisterCompletedToast.value = true
      sessionStorage.removeItem('kliniku_register_completed')
      setTimeout(() => {
        showRegisterCompletedToast.value = false
      }, 5000)
    }
  } catch (e) {
    // ignore
  }
})

// Handle input NIK: hanya menerima angka dan maksimal 16 digit
const handleNikInput = (event) => {
  const numericOnly = event.target.value.replace(/\D/g, '').slice(0, 16)
  nik.value = numericOnly
  event.target.value = numericOnly

  // Responsif: sembunyikan pesan error saat mulai mengetik ulang
  if (errorMessage.value) {
    errorMessage.value = ''
  }
}

// Handle input kata sandi
const handlePasswordInput = () => {
  // Responsif: sembunyikan pesan error saat mulai mengetik ulang
  if (errorMessage.value) {
    errorMessage.value = ''
  }
}

// Toggle tampilan kata sandi
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

// Handle submit login
const handleSubmit = () => {
  // Validasi: NIK kosong / < 16 digit, atau Password kosong / < 8 karakter
  if (!nik.value || nik.value.length < 16 || !password.value || password.value.length < 8) {
    errorMessage.value = 'Data Belum Lengkap!'
    return
  }

  errorMessage.value = ''

  // Cari data pengguna di localStorage (kliniku_users / kliniku_patient_profile) atau buat default
  let user = {
    nik: nik.value,
    namaLengkap: 'Rahmawati Soebandono',
    email: 'rahmawati.soebandono@gmail.com'
  }

  try {
    const existingUsers = JSON.parse(localStorage.getItem('kliniku_users') || '[]')
    const userFound = existingUsers.find(u => u.nik === nik.value)
    if (userFound) {
      user = userFound
    } else {
      const savedProfile = JSON.parse(localStorage.getItem('kliniku_patient_profile') || '{}')
      if (savedProfile.namaLengkap) {
        user.namaLengkap = savedProfile.namaLengkap
      }
      if (savedProfile.email) {
        user.email = savedProfile.email
      }
    }
    localStorage.setItem('kliniku_active_user', JSON.stringify(user))
  } catch (err) {
    console.error('Gagal menyimpan active user ke localStorage:', err)
  }

  // Set flag session untuk toast notifikasi di beranda
  try {
    sessionStorage.setItem('kliniku_show_login_toast', user.namaLengkap || 'Pasien')
  } catch (e) {
    // ignore
  }

  // Langsung navigasi ke Beranda (notifikasi login berhasil akan tampil di bagian atas beranda)
  router.push('/beranda')
}

// Fallback jika lupa kata sandi diklik
const handleForgotPassword = () => {
  alert('Fitur pemulihan kata sandi dapat dilakukan melalui layanan bantuan 0800-1-KLINIK atau hubungi admin.')
}
</script>

<template>
  <main class="bg-[#FFF7FA] min-h-screen flex justify-center items-center p-[32px_18px_40px] box-border relative">
    <!-- Pop Up Toast Notifikasi Registrasi Berhasil Selesai -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="-translate-y-6 opacity-0 scale-95"
      leave-active-class="transition-all duration-200 ease-in"
      leave-to-class="-translate-y-6 opacity-0 scale-95"
    >
      <div
        v-if="showRegisterCompletedToast"
        class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-fit max-w-[calc(100vw-32px)] sm:max-w-md bg-white border border-[#A7F3D0] rounded-[16px] p-[10px_16px] shadow-[0_14px_36px_rgba(0,0,0,0.22),0_4px_12px_rgba(16,185,129,0.18)] flex items-center justify-between gap-3 box-border"
        role="alert"
      >
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-full bg-[#EAFBF3] border border-[#A7F3D0] text-[#10B981] flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>
          </div>
          <div class="flex flex-col min-w-0 text-left">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="text-[12.5px] font-extrabold text-[#1F1A1D] whitespace-nowrap">Registrasi Berhasil!</span>
              <span class="px-2 py-0.2 bg-[#EAFBF3] text-[#059669] text-[9.5px] font-bold rounded-full whitespace-nowrap">Akun Aktif</span>
            </div>
            <span class="text-[11.5px] text-[#5C5257] leading-tight mt-0.5 break-words">Akun Anda telah aktif, silakan masuk dengan NIK dan kata sandi</span>
          </div>
        </div>

        <button
          type="button"
          @click="showRegisterCompletedToast = false"
          class="text-[#9CA3AF] hover:text-[#4B5563] p-1 rounded-lg hover:bg-[#F3F4F6] transition-colors cursor-pointer bg-transparent border-0 shrink-0 ml-1"
          aria-label="Tutup notifikasi"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
    </Transition>

    <div class="w-full max-w-[420px] flex flex-col items-center">
      <!-- 1. Logo KLINIKU -->
      <header class="flex justify-center items-center mb-6">
        <!-- Logo Image dari assets -->
        <img
          v-if="!logoLoadError"
          :src="logoKliniku"
          alt="Logo KLINIKU"
          class="w-[120px] max-w-[130px] h-auto object-contain block"
          @error="logoLoadError = true"
        />

        <!-- Fallback SVG jika file logo tidak ditemukan / gagal dimuat -->
        <div v-else class="w-[90px] h-[90px] flex items-center justify-center" aria-label="Logo KLINIKU">
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
      </header>

      <!-- 2. Header Sambutan -->
      <section class="flex flex-col items-center text-center mb-[22px] w-full">
        <!-- Pill Badge -->
        <div class="inline-flex items-center gap-[7px] bg-[#F8D6E6] text-[#6D314F] px-[14px] py-[6px] rounded-full text-[11px] font-bold tracking-[0.6px] uppercase mb-3">
          <!-- Inline SVG Icon Shield -->
          <svg
            class="w-[13px] h-[13px]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
          </svg>
          <span>LAYANAN PASIEN TERPADU</span>
        </div>

        <h1 class="text-[#6D314F] text-[26px] font-extrabold leading-[1.25] mb-[10px] tracking-[-0.3px] max-[360px]:text-[22px]">Selamat Datang Kembali</h1>
        <p class="text-[#2D2428] text-[13.5px] leading-[1.5] max-w-[320px] m-0">
          Masuk dengan NIK/NIP dan kata sandi untuk mengakses antrean &amp; rekam medis Anda.
        </p>
      </section>

      <!-- 3. Banner Peringatan Error (Muncul Kondisional) -->
      <Transition
        enter-active-class="transition-all duration-250 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        leave-active-class="transition-all duration-250 ease-in"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="errorMessage" class="w-full bg-[#FFB4B4] border border-[#991B1B] rounded-[10px] p-[11px_14px] flex items-center gap-[9px] text-[#DC2626] mb-4 box-border" role="alert">
          <!-- Inline SVG Icon Tanda Seru Lingkaran Merah -->
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

      <!-- 4. Kartu Form Login -->
      <div class="bg-white rounded-[18px] shadow-[0_4px_20px_rgba(109,49,79,0.05),0_1px_3px_rgba(0,0,0,0.03)] p-6 w-full box-border mb-[18px] max-[360px]:p-[18px]">
        <form @submit.prevent="handleSubmit" novalidate>
          <!-- Input 1: NIK -->
          <div class="mb-[18px]">
            <div class="flex justify-between items-center mb-2">
              <label for="nik-input" class="flex items-center gap-2 text-[#2D2428] text-[13px] font-bold cursor-pointer max-[360px]:text-[12px]">
                <!-- Inline SVG Icon ID Card -->
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
                <span>NIK (Nomor Induk Kependudukan)</span>
              </label>
              <span class="text-[12px] text-[#8E8489] font-medium">16 digit</span>
            </div>

            <input
              id="nik-input"
              type="text"
              inputmode="numeric"
              maxlength="16"
              :value="nik"
              @input="handleNikInput"
              placeholder="317xxxxxxxxxxxxx"
              autocomplete="username"
              class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] px-[15px] py-[13px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
            />
          </div>

          <!-- Input 2: Kata Sandi -->
          <div class="mb-[18px]">
            <div class="flex justify-between items-center mb-2">
              <label for="password-input" class="flex items-center gap-2 text-[#2D2428] text-[13px] font-bold cursor-pointer max-[360px]:text-[12px]">
                <!-- Inline SVG Icon Gembok -->
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
                <span>Kata Sandi</span>
              </label>

              <button
                type="button"
                class="bg-transparent border-0 p-0 text-[#7E7379] text-[12px] font-medium cursor-pointer hover:text-[#6D314F] hover:underline transition-colors duration-150"
                @click="handleForgotPassword"
              >
                Lupa Kata Sandi?
              </button>
            </div>

            <div class="relative flex items-center w-full">
              <input
                id="password-input"
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                @input="handlePasswordInput"
                placeholder="Min. 8 karakter, kombinasi huruf & angka"
                autocomplete="current-password"
                class="w-full bg-[#FCE3EE] border-[1.5px] border-transparent rounded-[10px] pl-[15px] pr-[44px] py-[13px] text-[13.5px] text-[#2D2428] outline-none box-border transition-colors duration-200 placeholder:text-[#A89DA4] focus:border-[#6D314F] focus:bg-[#FFF2F7]"
              />

              <!-- Tombol Toggle Tampilkan / Sembunyikan Password -->
              <button
                type="button"
                class="absolute right-3 bg-transparent border-0 p-1 flex items-center justify-center cursor-pointer text-[#8E8489] rounded-md hover:text-[#6D314F] transition-colors duration-150"
                :aria-label="showPassword ? 'Sembunyikan Kata Sandi' : 'Tampilkan Kata Sandi'"
                @click="togglePasswordVisibility"
              >
                <!-- SVG Icon Mata Terbuka -->
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

                <!-- SVG Icon Mata Dicoret (Hide) -->
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

          <!-- Tombol Submit Masuk -->
          <button type="submit" class="w-full bg-[#6D314F] text-white border-0 rounded-[10px] p-[14px_18px] text-[15px] font-bold cursor-pointer flex items-center justify-center gap-2 mt-[22px] transition-all duration-200 hover:bg-[#55233C] active:scale-[0.985] group">
            <span>Masuk</span>
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

        <!-- Link Daftar Akun -->
        <p class="mt-[18px] text-center text-[13px] text-[#2D2428] flex justify-center gap-[5px]">
          <span>Belum punya akun?</span>
          <RouterLink to="/register" class="text-[#6D314F] font-bold no-underline hover:underline transition-all duration-150">
            Daftar Akun
          </RouterLink>
        </p>
      </div>

      <!-- 5. Kartu Bantuan -->
      <aside class="w-full bg-[#F8D0E3] rounded-[14px] p-[13px_18px] flex items-center gap-[14px] box-border">
        <div class="w-[42px] h-[42px] rounded-full bg-white flex items-center justify-center shrink-0 shadow-[0_2px_6px_rgba(109,49,79,0.08)]">
          <!-- Inline SVG Icon Customer Service dengan Headset -->
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
          <span class="text-[12px] font-bold text-[#2D2428] leading-[1.2]">Butuh Bantuan Masuk?</span>
          <a href="tel:085951564958" class="text-[16.5px] font-extrabold text-[#6D314F] tracking-[0.5px] no-underline leading-[1.2] transition-opacity duration-150 hover:opacity-85">0800-1-KLINIK</a>
        </div>
      </aside>
    </div>
  </main>
</template>
