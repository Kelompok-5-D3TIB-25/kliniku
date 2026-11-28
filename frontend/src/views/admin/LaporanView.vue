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
  role: 'Loket Pelayanan & Kasir',
  statusLoket: 'Online',
  tanggalHariIni: 'Kamis, 24 Oktober 2024'
})

// Filter Form State
const filterTanggal = ref('2024-10-24')
const filterPoli = ref('Semua')
const filterStatus = ref('Semua')
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 10

// Master Data Kunjungan Pasien (48 Record)
const allVisits = ref([
  {
    id: 'vis-001',
    no: 1,
    waktu: '08:15',
    noAntrean: 'A-001',
    nama: 'Siti Rahmawati',
    nik: '3175084920190001',
    usia: '38 Thn (P)',
    poli: 'Poli Penyakit Dalam',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-002',
    no: 2,
    waktu: '08:30',
    noAntrean: 'A-002',
    nama: 'Hendro Wijaya',
    nik: '3172081987030004',
    usia: '45 Thn (L)',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    pendaftaran: 'Walk-in Loket',
    status: 'Selesai'
  },
  {
    id: 'vis-003',
    no: 3,
    waktu: '09:00',
    noAntrean: 'B-004',
    nama: 'Kayla Nadira',
    nik: '3174092215080002',
    usia: '7 Thn (P)',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-004',
    no: 4,
    waktu: '09:15',
    noAntrean: 'A-005',
    nama: 'Bambang Sutrisno',
    nik: '3174021204620003',
    usia: '62 Thn (L)',
    poli: 'Poli Jantung & Pembuluh',
    dokter: 'dr. Faisal Ridwan, Sp.JP',
    pendaftaran: 'Walk-in Loket',
    status: 'Sedang Menunggu'
  },
  {
    id: 'vis-005',
    no: 5,
    waktu: '09:40',
    noAntrean: 'C-008',
    nama: 'Kartini Siregar',
    nik: '3275034801750005',
    usia: '49 Thn (P)',
    poli: 'Poli Gigi & Mulut',
    dokter: 'drg. Rina Permata, Sp.Ort',
    pendaftaran: 'Online Booking',
    status: 'Batal / Dilewati'
  },
  {
    id: 'vis-006',
    no: 6,
    waktu: '10:05',
    noAntrean: 'A-011',
    nama: 'Dimas Anggara',
    nik: '3175021992040008',
    usia: '32 Thn (L)',
    poli: 'Poli Mata',
    dokter: 'dr. Maya Lestari, Sp.M',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-007',
    no: 7,
    waktu: '10:20',
    noAntrean: 'B-012',
    nama: 'Nurhasanah Lubis',
    nik: '1271045903820002',
    usia: '42 Thn (P)',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    pendaftaran: 'Walk-in Loket',
    status: 'Selesai'
  },
  {
    id: 'vis-008',
    no: 8,
    waktu: '10:45',
    noAntrean: 'A-015',
    nama: 'Wahyu Kusuma',
    nik: '3173081109950007',
    usia: '29 Thn (L)',
    poli: 'Poli Penyakit Dalam',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-009',
    no: 9,
    waktu: '11:10',
    noAntrean: 'A-018',
    nama: 'Bagas Saputra',
    nik: '3171091512190001',
    usia: '5 Thn (L)',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    pendaftaran: 'Walk-in Loket',
    status: 'Selesai'
  },
  {
    id: 'vis-010',
    no: 10,
    waktu: '11:30',
    noAntrean: 'A-019',
    nama: 'Dewita Sari',
    nik: '3175047802890006',
    usia: '35 Thn (P)',
    poli: 'Poli Gigi & Mulut',
    dokter: 'drg. Rina Permata, Sp.Ort',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  // Data Halaman 2 - 5 untuk menggenapi 48 total kunjungan
  {
    id: 'vis-011',
    no: 11,
    waktu: '11:45',
    noAntrean: 'A-020',
    nama: 'Budi Santoso',
    nik: '3171012304770002',
    usia: '47 Thn (L)',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    pendaftaran: 'Walk-in Loket',
    status: 'Selesai'
  },
  {
    id: 'vis-012',
    no: 12,
    waktu: '12:00',
    noAntrean: 'A-021',
    nama: 'Marlina Siregar',
    nik: '3174095408780002',
    usia: '46 Thn (P)',
    poli: 'Poli Jantung & Pembuluh',
    dokter: 'dr. Faisal Ridwan, Sp.JP',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-013',
    no: 13,
    waktu: '12:15',
    noAntrean: 'B-022',
    nama: 'Rizky Pratama',
    nik: '3175021104190006',
    usia: '5 Thn (L)',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-014',
    no: 14,
    waktu: '12:30',
    noAntrean: 'A-023',
    nama: 'Clarissa Putri',
    nik: '3175085409890002',
    usia: '25 Thn (P)',
    poli: 'Poli Umum',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-015',
    no: 15,
    waktu: '12:45',
    noAntrean: 'C-024',
    nama: 'Joko Supriyanto',
    nik: '3175031907720003',
    usia: '52 Thn (L)',
    poli: 'Poli Gigi & Mulut',
    dokter: 'drg. Rina Permata, Sp.Ort',
    pendaftaran: 'Walk-in Loket',
    status: 'Batal / Dilewati'
  },
  {
    id: 'vis-016',
    no: 16,
    waktu: '13:00',
    noAntrean: 'A-025',
    nama: 'Dewi Sartika',
    nik: '3173024509890007',
    usia: '35 Thn (P)',
    poli: 'Poli Penyakit Dalam',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-017',
    no: 17,
    waktu: '13:15',
    noAntrean: 'A-026',
    nama: 'Gunawan Wibisono',
    nik: '3171051203650001',
    usia: '59 Thn (L)',
    poli: 'Poli Mata',
    dokter: 'dr. Maya Lestari, Sp.M',
    pendaftaran: 'Walk-in Loket',
    status: 'Selesai'
  },
  {
    id: 'vis-018',
    no: 18,
    waktu: '13:30',
    noAntrean: 'B-027',
    nama: 'Cika Ramadhani',
    nik: '3175025501170002',
    usia: '7 Thn (P)',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  },
  {
    id: 'vis-019',
    no: 19,
    waktu: '13:45',
    noAntrean: 'A-028',
    nama: 'Putri Melati',
    nik: '3172046811980005',
    usia: '26 Thn (P)',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    pendaftaran: 'Walk-in Loket',
    status: 'Selesai'
  },
  {
    id: 'vis-020',
    no: 20,
    waktu: '14:00',
    noAntrean: 'A-029',
    nama: 'Ahmad Fauzi',
    nik: '3171092301850004',
    usia: '39 Thn (L)',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    pendaftaran: 'Online Booking',
    status: 'Selesai'
  }
])

// Tambahkan sisa data hingga 48
for (let i = 21; i <= 48; i++) {
  allVisits.value.push({
    id: `vis-0${i}`,
    no: i,
    waktu: `14:${String((i * 3) % 60).padStart(2, '0')}`,
    noAntrean: `A-0${i}`,
    nama: `Pasien Rekam Medis ${i}`,
    nik: `317508${Math.floor(1000000000 + i * 111111)}`,
    usia: `${20 + (i % 45)} Thn (${i % 2 === 0 ? 'L' : 'P'})`,
    poli: i % 4 === 0 ? 'Poli Gigi & Mulut' : (i % 3 === 0 ? 'Poli Spesialis Anak' : 'Poli Umum'),
    dokter: i % 2 === 0 ? 'dr. Hendra Wijaya' : 'dr. Sarah Amanda, Sp.PD',
    pendaftaran: i % 2 === 0 ? 'Online Booking' : 'Walk-in Loket',
    status: i === 23 || i === 35 ? 'Batal / Dilewati' : (i === 47 ? 'Sedang Menunggu' : 'Selesai')
  })
}

// Filtered List
const filteredVisits = computed(() => {
  return allVisits.value.filter(item => {
    // Search query
    const matchSearch =
      item.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.nik.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.noAntrean.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.poli.toLowerCase().includes(searchQuery.value.toLowerCase())

    // Filter Poli
    const matchPoli = filterPoli.value === 'Semua' || item.poli.includes(filterPoli.value)

    // Filter Status
    const matchStatus = filterStatus.value === 'Semua' || item.status === filterStatus.value

    return matchSearch && matchPoli && matchStatus
  })
})

// Metrics
const totalKunjungan = computed(() => filteredVisits.value.length)
const totalSelesai = computed(() => filteredVisits.value.filter(v => v.status === 'Selesai').length)
const totalBatal = computed(() => filteredVisits.value.filter(v => v.status === 'Batal / Dilewati').length)

// Pagination
const totalPages = computed(() => Math.ceil(filteredVisits.value.length / itemsPerPage) || 1)
const paginatedVisits = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredVisits.value.slice(start, start + itemsPerPage)
})

