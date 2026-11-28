<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import PatientHeader from '@/components/PatientHeader.vue'
import PatientBottomNav from '@/components/PatientBottomNav.vue'

const router = useRouter()

const activeUser = ref({
  namaLengkap: 'Rahmawati',
  email: 'rahmawati@gmail.com'
})

// Data Antrean Hari Ini (Aktif)
const activeQueue = ref({
  nomorAntrean: 'A-015',
  sedangDipanggil: 'A-014',
  sisaAntrean: 1,
  dokter: 'dr. Sarah Amanda, Sp.PD',
  poli: 'Spesialis Penyakit Dalam',
  jamPraktek: '09:30 WIB',
  lokasiRuang: 'R. Periksa 02 (Lt.1)',
  kodeRegistrasi: 'REC-20261125-A-015'
})

// Data Jadwal Mendatang
const upcomingSchedule = ref({
  tanggal: 'RABU , 04 NOV 2026',
  jam: '14:00 WIB',
  nomorAntrean: 'B-089',
  dokter: 'dr. Sarah Amanda, Sp.PD',
  poli: 'Spesialis Penyakit Dalam',
  jamPraktek: '09:30 WIB',
  lokasiRuang: 'R. Periksa 02 (Lt.1)',
  kodeRegistrasi: 'REC-20261104-B-089'
})

// State Status Antrean Selesai di Admin
const isQueueCompleted = ref(false)
const completedQueueData = ref(null)
const hasActiveTodayQueue = ref(true)

