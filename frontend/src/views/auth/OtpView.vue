<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import logoKliniku from '@/assets/logo-kliniku.png'

const router = useRouter()
const route = useRoute()

// Ambil email dari query URL atau localStorage, atau fallback default
const userEmail = computed(() => {
  if (route.query.email) return String(route.query.email)
  try {
    const existingUsers = JSON.parse(localStorage.getItem('kliniku_users') || '[]')
    if (existingUsers.length > 0) {
      return existingUsers[existingUsers.length - 1].email || 'rara@gmail.com'
    }
  } catch (e) {
    // ignore
  }
  return 'rara@gmail.com'
})

// Format sensor email (contoh: rara********@gmail.com)
const maskedEmail = computed(() => {
  const email = userEmail.value
  const parts = email.split('@')
  if (parts.length !== 2) return email
  const name = parts[0]
  const domain = parts[1]
  if (name.length <= 4) {
    return `${name}********@${domain}`
  }
  const visible = name.slice(0, 4)
  return `${visible}********@${domain}`
})

// State OTP (6 digit)
const otpDigits = ref(['', '', '', '', '', ''])
const inputRefs = ref([])
const isInvalid = ref(false)
const isSuccess = ref(false)
const isSubmitting = ref(false)
const logoLoadError = ref(false)
const showRegisterSuccessToast = ref(false)

// State Countdown Timer
const timer = ref(45)
let timerInterval = null

const startCountdown = (seconds = 45) => {
  if (timerInterval) clearInterval(timerInterval)
  timer.value = seconds
  timerInterval = setInterval(() => {
    if (timer.value > 0) {
      timer.value--
    } else {
      clearInterval(timerInterval)
    }
  }, 1000)
}

const formattedTimer = computed(() => {
  const minutes = Math.floor(timer.value / 60)
  const seconds = timer.value % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
})

