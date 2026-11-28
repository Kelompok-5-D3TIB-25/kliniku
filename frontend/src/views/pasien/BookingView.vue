<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PatientHeader from '@/components/PatientHeader.vue'
import PatientBottomNav from '@/components/PatientBottomNav.vue'

const router = useRouter()

// 1. Data Pasien Aktif dari localStorage
const activeUser = ref({
  namaLengkap: 'Rahmawati',
  email: 'rahmawati@gmail.com'
})

onMounted(() => {
  try {
    const saved = localStorage.getItem('kliniku_active_user')
    if (saved) {
      const parsed = JSON.parse(saved)
      if (parsed) {
        activeUser.value = {
          namaLengkap: parsed.namaLengkap || 'Rahmawati',
          email: parsed.email || 'rahmawati@gmail.com'
        }
      }
    }
  } catch (err) {
    console.error('Gagal memuat active user:', err)
  }
})

// 2. Data Dummy Poli & Dokter (Relasional)
const poliList = [
  {
    id: 'gigi',
    nama: 'Poli Gigi',
    doctors: [
      {
        id: 'drg-rina',
        nama: 'drg. Rina Permata, Sp.Ort',
        jamPraktek: '10:00 - 13:00 WIB',
        lokasiRuang: 'Gedung B, Lt.2 (Dental 1)',
        kodePrefix: 'B',
        kuotaPenuh: false
      },
      {
        id: 'drg-budi',
        nama: 'drg. Budi Santoso',
        jamPraktek: '13:30 - 16:00 WIB',
        lokasiRuang: 'Gedung B, Lt.2 (Dental 2)',
        kodePrefix: 'B',
        kuotaPenuh: true // Simulasi jadwal dokter kuota penuh
      }
    ]
  },
  {
    id: 'umum',
    nama: 'Poli Umum',
    doctors: [
      {
        id: 'dr-andi',
        nama: 'dr. Andi Wijaya',
        jamPraktek: '08:00 - 12:00 WIB',
        lokasiRuang: 'Gedung A, Lt.1 (R. 101)',
        kodePrefix: 'A',
        kuotaPenuh: false
      },
      {
        id: 'dr-maya',
        nama: 'dr. Maya Kusuma',
        jamPraktek: '13:00 - 17:00 WIB',
        lokasiRuang: 'Gedung A, Lt.1 (R. 103)',
        kodePrefix: 'A',
        kuotaPenuh: true // Simulasi jadwal dokter kuota penuh
      }
    ]
  },
  {
    id: 'penyakit-dalam',
    nama: 'Penyakit Dalam',
    doctors: [
      {
        id: 'dr-sarah',
        nama: 'dr. Sarah Amanda, Sp.PD',
        jamPraktek: '09:00 - 11:30 WIB',
        lokasiRuang: 'Gedung A, Lt.1 (R. 102)',
        kodePrefix: 'C',
        kuotaPenuh: false
      },
      {
        id: 'dr-hendra',
        nama: 'dr. Hendra Pratama, Sp.PD',
        jamPraktek: '14:00 - 16:30 WIB',
        lokasiRuang: 'Gedung A, Lt.1 (R. 105)',
        kodePrefix: 'C',
        kuotaPenuh: true // Simulasi jadwal dokter kuota penuh
      }
    ]
  },
  {
    id: 'anak',
    nama: 'Poli Anak',
    doctors: [
      {
        id: 'dr-siti',
        nama: 'dr. Siti Rahma, Sp.A',
        jamPraktek: '09:00 - 13:00 WIB',
        lokasiRuang: 'Gedung A, Lt.2 (R. 201)',
        kodePrefix: 'D',
        kuotaPenuh: false
      },
      {
        id: 'dr-dimas',
        nama: 'dr. Dimas Aditya, Sp.A',
        jamPraktek: '13:30 - 16:30 WIB',
        lokasiRuang: 'Gedung A, Lt.2 (R. 202)',
        kodePrefix: 'D',
        kuotaPenuh: true // Simulasi jadwal dokter kuota penuh
      }
    ]
  }
]

// 3. State Form dan Stepper
const currentStep = ref(1) // 1 = Formulir, 2 = Konfirmasi, 3 = E-Tiket
const selectedPoliId = ref('')
const selectedDoctorId = ref('')
const selectedDate = ref('')
const keluhanAwal = ref('')

const hasFormError = ref(false)
const showQuotaFullModal = ref(false)
const errors = ref({
  poli: false,
  dokter: false,
  tanggal: false,
  keluhan: false
})

const showToast = ref(false)
const toastMessage = ref('')

// Rentang Tanggal (Hari ini s/d 7 hari ke depan)
const minDate = computed(() => {
  const d = new Date()
  return d.toISOString().split('T')[0]
})

const maxDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 7)
  return d.toISOString().split('T')[0]
})

// Relasi Dropdown Dokter berdasarkan Poli
const availableDoctors = computed(() => {
  if (!selectedPoliId.value) return []
  const found = poliList.find(p => p.id === selectedPoliId.value)
  return found ? found.doctors : []
})