// Helper format tanggal YYYY-MM-DD
const getTodayDateStr = () => {
  const d = new Date()
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// State Pop Up Notifikasi Login Berhasil
const showLoginSuccessToast = ref(false)
const toastPatientName = ref('')
const profilePhoto = ref('')

// State Notifikasi Perubahan Jadwal Booking Berhasil
const showRescheduleToast = ref(false)
const isRescheduleModalOpen = ref(false)
const rescheduleForm = ref({
  tanggal: '2026-11-05',
  jam: '14:00 WIB',
  alasan: 'Penyesuaian waktu luang pasien'
})

const openRescheduleModal = () => {
  rescheduleForm.value = {
    tanggal: '2026-11-05',
    jam: upcomingSchedule.value.jam || '14:00 WIB',
    alasan: ''
  }
  isRescheduleModalOpen.value = true
}

const saveReschedule = () => {
  if (rescheduleForm.value.tanggal) {
    const d = new Date(rescheduleForm.value.tanggal)
    const days = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU']
    const months = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES']
    const dayName = days[d.getDay()]
    const dayNum = String(d.getDate()).padStart(2, '0')
    const monthName = months[d.getMonth()]
    const year = d.getFullYear()
    upcomingSchedule.value.tanggal = `${dayName} , ${dayNum} ${monthName} ${year}`
  }
  upcomingSchedule.value.jam = rescheduleForm.value.jam
  isRescheduleModalOpen.value = false

  // Tampilkan notifikasi perubahan berhasil disimpan
  showRescheduleToast.value = true
  setTimeout(() => {
    showRescheduleToast.value = false
  }, 4500)
}

// State Modal Rincian Tiket
const showTicketModal = ref(false)
const selectedTicket = ref(null)

const openTicketDetails = (ticket) => {
  selectedTicket.value = ticket
  showTicketModal.value = true
}

// Fungsi Pengecekan Status Antrean Selesai
const checkQueueStatus = () => {
  try {
    const todayStr = getTodayDateStr()
    const completedTickets = JSON.parse(localStorage.getItem('kliniku_completed_tickets') || '[]')
    const activeQueueCompletedFlag = localStorage.getItem('kliniku_active_queue_completed') === 'true'
    const lastCompletedTicket = localStorage.getItem('kliniku_last_completed_ticket')
    const lastCompletedDate = localStorage.getItem('kliniku_last_completed_date') || getTodayDateStr()

    const currentTicket = activeQueue.value?.nomorAntrean || ''
    const currentTicketStatus = currentTicket ? localStorage.getItem(`kliniku_queue_status_${currentTicket}`) : null

    // Cek juga dari kliniku_bookings
    const savedBookings = JSON.parse(localStorage.getItem('kliniku_bookings') || '[]')
    let isBookingCompleted = false
    let completedBookingObj = null

    if (savedBookings.length > 0) {
      const todayBooking = savedBookings[savedBookings.length - 1]
      if (todayBooking) {
        if (
          todayBooking.status === 'Selesai' ||
          completedTickets.includes(todayBooking.nomorAntrean) ||
          localStorage.getItem(`kliniku_queue_status_${todayBooking.nomorAntrean}`) === 'Selesai'
        ) {
          isBookingCompleted = true
          completedBookingObj = todayBooking
        }
      }
    }

    const isMarkedCompleted = (
      (currentTicket && completedTickets.includes(currentTicket)) ||
      currentTicketStatus === 'Selesai' ||
      isBookingCompleted ||
      (activeQueueCompletedFlag && (!currentTicket || currentTicket === lastCompletedTicket))
    )

    // Tanggal saat pemeriksaan diselesaikan (format YYYY-MM-DD)
    const completionDate = completedBookingObj?.tanggalSelesai || lastCompletedDate || getTodayDateStr()
    const isCompletedToday = isMarkedCompleted && (completionDate === todayStr)

    if (isCompletedToday) {
      // Diselesaikan pada hari yang sama (hari ini): tampilkan card selesai
      isQueueCompleted.value = true
      hasActiveTodayQueue.value = false
      completedQueueData.value = {
        nomorAntrean: completedBookingObj?.nomorAntrean || currentTicket || lastCompletedTicket || 'D-010',
        dokter: completedBookingObj?.dokter || activeQueue.value?.dokter || 'dr. Siti Rahma, Sp.A',
        poli: completedBookingObj?.poli || activeQueue.value?.poli || 'Poli Anak',
        jamPraktek: completedBookingObj?.jamPraktek || activeQueue.value?.jamPraktek || '09:00 - 13:00 WIB',
        lokasiRuang: completedBookingObj?.lokasiRuang || activeQueue.value?.lokasiRuang || 'Gedung A, Lt.2 (R. 201)',
        waktuSelesai: completedBookingObj?.selesaiPada || new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
      }
    } else {
      // JIKA SUDAH GANTI HARI (completionDate !== todayStr) ATAU BELUM SELESAI:
      // Tampilan "Pemeriksaan Selesai" HILANG!
      isQueueCompleted.value = false

      if (isMarkedCompleted && completionDate !== todayStr) {
        // Hari sudah berganti, pemeriksaan kemarin sudah selesai.
        // Antrean kemarin tidak dimunculkan kembali sebagai antrean aktif hari ini.
        hasActiveTodayQueue.value = false
      } else {
        // Antrean hari ini belum selesai dan masih aktif berjalan
        hasActiveTodayQueue.value = true
      }
    }
  } catch (e) {
    console.warn('Error checking queue status in Beranda:', e)
  }
}

let queueCheckInterval = null

onMounted(() => {
  // Bersihkan data simulasi jika ada
  try {
    localStorage.removeItem('kliniku_simulated_date')
  } catch (e) {
    // ignore
  }

  // Cek apakah ada notifikasi login berhasil dari session
  try {
    const notifyName = sessionStorage.getItem('kliniku_show_login_toast')
    if (notifyName) {
      toastPatientName.value = notifyName
      showLoginSuccessToast.value = true
      sessionStorage.removeItem('kliniku_show_login_toast')
      setTimeout(() => {
        showLoginSuccessToast.value = false
      }, 4500)
    }
  } catch (e) {
    // ignore
  }

  try {
    const saved = localStorage.getItem('kliniku_active_user')
    if (saved) {
      const parsed = JSON.parse(saved)
      if (parsed && parsed.namaLengkap) {
        activeUser.value = parsed
      }
    }

    // Ambil foto profil jika tersedia
    const savedProfile = localStorage.getItem('kliniku_patient_profile')
    if (savedProfile) {
      const parsedProf = JSON.parse(savedProfile)
      if (parsedProf.foto) {
        profilePhoto.value = parsedProf.foto
      }
    }

    // Mengambil riwayat booking pasien dari localStorage jika ada
    const savedBookings = JSON.parse(localStorage.getItem('kliniku_bookings') || '[]')
    if (savedBookings.length > 0) {
      const latest = savedBookings[savedBookings.length - 1]

      // Hitung antrean sedang dipanggil dan sisa antrean secara realistis
      const match = latest.nomorAntrean ? latest.nomorAntrean.match(/^([A-Za-z]+)-?(\d+)$/) : null
      let called = 'A-014'
      let rem = 1
      if (match) {
        const prefix = match[1]
        const num = parseInt(match[2], 10)
        const calledNum = Math.max(1, num - 1)
        called = `${prefix}-${String(calledNum).padStart(3, '0')}`
        rem = num - calledNum
      }

      activeQueue.value = {
        nomorAntrean: latest.nomorAntrean || 'A-015',
        sedangDipanggil: called,
        sisaAntrean: rem,
        dokter: latest.dokter || 'dr. Sarah Amanda, Sp.PD',
        poli: latest.poli || 'Spesialis Penyakit Dalam',
        jamPraktek: latest.jamPraktek || '09:30 WIB',
        lokasiRuang: latest.lokasiRuang || 'R. Periksa 02 (Lt.1)',
        kodeRegistrasi: latest.kodeRegistrasi || 'REC-20261125-A-015'
      }

      if (savedBookings.length > 1) {
        const prev = savedBookings[savedBookings.length - 2]
        upcomingSchedule.value = {
          tanggal: prev.tanggalFormatted ? prev.tanggalFormatted.toUpperCase() : 'RABU , 04 NOV 2026',
          jam: prev.jamPraktek ? prev.jamPraktek.split('-')[0].trim() : '14:00 WIB',
          nomorAntrean: prev.nomorAntrean || 'B-089',
          dokter: prev.dokter || 'dr. Sarah Amanda, Sp.PD',
          poli: prev.poli || 'Spesialis Penyakit Dalam',
          jamPraktek: prev.jamPraktek || '09:30 WIB',
          lokasiRuang: prev.lokasiRuang || 'R. Periksa 02 (Lt.1)',
          kodeRegistrasi: prev.kodeRegistrasi || 'REC-20261104-B-089'
        }
      }
    }
  } catch (err) {
    console.error('Gagal memuat antrean di Beranda:', err)
  }

  // Jalankan cek status antrean
  checkQueueStatus()

  // Pasang listener real-time agar jika admin menandai selesai di tab lain, langsung reaktif
  window.addEventListener('storage', checkQueueStatus)
  queueCheckInterval = setInterval(checkQueueStatus, 1500)
})

onUnmounted(() => {
  window.removeEventListener('storage', checkQueueStatus)
  if (queueCheckInterval) clearInterval(queueCheckInterval)
})
</script>

<template>
  <div class="w-full min-h-screen bg-[#FFF7FA] flex flex-col relative">
    <!-- Pop Up Toast Notifikasi Pasien (Teleport to body agar berada di atas semua modal) -->
    <Teleport to="body">
      <!-- 1. Toast Login Berhasil -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-6 opacity-0 scale-95"
        leave-active-class="transition-all duration-200 ease-in"
        leave-to-class="-translate-y-6 opacity-0 scale-95"
      >
        <div
          v-if="showLoginSuccessToast"
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
                <span class="text-[12.5px] font-extrabold text-[#1F1A1D] whitespace-nowrap">Login Berhasil!</span>
                <span class="px-2 py-0.2 bg-[#EAFBF3] text-[#059669] text-[9.5px] font-bold rounded-full whitespace-nowrap">Aktif</span>
              </div>
              <span class="text-[11.5px] text-[#5C5257] leading-tight mt-0.5 break-words">Selamat datang kembali, <strong>{{ toastPatientName }}</strong></span>
            </div>
          </div>

          <button
            type="button"
            @click="showLoginSuccessToast = false"
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

      <!-- 2. Toast Perubahan Jadwal Booking Berhasil Disimpan -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-6 opacity-0 scale-95"
        leave-active-class="transition-all duration-200 ease-in"
        leave-to-class="-translate-y-6 opacity-0 scale-95"
      >
        <div
          v-if="showRescheduleToast"
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
                <span class="text-[12.5px] font-extrabold text-[#1F1A1D] whitespace-nowrap">Perubahan Berhasil Disimpan!</span>
                <span class="px-2 py-0.2 bg-[#EAFBF3] text-[#059669] text-[9.5px] font-bold rounded-full whitespace-nowrap">Jadwal Baru</span>
              </div>
              <span class="text-[11.5px] text-[#5C5257] leading-tight mt-0.5 break-words">Jadwal kunjungan Anda telah diperbarui ke tanggal baru</span>
            </div>
          </div>

          <button
            type="button"
            @click="showRescheduleToast = false"
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
    </Teleport>

    <!-- Top Bar Sticky Header -->
    <PatientHeader />

    <!-- Main Content Container -->
    <main class="p-[20px_18px_84px] flex flex-col gap-[20px] box-border">
      <!-- 1. Bagian Sapaan Pasien Dinamis -->
      <section class="flex flex-col">
        <div class="flex items-center gap-4">
          <div class="w-[68px] h-[68px] rounded-full bg-[#D9D9D9] shrink-0 flex items-center justify-center overflow-hidden border border-[#F2CEDD]" aria-label="Foto Pasien">
            <img v-if="profilePhoto" :src="profilePhoto" alt="Foto Pasien" class="w-full h-full object-cover" />
            <!-- Avatar default placeholder -->
            <svg v-else viewBox="0 0 24 24" fill="#A89DA4" class="w-[44px] h-[44px]">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
          </div>
          <div class="flex flex-col gap-0.5">
            <h1 class="text-[19px] font-extrabold text-[#1F1A1D] m-0 tracking-[-0.2px]">Halo, {{ activeUser.namaLengkap || 'Rahmawati' }}</h1>
            <p class="text-[13px] text-[#2D2428] m-0 leading-[1.4]">Semoga lekas pulih dan sehat selalu!</p>
          </div>
        </div>
      </section>

      <!-- 2. Bagian: Jadwal Hari Ini (Antrean Aktif atau Ditandai Selesai) -->
      <section class="flex flex-col">
        <div class="flex items-center justify-between mb-2.5">
          <h2 class="text-[17px] font-extrabold text-[#1F1A1D] tracking-[-0.2px] m-0">Jadwal Hari Ini</h2>

          <!-- Status Tag jika sudah dinyatakan Selesai oleh Admin HARI INI -->
          <span
            v-if="isQueueCompleted"
            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-[#EAFBF3] text-[#059669] border border-[#A7F3D0]"
          >
            <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
            <span>Pemeriksaan Selesai</span>
          </span>
        </div>

        <!-- TAMPILAN 1: JIKA SUDAH DINYATAKAN SELESAI HARI INI -->
        <div
          v-if="isQueueCompleted"
          class="bg-[#EAFBF3] border border-[#A7F3D0] rounded-[20px] p-[16px] flex flex-col gap-3 shadow-[0_2px_14px_rgba(16,185,129,0.08)] animate-in fade-in duration-300"
        >
          <!-- Box Utama Status Selesai -->
          <div class="bg-white rounded-[14px] p-4 text-center flex flex-col items-center justify-center shadow-xs border border-[#D1FAE5]">
            <!-- Icon Check Bulat Hijau -->
            <div class="w-12 h-12 rounded-full bg-[#EAFBF3] border-2 border-[#10B981] flex items-center justify-center text-[#10B981] mb-2.5 shadow-xs">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
            <h3 class="text-[16px] font-black text-[#1F1A1D] m-0">Pelayanan &amp; Antrean Selesai</h3>
            <p class="text-[12.5px] text-[#55464D] mt-1.5 mb-2.5 leading-relaxed max-w-[280px]">
              Nomor antrean <strong class="text-[#6D314F]">{{ completedQueueData?.nomorAntrean || activeQueue.nomorAntrean }}</strong> telah dinyatakan <strong class="text-[#059669]">Selesai</strong> oleh loket pendaftaran &amp; poliklinik.
            </p>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F0FDF4] text-[#15803D] text-[11px] font-bold border border-[#BBF7D0]">
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
              <span>Selesai pada {{ completedQueueData?.waktuSelesai || 'Hari Ini' }}</span>
            </span>
          </div>

          <!-- Rincian Dokter & Poli yang Telah Selesai Diperiksa -->
          <div class="bg-white rounded-[14px] p-[12px_14px] shadow-xs border border-[#D1FAE5] flex items-center gap-3">
            <div class="w-[50px] h-[50px] rounded-[10px] bg-[#EAFBF3] shrink-0 flex items-center justify-center overflow-hidden border border-[#A7F3D0]">
              <svg class="w-6 h-6 text-[#10B981]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="text-[13px] font-extrabold text-[#1F1A1D] leading-tight truncate m-0">{{ completedQueueData?.dokter || activeQueue.dokter }}</h4>
              <p class="text-[11px] font-bold text-[#059669] mt-0.5 mb-1 leading-tight truncate m-0">{{ completedQueueData?.poli || activeQueue.poli }}</p>
              <div class="text-[11px] text-[#55464D] flex items-center gap-2">
                <span>{{ completedQueueData?.lokasiRuang || activeQueue.lokasiRuang }}</span>
              </div>
            </div>
          </div>

          <!-- Tombol Aksi: Buka Riwayat Rekam Medis -->
          <RouterLink
            to="/riwayat"
            class="w-full py-2.5 px-4 rounded-[12px] bg-[#6D314F] text-white text-[12.5px] font-bold flex items-center justify-center gap-2 no-underline hover:bg-[#58253E] transition-colors shadow-xs"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
            </svg>
            <span>Lihat Resume Medis di Riwayat &rarr;</span>
          </RouterLink>
        </div>

        <!-- TAMPILAN 2: JIKA ANTREAN MASIH BERJALAN HARI INI (BELUM SELESAI) -->
        <div
          v-else-if="hasActiveTodayQueue"
          class="bg-[#FBCDE4] rounded-[20px] p-[13px] flex flex-col gap-[10px] shadow-[0_2px_12px_rgba(109,49,79,0.04)]"
        >
          <!-- Box 1: Nomor Antrean Anda -->
          <div class="bg-white rounded-[14px] py-[15px] px-4 text-center shadow-[0_1px_4px_rgba(0,0,0,0.02)]">
            <p class="text-[11px] font-extrabold text-[#1F1A1D] tracking-[0.5px] uppercase mb-1 m-0">NOMOR ANTREAN ANDA</p>
            <h3 class="text-[32px] font-extrabold text-[#6D314F] leading-none m-0 tracking-tight">{{ activeQueue.nomorAntrean }}</h3>
          </div>

          <!-- Box 2: Dua Kotak Sejajar (Sedang Dipanggil & Sisa Antrean) -->
          <div class="grid grid-cols-2 gap-[10px]">
            <!-- Sedang Dipanggil -->
            <div class="bg-white rounded-[14px] py-[12px] px-2 text-center shadow-[0_1px_4px_rgba(0,0,0,0.02)] flex flex-col items-center justify-center">
              <p class="text-[11.5px] font-bold text-[#1F1A1D] mb-1 m-0">Sedang Dipanggil</p>
              <p class="text-[22px] font-extrabold text-[#6D314F] leading-none m-0">{{ activeQueue.sedangDipanggil }}</p>
            </div>

            <!-- Sisa Antrean -->
            <div class="bg-white rounded-[14px] py-[12px] px-2 text-center shadow-[0_1px_4px_rgba(0,0,0,0.02)] flex flex-col items-center justify-center">
              <p class="text-[11.5px] font-bold text-[#1F1A1D] mb-1 m-0">Sisa Antrean</p>
              <p class="text-[22px] font-extrabold text-[#1F1A1D] leading-none m-0">{{ activeQueue.sisaAntrean }}</p>
            </div>
          </div>

          <!-- Box 3: Detail Dokter & Lokasi -->
          <div class="bg-white rounded-[14px] p-[12px_14px] shadow-[0_1px_4px_rgba(0,0,0,0.02)] flex items-center gap-3">
            <div class="w-[54px] h-[54px] rounded-[10px] bg-[#D9D9D9] shrink-0 flex items-center justify-center overflow-hidden">
              <svg viewBox="0 0 24 24" fill="#A89DA4" class="w-8 h-8">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="text-[13.5px] font-extrabold text-[#1F1A1D] leading-tight truncate m-0">{{ activeQueue.dokter }}</h4>
              <p class="text-[11px] font-semibold text-[#8C4B6A] mt-0.5 mb-2 leading-tight truncate m-0">{{ activeQueue.poli }}</p>
              <div class="flex items-center gap-4 text-[11px] font-semibold text-[#1F1A1D]">
                <div class="flex items-center gap-1.5 shrink-0">
                  <svg class="w-3.5 h-3.5 text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                  </svg>
                  <span>{{ activeQueue.jamPraktek }}</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                  <svg class="w-3.5 h-3.5 text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 20V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14" />
                    <path d="M2 20h20" />
                  </svg>
                  <span>{{ activeQueue.lokasiRuang }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAMPILAN 3: JIKA SUDAH GANTI HARI & TIDAK ADA JADWAL HARI INI (TAMPILAN SELESAI HILANG) -->
        <div
          v-else
          class="bg-white rounded-[20px] p-5 border border-[#F2CEDD] text-center flex flex-col items-center justify-center shadow-[0_2px_12px_rgba(109,49,79,0.04)] animate-in fade-in duration-300"
        >
          <div class="w-12 h-12 rounded-full bg-[#FFF5F9] border border-[#F8D6E6] flex items-center justify-center text-[#6D314F] mb-2.5">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
          </div>
          <h3 class="text-[15px] font-extrabold text-[#1F1A1D] m-0">Tidak Ada Antrean Hari Ini</h3>
          <p class="text-[12px] text-[#7E7379] mt-1 mb-3.5 leading-relaxed max-w-[280px]">
            Pemeriksaan hari sebelumnya telah selesai. Anda belum memiliki jadwal antrean aktif untuk hari ini.
          </p>
          <RouterLink
            to="/booking"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#6D314F] hover:bg-[#58253E] text-white text-[12px] font-bold no-underline transition-all shadow-xs cursor-pointer"
          >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            <span>Daftar / Buat Janji Temu</span>
          </RouterLink>
        </div>
      </section>

      <!-- 3. Bagian: Jadwal Mendatang -->
      <section class="flex flex-col">
        <h2 class="text-[17px] font-extrabold text-[#1F1A1D] tracking-[-0.2px] mb-2.5">Jadwal Mendatang</h2>

        <!-- Outer Card Soft Pink (Kontras Lebih Jelas) -->
        <div class="bg-[#FBCDE4] rounded-[20px] p-[13px] flex flex-col gap-[10px] shadow-[0_2px_12px_rgba(109,49,79,0.04)]">
          <!-- Top Bar: Tanggal & Tiket -->
          <div class="bg-white rounded-[10px] px-3.5 py-2 flex justify-between items-center shadow-[0_1px_4px_rgba(0,0,0,0.02)]">
            <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#1F1A1D]">
              <svg class="w-3.5 h-3.5 text-[#1F1A1D] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z" />
              </svg>
              <span>{{ upcomingSchedule.tanggal }} • {{ upcomingSchedule.jam }}</span>
            </div>
            <span class="text-[11px] font-extrabold text-[#1F1A1D]">Tiket: {{ upcomingSchedule.nomorAntrean }}</span>
          </div>

          <!-- Middle Box: Detail Dokter & Lokasi -->
          <div class="bg-white rounded-[14px] p-[12px_14px] shadow-[0_1px_4px_rgba(0,0,0,0.02)] flex items-center gap-3">
            <div class="w-[54px] h-[54px] rounded-[10px] bg-[#D9D9D9] shrink-0 flex items-center justify-center overflow-hidden">
              <svg viewBox="0 0 24 24" fill="#A89DA4" class="w-8 h-8">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="text-[13.5px] font-extrabold text-[#1F1A1D] leading-tight truncate m-0">{{ upcomingSchedule.dokter }}</h4>
              <p class="text-[11px] font-semibold text-[#8C4B6A] mt-0.5 mb-2 leading-tight truncate m-0">{{ upcomingSchedule.poli }}</p>
              <div class="flex items-center gap-4 text-[11px] font-semibold text-[#1F1A1D]">
                <div class="flex items-center gap-1.5 shrink-0">
                  <svg class="w-3.5 h-3.5 text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                  </svg>
                  <span>{{ upcomingSchedule.jamPraktek }}</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                  <svg class="w-3.5 h-3.5 text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 20V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14" />
                    <path d="M2 20h20" />
                  </svg>
                  <span>{{ upcomingSchedule.lokasiRuang }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Row: Dua Tombol Aksi (Ubah Jadwal & Rincian Tiket) -->
          <div class="grid grid-cols-2 gap-[10px]">
            <button
              type="button"
              @click="openRescheduleModal"
              class="bg-white rounded-[10px] py-[10px] px-3 flex items-center justify-center gap-1.5 text-[12px] font-bold text-[#1F1A1D] shadow-[0_1px_4px_rgba(0,0,0,0.02)] hover:bg-gray-50 active:scale-[0.985] transition-all cursor-pointer"
            >
              <svg class="w-4 h-4 text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
                <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" />
              </svg>
              <span>Ubah Jadwal</span>
            </button>

            <button
              type="button"
              @click="openTicketDetails(upcomingSchedule)"
              class="bg-white rounded-[10px] py-[10px] px-3 flex items-center justify-center gap-1.5 text-[12px] font-bold text-[#1F1A1D] shadow-[0_1px_4px_rgba(0,0,0,0.02)] hover:bg-gray-50 active:scale-[0.985] transition-all cursor-pointer"
            >
              <svg class="w-4 h-4 text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
              </svg>
              <span>Rincian Tiket</span>
            </button>
          </div>
        </div>
      </section>

      <!-- 4. Bagian Aksi Cepat -->
      <section class="flex flex-col gap-[10px]">
        <div class="flex flex-col gap-0.5">
          <h2 class="text-[16px] font-extrabold text-[#1F1A1D] m-0 tracking-[-0.2px]">Aksi Cepat</h2>
          <p class="text-[12px] text-[#4A3B42] m-0">Pilihan layanan utama untuk pasien</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <!-- Kartu 1: Buat Janji Temu -->
          <div
            @click="router.push('/booking')"
            class="bg-white rounded-[14px] p-[14px] shadow-[0_2px_12px_rgba(109,49,79,0.04)] flex flex-col cursor-pointer transition-all duration-150 hover:-translate-y-0.5 hover:shadow-[0_4px_16px_rgba(109,49,79,0.08)]"
          >
            <div class="w-[38px] h-[38px] rounded-[10px] bg-[#FBCDE4] flex items-center justify-center mb-3">
              <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="none" stroke="#6D314F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
                <line x1="12" y1="13" x2="12" y2="17" />
                <line x1="10" y1="15" x2="14" y2="15" />
              </svg>
            </div>
            <h3 class="text-[13px] font-extrabold text-[#1F1A1D] mb-0.5">Buat Janji Temu</h3>
            <p class="text-[11.5px] text-[#7E7379] m-0 leading-[1.3]">Pilih dokter &amp; kuota</p>
          </div>

          <!-- Kartu 2: Riwayat Medis -->
          <div
            @click="router.push('/riwayat')"
            class="bg-white rounded-[14px] p-[14px] shadow-[0_2px_12px_rgba(109,49,79,0.04)] flex flex-col cursor-pointer transition-all duration-150 hover:-translate-y-0.5 hover:shadow-[0_4px_16px_rgba(109,49,79,0.08)]"
          >
            <div class="w-[38px] h-[38px] rounded-[10px] bg-[#FBCDE4] flex items-center justify-center mb-3">
              <svg viewBox="0 0 24 24" class="w-[18px] h-[18px]" fill="#1F1A1D">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
              </svg>
            </div>
            <h3 class="text-[13px] font-extrabold text-[#1F1A1D] mb-0.5">Riwayat Medis</h3>
            <p class="text-[11.5px] text-[#7E7379] m-0 leading-[1.3]">Hasil periksa &amp; Resep</p>
          </div>
        </div>
      </section>
    </main>

    <!-- Modal Rincian Tiket -->
    <div
      v-if="showTicketModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
      @click.self="showTicketModal = false"
    >
      <div class="bg-white rounded-[20px] w-full max-w-[360px] p-5 shadow-xl relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-[16px] font-extrabold text-[#1F1A1D] m-0">Rincian Tiket Antrean</h3>
          <button
            type="button"
            @click="showTicketModal = false"
            class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-[#1F1A1D] border-0 cursor-pointer text-sm font-bold"
          >
            ✕
          </button>
        </div>

        <div class="bg-[#FBCDE4] rounded-xl p-3.5 text-center mb-4">
          <p class="text-[10px] font-extrabold text-[#1F1A1D] tracking-wider uppercase mb-1 m-0">NOMOR TIKET</p>
          <p class="text-[28px] font-extrabold text-[#6D314F] leading-none m-0">{{ selectedTicket?.nomorAntrean }}</p>
          <p class="text-[11px] font-mono text-[#7E7379] mt-1.5 m-0">{{ selectedTicket?.kodeRegistrasi }}</p>
        </div>

        <div class="space-y-2.5 text-xs border-y border-gray-100 py-3 mb-4">
          <div class="flex justify-between">
            <span class="text-[#7E7379]">Dokter</span>
            <span class="font-bold text-[#1F1A1D] text-right">{{ selectedTicket?.dokter }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#7E7379]">Poli</span>
            <span class="font-bold text-[#1F1A1D] text-right">{{ selectedTicket?.poli }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#7E7379]">Waktu Praktek</span>
            <span class="font-bold text-[#1F1A1D] text-right">{{ selectedTicket?.jamPraktek || selectedTicket?.jam }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#7E7379]">Lokasi Ruang</span>
            <span class="font-bold text-[#1F1A1D] text-right">{{ selectedTicket?.lokasiRuang }}</span>
          </div>
        </div>

        <button
          type="button"
          @click="showTicketModal = false"
          class="w-full bg-[#6D314F] hover:bg-[#55233C] text-white font-bold py-2.5 rounded-xl text-xs transition-colors cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>

    <!-- Modal Ubah Jadwal Booking di Halaman Beranda -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isRescheduleModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-[2px]"
        @click.self="isRescheduleModalOpen = false"
      >
        <div class="bg-white rounded-[22px] border border-[#F2CEDD] p-5 w-full max-w-[370px] shadow-[0_16px_40px_rgba(109,49,79,0.22)] flex flex-col box-border">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-[#FCE4EC] text-[#6D314F] flex items-center justify-center">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                  <line x1="16" y1="2" x2="16" y2="6" />
                  <line x1="8" y1="2" x2="8" y2="6" />
                  <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
              </div>
              <h3 class="text-[15px] font-extrabold text-[#1F1A1D] m-0">Ubah Jadwal Kunjungan</h3>
            </div>
            <button
              type="button"
              @click="isRescheduleModalOpen = false"
              class="w-7 h-7 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center cursor-pointer border-0"
            >
              ✕
            </button>
          </div>

          <!-- Info Dokter & Poli -->
          <div class="bg-[#FFF5F9] rounded-xl p-3 border border-[#F8D6E6] mb-3.5">
            <p class="text-[11px] font-bold text-[#8C4B6A] uppercase tracking-wide m-0 mb-1">DOKTER &amp; POLIKLINIK</p>
            <p class="text-[13px] font-extrabold text-[#1F1A1D] m-0 leading-tight">{{ upcomingSchedule.dokter }}</p>
            <p class="text-[11.5px] text-[#6D314F] font-semibold m-0 mt-0.5">{{ upcomingSchedule.poli }} • {{ upcomingSchedule.lokasiRuang }}</p>
          </div>

          <!-- Form Ubah Tanggal -->
          <div class="flex flex-col gap-3 mb-4">
            <div>
              <label class="block text-[11.5px] font-bold text-[#1F1A1D] mb-1">Pilih Tanggal Baru</label>
              <input
                type="date"
                v-model="rescheduleForm.tanggal"
                class="w-full bg-[#FCE3EE]/60 border border-[#F2CEDD] rounded-xl p-2.5 text-[12.5px] font-semibold text-[#1F1A1D] outline-none focus:border-[#6D314F] box-border"
              />
            </div>

            <div>
              <label class="block text-[11.5px] font-bold text-[#1F1A1D] mb-1">Pilih Jam Praktik</label>
              <div class="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  @click="rescheduleForm.jam = '09:30 WIB'"
                  :class="[
                    'py-2 px-2.5 rounded-xl text-[11.5px] font-bold border transition-all cursor-pointer',
                    rescheduleForm.jam.includes('09:30')
                      ? 'bg-[#6D314F] text-white border-[#6D314F]'
                      : 'bg-white text-[#1F1A1D] border-gray-200 hover:bg-gray-50'
                  ]"
                >
                  09:30 WIB (Pagi)
                </button>
                <button
                  type="button"
                  @click="rescheduleForm.jam = '14:00 WIB'"
                  :class="[
                    'py-2 px-2.5 rounded-xl text-[11.5px] font-bold border transition-all cursor-pointer',
                    rescheduleForm.jam.includes('14:00')
                      ? 'bg-[#6D314F] text-white border-[#6D314F]'
                      : 'bg-white text-[#1F1A1D] border-gray-200 hover:bg-gray-50'
                  ]"
                >
                  14:00 WIB (Siang)
                </button>
              </div>
            </div>

            <div>
              <label class="block text-[11.5px] font-bold text-[#1F1A1D] mb-1">Alasan Perubahan (Opsional)</label>
              <input
                type="text"
                v-model="rescheduleForm.alasan"
                placeholder="Misal: Bentrok jadwal kerja / keperluan keluarga"
                class="w-full bg-[#FCE3EE]/60 border border-[#F2CEDD] rounded-xl p-2.5 text-[12px] text-[#1F1A1D] outline-none focus:border-[#6D314F] box-border placeholder:text-gray-400"
              />
            </div>
          </div>

          <!-- Tombol Aksi Modal -->
          <div class="grid grid-cols-2 gap-2.5">
            <button
              type="button"
              @click="isRescheduleModalOpen = false"
              class="w-full py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-[12.5px] font-bold hover:bg-gray-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="saveReschedule"
              class="w-full py-2.5 rounded-xl bg-[#6D314F] hover:bg-[#55233C] text-white text-[12.5px] font-bold cursor-pointer border-0 shadow-sm transition-all transform active:scale-95"
            >
              Simpan Perubahan
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Bottom Navigation Bar Fixed -->
    <PatientBottomNav />
  </div>
</template>
