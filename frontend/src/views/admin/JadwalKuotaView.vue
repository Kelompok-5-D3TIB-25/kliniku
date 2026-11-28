<script setup>
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import AdminLogoutModal from '@/components/AdminLogoutModal.vue'
import logoKliniku from '@/assets/logo-kliniku.png'

const router = useRouter()
const showLogoutModal = ref(false)
const logoLoadError = ref(false)

// Data Admin Aktif
const adminUser = ref({
  nama: 'Siti Rahmawati',
  role: 'Admin Loket',
  statusSistem: 'Online',
  tanggalHariIni: 'Kamis, 24 Okt 2024'
})

// Search & Filter State
const searchQuery = ref('')
const selectedPoliFilter = ref('Semua')
const selectedStatusFilter = ref('Semua')
const showFilterDropdown = ref(false)
const currentPage = ref(1)
const itemsPerPage = 6

// Data Jadwal & Kuota Dokter
const doctorSchedules = ref([
  {
    id: 'doc-001',
    initial: 'SA',
    nama: 'dr. Sarah Amanda, Sp.PD',
    spesialis: 'Poli Penyakit Dalam',
    sip: 'SIP: 446/SIP.D/2021/0491',
    hari: ['Senin', 'Rabu', 'Jumat'],
    jam: '08:00 - 13:00 WIB',
    durasi: 'Durasi: 5 Jam (Sesi Pagi)',
    kuotaTerpakai: 26,
    kuotaMaksimal: 30,
    status: 'Aktif',
    keteranganStatus: ''
  },
  {
    id: 'doc-002',
    initial: 'HW',
    nama: 'dr. Hendra Wijaya, Sp.A',
    spesialis: 'Poli Anak (Pediatri)',
    sip: 'SIP: 446/SIP.D/2020/0112',
    hari: ['Senin - Kamis'],
    jam: '09:00 - 14:00 WIB',
    durasi: 'Durasi: 5 Jam (Sesi Siang)',
    kuotaTerpakai: 21,
    kuotaMaksimal: 25,
    status: 'Aktif',
    keteranganStatus: ''
  },
  {
    id: 'doc-003',
    initial: 'RP',
    nama: 'drg. Rina Permata, Sp.Ort',
    spesialis: 'Poli Gigi & Ortodonti',
    sip: 'SIP: 446/SIP.G/2019/0882',
    hari: ['Selasa', 'Kamis', 'Sabtu'],
    jam: '13:00 - 17:00 WIB',
    durasi: 'Durasi: 4 Jam (Sesi Sore)',
    kuotaTerpakai: 18,
    kuotaMaksimal: 18,
    status: 'Aktif',
    keteranganStatus: ''
  },
  {
    id: 'doc-004',
    initial: 'FM',
    nama: 'dr. Farhan Malik, Sp.JP',
    spesialis: 'Poli Jantung & Pembuluh',
    sip: 'SIP: 446/SIP.D/2018/0023',
    hari: ['Senin', 'Rabu'],
    jam: '15:00 - 19:00 WIB',
    durasi: 'Durasi: 4 Jam (Sesi Malam)',
    kuotaTerpakai: 12,
    kuotaMaksimal: 20,
    status: 'Aktif',
    keteranganStatus: ''
  },
  {
    id: 'doc-005',
    initial: 'AP',
    nama: 'dr. Anisa Putri, Sp.M',
    spesialis: 'Poli Spesialis Mata',
    sip: 'SIP: 446/SIP.D/2022/0315',
    hari: ['Senin - Jumat'],
    jam: '08:30 - 12:30 WIB',
    durasi: 'Konferensi PERDAMI',
    kuotaTerpakai: 0,
    kuotaMaksimal: 22,
    status: 'Cuti Resmi',
    keteranganStatus: 'Pendaftaran ditutup otomatis'
  },
  {
    id: 'doc-006',
    initial: 'BS',
    nama: 'dr. Budi Santoso, Sp.THT-KL',
    spesialis: 'Poli THT Bedah Kepala Leher',
    sip: 'SIP: 446/SIP.D/2017/0402',
    hari: ['Rabu', 'Jumat', 'Sabtu'],
    jam: '10:00 - 15:00 WIB',
    durasi: 'Durasi: 5 Jam (Sesi Siang)',
    kuotaTerpakai: 15,
    kuotaMaksimal: 25,
    status: 'Aktif',
    keteranganStatus: ''
  },
  {
    id: 'doc-007',
    initial: 'NH',
    nama: 'dr. Nurul Hidayati, Sp.OG',
    spesialis: 'Poli Kandungan & Kebidanan',
    sip: 'SIP: 446/SIP.D/2019/0122',
    hari: ['Selasa', 'Kamis'],
    jam: '08:30 - 12:30 WIB',
    durasi: 'Durasi: 4 Jam (Sesi Pagi)',
    kuotaTerpakai: 25,
    kuotaMaksimal: 30,
    status: 'Aktif',
    keteranganStatus: ''
  },
  {
    id: 'doc-008',
    initial: 'DS',
    nama: 'dr. Dimas Setiawan, Sp.S',
    spesialis: 'Poli Saraf & Neurologi',
    sip: 'SIP: 446/SIP.D/2020/0553',
    hari: ['Senin', 'Kamis'],
    jam: '14:00 - 18:00 WIB',
    durasi: 'Durasi: 4 Jam (Sesi Sore)',
    kuotaTerpakai: 25,
    kuotaMaksimal: 30,
    status: 'Aktif',
    keteranganStatus: ''
  }
])