// Aksi Tampilkan Filter
const applyFilter = () => {
  currentPage.value = 1
  alert(`Memuat laporan kunjungan tanggal ${filterTanggal.value} untuk ${filterPoli.value}... Data berhasil diperbarui!`)
}

// Reset Filter
const resetFilter = () => {
  filterTanggal.value = '2024-10-24'
  filterPoli.value = 'Semua'
  filterStatus.value = 'Semua'
  searchQuery.value = ''
  currentPage.value = 1
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

// Aksi Unduh PDF Resmi
const unduhPdf = () => {
  triggerToast(
    'Laporan PDF Berhasil Diunduh!',
    'Berhasil mengunduh laporan kunjungan dalam bentuk format PDF resmi cetak.',
    'Unduh PDF'
  )
  setTimeout(() => {
    window.print()
  }, 400)
}

// Aksi Unduh Excel (.xlsx) / CSV
const unduhExcel = () => {
  const headers = ['No', 'Waktu', 'No Antrean', 'Nama Pasien', 'NIK', 'Usia', 'Poli', 'Dokter', 'Pendaftaran', 'Status']
  const rows = filteredVisits.value.map(v => [
    v.no,
    v.waktu,
    v.noAntrean,
    `"${v.nama}"`,
    `'${v.nik}'`,
    `"${v.usia}"`,
    `"${v.poli}"`,
    `"${v.dokter}"`,
    `"${v.pendaftaran}"`,
    `"${v.status}"`
  ])

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `Laporan_Kunjungan_Kliniku_${filterTanggal.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  triggerToast(
    'Laporan Excel Berhasil Diunduh!',
    'Berhasil mengunduh laporan kunjungan dalam bentuk format file Excel (.csv).',
    'Unduh Excel'
  )
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
    <aside class="w-[240px] shrink-0 bg-[#FFF7FA] border-r border-[#F2CEDD] min-h-screen p-5 flex flex-col justify-between box-border print:hidden">
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

        <!-- Status Loket (Aktif) -->
        <div class="flex items-center justify-between px-1 text-[12px] text-[#55464D]">
          <span>Status Loket</span>
          <span class="inline-flex items-center gap-1.5 font-bold text-[#1F1A1D]">
            <span class="w-2 h-2 rounded-full bg-[#6D314F]"></span>
            <span>{{ adminUser.statusLoket }}</span>
          </span>
        </div>

        <!-- Menu Navigasi Utama -->
        <nav class="flex flex-col gap-1.5">
          <!-- Beranda / Dashboard Loket -->
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

          <!-- Jadwal & Kuota Dokter -->
          <RouterLink
            to="/admin/jadwal"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all no-underline text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <span>Jadwal &amp; Kuota Dokter</span>
          </RouterLink>

          <!-- Laporan Kunjungan (Aktif) -->
          <button
            type="button"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-bold text-left transition-all cursor-default border-0 bg-[#6D314F] text-white shadow-sm"
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
    <!-- 2. KONTEN UTAMA LAPORAN KUNJUNGAN              -->
    <!-- ============================================== -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- 2.1 Top Bar Header: Tanggal & Profil Admin -->
      <header class="w-full bg-transparent px-6 lg:px-8 py-4 flex items-center justify-between box-border print:hidden">
        <!-- Tanggal Hari Ini -->
        <div class="flex items-center gap-2 text-[12.5px] font-medium text-[#55464D]">
        </div>

        <!-- Profil Admin Pill -->
        <div class="flex items-center gap-2.5 bg-white border border-[#F2CEDD] rounded-full px-3 py-1.5 shadow-2xs shrink-0">
          <div class="w-7 h-7 rounded-full bg-[#6D314F] text-white flex items-center justify-center font-bold text-[11px]">
            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </div>
          <span class="text-[13px] font-extrabold text-[#1F1A1D] pr-1">{{ adminUser.nama }}</span>
        </div>
      </header>

      <!-- 2.2 Konten Utama Halaman -->
      <main class="flex-1 px-6 lg:px-8 pb-10 flex flex-col gap-6 box-border">
        <!-- Judul Halaman -->
        <section>
          <div>
            <h1 class="text-[22px] md:text-[24px] font-black text-[#1F1A1D] m-0 tracking-tight">
              Rekap &amp; Unduh Laporan Kunjungan Pasien
            </h1>
          </div>
        </section>

        <!-- Form Filter Kunjungan -->
        <section class="bg-[#FFF7FA] p-4 rounded-[18px] border border-[#F4E2EB] shadow-xs print:hidden">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <!-- 1. Periode Tanggal -->
            <div class="flex flex-col gap-1">
              <label class="text-[11.5px] font-bold text-[#55464D]">Periode Tanggal (Harian / Rentang)</label>
              <div class="relative">
                <input
                  type="date"
                  v-model="filterTanggal"
                  class="w-full bg-white border border-[#F2CEDD] rounded-[12px] px-3 py-2 text-[12.5px] text-[#1F1A1D] outline-none shadow-2xs"
                />
              </div>
            </div>

            <!-- 2. Poliklinik Tujuan -->
            <div class="flex flex-col gap-1">
              <label class="text-[11.5px] font-bold text-[#55464D]">Poliklinik Tujuan</label>
              <div class="relative flex items-center">
                <select
                  v-model="filterPoli"
                  class="w-full appearance-none bg-white border border-[#F2CEDD] rounded-[12px] pl-3 pr-9 py-2 text-[12.5px] text-[#1F1A1D] outline-none shadow-2xs cursor-pointer"
                >
                  <option value="Semua">Semua Poli (Semua Departemen)</option>
                  <option value="Penyakit Dalam">Poli Penyakit Dalam</option>
                  <option value="Umum">Poli Umum</option>
                  <option value="Anak">Poli Spesialis Anak</option>
                  <option value="Gigi">Poli Gigi &amp; Mulut</option>
                  <option value="Jantung">Poli Jantung &amp; Pembuluh</option>
                  <option value="Mata">Poli Mata</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- 3. Status Kunjungan Pasien -->
            <div class="flex flex-col gap-1">
              <label class="text-[11.5px] font-bold text-[#55464D]">Status Kunjungan Pasien</label>
              <div class="relative flex items-center">
                <select
                  v-model="filterStatus"
                  class="w-full appearance-none bg-white border border-[#F2CEDD] rounded-[12px] pl-3 pr-9 py-2 text-[12.5px] text-[#1F1A1D] outline-none shadow-2xs cursor-pointer"
                >
                  <option value="Semua">Semua Status Pelayanan</option>
                  <option value="Selesai">Selesai</option>
                  <option value="Sedang Menunggu">Sedang Menunggu</option>
                  <option value="Batal / Dilewati">Batal / Dilewati</option>
                </select>
                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- 4. Tombol Tampilkan & Reset -->
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="applyFilter"
                class="flex-1 bg-[#6D314F] hover:bg-[#55233C] text-white py-2 px-4 rounded-[12px] text-[12.5px] font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer border-0 shadow-xs"
              >
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Tampilkan</span>
              </button>

              <button
                type="button"
                @click="resetFilter"
                title="Reset Filter"
                class="w-9 h-9 rounded-[12px] bg-white border border-[#F2CEDD] flex items-center justify-center text-[#7E7379] hover:bg-rose-50 hover:text-[#6D314F] transition-colors cursor-pointer shadow-2xs"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 4v6h6M23 20v-6h-6" />
                  <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" />
                </svg>
              </button>
            </div>
          </div>
        </section>

        <!-- 3 Kartu Metrik Ringkasan -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <!-- Card 1: TOTAL KUNJUNGAN -->
          <div class="bg-white rounded-[16px] p-4 border border-[#F4E2EB] shadow-[0_2px_10px_rgba(109,49,79,0.03)] flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider">TOTAL KUNJUNGAN</span>
              <div class="flex items-baseline gap-1 mt-1">
                <span class="text-[28px] font-black text-[#1F1A1D] leading-none">{{ totalKunjungan }}</span>
                <span class="text-[12.5px] font-semibold text-[#55464D]">Pasien</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#FFF0F5] text-[#6D314F] flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>
          </div>

          <!-- Card 2: SELESAI DIPERIKSA -->
          <div class="bg-white rounded-[16px] p-4 border border-[#F4E2EB] shadow-[0_2px_10px_rgba(109,49,79,0.03)] flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider">SELESAI DIPERIKSA</span>
              <div class="flex items-baseline gap-1 mt-1">
                <span class="text-[28px] font-black text-[#1F1A1D] leading-none">{{ totalSelesai }}</span>
                <span class="text-[12.5px] font-semibold text-[#55464D]">Pasien</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#ECFDF5] text-[#059669] flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
          </div>

          <!-- Card 3: BATAL / DILEWATI -->
          <div class="bg-white rounded-[16px] p-4 border border-[#F4E2EB] shadow-[0_2px_10px_rgba(109,49,79,0.03)] flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider">BATAL / DILEWATI</span>
              <div class="flex items-baseline gap-1 mt-1">
                <span class="text-[28px] font-black text-[#1F1A1D] leading-none">{{ totalBatal }}</span>
                <span class="text-[12.5px] font-semibold text-[#55464D]">Pasien</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#FFF0F5] text-[#BE123C] flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </div>
          </div>
        </section>

        <!-- Tabel Pratinjau Data Kunjungan Pasien -->
        <section class="bg-white rounded-[18px] border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] overflow-hidden flex flex-col">
          <!-- Baris Kontrol Atas Tabel: Judul, Search, Tombol Cetak/Unduh -->
          <div class="p-5 border-b border-[#F4E2EB] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
              <h2 class="text-[16px] font-extrabold text-[#1F1A1D] m-0">Pratinjau Data Kunjungan Pasien</h2>
              <span class="bg-[#FFF0F5] border border-[#FCE4EC] text-[#6D314F] text-[11px] font-bold px-2.5 py-0.5 rounded-full">
                {{ totalKunjungan }} Data Terfilter
              </span>
            </div>

            <!-- Search Bar Tengah -->
            <div class="relative w-full lg:max-w-xs print:hidden">
              <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[#7E7379]">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <circle cx="11" cy="11" r="8" />
                  <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
              </span>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari nama pasien, NIK, atau no antrean..."
                class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[12px] pl-9 pr-3 py-1.5 text-[12px] text-[#1F1A1D] outline-none shadow-2xs focus:border-[#6D314F]"
              />
            </div>

            <!-- Tombol Aksi Ekspor (PDF, Excel) -->
            <div class="flex items-center gap-2 flex-wrap print:hidden">
              <!-- Tombol Unduh PDF (Resmi) -->
              <button
                type="button"
                @click="unduhPdf"
                class="bg-white hover:bg-[#FFF7FA] border border-[#F2CEDD] text-[#55464D] px-3.5 py-1.5 rounded-[10px] text-[12px] font-bold flex items-center gap-1.5 cursor-pointer shadow-2xs transition-colors"
              >
                <svg class="w-3.5 h-3.5 text-[#BE123C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="16" y1="13" x2="8" y2="13" />
                  <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                <span>Unduh PDF (Resmi)</span>
              </button>

              <!-- Tombol Unduh Excel (.xlsx) -->
              <button
                type="button"
                @click="unduhExcel"
                class="bg-[#6D314F] hover:bg-[#55233C] text-white px-3.5 py-1.5 rounded-[10px] text-[12px] font-bold flex items-center gap-1.5 cursor-pointer shadow-xs border-0 transition-colors"
              >
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                  <polyline points="14 2 14 8 20 8" />
                  <line x1="8" y1="13" x2="16" y2="13" />
                  <line x1="8" y1="17" x2="16" y2="17" />
                  <polyline points="10 9 9 9 8 9" />
                </svg>
                <span>Unduh Excel (.xlsx)</span>
              </button>
            </div>
          </div>

          <!-- Tabel Data Kunjungan -->
          <div class="w-full overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[950px]">
              <thead class="bg-[#FFF7FA] border-b border-[#F4E2EB]">
                <tr class="text-[#7E7379] text-[11px] font-bold uppercase tracking-wider">
                  <th class="py-3 px-4 w-12 text-center">No.</th>
                  <th class="py-3 px-4">Waktu</th>
                  <th class="py-3 px-4">No. Antrean</th>
                  <th class="py-3 px-4">Nama Pasien &amp; Identitas</th>
                  <th class="py-3 px-4">Poliklinik &amp; Dokter</th>
                  <th class="py-3 px-4">Pendaftaran</th>
                  <th class="py-3 px-4 text-center">Status Layanan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F8E9F0] text-[13px]">
                <tr
                  v-for="row in paginatedVisits"
                  :key="row.id"
                  class="hover:bg-[#FFF9FB] transition-colors"
                >
                  <!-- 1. No. -->
                  <td class="py-3.5 px-4 text-center text-[#7E7379] font-medium">
                    {{ row.no }}
                  </td>

                  <!-- 2. Waktu -->
                  <td class="py-3.5 px-4 font-bold text-[#1F1A1D] whitespace-nowrap">
                    {{ row.waktu }}
                  </td>

                  <!-- 3. No. Antrean -->
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <span class="inline-block bg-[#FDEBF3] text-[#6D314F] font-extrabold text-[12.5px] px-2.5 py-0.5 rounded-[6px]">
                      {{ row.noAntrean }}
                    </span>
                  </td>

                  <!-- 4. Nama Pasien & Identitas -->
                  <td class="py-3.5 px-4">
                    <div class="flex flex-col">
                      <span class="font-extrabold text-[13.5px] text-[#1F1A1D]">
                        {{ row.nama }}
                      </span>
                      <span class="text-[11px] text-[#7E7379] font-mono mt-0.5">
                        NIK: {{ row.nik }} • {{ row.usia }}
                      </span>
                    </div>
                  </td>

                  <!-- 5. Poliklinik & Dokter -->
                  <td class="py-3.5 px-4">
                    <div class="flex flex-col">
                      <span class="font-bold text-[13px] text-[#1F1A1D]">
                        {{ row.poli }}
                      </span>
                      <span class="text-[11px] text-[#7E7379] mt-0.5">
                        {{ row.dokter }}
                      </span>
                    </div>
                  </td>

                  <!-- 6. Pendaftaran (Online Booking / Walk-in Loket) -->
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <!-- Online Booking -->
                    <span
                      v-if="row.pendaftaran === 'Online Booking'"
                      class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#6D314F]"
                    >
                      <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                        <line x1="12" y1="18" x2="12.01" y2="18" />
                      </svg>
                      <span>Online Booking</span>
                    </span>

                    <!-- Walk-in Loket -->
                    <span
                      v-else
                      class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#55464D]"
                    >
                      <svg class="w-3.5 h-3.5 text-[#7E7379]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                      </svg>
                      <span>Walk-in Loket</span>
                    </span>
                  </td>

                  <!-- 7. Status Layanan -->
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <!-- Status Selesai -->
                    <span
                      v-if="row.status === 'Selesai'"
                      class="bg-[#ECFDF5] text-[#059669] text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1"
                    >
                      <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                      </svg>
                      <span>Selesai</span>
                    </span>

                    <!-- Status Sedang Menunggu -->
                    <span
                      v-else-if="row.status === 'Sedang Menunggu'"
                      class="bg-[#FFFBEB] text-[#D97706] text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1"
                    >
                      <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                      </svg>
                      <span>Sedang Menunggu</span>
                    </span>

                    <!-- Status Batal / Dilewati -->
                    <span
                      v-else
                      class="bg-[#FFF0F5] text-[#BE123C] text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1"
                    >
                      <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                      </svg>
                      <span>Batal / Dilewati</span>
                    </span>
                  </td>
                </tr>

                <!-- Baris Kosong (Simulasi Kosong) -->
                <tr v-if="paginatedVisits.length === 0">
                  <td colspan="7" class="text-center py-12">
                    <div class="flex flex-col items-center justify-center gap-2">
                      <div class="w-12 h-12 rounded-full bg-[#FFF0F5] text-[#6D314F] flex items-center justify-center">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                          <line x1="9" y1="9" x2="15" y2="15" />
                          <line x1="15" y1="9" x2="9" y2="15" />
                        </svg>
                      </div>
                      <h4 class="text-[14px] font-bold text-[#1F1A1D] m-0">Tidak Ada Data Kunjungan Pasien</h4>
                      <p class="text-[12px] text-[#7E7379] m-0">
                        Tidak ditemukan antrean pasien untuk kriteria filter atau tanggal yang dipilih.
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Tabel -->
          <div class="p-4 border-t border-[#F4E2EB] flex flex-col sm:flex-row items-center justify-between gap-3 text-[12px] text-[#7E7379] print:hidden">
            <span>
              Menampilkan {{ filteredVisits.length > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0 }} – {{ Math.min(currentPage * itemsPerPage, filteredVisits.length) }} dari {{ filteredVisits.length }} total kunjungan
            </span>

            <!-- Tombol Navigasi Halaman -->
            <div class="flex items-center gap-1.5" v-if="totalPages > 1">
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

<style>
@media print {
  body {
    background: white !important;
  }
}
</style>