const selectedPoliObj = computed(() => {
  return poliList.find(p => p.id === selectedPoliId.value) || null
})

const selectedDoctorObj = computed(() => {
  return availableDoctors.value.find(d => d.id === selectedDoctorId.value) || null
})

// Reset dokter saat poli diganti
const handlePoliChange = () => {
  selectedDoctorId.value = ''
  errors.value.poli = false
  if (!errors.value.dokter && !errors.value.tanggal) {
    hasFormError.value = false
  }
}

const handleDoctorChange = () => {
  errors.value.dokter = false
  if (!errors.value.poli && !errors.value.tanggal) {
    hasFormError.value = false
  }
}

const handleDateChange = () => {
  errors.value.tanggal = false
  if (!errors.value.poli && !errors.value.dokter) {
    hasFormError.value = false
  }
}

// Format Tanggal Bahasa Indonesia (contoh: Kamis, 19 Nov 2026)
const formatDateIndo = (dateStr) => {
  if (!dateStr) return ''
  const parts = dateStr.split('-')
  if (parts.length !== 3) return dateStr
  const y = parseInt(parts[0], 10)
  const m = parseInt(parts[1], 10) - 1
  const d = parseInt(parts[2], 10)
  const dateObj = new Date(y, m, d)
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
  const dayName = days[dateObj.getDay()]
  const dayNum = String(d).padStart(2, '0')
  const monthName = months[m]
  return `${dayName}, ${dayNum} ${monthName} ${y}`
}

// Sensor / Masking Email (contoh: r***i@gmail.com)
const maskedEmail = computed(() => {
  const email = String(activeUser.value.email || 'rahmawati@gmail.com').trim()
  const parts = email.split('@')
  if (parts.length !== 2) return email
  const [name, domain] = parts
  if (name.length <= 2) {
    return `${name[0]}*@${domain}`
  }
  return `${name[0]}***${name.slice(-1)}@${domain}`
})

// Data Hasil Booking untuk E-Tiket
const bookingResult = ref({
  kodeRegistrasi: 'REC-20261122-B-010',
  nomorAntrean: 'B-010',
  poli: 'Poli Gigi',
  dokter: 'drg. Rina Permata, Sp.Ort',
  jamPraktek: '10:00 - 13:00 WIB',
  lokasiRuang: 'Gedung B, Lt.2 (Dental 1)',
  tanggalFormatted: 'Kamis, 19 Nov 2026'
})