onMounted(() => {
  try {
    if (sessionStorage.getItem('kliniku_register_success')) {
      showRegisterSuccessToast.value = true
      sessionStorage.removeItem('kliniku_register_success')
      setTimeout(() => {
        showRegisterSuccessToast.value = false
      }, 4500)
    }
  } catch (e) {
    // ignore
  }

  startCountdown(45)
  nextTick(() => {
    if (inputRefs.value[0]) {
      inputRefs.value[0].focus()
    }
  })
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

// Handle Input per digit
const handleInput = (index, event) => {
  const value = event.target.value
  // Reset status invalid saat pengguna mulai mengetik ulang
  if (isInvalid.value) {
    isInvalid.value = false
  }

  // Hanya izinkan angka
  const numericVal = value.replace(/\D/g, '')

  if (!numericVal) {
    otpDigits.value[index] = ''
    return
  }

  // Jika paste banyak angka di satu input
  if (numericVal.length > 1) {
    handlePastedCode(numericVal)
    return
  }

  otpDigits.value[index] = numericVal

  // Pindah fokus ke input berikutnya secara otomatis
  if (numericVal && index < 5) {
    nextTick(() => {
      inputRefs.value[index + 1]?.focus()
    })
  }

  // Jika sudah 6 digit terisi semua, bisa otomatis validasi atau tunggu tombol klik
  if (index === 5 && otpDigits.value.every(d => d !== '')) {
    // Tetap fokus atau biarkan pengguna klik tombol verifikasi
  }
}

// Handle Keydown (Backspace & Navigasi Panah)
const handleKeyDown = (index, event) => {
  if (event.key === 'Backspace') {
    if (isInvalid.value) {
      isInvalid.value = false
    }
    if (!otpDigits.value[index] && index > 0) {
      // Jika box saat ini kosong dan tekan backspace, pindah ke box sebelumnya dan kosongkan
      otpDigits.value[index - 1] = ''
      nextTick(() => {
        inputRefs.value[index - 1]?.focus()
      })
    } else {
      otpDigits.value[index] = ''
    }
  } else if (event.key === 'ArrowLeft' && index > 0) {
    inputRefs.value[index - 1]?.focus()
  } else if (event.key === 'ArrowRight' && index < 5) {
    inputRefs.value[index + 1]?.focus()
  }
}

// Handle Paste seluruh 6 digit kode OTP
const handlePaste = (event) => {
  event.preventDefault()
  const pastedData = (event.clipboardData || window.clipboardData).getData('text')
  handlePastedCode(pastedData)
}

const handlePastedCode = (text) => {
  const numericOnly = text.replace(/\D/g, '').slice(0, 6)
  if (!numericOnly) return

  if (isInvalid.value) {
    isInvalid.value = false
  }

  const chars = numericOnly.split('')
  chars.forEach((char, i) => {
    if (i < 6) {
      otpDigits.value[i] = char
    }
  })

  // Fokuskan ke input terakhir yang terisi atau box ke-6
  const nextIndex = Math.min(chars.length, 5)
  nextTick(() => {
    inputRefs.value[nextIndex]?.focus()
  })
}

// Verifikasi Kode OTP
// Kode default yang dianggap valid untuk demo: 123456
const verifyOtp = () => {
  const fullCode = otpDigits.value.join('')

  // Jika belum lengkap 6 digit
  if (fullCode.length < 6) {
    isInvalid.value = true
    return
  }

  isSubmitting.value = true

  // Simulasi cek verifikasi
  setTimeout(() => {
    isSubmitting.value = false

    // Validasi kode verifikasi OTP
    if (fullCode === '123456') {
      isInvalid.value = false
      isSuccess.value = true
      try {
        sessionStorage.setItem('kliniku_register_completed', 'true')
      } catch (e) {
        // ignore
      }
      setTimeout(() => {
        router.push('/login')
      }, 500)
    } else {
      isInvalid.value = true
    }
  }, 300)
}

// Kirim Ulang Kode OTP
const handleResend = () => {
  // Reset state
  otpDigits.value = ['', '', '', '', '', '']
  isInvalid.value = false
  startCountdown(45)
  alert(`Kode OTP baru telah dikirimkan ke email ${userEmail.value}! (Gunakan 123456 untuk tes berhasil)`)
  nextTick(() => {
    inputRefs.value[0]?.focus()
  })
}

// Ubah Email
const handleUbahEmail = () => {
  router.push('/register')
}

// Helper simulasi kode tidak valid
const demoSetInvalid = () => {
  otpDigits.value = ['4', '8', '2', '9', '1', '0']
  verifyOtp()
}

// Helper untuk demo cepat: isi kode valid
const demoSetValid = () => {
  otpDigits.value = ['1', '2', '3', '4', '5', '6']
  isInvalid.value = false
  nextTick(() => {
    inputRefs.value[5]?.focus()
  })
}
</script>

<template>
  <main class="bg-[#FFF7FA] min-h-screen w-full flex justify-center items-start p-[24px_20px_36px] box-border relative">
    <!-- Pop Up Toast Notifikasi Registrasi Berhasil -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="-translate-y-6 opacity-0 scale-95"
      leave-active-class="transition-all duration-200 ease-in"
      leave-to-class="-translate-y-6 opacity-0 scale-95"
    >
      <div
        v-if="showRegisterSuccessToast"
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
              <span class="px-2 py-0.2 bg-[#EAFBF3] text-[#059669] text-[9.5px] font-bold rounded-full whitespace-nowrap">Langkah 1 Selesai</span>
            </div>
            <span class="text-[11.5px] text-[#5C5257] leading-tight mt-0.5 break-words">Kode OTP verifikasi telah dikirimkan ke email Anda</span>
          </div>
        </div>

        <button
          type="button"
          @click="showRegisterSuccessToast = false"
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
      <!-- 1. Header & Logo -->
      <header class="flex flex-col items-center text-center mb-[20px] w-full">
        <!-- Logo KLINIKU -->
        <div class="flex justify-center items-center mb-3">
          <img
            v-if="!logoLoadError"
            :src="logoKliniku"
            alt="Logo KLINIKU"
            class="w-[105px] max-w-[115px] h-auto object-contain block"
            @error="logoLoadError = true"
          />

          <!-- Fallback SVG jika file logo tidak ditemukan / gagal dimuat -->
          <div v-else class="w-16 h-16 flex items-center justify-center" aria-label="Logo KLINIKU">
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

        <h1 class="text-[#1F1A1D] text-[22px] font-extrabold leading-[1.25] mb-[8px] tracking-[-0.2px]">
          Verifikasi Kode OTP
        </h1>
        <p class="text-[#3D3338] text-[13.5px] leading-[1.5] max-w-[340px] m-0">
          Masukkan 6 digit kode verifikasi yang telah kami kirimkan ke email <strong class="text-[#1F1A1D] font-bold">{{ maskedEmail }}</strong>
        </p>
      </header>

      <!-- 2. Peringatan Alert Jika Kode OTP Tidak Valid -->
      <Transition
        enter-active-class="transition-all duration-250 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        leave-active-class="transition-all duration-200 ease-in"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="isInvalid"
          class="w-full bg-[#FFD6D6] border border-[#F87171] rounded-[10px] p-[11px_14px] flex items-center gap-[9px] text-[#DC2626] mb-4 box-border shadow-sm"
          role="alert"
        >
          <!-- Lingkaran Tanda Seru Merah (!) -->
          <svg
            class="w-[19px] h-[19px] shrink-0"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <span class="text-[13.5px] font-bold tracking-[0.2px]">Kode OTP Tidak Valid!</span>
        </div>
      </Transition>

      <!-- 3. Kartu Verifikasi OTP -->
      <div class="bg-white rounded-[22px] shadow-[0_4px_24px_rgba(109,49,79,0.06),0_1px_3px_rgba(0,0,0,0.03)] p-[26px_20px] w-full box-border mb-[18px] flex flex-col items-center">
        <!-- Shield Badge Icon -->
        <div class="w-[58px] h-[58px] rounded-full bg-[#FCE4EC] flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-[#6D314F]" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z" />
            <path
              d="M9.2 12l2 2 3.8-4"
              fill="none"
              stroke="#FFFFFF"
              stroke-width="2.2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>

        <!-- 6 Digit Input OTP -->
        <div class="flex items-center justify-center gap-[8px] w-full max-w-[340px]" @paste="handlePaste">
          <input
            v-for="(digit, idx) in otpDigits"
            :key="idx"
            :ref="el => inputRefs[idx] = el"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="1"
            :value="digit"
            @input="handleInput(idx, $event)"
            @keydown="handleKeyDown(idx, $event)"
            :class="[
              'w-[44px] h-[50px] text-center text-[22px] font-extrabold rounded-[10px] transition-all duration-150 outline-none',
              isInvalid
                ? 'bg-[#FFEFEF] border-2 border-[#DC2626] text-[#DC2626] focus:border-[#B91C1C]'
                : digit
                  ? 'bg-[#FCEBF3] border-2 border-transparent text-[#1F1A1D] focus:border-[#6D314F] focus:bg-white'
                  : 'bg-[#FCEBF3] border-2 border-transparent text-[#1F1A1D] focus:border-[#6D314F] focus:bg-white'
            ]"
          />
        </div>

        <!-- Pesan Error di Bawah Kotak Input -->
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 -translate-y-1"
          leave-active-class="transition-all duration-150 ease-in"
          leave-to-class="opacity-0"
        >
          <div v-if="isInvalid" class="flex items-center gap-[6px] text-[#DC2626] text-[12.5px] mt-[12px] font-medium leading-tight">
            <!-- Tanda Seru Merah Lingkaran Kecil -->
            <svg class="w-[14px] h-[14px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <span>Kode salah atau kadaluwarsa. Silahkan periksa kembali.</span>
          </div>
        </Transition>

        <!-- Timer & Kirim Ulang -->
        <div class="flex flex-col items-center mt-5 text-center">
          <!-- Waktu hitung mundur -->
          <div class="flex items-center gap-1.5 text-[12.5px] text-[#55464D]">
            <!-- Clock Icon -->
            <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.1.8-1.3-4.5-2.7V7z" />
            </svg>
            <span>Kirim ulang kode dalam <strong class="font-bold text-[#6D314F]">{{ formattedTimer }}</strong></span>
          </div>

          <!-- Belum menerima kode? Kirim Ulang -->
          <p class="text-[12.5px] text-[#55464D] mt-1 mb-0">
            Belum menerima kode?
            <button
              type="button"
              @click="handleResend"
              class="font-bold text-[#6D314F] bg-transparent border-0 p-0 ml-1 cursor-pointer hover:underline disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="timer > 0"
            >
              Kirim Ulang
            </button>
          </p>
        </div>

        <!-- Tombol Verifikasi Sekarang -->
        <button
          type="button"
          @click="verifyOtp"
          :disabled="isSubmitting"
          class="w-full mt-6 bg-[#6D314F] text-white border-0 rounded-[12px] p-[13px_18px] text-[15px] font-bold cursor-pointer flex items-center justify-center gap-2 transition-all duration-200 hover:bg-[#55233C] active:scale-[0.985] group disabled:opacity-70 disabled:cursor-not-allowed"
        >
          <span>{{ isSubmitting ? 'Memverifikasi...' : 'Verifikasi Sekarang' }}</span>
          <!-- Inline Arrow Right Icon -->
          <svg
            v-if="!isSubmitting"
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

        <!-- Link Email Salah? Ubah Email -->
        <p class="mt-[18px] mb-0 text-center text-[13px] text-[#473B41] flex justify-center gap-1">
          <span>Email salah?</span>
          <button
            type="button"
            @click="handleUbahEmail"
            class="text-[#6D314F] font-bold bg-transparent border-0 p-0 cursor-pointer hover:underline"
          >
            Ubah Email
          </button>
        </p>
      </div>

      <!-- Helper Pengujian Cepat / Demo Mode -->
      <div class="w-full flex items-center justify-center gap-2 mb-4">
        <button
          type="button"
          @click="demoSetValid"
          class="text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-md font-medium hover:bg-emerald-100 transition-colors cursor-pointer"
        >
          Demo Kode Benar (123456)
        </button>
        <button
          type="button"
          @click="demoSetInvalid"
          class="text-[11px] bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-md font-medium hover:bg-rose-100 transition-colors cursor-pointer"
        >
          Demo Kode Salah (482910)
        </button>
      </div>

      <!-- 4. Kartu Bantuan Pendaftaran (Customer Support) -->
      <aside class="w-full bg-[#FCE4EC] rounded-[16px] p-[13px_18px] flex items-center gap-[14px] box-border">
        <div class="w-[42px] h-[42px] rounded-full bg-white flex items-center justify-center shrink-0 shadow-[0_2px_6px_rgba(109,49,79,0.08)]">
          <!-- Customer Service Headset Icon -->
          <svg
            class="w-[22px] h-[22px]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="#6D314F"
            stroke-width="1.9"
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
          <a
            href="tel:08001554645"
            class="text-[16.5px] font-extrabold text-[#6D314F] tracking-[0.5px] no-underline leading-[1.2] transition-opacity duration-150 hover:opacity-85"
          >
            0800-1-KLINIK
          </a>
        </div>
      </aside>
    </div>
  </main>
</template>