// Statistik Kartu
const totalDokter = computed(() => doctorSchedules.value.length)
const dokterAktif = computed(() => doctorSchedules.value.filter(d => d.status === 'Aktif').length)
const totalKuotaTerpakai = computed(() => doctorSchedules.value.reduce((acc, d) => acc + d.kuotaTerpakai, 0))
const totalKuotaMaksimal = computed(() => doctorSchedules.value.reduce((acc, d) => acc + d.kuotaMaksimal, 0))
const persentaseUtilisasi = computed(() => {
  if (totalKuotaMaksimal.value === 0) return 0
  return ((totalKuotaTerpakai.value / totalKuotaMaksimal.value) * 100).toFixed(1)
})

// Filter Data
const filteredList = computed(() => {
  return doctorSchedules.value.filter(item => {
    const matchSearch =
      item.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.spesialis.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.sip.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchPoli = selectedPoliFilter.value === 'Semua' || item.spesialis.includes(selectedPoliFilter.value)
    const matchStatus = selectedStatusFilter.value === 'Semua' || item.status === selectedStatusFilter.value

    return matchSearch && matchPoli && matchStatus
  })
})

// Pagination
const totalPages = computed(() => Math.ceil(filteredList.value.length / itemsPerPage) || 1)
const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredList.value.slice(start, start + itemsPerPage)
})

// Modal Atur Kuota / Ubah Cuti
const showQuotaModal = ref(false)
const selectedDoctor = ref(null)
const tempQuota = ref(30)
const tempStatus = ref('Aktif')
const tempCutiNote = ref('')

const openQuotaModal = (doc) => {
  selectedDoctor.value = doc
  tempQuota.value = doc.kuotaMaksimal
  tempStatus.value = doc.status
  tempCutiNote.value = doc.keteranganStatus || ''
  showQuotaModal.value = true
}

// State Toast Notifikasi
const showToast = ref(false)
const toastTitle = ref('')
const toastMessage = ref('')
const toastTag = ref('')
const toastType = ref('success')
let toastTimer = null

const triggerToast = (title, message, tag = 'Notifikasi', type = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toastTitle.value = title
  toastMessage.value = message
  toastTag.value = tag
  toastType.value = type
  showToast.value = true
  toastTimer = setTimeout(() => {
    showToast.value = false
  }, 3500)
}

const saveQuotaModal = () => {
  if (!selectedDoctor.value) return
  const doctorName = selectedDoctor.value.nama
  selectedDoctor.value.kuotaMaksimal = Number(tempQuota.value)
  selectedDoctor.value.status = tempStatus.value
  selectedDoctor.value.keteranganStatus = tempStatus.value === 'Cuti Resmi' ? (tempCutiNote.value || 'Pendaftaran ditutup sementara') : ''
  showQuotaModal.value = false
  triggerToast(
    'Perubahan Jadwal & Kuota Disimpan!',
    `Berhasil menyimpan perubahan mengatur jadwal kuota dokter (${doctorName}).`,
    'Perubahan Disimpan'
  )
}

// Modal Tambah Jadwal Baru
const showAddModal = ref(false)
const newSchedule = ref({
  nama: '',
  spesialis: 'Poli Umum',
  sip: '',
  hari: ['Senin', 'Rabu', 'Jumat'],
  jamMulai: '08:00',
  jamSelesai: '13:00',
  sesi: 'Pagi',
  kuotaMaksimal: 30
})

const submitNewSchedule = () => {
  if (!newSchedule.value.nama) {
    triggerToast('Nama Belum Diisi', 'Harap masukkan nama dokter terlebih dahulu!', 'Peringatan', 'warning')
    return
  }

  const doctorName = newSchedule.value.nama
  const initials = doctorName
    .replace('dr.', '')
    .replace('drg.', '')
    .trim()
    .split(' ')
    .map(w => w[0])
    .join('')
    .substring(0, 2)
    .toUpperCase()

  doctorSchedules.value.unshift({
    id: `doc-${Date.now()}`,
    initial: initials || 'DR',
    nama: doctorName,
    spesialis: newSchedule.value.spesialis,
    sip: newSchedule.value.sip || `SIP: 446/SIP.D/${new Date().getFullYear()}/0${Math.floor(100 + Math.random() * 900)}`,
    hari: newSchedule.value.hari,
    jam: `${newSchedule.value.jamMulai} - ${newSchedule.value.jamSelesai} WIB`,
    durasi: `Durasi Sesi ${newSchedule.value.sesi}`,
    kuotaTerpakai: 0,
    kuotaMaksimal: Number(newSchedule.value.kuotaMaksimal),
    status: 'Aktif',
    keteranganStatus: ''
  })

  showAddModal.value = false
  triggerToast(
    'Jadwal Dokter Berhasil Ditambahkan!',
    `Berhasil menambahkan jadwal dokter (${doctorName}) di halaman jadwal dan kuota dokter.`,
    'Jadwal Ditambahkan'
  )
}

// State Pop Up Konfirmasi Hapus Jadwal Dokter (Pilihan Yakin atau Tidak)
const showDeleteConfirmModal = ref(false)
const scheduleToDelete = ref(null)

const openDeleteConfirmModal = (doc) => {
  scheduleToDelete.value = doc
  showDeleteConfirmModal.value = true
}

const cancelDeleteSchedule = () => {
  showDeleteConfirmModal.value = false
  scheduleToDelete.value = null
}

const confirmDeleteSchedule = () => {
  if (!scheduleToDelete.value) return
  const doc = scheduleToDelete.value
  const idx = doctorSchedules.value.findIndex(d => d.id === doc.id)
  if (idx !== -1) {
    const removedName = doc.nama
    doctorSchedules.value.splice(idx, 1)
    triggerToast(
      'Jadwal Dokter Dihapus!',
      `Berhasil menghapus jadwal dokter (${removedName}) dari data.`,
      'Jadwal Dihapus',
      'warning'
    )
  }
  showDeleteConfirmModal.value = false
  scheduleToDelete.value = null
}