// Navigasi ke Tahap 2: Validasi Form & Pengecekan Kuota Dokter
const handleToStep2 = () => {
  let valid = true
  errors.value.poli = !selectedPoliId.value
  errors.value.dokter = !selectedDoctorId.value
  errors.value.tanggal = !selectedDate.value
  errors.value.keluhan = !keluhanAwal.value.trim()

  if (errors.value.poli || errors.value.dokter || errors.value.tanggal || errors.value.keluhan) {
    hasFormError.value = true
    valid = false
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  // Jika jadwal dokter sudah penuh pada tanggal yang dipilih
  if (selectedDoctorObj.value?.kuotaPenuh) {
    showQuotaFullModal.value = true
    return
  }

  hasFormError.value = false
  currentStep.value = 2
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Navigasi ke Tahap 3: Simpan Booking
const handleSaveBooking = () => {
  // Pengecekan jika kuota penuh saat konfirmasi simpan
  if (selectedDoctorObj.value?.kuotaPenuh) {
    showQuotaFullModal.value = true
    return
  }

  const now = new Date()
  const yyyy = now.getFullYear()
  const mm = String(now.getMonth() + 1).padStart(2, '0')
  const dd = String(now.getDate()).padStart(2, '0')
  const dateCode = `${yyyy}${mm}${dd}`
  const prefix = selectedDoctorObj.value?.kodePrefix || 'A'
  const queueNumber = `${prefix}-010`
  const regCode = `REC-${dateCode}-${prefix}-010`

  bookingResult.value = {
    kodeRegistrasi: regCode,
    nomorAntrean: queueNumber,
    poli: selectedPoliObj.value?.nama || 'Poli Umum',
    dokter: selectedDoctorObj.value?.nama || '',
    jamPraktek: selectedDoctorObj.value?.jamPraktek || '',
    lokasiRuang: selectedDoctorObj.value?.lokasiRuang || '',
    tanggal: selectedDate.value || `${yyyy}-${mm}-${dd}`,
    tanggalFormatted: formatDateIndo(selectedDate.value),
    pasienNama: activeUser.value.namaLengkap,
    nik: activeUser.value.nik || '3172345678987',
    keluhanAwal: keluhanAwal.value.trim(),
    createdAt: now.toISOString()
  }

  // Simpan data booking ke array kliniku_bookings di localStorage
  try {
    const existing = JSON.parse(localStorage.getItem('kliniku_bookings') || '[]')
    existing.push(bookingResult.value)
    localStorage.setItem('kliniku_bookings', JSON.stringify(existing))
  } catch (e) {
    console.error('Gagal menyimpan booking ke localStorage:', e)
  }

  currentStep.value = 3
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Aksi tombol "Buat Janji Ulang" di pop-up kuota dokter penuh
const handleBuatJanjiUlang = () => {
  showQuotaFullModal.value = false
  currentStep.value = 1
  selectedDoctorId.value = ''
  selectedDate.value = ''
  hasFormError.value = false
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Download E-Tiket: Notifikasi Toast & Print
const handleDownloadTicket = () => {
  toastMessage.value = 'E-Tiket berhasil diunduh!'
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
    window.print()
  }, 900)
}
</script>

<template>
  <div class="w-full min-h-screen bg-[#FFF7FA] flex flex-col">
    <!-- Top Bar Sticky Header -->
    <PatientHeader />

    <!-- Main Content Container (pb-24 agar tidak tertutup bottom nav) -->
    <main class="p-[20px_18px_96px] flex flex-col gap-4 box-border">
      <!-- Stepper 3 Tahap -->
      <section class="bg-white rounded-2xl p-4 shadow-sm border border-[#F2CEDD] mb-1">
        <div class="flex items-center justify-between relative">
          <!-- Step 1 Item -->
          <div class="flex flex-col items-center z-10">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors duration-200"
              :class="currentStep > 1 ? 'bg-green-600 text-white' : (currentStep === 1 ? 'bg-[#6D314F] text-white shadow-sm' : 'bg-[#A68E9A] text-white')"
            >
              <svg v-if="currentStep > 1" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
              <span v-else>1</span>
            </div>
            <span
              class="text-[11px] mt-1.5 transition-colors"
              :class="currentStep > 1 ? 'font-bold text-green-700' : (currentStep === 1 ? 'font-bold text-[#1F1A1D]' : 'font-medium text-[#7E7379]')"
            >
              Formulir
            </span>
          </div>

          <!-- Garis Antara Step 1 dan 2 -->
          <div class="flex-1 h-[2px] mx-2 -mt-5" :class="currentStep >= 2 ? 'bg-green-600' : 'bg-[#E5D2DC]'"></div>

          <!-- Step 2 Item -->
          <div class="flex flex-col items-center z-10">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors duration-200"
              :class="currentStep > 2 ? 'bg-green-600 text-white' : (currentStep === 2 ? 'bg-[#6D314F] text-white shadow-sm' : 'bg-[#A68E9A] text-white')"
            >
              <svg v-if="currentStep > 2" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
              <span v-else>2</span>
            </div>
            <span
              class="text-[11px] mt-1.5 transition-colors"
              :class="currentStep > 2 ? 'font-bold text-green-700' : (currentStep === 2 ? 'font-bold text-[#1F1A1D]' : 'font-medium text-[#7E7379]')"
            >
              Konfirmasi
            </span>
          </div>

          <!-- Garis Antara Step 2 dan 3 -->
          <div class="flex-1 h-[2px] mx-2 -mt-5" :class="currentStep >= 3 ? 'bg-green-600' : 'bg-[#E5D2DC]'"></div>

          <!-- Step 3 Item -->
          <div class="flex flex-col items-center z-10">
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors duration-200"
              :class="currentStep === 3 ? 'bg-[#6D314F] text-white shadow-sm' : 'bg-[#A68E9A] text-white'"
            >
              <span>3</span>
            </div>
            <span
              class="text-[11px] mt-1.5 transition-colors"
              :class="currentStep === 3 ? 'font-bold text-[#1F1A1D]' : 'font-medium text-[#7E7379]'"
            >
              E-Tiket
            </span>
          </div>
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAHAP 1: FORMULIR BOOKING (currentStep === 1) -->
      <!-- ============================================== -->
      <section v-if="currentStep === 1" class="flex flex-col">
        <!-- Header Judul -->
        <div class="mb-3">
          <h1 class="text-[20px] font-extrabold text-[#1F1A1D] tracking-[-0.2px] m-0">Pesan Janji Temu</h1>
          <p class="text-[13px] text-[#4A3B42] mt-0.5 m-0">Pilih poli, dokter dan waktu kunjungan Anda</p>
        </div>

        <!-- Banner Error Kondisional -->
        <div v-if="hasFormError" class="rounded-xl bg-[#FCA5A5] border border-[#991B1B] text-[#DC2626] font-bold p-3 flex items-center gap-2 mb-3.5 shadow-sm">
          <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          <span class="text-sm">Data Belum Lengkap!</span>
        </div>

        <!-- Kartu Form Putih -->
        <div class="rounded-2xl border border-[#F2CEDD] bg-white p-5 shadow-sm space-y-4">
          <!-- Field 1: Pilih Poli -->
          <div>
            <label class="flex items-center gap-1.5 text-xs font-bold text-[#2D2428] mb-1.5">
              <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18" />
                <path d="M5 21V7l8-4v18" />
                <path d="M19 21V11l-6-4" />
                <path d="M9 9v.01" />
                <path d="M9 12v.01" />
                <path d="M9 15v.01" />
                <path d="M9 18v.01" />
              </svg>
              <span>Pilih Poli <span class="text-[#DC2626]">*</span></span>
            </label>
            <div class="relative flex items-center">
              <select
                v-model="selectedPoliId"
                @change="handlePoliChange"
                class="w-full appearance-none bg-[#FCE3EE] border border-transparent focus:border-[#6D314F] focus:bg-[#FFF2F7] rounded-xl pl-4 pr-10 py-3 text-sm text-[#2D2428] outline-none transition-colors cursor-pointer"
              >
                <option value="" disabled>Pilih poli tujuan</option>
                <option v-for="poli in poliList" :key="poli.id" :value="poli.id">
                  {{ poli.nama }}
                </option>
              </select>
              <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9" />
                </svg>
              </div>
            </div>
            <p v-if="errors.poli" class="text-xs text-[#DC2626] flex items-center gap-1 mt-1.5 font-medium">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              <span>Poli tujuan wajib dipilih</span>
            </p>
          </div>

          <!-- Field 2: Pilih Dokter -->
          <div>
            <label class="flex items-center gap-1.5 text-xs font-bold text-[#2D2428] mb-1.5">
              <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" />
                <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" />
                <circle cx="20" cy="10" r="2" />
              </svg>
              <span>Pilih Dokter <span class="text-[#DC2626]">*</span></span>
            </label>
            <div class="relative flex items-center">
              <select
                v-model="selectedDoctorId"
                @change="handleDoctorChange"
                :disabled="!selectedPoliId"
                class="w-full appearance-none bg-[#FCE3EE] border border-transparent focus:border-[#6D314F] focus:bg-[#FFF2F7] rounded-xl pl-4 pr-10 py-3 text-sm text-[#2D2428] outline-none transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <option value="" disabled>Pilih dokter tujuan</option>
                <option v-for="doc in availableDoctors" :key="doc.id" :value="doc.id">
                  {{ doc.nama }} ({{ doc.jamPraktek }}){{ doc.kuotaPenuh ? ' [Kuota Penuh]' : '' }}
                </option>
              </select>
              <div
                class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 flex items-center transition-opacity"
                :class="!selectedPoliId ? 'text-[#8E8489] opacity-60' : 'text-[#6D314F]'"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9" />
                </svg>
              </div>
            </div>
            <p v-if="errors.dokter" class="text-xs text-[#DC2626] flex items-center gap-1 mt-1.5 font-medium">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              <span>Dokter spesialis wajib diisi</span>
            </p>
          </div>

          <!-- Field 3: Pilih Tanggal Kunjungan -->
          <div>
            <label class="flex items-center gap-1.5 text-xs font-bold text-[#2D2428] mb-1.5">
              <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
              <span>Pilih Tanggal Kunjungan <span class="text-[#DC2626]">*</span></span>
            </label>
            <input
              type="date"
              v-model="selectedDate"
              :min="minDate"
              :max="maxDate"
              @change="handleDateChange"
              class="w-full bg-[#FCE3EE] border border-transparent focus:border-[#6D314F] focus:bg-[#FFF2F7] rounded-xl px-3.5 py-3 text-sm text-[#2D2428] outline-none transition-colors"
            />
            <div class="text-[11.5px] text-[#7E7379] flex items-center gap-1.5 mt-1.5">
              <svg class="w-3.5 h-3.5 text-[#8E8489] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
              </svg>
              <span>Maksimal pemesanan 7 hari ke depan</span>
            </div>
            <p v-if="errors.tanggal" class="text-xs text-[#DC2626] flex items-center gap-1 mt-1.5 font-medium">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              <span>Tanggal kunjungan wajib dipilih</span>
            </p>
          </div>

          <!-- Field 4: Keluhan Awal Pasien -->
          <div>
            <label class="flex items-center gap-1.5 text-xs font-bold text-[#2D2428] mb-1.5">
              <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <polyline points="10 9 9 9 8 9" />
              </svg>
              <span>Keluhan Awal Pasien <span class="text-[#DC2626]">*</span></span>
            </label>
            <textarea
              v-model="keluhanAwal"
              rows="3"
              placeholder="Tuliskan keluhan atau gejala yang Anda rasakan secara singkat (contoh: Nyeri lambung berulang sejak 3 hari, sensasi terbakar, dan pusing berputar)..."
              class="w-full bg-[#FCE3EE] border border-transparent focus:border-[#6D314F] focus:bg-[#FFF2F7] rounded-xl px-3.5 py-2.5 text-sm text-[#2D2428] outline-none transition-colors resize-none leading-relaxed"
            ></textarea>
            <div class="text-[11px] text-[#7E7379] flex items-center gap-1 mt-1"></div>
            <p v-if="errors.keluhan" class="text-xs text-[#DC2626] flex items-center gap-1 mt-1 font-medium">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              <span>Keluhan awal pasien wajib diisi</span>
            </p>
          </div>
        </div>

        <!-- Tombol Konfirmasi Booking -->
        <button
          type="button"
          @click="handleToStep2"
          class="w-full bg-[#6D314F] hover:bg-[#55233C] text-white font-semibold rounded-xl py-3.5 mt-5 flex items-center justify-center gap-2 transition-all active:scale-[0.985] shadow-sm cursor-pointer"
        >
          <span>Konfirmasi Booking</span>
          <span class="text-base">→</span>
        </button>
      </section>

      <!-- ============================================== -->
      <!-- TAHAP 2: KONFIRMASI JANJI TEMU (currentStep === 2) -->
      <!-- ============================================== -->
      <section v-else-if="currentStep === 2" class="flex flex-col">
        <!-- Header Judul -->
        <div class="mb-3">
          <h1 class="text-[20px] font-extrabold text-[#1F1A1D] tracking-[-0.2px] m-0">Konfirmasi Janji Temu</h1>
          <p class="text-[13px] text-[#4A3B42] mt-0.5 m-0">Periksa kembali detail kunjungan Anda sebelum disimpan ke sistem.</p>
        </div>

        <!-- Kartu Ringkasan Putih -->
        <div class="rounded-2xl border border-[#F2CEDD] bg-white p-5 shadow-sm space-y-4">
          <!-- Header Dokter -->
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-[#FCE3EE] flex items-center justify-center shrink-0 text-[#6D314F]">
              <!-- Ikon Tas Medis -->
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
              </svg>
            </div>
            <div class="flex flex-col">
              <span class="text-[11px] font-extrabold text-[#6D314F] uppercase tracking-wider">
                {{ selectedPoliObj?.nama }}
              </span>
              <h3 class="text-base font-extrabold text-[#1F1A1D] m-0 leading-tight">
                {{ selectedDoctorObj?.nama }}
              </h3>
              <div class="text-xs text-green-700 font-medium flex items-center gap-1.5 mt-0.5">
                <svg class="w-3.5 h-3.5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>SIP Terverifikasi • Kuota Tersedia</span>
              </div>
            </div>
          </div>

          <hr class="border-gray-100 my-1" />

          <!-- Grid 2x2 Detail Janji Temu -->
          <div class="grid grid-cols-2 gap-2.5">
            <!-- Box Poli Tujuan -->
            <div class="bg-[#FCE3EE] border border-[#D8B4C6] rounded-xl p-3 flex flex-col gap-1">
              <span class="text-[10px] font-bold text-[#6D314F] tracking-wider uppercase">POLI TUJUAN</span>
              <div class="flex items-center gap-1.5 text-xs font-bold text-[#1F1A1D]">
                <svg class="w-3.5 h-3.5 text-[#6D314F] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 21h18" />
                  <path d="M5 21V7l8-4v18" />
                  <path d="M19 21V11l-6-4" />
                </svg>
                <span class="truncate">{{ selectedPoliObj?.nama }}</span>
              </div>
            </div>

            <!-- Box Tanggal Kunjungan -->
            <div class="bg-[#FCE3EE] border border-[#D8B4C6] rounded-xl p-3 flex flex-col gap-1">
              <span class="text-[10px] font-bold text-[#6D314F] tracking-wider uppercase">TANGGAL KUNJUNGAN</span>
              <div class="flex items-center gap-1.5 text-xs font-bold text-[#1F1A1D]">
                <svg class="w-3.5 h-3.5 text-[#6D314F] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                  <line x1="16" y1="2" x2="16" y2="6" />
                  <line x1="8" y1="2" x2="8" y2="6" />
                  <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span class="truncate">{{ formatDateIndo(selectedDate) }}</span>
              </div>
            </div>

            <!-- Box Estimasi Waktu Praktek -->
            <div class="bg-[#FCE3EE] border border-[#D8B4C6] rounded-xl p-3 flex flex-col gap-1">
              <span class="text-[10px] font-bold text-[#6D314F] tracking-wider uppercase">ESTIMASI WAKTU PRAKTEK</span>
              <div class="flex items-center gap-1.5 text-xs font-bold text-[#1F1A1D]">
                <svg class="w-3.5 h-3.5 text-[#6D314F] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
                <span class="truncate">{{ selectedDoctorObj?.jamPraktek }}</span>
              </div>
            </div>

            <!-- Box Lokasi Ruang -->
            <div class="bg-[#FCE3EE] border border-[#D8B4C6] rounded-xl p-3 flex flex-col gap-1">
              <span class="text-[10px] font-bold text-[#6D314F] tracking-wider uppercase">LOKASI RUANG</span>
              <div class="flex items-center gap-1.5 text-xs font-bold text-[#1F1A1D]">
                <svg class="w-3.5 h-3.5 text-[#6D314F] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 20V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14" />
                  <path d="M2 20h20" />
                </svg>
                <span class="truncate">{{ selectedDoctorObj?.lokasiRuang }}</span>
              </div>
            </div>
          </div>

          <!-- Box Keluhan Awal Pasien -->
          <div class="bg-[#FFF2F7] border border-[#FADCEB] rounded-xl p-3 flex flex-col gap-1 mt-2.5">
            <span class="text-[10px] font-bold text-[#6D314F] tracking-wider uppercase">KELUHAN AWAL PASIEN</span>
            <p class="text-xs font-semibold text-[#1F1A1D] m-0 italic leading-relaxed">
              "{{ keluhanAwal }}"
            </p>
          </div>
        </div>

        <!-- Kotak Info Pengingat Email -->
        <div class="bg-[#FCE3EE] border border-[#D8B4C6] rounded-xl p-3.5 flex items-start gap-3 my-4">
          <div class="w-6 h-6 rounded-md bg-[#6D314F] text-white flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="4" width="20" height="16" rx="2" />
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </svg>
          </div>
          <p class="text-xs text-[#2D2428] leading-relaxed m-0">
            <strong class="font-bold text-[#1F1A1D] block mb-0.5">Pengingat Email Otomatis:</strong>
            Sistem akan mengunci 1 slot kuota dan mengirimkan reminder H-1 Hari sebelum estimasi praktek dimulai ke email Anda.
          </p>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-col gap-2">
          <button
            type="button"
            @click="handleSaveBooking"
            class="w-full bg-[#6D314F] hover:bg-[#55233C] text-white font-semibold rounded-xl py-3.5 text-sm transition-all active:scale-[0.985] shadow-sm cursor-pointer"
          >
            Simpan
          </button>
          <button
            type="button"
            @click="currentStep = 1"
            class="w-full bg-white hover:bg-gray-50 text-[#4A3B42] font-semibold border border-[#D8B4C6] rounded-xl py-3 text-sm transition-all cursor-pointer"
          >
            ← Edit Form
          </button>
        </div>
      </section>

      <!-- ============================================== -->
      <!-- TAHAP 3: BOOKING BERHASIL & E-TIKET (currentStep === 3) -->
      <!-- ============================================== -->
      <section v-else-if="currentStep === 3" class="flex flex-col">
        <!-- Header Judul -->
        <div class="mb-2">
          <h1 class="text-[20px] font-extrabold text-[#1F1A1D] tracking-[-0.2px] m-0">E-Tiket Janji Temu</h1>
          <p class="text-[13px] text-[#4A3B42] mt-0.5 m-0">Simpan atau unduh E-Tiket Anda untuk verifikasi saat tiba di klinik.</p>
        </div>

        <!-- Blok Status Sukses -->
        <div class="text-center my-2">
          <div class="w-14 h-14 mx-auto rounded-2xl bg-green-100 flex items-center justify-center text-green-600 mb-2.5 shadow-sm">
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
          </div>
          <h2 class="text-xl font-extrabold text-[#6D314F] mb-1">Booking Berhasil!</h2>
          <p class="text-xs text-[#4A3B42] max-w-[300px] mx-auto leading-relaxed m-0">
            E-Tiket resmi Anda telah diterbitkan dan tersimpan dalam sistem KLINIKU.
          </p>
        </div>

        <!-- Kartu E-Tiket Putih -->
        <div id="e-ticket-card" class="rounded-2xl shadow-sm border border-[#E5D2DC] bg-white overflow-hidden my-3">
          <!-- Strip Header Ungu Plum -->
          <div class="bg-[#8C4B6A] text-white px-4 py-3 flex justify-between items-center text-xs font-bold tracking-wide">
            <div class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-green-300" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
              <span>E-TIKET RESMI TERDAFTAR</span>
            </div>
            <span class="font-mono text-[11px] tracking-wider">{{ bookingResult.kodeRegistrasi }}</span>
          </div>

          <!-- Bagian QR Code -->
          <div class="py-5 px-4 text-center bg-gradient-to-b from-white to-[#FFF7FA] border-b border-[#F0D9E5]">
            <p class="text-[10px] uppercase font-bold text-[#8E8489] tracking-wider mb-2 m-0">NOMOR ANTREAN &amp; KODE BOOKING</p>

            <div class="inline-flex items-center gap-2 bg-[#FCE3EE] text-[#6D314F] font-extrabold text-base rounded-lg px-4 py-1.5 mb-3 border border-[#E8C5D6]">
              <!-- Ikon Tiket -->
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
                <path d="M13 5v2"/>
                <path d="M13 17v2"/>
                <path d="M13 11v2"/>
              </svg>
              <span>{{ bookingResult.nomorAntrean }}</span>
            </div>

            <!-- Gambar QR Code Bersih Menggunakan Inline SVG -->
            <div class="flex justify-center my-1">
              <svg class="w-32 h-32" viewBox="0 0 100 100" fill="none">
                <!-- Latar Putih QR -->
                <rect width="100" height="100" fill="#FFFFFF" rx="8" />
                <!-- Sudut Kiri Atas -->
                <rect x="10" y="10" width="24" height="24" rx="3" fill="#1F1A1D" />
                <rect x="14" y="14" width="16" height="16" fill="#FFFFFF" />
                <rect x="18" y="18" width="8" height="8" fill="#1F1A1D" />

                <!-- Sudut Kanan Atas -->
                <rect x="66" y="10" width="24" height="24" rx="3" fill="#1F1A1D" />
                <rect x="70" y="14" width="16" height="16" fill="#FFFFFF" />
                <rect x="74" y="18" width="8" height="8" fill="#1F1A1D" />

                <!-- Sudut Kiri Bawah -->
                <rect x="10" y="66" width="24" height="24" rx="3" fill="#1F1A1D" />
                <rect x="14" y="70" width="16" height="16" fill="#FFFFFF" />
                <rect x="18" y="74" width="8" height="8" fill="#1F1A1D" />

                <!-- Modul Data QR Pattern -->
                <rect x="38" y="10" width="5" height="5" fill="#1F1A1D" />
                <rect x="47" y="10" width="5" height="5" fill="#1F1A1D" />
                <rect x="56" y="10" width="5" height="5" fill="#1F1A1D" />

                <rect x="38" y="19" width="5" height="5" fill="#1F1A1D" />
                <rect x="56" y="19" width="5" height="5" fill="#1F1A1D" />

                <rect x="38" y="29" width="5" height="5" fill="#1F1A1D" />
                <rect x="47" y="29" width="5" height="5" fill="#1F1A1D" />
                <rect x="56" y="29" width="5" height="5" fill="#1F1A1D" />

                <rect x="10" y="38" width="5" height="5" fill="#1F1A1D" />
                <rect x="19" y="38" width="5" height="5" fill="#1F1A1D" />
                <rect x="29" y="38" width="5" height="5" fill="#1F1A1D" />
                <rect x="38" y="38" width="5" height="5" fill="#1F1A1D" />
                <rect x="47" y="38" width="5" height="5" fill="#1F1A1D" />
                <rect x="56" y="38" width="5" height="5" fill="#1F1A1D" />
                <rect x="66" y="38" width="5" height="5" fill="#1F1A1D" />
                <rect x="85" y="38" width="5" height="5" fill="#1F1A1D" />

                <rect x="10" y="47" width="5" height="5" fill="#1F1A1D" />
                <rect x="29" y="47" width="5" height="5" fill="#1F1A1D" />
                <rect x="47" y="47" width="5" height="5" fill="#1F1A1D" />
                <rect x="75" y="47" width="5" height="5" fill="#1F1A1D" />

                <rect x="19" y="56" width="5" height="5" fill="#1F1A1D" />
                <rect x="38" y="56" width="5" height="5" fill="#1F1A1D" />
                <rect x="47" y="56" width="5" height="5" fill="#1F1A1D" />
                <rect x="56" y="56" width="5" height="5" fill="#1F1A1D" />
                <rect x="66" y="56" width="5" height="5" fill="#1F1A1D" />
                <rect x="85" y="56" width="5" height="5" fill="#1F1A1D" />

                <rect x="38" y="66" width="5" height="5" fill="#1F1A1D" />
                <rect x="47" y="66" width="5" height="5" fill="#1F1A1D" />
                <rect x="66" y="66" width="5" height="5" fill="#1F1A1D" />
                <rect x="75" y="66" width="5" height="5" fill="#1F1A1D" />

                <rect x="38" y="75" width="5" height="5" fill="#1F1A1D" />
                <rect x="56" y="75" width="5" height="5" fill="#1F1A1D" />
                <rect x="85" y="75" width="5" height="5" fill="#1F1A1D" />

                <rect x="38" y="85" width="5" height="5" fill="#1F1A1D" />
                <rect x="47" y="85" width="5" height="5" fill="#1F1A1D" />
                <rect x="66" y="85" width="5" height="5" fill="#1F1A1D" />
                <rect x="75" y="85" width="5" height="5" fill="#1F1A1D" />
              </svg>
            </div>

            <p class="text-[10px] text-[#8E8489] font-bold tracking-wider mt-2 m-0">SCAN KODE INI DI LOKET</p>
          </div>

          <!-- Rincian Detail E-Tiket -->
          <div class="p-4 space-y-2.5 text-xs bg-white">
            <div class="flex justify-between items-center py-1 border-b border-gray-100">
              <span class="text-[#7E7379] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                <span>Pasien</span>
              </span>
              <span class="font-extrabold text-[#1F1A1D] text-right">{{ activeUser.namaLengkap }}</span>
            </div>

            <div class="flex justify-between items-center py-1 border-b border-gray-100">
              <span class="text-[#7E7379] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 21h18" />
                  <path d="M5 21V7l8-4v18" />
                  <path d="M19 21V11l-6-4" />
                </svg>
                <span>Poli</span>
              </span>
              <span class="font-extrabold text-[#1F1A1D] text-right">{{ bookingResult.poli }}</span>
            </div>

            <div class="flex justify-between items-center py-1 border-b border-gray-100">
              <span class="text-[#7E7379] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" />
                  <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" />
                  <circle cx="20" cy="10" r="2" />
                </svg>
                <span>Dokter</span>
              </span>
              <span class="font-extrabold text-[#1F1A1D] text-right">{{ bookingResult.dokter }}</span>
            </div>

            <div class="flex justify-between items-center py-1 border-b border-gray-100">
              <span class="text-[#7E7379] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                  <line x1="16" y1="2" x2="16" y2="6" />
                  <line x1="8" y1="2" x2="8" y2="6" />
                  <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span>Tanggal</span>
              </span>
              <span class="font-extrabold text-[#1F1A1D] text-right">{{ bookingResult.tanggalFormatted }}</span>
            </div>

            <div class="flex justify-between items-center py-1 border-b border-gray-100">
              <span class="text-[#7E7379] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
                <span>Jam Praktek</span>
              </span>
              <span class="font-extrabold text-[#1F1A1D] text-right">{{ bookingResult.jamPraktek }}</span>
            </div>

            <div class="flex justify-between items-center py-1">
              <span class="text-[#7E7379] flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 20V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14" />
                  <path d="M2 20h20" />
                </svg>
                <span>Lokasi Ruang</span>
              </span>
              <span class="font-extrabold text-[#1F1A1D] text-right">{{ bookingResult.lokasiRuang }}</span>
            </div>
          </div>
        </div>

        <!-- Kotak Info Pengingat Email H-1 Jam -->
        <div class="bg-[#FCE3EE] border border-[#D8B4C6] rounded-xl p-3.5 flex items-start gap-3 my-3">
          <div class="w-6 h-6 rounded-md bg-[#6D314F] text-white flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="4" width="20" height="16" rx="2" />
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
            </svg>
          </div>
          <p class="text-xs text-[#2D2428] leading-relaxed m-0">
            <strong class="font-bold text-[#1F1A1D] block mb-0.5">Pengingat Email H-1 Jam Aktif :</strong>
            Notifikasi barcode check-in &amp; estimasi antrean telah dijadwalkan otomatis ke email pasien ({{ maskedEmail }}).
          </p>
        </div>

        <!-- Tombol Aksi Akhir -->
        <div class="flex flex-col gap-2 mt-2">
          <button
            type="button"
            @click="handleDownloadTicket"
            class="w-full bg-[#6D314F] hover:bg-[#55233C] text-white font-semibold rounded-xl py-3.5 text-sm flex items-center justify-center gap-2 shadow-sm transition-all active:scale-[0.985] cursor-pointer"
          >
            <!-- Ikon Download -->
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            <span>Download E-Tiket</span>
          </button>

          <button
            type="button"
            @click="router.push('/beranda')"
            class="w-full bg-white hover:bg-gray-50 text-[#4A3B42] font-semibold border border-[#D8B4C6] rounded-xl py-3 text-sm transition-all cursor-pointer flex items-center justify-center"
          >
            Kembali ke Beranda
          </button>
        </div>
      </section>
    </main>

    <!-- Floating Toast Notification -->
    <div
      v-if="showToast"
      class="fixed top-5 left-1/2 -translate-x-1/2 z-50 bg-[#1F1A1D] text-white text-xs font-semibold px-4 py-2.5 rounded-full shadow-lg flex items-center gap-2"
    >
      <svg class="w-4 h-4 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Modal Pop-Up Kuota Dokter Penuh -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showQuotaFullModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 backdrop-blur-[2px]"
        @click.self="showQuotaFullModal = false"
      >
        <div class="bg-white rounded-[26px] w-full max-w-[340px] p-[30px_22px_24px] shadow-2xl flex flex-col items-center text-center animate-in fade-in zoom-in-95 duration-200 border border-[#F2CEDD]/50">
          <!-- Icon Kotak Pink dengan Ikon Kalender Silang Merah -->
          <div class="w-[72px] h-[72px] rounded-[18px] bg-[#FCE3EE] flex items-center justify-center mb-4">
            <svg
              class="w-10 h-10 text-[#E11D48]"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.3"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M8 2v4" />
              <path d="M16 2v4" />
              <path d="M21 13V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8" />
              <path d="M3 10h18" />
              <path d="m17 17 4 4" />
              <path d="m21 17-4 4" />
            </svg>
          </div>

          <!-- Judul Modal -->
          <h3 class="text-[19px] font-extrabold text-[#1F1A1D] mb-2 leading-tight tracking-[-0.2px]">
            Kuota Dokter Penuh
          </h3>

          <!-- Pesan Deskripsi -->
          <p class="text-[13px] text-[#2D2428] leading-[1.55] max-w-[280px] mb-6 m-0">
            Mohon maaf, kuota dokter pada tanggal tersebut sudah penuh. Silakan pilih tanggal lain atau pilih jadwal dokter lain.
          </p>

          <!-- Tombol Buat Janji Ulang -->
          <button
            type="button"
            @click="handleBuatJanjiUlang"
            class="w-full bg-[#6D314F] hover:bg-[#55233C] text-white font-bold rounded-xl py-3.5 text-[14px] transition-all active:scale-[0.985] shadow-sm cursor-pointer"
          >
            Buat Janji Ulang
          </button>
        </div>
      </div>
    </Transition>

    <!-- Bottom Navigation Bar Fixed -->
    <PatientBottomNav />
  </div>
</template>