// Refresh Live
const isRefreshing = ref(false)
const refreshLive = () => {
  isRefreshing.value = true
  setTimeout(() => {
    isRefreshing.value = false
    triggerToast(
      'Sinkronisasi Kuota Berhasil!',
      'Berhasil sinkronisasi kuota dokter secara real-time ke Quota Engine.',
      'Sinkronisasi Kuota'
    )
  }, 600)
}

// Logout Admin
const handleLogout = () => {
  showLogoutModal.value = true
}

const confirmLogout = () => {
  showLogoutModal.value = false
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#FFF7FA] text-[#1F1A1D] flex font-sans">
    <!-- ============================================== -->
    <!-- 1. SIDEBAR KIRI ADMIN -->
    <!-- ============================================== -->
    <aside class="w-[240px] shrink-0 bg-[#FFF7FA] border-r border-[#F2CEDD] min-h-screen p-5 flex flex-col justify-between box-border">
      <div class="flex flex-col gap-6">
        <!-- Logo KLINIKU ADMIN LOKET -->
        <div class="flex items-center gap-2.5">
          <img
            v-if="!logoLoadError"
            :src="logoKliniku"
            alt="Logo KLINIKU"
            class="h-[30px] w-auto object-contain shrink-0"
            @error="logoLoadError = true"
          />
          <svg
            v-else
            class="w-[30px] h-[30px] shrink-0"
            viewBox="0 0 32 32"
            fill="none"
          >
            <rect width="32" height="32" rx="8" fill="#F8D6E6" />
            <path d="M16 6v20M6 16h20" stroke="#6D314F" stroke-width="4" stroke-linecap="round" />
          </svg>
          <div class="flex flex-col">
            <span class="text-[16px] font-extrabold tracking-[0.5px] text-[#1F1A1D] leading-tight">KLINIKU</span>
            <span class="text-[10px] font-bold text-[#6D314F] tracking-wider uppercase leading-none mt-0.5">Admin Loket</span>
          </div>
        </div>

        <!-- Status Sistem (Online) -->
        <div class="flex items-center justify-between px-1 text-[12px] text-[#55464D]">
          <span>Status Loket</span>
          <span class="inline-flex items-center gap-1.5 font-bold text-[#1F1A1D]">
            <span class="w-2 h-2 rounded-full bg-[#6D314F]"></span>
            <span>{{ adminUser.statusSistem }}</span>
          </span>
        </div>

        <!-- Menu Navigasi Utama -->
        <nav class="flex flex-col gap-1.5">
          <!-- Dashboard Loket / Beranda -->
          <RouterLink
            to="/admin"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all no-underline text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
              <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
            </svg>
            <span>Beranda</span>
          </RouterLink>

          <!-- Loket Pendaftaran -->
          <RouterLink
            to="/admin/pendaftaran"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all no-underline text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <line x1="19" y1="8" x2="19" y2="14" />
              <line x1="22" y1="11" x2="16" y2="11" />
            </svg>
            <span>Loket Pendaftaran Pasien Walk-In</span>
          </RouterLink>

          <!-- Jadwal & Kuota Dokter (Aktif) -->
          <button
            type="button"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-bold text-left transition-all cursor-default border-0 bg-[#6D314F] text-white shadow-sm"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>Jadwal &amp; Kuota Dokter</span>
          </button>

          <!-- Laporan Kunjungan -->
          <button
            type="button"
            @click="router.push('/admin/laporan')"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all cursor-pointer border-0 bg-transparent text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
              <polyline points="10 9 9 9 8 9" />
            </svg>
            <span>Laporan Kunjungan</span>
          </button>
        </nav>
      </div>

      <!-- Bottom Sidebar Menu: Keluar -->
      <div class="flex flex-col gap-1 pt-4 border-t border-[#F2CEDD]">
        <button
          type="button"
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-3.5 py-2 rounded-[10px] text-[13px] font-semibold text-[#DC2626] hover:bg-rose-50 text-left transition-all cursor-pointer border-0 bg-transparent"
        >
          <svg class="w-4 h-4 text-[#DC2626]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
          <span>Keluar</span>
        </button>
      </div>
    </aside>

    <!-- ============================================== -->
    <!-- 2. KONTEN UTAMA MANAJEMEN JADWAL & KUOTA DOKTER -->
    <!-- ============================================== -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- 2.1 Top Bar Header: Tanggal & Profil Admin -->
      <header class="w-full bg-transparent px-6 lg:px-8 py-4 flex items-center justify-end gap-3 box-border">

        <!-- Profil Admin Pill -->
        <div class="flex items-center gap-2.5 bg-white border border-[#F2CEDD] rounded-full px-3 py-1.5 shadow-2xs shrink-0">
          <div class="w-7 h-7 rounded-full bg-[#6D314F] text-white flex items-center justify-center font-bold text-[11px]">
            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </div>
          <span class="text-[13px] font-extrabold text-[#1F1A1D] pr-1">{{ adminUser.nama }}</span>
        </div>
      </header>

      <!-- 2.2 Konten Utama Halaman -->
      <main class="flex-1 px-6 lg:px-8 pb-10 flex flex-col gap-6 box-border">
        <!-- Title & Tombol Tambah Jadwal Baru -->
        <section class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="text-[22px] md:text-[24px] font-black text-[#1F1A1D] m-0 tracking-tight">
              Manajemen Jadwal dan Kuota Dokter
            </h1>
          </div>

          <!-- Aksi Kanan: Refresh & + Tambah Jadwal Baru -->
          <div class="flex items-center gap-2.5 shrink-0">
            <button
              type="button"
              @click="refreshLive"
              title="Perbarui Sinkronisasi"
              class="w-10 h-10 rounded-xl bg-white border border-[#F2CEDD] flex items-center justify-center text-[#6D314F] hover:bg-[#FDEBF3] transition-colors cursor-pointer shadow-xs"
            >
              <svg
                class="w-4 h-4"
                :class="{ 'animate-spin': isRefreshing }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M23 4v6h-6" />
                <path d="M1 20v-6h6" />
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
              </svg>
            </button>

            <button
              type="button"
              @click="showAddModal = true"
              class="bg-[#6D314F] hover:bg-[#55233C] text-white px-4 py-2.5 rounded-[12px] font-bold text-[13px] flex items-center gap-2 transition-all cursor-pointer border-0 shadow-sm"
            >
              <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="12" y1="11" x2="12" y2="17" />
                <line x1="9" y1="14" x2="15" y2="14" />
              </svg>
              <span>+ Tambah Jadwal Baru</span>
            </button>
          </div>
        </section>

        <!-- 2 Kartu Metrik Utama -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Card 1: Dokter Spesialis Terjadwal -->
          <div class="bg-white rounded-[18px] p-5 border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-[12px] font-medium text-[#7E7379]">Dokter Spesialis Terjadwal</span>
              <div class="w-9 h-9 rounded-full bg-[#FFF0F5] text-[#6D314F] flex items-center justify-center">
                <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" />
                  <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" />
                  <circle cx="20" cy="10" r="2" />
                </svg>
              </div>
            </div>

            <div class="mt-3">
              <div class="flex items-baseline gap-1.5">
                <span class="text-[34px] font-black text-[#1F1A1D] leading-none">{{ totalDokter }}</span>
                <span class="text-[13px] font-bold text-[#55464D]">Dokter</span>
              </div>
              <span class="text-[11.5px] font-semibold text-[#6D314F] block mt-1.5">
                {{ dokterAktif }} Aktif Praktik
              </span>
            </div>
          </div>

          <!-- Card 2: Total Kuota Hari Ini -->
          <div class="bg-white rounded-[18px] p-5 border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-[12px] font-medium text-[#7E7379]">Total Kuota Hari Ini</span>
              <div class="w-9 h-9 rounded-full bg-[#FFF0F5] text-[#6D314F] flex items-center justify-center">
                <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                  <path d="M22 12A10 10 0 0 0 12 2v10z" />
                </svg>
              </div>
            </div>

            <div class="mt-3">
              <div class="flex items-baseline justify-between">
                <div class="flex items-baseline gap-1.5">
                  <span class="text-[34px] font-black text-[#1F1A1D] leading-none">{{ totalKuotaTerpakai }}</span>
                  <span class="text-[13px] font-bold text-[#7E7379]">/ {{ totalKuotaMaksimal }} Slot</span>
                </div>
                <span class="text-[14px] font-extrabold text-[#1F1A1D]">{{ persentaseUtilisasi }}%</span>
              </div>

              <!-- Progress Bar -->
              <div class="w-full h-2.5 bg-[#FDEBF3] rounded-full overflow-hidden mt-3">
                <div
                  class="h-full bg-[#6D314F] rounded-full transition-all duration-500"
                  :style="{ width: `${persentaseUtilisasi}%` }"
                ></div>
              </div>
            </div>
          </div>
        </section>

        <!-- Search & Filter Bar -->
        <section class="flex flex-col sm:flex-row items-center justify-between gap-3 relative">
          <!-- Input Search -->
          <div class="relative w-full sm:max-w-md">
            <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-[#7E7379]">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari dokter, spesialisasi, atau poli (Ctrl+F)..."
              class="w-full bg-white border border-[#F2CEDD] rounded-[14px] pl-10 pr-4 py-2.5 text-[12.5px] text-[#1F1A1D] outline-none shadow-xs focus:border-[#6D314F] transition-colors"
            />
          </div>

          <!-- Tombol Filter Jadwal -->
          <div class="relative w-full sm:w-auto">
            <button
              type="button"
              @click="showFilterDropdown = !showFilterDropdown"
              class="w-full sm:w-auto bg-white border border-[#F2CEDD] rounded-[14px] px-4 py-2.5 text-[12.5px] font-bold text-[#55464D] hover:bg-[#FFF7FA] flex items-center justify-center gap-2 shadow-xs cursor-pointer transition-colors"
            >
              <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="21" x2="4" y2="14" />
                <line x1="4" y1="10" x2="4" y2="3" />
                <line x1="12" y1="21" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12" y2="3" />
                <line x1="20" y1="21" x2="20" y2="16" />
                <line x1="20" y1="12" x2="20" y2="3" />
                <line x1="1" y1="14" x2="7" y2="14" />
                <line x1="9" y1="8" x2="15" y2="8" />
                <line x1="17" y1="16" x2="23" y2="16" />
              </svg>
              <span>Filter Jadwal</span>
              <span class="w-5 h-5 rounded-full bg-[#FCE4EC] text-[#6D314F] text-[11px] font-bold flex items-center justify-center">3</span>
            </button>

            <!-- Dropdown Filter Popover -->
            <div
              v-if="showFilterDropdown"
              class="absolute right-0 mt-2 w-64 bg-white border border-[#F2CEDD] rounded-[16px] shadow-lg p-4 z-20 flex flex-col gap-3"
            >
              <div>
                <span class="text-[11px] font-bold uppercase text-[#7E7379] block mb-1">Status Kehadiran</span>
                <div class="relative flex items-center">
                  <select
                    v-model="selectedStatusFilter"
                    class="w-full appearance-none bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] pl-3 pr-8 py-1.5 text-[12px] text-[#1F1A1D] outline-none cursor-pointer"
                  >
                    <option value="Semua">Semua Status</option>
                    <option value="Aktif">Aktif Praktik</option>
                    <option value="Cuti Resmi">Cuti Resmi</option>
                  </select>
                  <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="6 9 12 15 18 9" />
                    </svg>
                  </div>
                </div>
              </div>

              <div>
                <span class="text-[11px] font-bold uppercase text-[#7E7379] block mb-1">Poli Spesialis</span>
                <div class="relative flex items-center">
                  <select
                    v-model="selectedPoliFilter"
                    class="w-full appearance-none bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] pl-3 pr-8 py-1.5 text-[12px] text-[#1F1A1D] outline-none cursor-pointer"
                  >
                    <option value="Semua">Semua Poliklinik</option>
                    <option value="Penyakit Dalam">Penyakit Dalam</option>
                    <option value="Anak">Anak (Pediatri)</option>
                    <option value="Gigi">Gigi &amp; Ortodonti</option>
                    <option value="Jantung">Jantung &amp; Pembuluh</option>
                    <option value="Mata">Spesialis Mata</option>
                    <option value="THT">THT</option>
                  </select>
                  <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="6 9 12 15 18 9" />
                    </svg>
                  </div>
                </div>
              </div>

              <button
                type="button"
                @click="selectedStatusFilter = 'Semua'; selectedPoliFilter = 'Semua'; showFilterDropdown = false"
                class="text-[11px] text-[#6D314F] font-bold underline cursor-pointer bg-transparent border-0 text-left mt-1"
              >
                Reset Filter
              </button>
            </div>
          </div>
        </section>

        <!-- Tabel Daftar Jadwal & Alokasi Kuota -->
        <section class="bg-white rounded-[18px] border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] overflow-hidden flex flex-col">
          <!-- Header Tabel -->
          <div class="p-5 border-b border-[#F4E2EB] flex items-center justify-between">
            <h2 class="text-[16px] font-extrabold text-[#1F1A1D] m-0">Daftar Jadwal &amp; Alokasi Kuota</h2>
          </div>

          <!-- Tabel Responsif -->
          <div class="w-full overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
              <thead class="bg-[#FFF7FA] border-b border-[#F4E2EB]">
                <tr class="text-[#7E7379] text-[11px] font-bold uppercase tracking-wider">
                  <th class="py-3.5 px-4">Dokter &amp; Spesialisasi</th>
                  <th class="py-3.5 px-4">Hari Praktik Aktif</th>
                  <th class="py-3.5 px-4">Jam Operasional</th>
                  <th class="py-3.5 px-4">Kuota &amp; Utilisasi</th>
                  <th class="py-3.5 px-4">Status Kehadiran</th>
                  <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F8E9F0] text-[13px]">
                <tr
                  v-for="row in paginatedList"
                  :key="row.id"
                  class="hover:bg-[#FFF9FB] transition-colors"
                >
                  <!-- 1. Dokter & Spesialisasi -->
                  <td class="py-4 px-4">
                    <div class="flex items-center gap-3">
                      <!-- Inisial Avatar Bulat -->
                      <div class="w-9 h-9 rounded-full bg-[#FFF0F5] border border-[#F2CEDD] text-[#6D314F] font-black text-[12px] flex items-center justify-center shrink-0">
                        {{ row.initial }}
                      </div>
                      <div class="flex flex-col">
                        <span class="font-extrabold text-[13.5px] text-[#1F1A1D]">
                          {{ row.nama }}
                        </span>
                        <span class="text-[11.5px] text-[#7E7379]">
                          {{ row.spesialis }}
                        </span>
                        <span class="text-[10.5px] text-[#A89CA2] font-mono mt-0.5">
                          {{ row.sip }}
                        </span>
                      </div>
                    </div>
                  </td>

                  <!-- 2. Hari Praktik Aktif -->
                  <td class="py-4 px-4 whitespace-nowrap">
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <span
                        v-for="(h, i) in row.hari"
                        :key="i"
                        :class="[
                          'px-2.5 py-1 rounded-[8px] text-[11px] font-semibold border',
                          row.status === 'Cuti Resmi'
                            ? 'bg-gray-100 border-gray-200 text-gray-400 line-through'
                            : 'bg-[#FFF0F5] border-[#FCE4EC] text-[#6D314F]'
                        ]"
                      >
                        {{ h }}
                      </span>
                    </div>
                  </td>

                  <!-- 3. Jam Operasional -->
                  <td class="py-4 px-4 whitespace-nowrap">
                    <div class="flex flex-col">
                      <div class="flex items-center gap-1.5 font-bold text-[12.5px] text-[#1F1A1D]">
                        <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <circle cx="12" cy="12" r="10" />
                          <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span>{{ row.jam }}</span>
                      </div>
                      <span
                        :class="[
                          'text-[11px] mt-0.5',
                          row.status === 'Cuti Resmi' ? 'text-[#BE123C] font-semibold' : 'text-[#7E7379]'
                        ]"
                      >
                        {{ row.durasi }}
                      </span>
                    </div>
                  </td>

                  <!-- 4. Kuota & Utilisasi -->
                  <td class="py-4 px-4 min-w-[170px]">
                    <div v-if="row.status !== 'Cuti Resmi'" class="flex flex-col gap-1.5">
                      <div class="flex items-center justify-between text-[11.5px]">
                        <span class="font-bold text-[#1F1A1D]">
                          {{ row.kuotaTerpakai }} / {{ row.kuotaMaksimal }} Pasien
                        </span>
                        <!-- Label PENUH atau Sisa Slot -->
                        <span
                          v-if="row.kuotaTerpakai >= row.kuotaMaksimal"
                          class="bg-[#E11D48] text-white text-[9.5px] font-extrabold px-1.5 py-0.5 rounded-[4px]"
                        >
                          PENUH
                        </span>
                        <span v-else class="text-[#7E7379] font-medium">
                          Sisa {{ row.kuotaMaksimal - row.kuotaTerpakai }} Slot
                        </span>
                      </div>

                      <!-- Progress Kuota -->
                      <div class="w-full h-1.5 bg-[#FDEBF3] rounded-full overflow-hidden">
                        <div
                          class="h-full rounded-full transition-all"
                          :class="row.kuotaTerpakai >= row.kuotaMaksimal ? 'bg-[#E11D48]' : 'bg-[#6D314F]'"
                          :style="{ width: `${Math.min(100, (row.kuotaTerpakai / row.kuotaMaksimal) * 100)}%` }"
                        ></div>
                      </div>
                    </div>

                    <!-- Jika Cuti Resmi -->
                    <div v-else class="flex flex-col">
                      <span class="text-[11px] font-bold text-[#7E7379]">
                        0 / {{ row.kuotaMaksimal }} Pasien Nonaktif Sementara
                      </span>
                      <span class="text-[10px] text-[#BE123C] mt-0.5">
                        {{ row.keteranganStatus || 'Pendaftaran ditutup otomatis' }}
                      </span>
                    </div>
                  </td>

                  <!-- 5. Status Kehadiran -->
                  <td class="py-4 px-4 whitespace-nowrap">
                    <!-- Status Aktif -->
                    <span
                      v-if="row.status === 'Aktif'"
                      class="inline-flex items-center gap-1.5 text-[11.5px] font-bold text-[#1F1A1D]"
                    >
                      <span class="w-2 h-2 rounded-full bg-[#6D314F]"></span>
                      <span>Aktif</span>
                    </span>

                    <!-- Status Cuti Resmi -->
                    <span
                      v-else-if="row.status === 'Cuti Resmi'"
                      class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#BE123C] bg-[#FFF0F5] border border-[#FBCFE8] px-2.5 py-1 rounded-full"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-[#BE123C]"></span>
                      <span>Cuti Resmi</span>
                    </span>
                  </td>

                  <!-- 6. Aksi Tindakan -->
                  <td class="py-4 px-4 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                      <!-- Tombol Atur Kuota / Ubah Cuti -->
                      <button
                        type="button"
                        @click="openQuotaModal(row)"
                        class="bg-white hover:bg-[#FFF0F5] text-[#6D314F] border border-[#F2CEDD] font-bold text-[11.5px] px-3 py-1.5 rounded-[8px] inline-flex items-center gap-1 transition-all cursor-pointer shadow-2xs"
                      >
                        <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M12 20h9" />
                          <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                        </svg>
                        <span>{{ row.status === 'Cuti Resmi' ? 'Ubah Cuti' : 'Atur Kuota' }}</span>
                      </button>

                      <!-- Tombol Hapus Jadwal (Tempat Sampah Merah) -->
                      <button
                        type="button"
                        @click="openDeleteConfirmModal(row)"
                        title="Hapus Jadwal Dokter"
                        class="w-7 h-7 rounded-[8px] bg-[#FFF0F5] hover:bg-[#FCE4EC] text-[#BE123C] border border-[#FBCFE8] flex items-center justify-center cursor-pointer transition-colors"
                      >
                        <svg class="w-3.5 h-3.5 text-[#BE123C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <polyline points="3 6 5 6 21 6" />
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Baris Kosong jika tidak ada hasil -->
                <tr v-if="filteredList.length === 0">
                  <td colspan="6" class="text-center py-8 text-[#7E7379] text-[13px]">
                    Tidak ada jadwal dokter yang sesuai dengan pencarian atau filter.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Tabel -->
          <div class="p-4 border-t border-[#F4E2EB] flex flex-col sm:flex-row items-center justify-between gap-3 text-[12px] text-[#7E7379]">
            <span>
              Menampilkan {{ Math.min(1, filteredList.length) }} – {{ Math.min(currentPage * itemsPerPage, filteredList.length) }} dari {{ filteredList.length }} jadwal dokter aktif
            </span>

            <!-- Tombol Navigasi Halaman -->
            <div class="flex items-center gap-1.5">
              <button
                type="button"
                @click="currentPage = Math.max(1, currentPage - 1)"
                :disabled="currentPage === 1"
                class="w-7 h-7 rounded-[8px] border border-[#F2CEDD] bg-white flex items-center justify-center text-[#55464D] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#FFF7FA] cursor-pointer"
              >
                &lt;
              </button>

              <button
                v-for="page in totalPages"
                :key="page"
                type="button"
                @click="currentPage = page"
                :class="[
                  'w-7 h-7 rounded-[8px] font-bold text-[12px] flex items-center justify-center transition-colors cursor-pointer border',
                  currentPage === page
                    ? 'bg-[#6D314F] text-white border-[#6D314F]'
                    : 'bg-white border-[#F2CEDD] text-[#55464D] hover:bg-[#FFF7FA]'
                ]"
              >
                {{ page }}
              </button>

              <button
                type="button"
                @click="currentPage = Math.min(totalPages, currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="w-7 h-7 rounded-[8px] border border-[#F2CEDD] bg-white flex items-center justify-center text-[#55464D] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#FFF7FA] cursor-pointer"
              >
                &gt;
              </button>
            </div>
          </div>
        </section>
      </main>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1: ATUR KUOTA & STATUS DOKTER            -->
    <!-- ============================================== -->
    <div
      v-if="showQuotaModal && selectedDoctor"
      class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-[20px] max-w-md w-full p-6 border border-[#F2CEDD] shadow-xl flex flex-col gap-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#F8E9F0]">
          <div>
            <h3 class="text-[17px] font-bold text-[#1F1A1D] m-0">Atur Kuota &amp; Status Dokter</h3>
            <span class="text-[12px] text-[#7E7379]">{{ selectedDoctor.nama }}</span>
          </div>
          <button
            type="button"
            @click="showQuotaModal = false"
            class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer border-0"
          >
            ✕
          </button>
        </div>

        <div class="flex flex-col gap-3.5 text-[13px]">
          <div>
            <label class="font-bold text-[#1F1A1D] block mb-1">Status Praktik</label>
            <div class="flex items-center gap-3">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" value="Aktif" v-model="tempStatus" class="accent-[#6D314F]" />
                <span>Aktif Praktik</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" value="Cuti Resmi" v-model="tempStatus" class="accent-[#6D314F]" />
                <span>Cuti Resmi</span>
              </label>
            </div>
          </div>

          <div v-if="tempStatus === 'Cuti Resmi'">
            <label class="font-bold text-[#1F1A1D] block mb-1">Keterangan Cuti</label>
            <input
              type="text"
              v-model="tempCutiNote"
              placeholder="Contoh: Konferensi PERDAMI / Izin Sakit"
              class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] px-3 py-2 text-[13px] outline-none"
            />
          </div>

          <div>
            <label class="font-bold text-[#1F1A1D] block mb-1">Batas Maksimal Kuota Pasien Harian</label>
            <input
              type="number"
              v-model="tempQuota"
              min="1"
              max="100"
              class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] px-3 py-2 text-[13px] outline-none"
            />
            <span class="text-[11px] text-[#7E7379] mt-1 block">
              Saat kuota terisi {{ tempQuota }}, sistem booking online otomatis ditutup.
            </span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#F8E9F0]">
          <button
            type="button"
            @click="showQuotaModal = false"
            class="px-4 py-2 rounded-[10px] text-[12.5px] font-semibold text-[#55464D] hover:bg-gray-100 border-0 cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveQuotaModal"
            class="px-4 py-2 rounded-[10px] text-[12.5px] font-bold bg-[#6D314F] text-white hover:bg-[#55233C] border-0 cursor-pointer shadow-xs"
          >
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2: TAMBAH JADWAL DOKTER BARU             -->
    <!-- ============================================== -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-[20px] max-w-lg w-full p-6 border border-[#F2CEDD] shadow-xl flex flex-col gap-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-[#F8E9F0]">
          <div>
            <h3 class="text-[17px] font-bold text-[#1F1A1D] m-0">Tambah Jadwal Praktik Baru</h3>
            <span class="text-[12px] text-[#7E7379]">Alokasi poliklinik dan kuota antrean dokter</span>
          </div>
          <button
            type="button"
            @click="showAddModal = false"
            class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer border-0"
          >
            ✕
          </button>
        </div>

        <form @submit.prevent="submitNewSchedule" class="flex flex-col gap-3.5 text-[13px]">
          <div>
            <label class="font-bold text-[#1F1A1D] block mb-1">Nama Lengkap &amp; Gelar Dokter *</label>
            <input
              type="text"
              v-model="newSchedule.nama"
              placeholder="Contoh: dr. Amanda Kusuma, Sp.A"
              required
              class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] px-3.5 py-2 text-[13px] outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-bold text-[#1F1A1D] block mb-1">Poliklinik Tujuan *</label>
              <div class="relative flex items-center">
                <select
                  v-model="newSchedule.spesialis"
                  class="w-full appearance-none bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] pl-3 pr-9 py-2 text-[13px] outline-none cursor-pointer"
                >
                  <option value="Poli Penyakit Dalam">Poli Penyakit Dalam</option>
                  <option value="Poli Anak (Pediatri)">Poli Anak (Pediatri)</option>
                  <option value="Poli Gigi & Ortodonti">Poli Gigi & Ortodonti</option>
                  <option value="Poli Jantung & Pembuluh">Poli Jantung & Pembuluh</option>
                  <option value="Poli Spesialis Mata">Poli Spesialis Mata</option>
                  <option value="Poli THT Bedah Kepala Leher">Poli THT Bedah Kepala Leher</option>
                  <option value="Poli Umum">Poli Umum</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </div>
              </div>
            </div>
            <div>
              <label class="font-bold text-[#1F1A1D] block mb-1">Nomor SIP Dokter</label>
              <input
                type="text"
                v-model="newSchedule.sip"
                placeholder="SIP: 446/SIP.D/2024/..."
                class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] px-3.5 py-2 text-[13px] outline-none"
              />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="font-bold text-[#1F1A1D] block mb-1">Jam Mulai</label>
              <input
                type="time"
                v-model="newSchedule.jamMulai"
                class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] px-3 py-2 text-[13px] outline-none"
              />
            </div>
            <div>
              <label class="font-bold text-[#1F1A1D] block mb-1">Jam Selesai</label>
              <input
                type="time"
                v-model="newSchedule.jamSelesai"
                class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] px-3 py-2 text-[13px] outline-none"
              />
            </div>
            <div>
              <label class="font-bold text-[#1F1A1D] block mb-1">Sesi Praktik</label>
              <div class="relative flex items-center">
                <select
                  v-model="newSchedule.sesi"
                  class="w-full appearance-none bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] pl-3 pr-8 py-2 text-[13px] outline-none cursor-pointer"
                >
                  <option value="Pagi">Pagi</option>
                  <option value="Siang">Siang</option>
                  <option value="Sore">Sore</option>
                  <option value="Malam">Malam</option>
                </select>
                <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="font-bold text-[#1F1A1D] block mb-1">Alokasi Maksimal Kuota Pasien</label>
            <input
              type="number"
              v-model="newSchedule.kuotaMaksimal"
              min="5"
              max="100"
              required
              class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[10px] px-3.5 py-2 text-[13px] outline-none"
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#F8E9F0]">
            <button
              type="button"
              @click="showAddModal = false"
              class="px-4 py-2 rounded-[10px] text-[12.5px] font-semibold text-[#55464D] hover:bg-gray-100 border-0 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 rounded-[10px] text-[12.5px] font-bold bg-[#6D314F] text-white hover:bg-[#55233C] border-0 cursor-pointer shadow-xs"
            >
              Simpan Jadwal Baru
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- POP UP KONFIRMASI HAPUS JADWAL DOKTER (PILIHAN YAKIN ATAU TIDAK) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="showDeleteConfirmModal && scheduleToDelete"
          class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-[2px] flex items-center justify-center p-4"
          @click.self="cancelDeleteSchedule"
        >
          <div class="w-full max-w-[420px] bg-white rounded-[22px] border border-[#F2CEDD] p-6 shadow-[0_20px_50px_rgba(109,49,79,0.2)] text-center flex flex-col items-center box-border">
            <!-- Icon Sampah Peringatan Merah -->
            <div class="w-14 h-14 rounded-full bg-rose-50 text-[#BE123C] border-2 border-rose-100 flex items-center justify-center mb-4 shadow-xs">
              <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6" />
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                <line x1="10" y1="11" x2="10" y2="17" />
                <line x1="14" y1="11" x2="14" y2="17" />
              </svg>
            </div>

            <h3 class="text-[18px] font-extrabold text-[#1F1A1D] m-0">Hapus Jadwal Dokter?</h3>
            <p class="text-[13px] text-[#55464D] mt-2 mb-4 leading-relaxed">
              Apakah Anda yakin ingin menghapus jadwal dan alokasi kuota dokter ini dari data? Tindakan ini tidak dapat dibatalkan.
            </p>

            <!-- Info Jadwal Dokter yang Akan Dihapus -->
            <div class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[14px] p-3.5 mb-5 text-left flex items-center justify-between">
              <div class="min-w-0 pr-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#7E7379] block">Dokter &amp; Poliklinik</span>
                <span class="text-[14px] font-black text-[#6D314F] block mt-0.5 truncate">
                  {{ scheduleToDelete.nama }}
                </span>
                <span class="text-[11.5px] text-[#55464D] block mt-0.5">
                  {{ scheduleToDelete.spesialis }} &bull; {{ scheduleToDelete.jam }}
                </span>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[10.5px] font-black bg-rose-100 text-[#BE123C] shrink-0">
                Hapus
              </span>
            </div>

            <!-- Tombol Pilihan: Yakin / Tidak -->
            <div class="flex items-center gap-3 w-full">
              <button
                type="button"
                @click="cancelDeleteSchedule"
                class="flex-1 py-2.5 px-4 rounded-[12px] border border-[#E5BED1] bg-white text-[#55464D] font-bold text-[13px] hover:bg-[#FFF7FA] cursor-pointer transition-colors"
              >
                Tidak, Batalkan
              </button>
              <button
                type="button"
                @click="confirmDeleteSchedule"
                class="flex-1 py-2.5 px-4 rounded-[12px] border border-transparent bg-[#BE123C] text-white font-bold text-[13px] hover:bg-[#9F1239] cursor-pointer transition-colors shadow-sm"
              >
                Ya, Yakin Hapus
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- TOAST NOTIFIKASI REAL-TIME ADMIN -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-[-16px] opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-[-16px] opacity-0"
      >
        <div
          v-if="showToast"
          class="fixed top-5 left-1/2 -translate-x-1/2 z-[10000] w-auto max-w-[92vw] sm:max-w-[480px] pointer-events-auto"
        >
          <div
            :class="[
              'rounded-[20px] px-4 py-3 flex items-center gap-3 shadow-[0_8px_30px_rgba(16,185,129,0.18)] border',
              toastType === 'warning'
                ? 'bg-[#FFFBEB] border-[#FDE68A]'
                : 'bg-[#EAFBF3] border-[#A7F3D0]'
            ]"
          >
            <!-- Checkmark Icon -->
            <div
              :class="[
                'w-7 h-7 rounded-full flex items-center justify-center shrink-0',
                toastType === 'warning'
                  ? 'bg-[#F59E0B] text-white'
                  : 'bg-[#10B981] text-white'
              ]"
            >
              <svg v-if="toastType !== 'warning'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
              <svg v-else class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
            </div>

            <div class="flex flex-col min-w-0 pr-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span
                  :class="[
                    'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider',
                    toastType === 'warning'
                      ? 'bg-amber-100 text-amber-800'
                      : 'bg-emerald-100 text-emerald-800'
                  ]"
                >
                  {{ toastTag }}
                </span>
                <span class="text-[13px] font-bold text-[#1F1A1D]">{{ toastTitle }}</span>
              </div>
              <span class="text-[11.5px] text-[#55464D] mt-0.5 leading-snug">{{ toastMessage }}</span>
            </div>

            <!-- Close Button -->
            <button
              type="button"
              @click="showToast = false"
              class="ml-auto text-[#7E7379] hover:text-[#1F1A1D] p-1 border-0 bg-transparent cursor-pointer rounded-full"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Pop Up Konfirmasi Keluar dari Portal Admin (Yakin atau Tidak) -->
    <AdminLogoutModal
      :isOpen="showLogoutModal"
      :adminName="adminUser.nama"
      @close="showLogoutModal = false"
      @confirm="confirmLogout"
    />
  </div>
</template>
