<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import logoKliniku from '@/assets/logo-kliniku.png'

const router = useRouter()
const logoLoadError = ref(false)

// Data Dokter Aktif
const doctorInfo = ref({
  nama: 'dr. Sarah Amanda, Sp.PD',
  spesialis: 'Poli Penyakit Dalam',
  shift: 'Shift Pagi: 08:00 - 14:00 WIB'
})

// Data Antrean Pasien
const queueList = ref([
  {
    id: 'ant-002',
    noAntrean: 'A-002',
    nama: 'Hendro Wijaya',
    usia: '45 Tahun',
    gender: 'Laki-laki',
    nik: '3172081987030004',
    kategori: 'Pasien Lama',
    checkIn: '08:15 WIB',
    status: 'Sedang Dipanggil',
    riwayatMedis: 'Tersedia (AES-256)',
    keluhanAwal: 'Nyeri lambung berulang sejak 3 hari, sensasi terbakar, dan pusing berputar.',
    tandaVital: { tensi: '130/85', nadi: '82', suhu: '36.8', bb: '68' },
    diagnosaList: [
      { kode: 'K29.7', nama: 'Dispepsia Fungsional & GERD ringan' },
      { kode: 'K30', nama: 'Gastritis Akut Erosif' }
    ],
    tindakanList: [
      'Palpasi abdomen epigastrium (nyeri tekan +)',
      'Edukasi pola diet lambung & pembatasan kafein'
    ],
    resepList: [
      { nama: 'Omeprazole 20 mg', aturan: '2 x 1 kapsul (30 menit sebelum makan)' },
      { nama: 'Sucralfate Sirup 500mg/5ml', aturan: '3 x 1 sendok takar sebelum makan' }
    ],
    catatan: 'Hindari makanan pedas, asam, dan santan. Makan teratur dengan porsi kecil tapi sering.'
  },
  {
    id: 'ant-003',
    noAntrean: 'A-003',
    nama: 'Kayla Nadira',
    usia: '7 Tahun',
    gender: 'Perempuan',
    nik: '3174092215080002',
    kategori: 'Pasien Baru',
    checkIn: '08:24 WIB',
    status: 'Menunggu',
    riwayatMedis: 'Rekam Medis Perdana',
    keluhanAwal: 'Demam sejak semalam, batuk kering berdahak, nafsu makan menurun.',
    tandaVital: { tensi: '100/70', nadi: '92', suhu: '38.2', bb: '22' },
    diagnosaList: [
      { kode: 'J02.9', nama: 'Faringitis Akut & Influenza' },
      { kode: 'J20.9', nama: 'Bronkitis Akut Ringan' }
    ],
    tindakanList: [
      'Pemeriksaan fisik orofaring & auskultasi paru',
      'Edukasi kompres air hangat & hidrasi cairan'
    ],
    resepList: [
      { nama: 'Paracetamol Sirup 120mg/5ml', aturan: '3 x 1 sendok takar (bila demam)' },
      { nama: 'Ambroxol Sirup 15mg/5ml', aturan: '3 x 1/2 sendok takar sesudah makan' },
      { nama: 'Vitamin C Sirup', aturan: '1 x 1 sendok takar setelah sarapan' }
    ],
    catatan: 'Istirahat di rumah selama 2-3 hari, perbanyak minum air putih hangat.'
  },
  {
    id: 'ant-004',
    noAntrean: 'A-004',
    nama: 'Siti Rahmawati',
    usia: '38 Tahun',
    gender: 'Perempuan',
    nik: '3175084902810001',
    kategori: 'Pasien Lama',
    checkIn: '08:35 WIB',
    status: 'Menunggu',
    riwayatMedis: 'Tersedia (AES-256)',
    keluhanAwal: 'Kontrol rutin hipertensi, tengkuk terasa berat saat bangun pagi.',
    tandaVital: { tensi: '145/90', nadi: '78', suhu: '36.5', bb: '62' },
    diagnosaList: [
      { kode: 'I10', nama: 'Hipertensi Primer Stage 1' }
    ],
    tindakanList: [
      'Pemeriksaan tensimeter digital & manual',
      'Evaluasi obat rutin & diet rendah garam'
    ],
    resepList: [
      { nama: 'Amlodipine 5 mg', aturan: '1 x 1 tablet malam hari' }
    ],
    catatan: 'Kurangi konsumsi garam dan makanan olahan. Kontrol ulang 1 bulan kemudian.'
  },
  {
    id: 'ant-001',
    noAntrean: 'A-001',
    nama: 'Ahmad Fauzi',
    usia: '52 Tahun',
    gender: 'Laki-laki',
    nik: '3171050209740003',
    kategori: 'Pasien Lama',
    checkIn: '08:00 WIB',
    status: 'Selesai',
    riwayatMedis: 'Tersimpan & Terenkripsi',
    keluhanAwal: 'Nyeri sendi lutut kanan saat berdiri lama.',
    tandaVital: { tensi: '125/80', nadi: '76', suhu: '36.6', bb: '70' },
    diagnosaList: [
      { kode: 'M17.0', nama: 'Osteoarthritis Genu Dextra' }
    ],
    tindakanList: [
      'Pemeriksaan palpasi & range of motion sendi lutut'
    ],
    resepList: [
      { nama: 'Glukosamin 500 mg', aturan: '1 x 1 tablet sesudah makan' },
      { nama: 'Natrium Diklofenak 50 mg', aturan: '2 x 1 tablet bila nyeri' }
    ],
    catatan: 'Hindari aktivitas naik turun tangga berlebihan dan olahraga benturan tinggi.'
  }
])

// Muat data booking pasien dari sisi pasien (localStorage) jika ada
onMounted(() => {
  try {
    const savedBookings = JSON.parse(localStorage.getItem('kliniku_bookings') || '[]')
    if (savedBookings.length > 0) {
      // Ambil booking terakhir pasien
      const latestBooking = savedBookings[savedBookings.length - 1]
      if (latestBooking && latestBooking.keluhanAwal) {
        // Cek apakah nomor antrean sudah ada di daftar
        const existingIdx = queueList.value.findIndex(q => q.noAntrean === latestBooking.nomorAntrean)
        if (existingIdx >= 0) {
          queueList.value[existingIdx].keluhanAwal = latestBooking.keluhanAwal
          queueList.value[existingIdx].nama = latestBooking.pasienNama || queueList.value[existingIdx].nama
        } else {
          // Jadikan pasien yang baru booking sebagai pasien yang sedang dipanggil
          queueList.value.forEach(q => {
            if (q.status === 'Sedang Dipanggil') q.status = 'Menunggu'
          })
          queueList.value.unshift({
            id: `ant-${latestBooking.nomorAntrean || Date.now()}`,
            noAntrean: latestBooking.nomorAntrean || 'A-002',
            nama: latestBooking.pasienNama || 'Rahmawati Soebandono',
            usia: '34 Tahun',
            gender: 'Perempuan',
            nik: latestBooking.nik || '3172345678987',
            
            checkIn: 'Hari Ini',
            status: 'Sedang Dipanggil',
            riwayatMedis: 'Tersedia (AES-256)',
            keluhanAwal: latestBooking.keluhanAwal,
            tandaVital: { tensi: '120/80', nadi: '80', suhu: '36.7', bb: '56' },
            diagnosaList: [
              { kode: 'K29.7', nama: 'Dispepsia Fungsional & GERD ringan' }
            ],
            tindakanList: [
              'Palpasi abdomen epigastrium, edukasi pola diet lambung'
            ],
            resepList: [
              { nama: 'Omeprazole 20 mg', aturan: '2 x 1 kapsul (30 menit sebelum makan)' },
              { nama: 'Sucralfate Sirup 500mg/5ml', aturan: '3 x 1 sendok takar sebelum makan' }
            ],
            catatan: 'Hindari makanan pedas, asam, dan bersantan.'
          })
        }
      }
    }
  } catch (e) {
    console.error('Gagal sinkron data booking pasien ke dashboard dokter:', e)
  }
})

// Statistik Antrean
const stats = computed(() => {
  const menunggu = queueList.value.filter(q => q.status === 'Menunggu').length
  const diperiksa = queueList.value.filter(q => q.status === 'Sedang Dipanggil' || q.status === 'Sedang Diperiksa').length
  const selesai = queueList.value.filter(q => q.status === 'Selesai').length
  return {
    menunggu: menunggu || 5,
    diperiksa: diperiksa || 1,
    selesai: selesai + 7
  }
})

// Pasien Aktif yang Sedang Dipanggil
const activePatient = computed(() => {
  return queueList.value.find(q => q.status === 'Sedang Dipanggil' || q.status === 'Sedang Diperiksa') || queueList.value[0]
})

// State Modal Pemeriksaan Klinis Dokter
const isExamModalOpen = ref(false)
const currentExamPatient = ref(null)

// Form Pemeriksaan Pasien
const examForm = ref({
  tensi: '120/80',
  nadi: '80',
  suhu: '36.7',
  bb: '65',
  diagnosaList: [],
  tindakanList: [],
  resepList: [],
  catatan: ''
})

// Helper List Diagnosa
const addDiagnosa = () => {
  examForm.value.diagnosaList.push({ kode: 'K29.7', nama: '' })
}
const removeDiagnosa = (index) => {
  if (examForm.value.diagnosaList.length > 1) {
    examForm.value.diagnosaList.splice(index, 1)
  }
}

// Helper List Tindakan
const addTindakan = () => {
  examForm.value.tindakanList.push('')
}
const removeTindakan = (index) => {
  if (examForm.value.tindakanList.length > 1) {
    examForm.value.tindakanList.splice(index, 1)
  }
}

// Helper List Resep Obat
const addResep = () => {
  examForm.value.resepList.push({ nama: '', aturan: '' })
}
const removeResep = (index) => {
  if (examForm.value.resepList.length > 1) {
    examForm.value.resepList.splice(index, 1)
  }
}

// Buka Modal Pemeriksaan
const startExamination = (patient) => {
  currentExamPatient.value = patient

  examForm.value = {
    tensi: patient.tandaVital?.tensi || '120/80',
    nadi: patient.tandaVital?.nadi || '80',
    suhu: patient.tandaVital?.suhu || '36.7',
    bb: patient.tandaVital?.bb || '65',
    diagnosaList: patient.diagnosaList ? JSON.parse(JSON.stringify(patient.diagnosaList)) : [
      { kode: 'K29.7', nama: 'Dispepsia Fungsional & GERD ringan' }
    ],
    tindakanList: patient.tindakanList ? [...patient.tindakanList] : [
      'Palpasi abdomen epigastrium, edukasi pola diet lambung'
    ],
    resepList: patient.resepList ? JSON.parse(JSON.stringify(patient.resepList)) : [
      { nama: 'Omeprazole 20 mg', aturan: '2 x 1 kapsul (30 menit sebelum makan)' },
      { nama: 'Sucralfate Sirup 500mg/5ml', aturan: '3 x 1 sendok takar sebelum makan' }
    ],
    catatan: patient.catatan || 'Hindari makanan pedas, asam, kafein, dan santan. Kontrol ulang jika keluhan berlanjut.'
  }

  isExamModalOpen.value = true
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
  }, 4000)
}

// Simpan Hasil Pemeriksaan dan Selesaikan Konsultasi
const finishExamination = () => {
  if (!currentExamPatient.value) return

  const patientName = currentExamPatient.value.nama
  const patientTicket = currentExamPatient.value.noAntrean || currentExamPatient.value.noTiket || ''

  const target = queueList.value.find(q => q.id === currentExamPatient.value.id)
  if (target) {
    target.status = 'Selesai'
    target.tandaVital = {
      tensi: examForm.value.tensi,
      nadi: examForm.value.nadi,
      suhu: examForm.value.suhu,
      bb: examForm.value.bb
    }
    target.diagnosaList = [...examForm.value.diagnosaList]
    target.tindakanList = [...examForm.value.tindakanList]
    target.resepList = [...examForm.value.resepList]
    target.catatan = examForm.value.catatan

    // Sinkronkan ke rekam medis pasien di localStorage (kliniku_completed_visits)
    try {
      const savedVisits = JSON.parse(localStorage.getItem('kliniku_completed_visits') || '[]')
      const diagnosaSummary = examForm.value.diagnosaList.map(d => d.nama).filter(Boolean).join(' & ') || 'Pemeriksaan Rutin'

      const newRecord = {
        id: `vis-${Date.now()}`,
        tanggal: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ', ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB',
        status: 'Selesai',
        poli: doctorInfo.value.spesialis,
        dokter: doctorInfo.value.nama,
        diagnosa: diagnosaSummary,
        noRm: `RM-${Date.now().toString().slice(-8)}`,
        keluhan: target.keluhanAwal,
        tandaVital: {
          tekananDarah: `${examForm.value.tensi} mmHg`,
          nadi: `${examForm.value.nadi} x/menit`,
          suhu: `${examForm.value.suhu} °C`,
          beratBadan: `${examForm.value.bb} kg`
        },
        diagnosaDetail: examForm.value.diagnosaList.map(d => ({
          kode: d.kode || 'K29.7',
          nama: d.nama
        })),
        tindakan: examForm.value.tindakanList.filter(Boolean),
        resepObat: examForm.value.resepList.filter(r => r.nama).map(r => ({
          nama: r.nama,
          aturan: r.aturan || 'Sesuai petunjuk dokter'
        })),
        catatanDokter: examForm.value.catatan
      }

      savedVisits.unshift(newRecord)
      localStorage.setItem('kliniku_completed_visits', JSON.stringify(savedVisits))
      localStorage.setItem('kliniku_riwayat_visits', JSON.stringify(savedVisits))

      // Sinkronkan tiket & status ke localStorage agar sisi admin dan pasien ikut terupdate
      const ticketNum = target.noAntrean || target.noTiket || ''
      if (ticketNum) {
        const completedTickets = JSON.parse(localStorage.getItem('kliniku_completed_tickets') || '[]')
        if (!completedTickets.includes(ticketNum)) {
          completedTickets.push(ticketNum)
          localStorage.setItem('kliniku_completed_tickets', JSON.stringify(completedTickets))
        }
        localStorage.setItem(`kliniku_queue_status_${ticketNum}`, 'Selesai')
      }

      const now = new Date()
      const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
      localStorage.setItem('kliniku_active_queue_completed', 'true')
      if (ticketNum) localStorage.setItem('kliniku_last_completed_ticket', ticketNum)
      localStorage.setItem('kliniku_last_completed_patient', target.nama)
      localStorage.setItem('kliniku_last_completed_date', todayStr)

      // Broadcast storage event agar tab lain otomatis reaktif
      window.dispatchEvent(new Event('storage'))
    } catch (e) {
      console.error('Gagal sinkron rekam medis:', e)
    }

    // Panggil antrean berikutnya
    const nextWaiting = queueList.value.find(q => q.status === 'Menunggu')
    if (nextWaiting) {
      nextWaiting.status = 'Sedang Dipanggil'
      try {
        localStorage.setItem('kliniku_calling_queue', JSON.stringify({
          noTiket: nextWaiting.noAntrean || nextWaiting.noTiket || 'A-004',
          nama: nextWaiting.nama,
          poli: doctorInfo.value.spesialis || 'Poli Umum',
          loket: 'Ruang Dokter 01',
          lokasi: 'POLIKLINIK 1 • LT. 1',
          timestamp: Date.now()
        }))
        window.dispatchEvent(new Event('storage'))
      } catch (e) {}
    }
  }

  isExamModalOpen.value = false
  triggerToast(
    'Perubahan Berhasil Disimpan',
    `Pemeriksaan pasien ${patientName} (${patientTicket}) telah selesai. Data rekam medis, diagnosa, dan resep obat berhasil disimpan ke sistem.`,
    'Pemeriksaan Selesai',
    'success'
  )
}

// State Pop Up Konfirmasi Keluar Portal Dokter (Keluar atau Batal)
const showLogoutModal = ref(false)

const handleLogout = () => {
  showLogoutModal.value = true
}

const cancelLogout = () => {
  showLogoutModal.value = false
}

const confirmLogout = () => {
  showLogoutModal.value = false
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#FFF7FA] text-[#1F1A1D] flex flex-col font-sans">
    <!-- 1. Top Navbar Dokter (Tanpa Sidebar Kiri) -->
    <header class="w-full bg-white border-b border-[#F2CEDD] px-4 md:px-8 py-3 sticky top-0 z-40 flex items-center justify-between shadow-xs">
      <!-- Sisi Kiri: Logo Portal Dokter & Shift -->
      <div class="flex items-center gap-4">
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
            <span class="text-[10px] font-bold text-[#6D314F] tracking-wider uppercase leading-none mt-0.5">Dokter Poli Portal</span>
          </div>
        </div>

        <div class="hidden sm:block h-6 w-px bg-[#E5D7DF]"></div>

        <!-- Shift Info -->
        <div class="hidden sm:flex items-center gap-1.5 text-[12.5px] text-[#55464D] font-medium">
          <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
          </svg>
          <span>{{ doctorInfo.shift }}</span>
        </div>
      </div>

      <!-- Sisi Kanan: Identitas Dokter & Profil -->
      <div class="flex items-center gap-3 md:gap-4">
        <div class="flex flex-col text-right">
          <span class="text-[13.5px] font-extrabold text-[#1F1A1D] leading-tight">{{ doctorInfo.nama }}</span>
          <span class="text-[11px] font-semibold text-[#6D314F] leading-tight">{{ doctorInfo.spesialis }}</span>
        </div>

        <div class="w-9 h-9 rounded-full bg-[#6D314F] text-white flex items-center justify-center font-bold text-[13px] shadow-sm">
          <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        </div>

        <button
          type="button"
          @click="handleLogout"
          title="Keluar dari Portal"
          class="p-2 rounded-lg text-[#7E7379] hover:text-[#DC2626] hover:bg-rose-50 transition-colors cursor-pointer border-0 bg-transparent"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
        </button>
      </div>
    </header>

    <!-- 2. Konten Utama Dashboard Dokter (Lebar Penuh Tanpa Sidebar Kiri) -->
    <main class="w-full max-w-[1240px] mx-auto p-4 md:p-6 lg:p-8 flex flex-col gap-6 box-border">
      <!-- 2.1 Judul Antrean & Kartu Statistik -->
      <section class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-1.5 text-[11.5px] font-bold text-[#6D314F] uppercase tracking-wider mb-1">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
              <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            <span>Instalasi Rawat Jalan • Poli Penyakit Dalam</span>
          </div>

          <h1 class="text-[22px] md:text-[25px] font-extrabold text-[#1F1A1D] tracking-[-0.3px] m-0">
            Antrean Ruang Periksa Hari Ini
          </h1>
          <p class="text-[13px] text-[#55464D] mt-1 max-w-xl m-0 leading-normal">
            Kelola pemeriksaan klinis pasien, telaah riwayat medis terdahulu, dan terbitkan resep digital.
          </p>
        </div>

        <!-- 3 Kartu Statistik -->
        <div class="flex items-center gap-3">
          <div class="bg-white rounded-[14px] p-[10px_16px] border border-[#F4E2EB] shadow-[0_2px_8px_rgba(109,49,79,0.03)] flex flex-col min-w-[110px]">
            <span class="text-[11px] font-medium text-[#7E7379] leading-tight">Menunggu Giliran</span>
            <div class="flex items-baseline gap-2 mt-1">
              <span class="text-[22px] font-extrabold text-[#1F1A1D] leading-none">{{ stats.menunggu }}</span>
              <span class="bg-[#FDEBF3] text-[#6D314F] text-[10.5px] font-bold px-1.5 py-0.5 rounded">Siap</span>
            </div>
          </div>

          <div class="bg-white rounded-[14px] p-[10px_16px] border border-[#F4E2EB] shadow-[0_2px_8px_rgba(109,49,79,0.03)] flex flex-col min-w-[110px]">
            <span class="text-[11px] font-medium text-[#7E7379] leading-tight">Sedang Diperiksa</span>
            <div class="flex items-baseline gap-2 mt-1">
              <span class="text-[22px] font-extrabold text-[#1F1A1D] leading-none">{{ stats.diperiksa }}</span>
              <span class="bg-[#FCE4EC] text-[#9F1239] text-[10.5px] font-bold px-1.5 py-0.5 rounded">#{{ activePatient.noAntrean }}</span>
            </div>
          </div>

          <div class="bg-white rounded-[14px] p-[10px_16px] border border-[#F4E2EB] shadow-[0_2px_8px_rgba(109,49,79,0.03)] flex flex-col min-w-[110px]">
            <span class="text-[11px] font-medium text-[#7E7379] leading-tight">Selesai Konsultasi</span>
            <div class="flex items-baseline gap-2 mt-1">
              <span class="text-[22px] font-extrabold text-[#1F1A1D] leading-none">{{ stats.selesai }}</span>
              <span class="text-[11px] text-[#7E7379] font-medium">Pasien</span>
            </div>
          </div>
        </div>
      </section>

      <!-- 2.2 Banner Pasien Aktif yang Sedang Dipanggil -->
      <section class="bg-white rounded-[18px] border border-[#F4D2E3] p-5 shadow-[0_4px_20px_rgba(109,49,79,0.05)] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
          <!-- Badge No. Antrean Ungu Marun yang Diperbesar & Proporsional -->
          <div class="min-w-[70px] h-[66px] rounded-[16px] bg-[#6D314F] text-white flex flex-col items-center justify-center shrink-0 shadow-sm px-2.5 py-1.5 box-border">
            <span class="text-[10px] font-bold tracking-widest uppercase opacity-85 leading-none mb-1">NO.</span>
            <span class="text-[17px] font-black tracking-tight leading-none whitespace-nowrap">{{ activePatient.noAntrean }}</span>
          </div>

          <div class="flex flex-col">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span class="bg-[#FFE4EE] text-[#9F1239] text-[11px] font-extrabold px-2.5 py-0.5 rounded-full flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-[#E11D48] animate-ping"></span>
                <span>{{ activePatient.status }}</span>
              </span>

              <span class="text-[11.5px] text-[#7E7379] font-mono">
                NIK: {{ activePatient.nik }}
              </span>
            </div>

            <h2 class="text-[18px] font-extrabold text-[#1F1A1D] m-0 leading-tight">
              {{ activePatient.nama }} ({{ activePatient.usia }})
            </h2>

            <div class="flex items-start gap-1.5 text-[12.5px] text-[#6D314F] bg-[#FFF2F7] px-3 py-1.5 rounded-[8px] border border-[#FADCEB] mt-2 max-w-2xl">
              <span class="font-extrabold text-[#E11D48]">!</span>
              <span class="leading-snug">
                <strong>Keluhan awal pasien:</strong> {{ activePatient.keluhanAwal }}
              </span>
            </div>
          </div>
        </div>

        <!-- Tombol Periksa Pasien Ini -->
        <button
          type="button"
          @click="startExamination(activePatient)"
          class="bg-[#6D314F] hover:bg-[#55233C] text-white font-extrabold px-6 py-3.5 rounded-[12px] text-[14.5px] flex items-center justify-center gap-2.5 transition-all shadow-[0_2px_10px_rgba(109,49,79,0.2)] active:scale-[0.985] cursor-pointer shrink-0 border-0"
        >
          <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" />
            <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" />
            <circle cx="20" cy="10" r="2" />
          </svg>
          <span>Periksa Pasien Ini (Mulai Konsultasi)</span>
        </button>
      </section>

      <!-- 2.3 Tabel Daftar Antrean Konsultasi Sesi 1 -->
      <section class="bg-white rounded-[18px] border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] overflow-hidden flex flex-col">
        <div class="p-[18px_20px] border-b border-[#F4E2EB] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div class="flex items-center gap-2 text-[#1F1A1D]">
            <svg class="w-5 h-5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="8" y1="6" x2="21" y2="6" />
              <line x1="8" y1="12" x2="21" y2="12" />
              <line x1="8" y1="18" x2="21" y2="18" />
              <line x1="3" y1="6" x2="3.01" y2="6" />
              <line x1="3" y1="12" x2="3.01" y2="12" />
              <line x1="3" y1="18" x2="3.01" y2="18" />
            </svg>
            <h3 class="text-[16px] font-extrabold m-0">Daftar Antrean Konsultasi Sesi 1</h3>
          </div>

          <span class="text-[12px] text-[#7E7379] font-medium">
            Update otomatis setiap 30 detik
          </span>
        </div>

        <div class="w-full overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[760px]">
            <thead>
              <tr class="bg-[#FFF7FA] text-[#7E7379] text-[11.5px] font-bold uppercase tracking-wider border-b border-[#F4E2EB]">
                <th class="py-3 px-4">No. Antrean</th>
                <th class="py-3 px-4">Nama Pasien &amp; NIK</th>
                <th class="py-3 px-4">Kategori</th>
                <th class="py-3 px-4">Check-in</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Riwayat Medis</th>
                <th class="py-3 px-4 text-center">Aksi Tindakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F8E9F0] text-[13px]">
              <tr
                v-for="item in queueList"
                :key="item.id"
                :class="[
                  'transition-colors duration-150',
                  item.status === 'Selesai' ? 'opacity-60 bg-gray-50/50' : 'hover:bg-[#FFF9FB]'
                ]"
              >
                <td class="py-4 px-4 font-black text-[15px]" :class="item.status === 'Selesai' ? 'text-gray-400' : 'text-[#1F1A1D]'">
                  {{ item.noAntrean }}
                </td>

                <td class="py-4 px-4">
                  <div class="flex flex-col">
                    <span class="font-extrabold text-[14px]" :class="item.status === 'Selesai' ? 'text-gray-500' : 'text-[#1F1A1D]'">
                      {{ item.nama }}
                    </span>
                    <span class="text-[11.5px] text-[#7E7379] mt-0.5">
                      NIK: {{ item.nik }} • {{ item.usia }} ({{ item.gender }})
                    </span>
                  </div>
                </td>

                <td class="py-4 px-4">
                  <span
                    :class="[
                      'text-[11px] font-bold px-2 py-0.5 rounded-full',
                      item.kategori === 'Pasien Baru'
                        ? 'bg-[#FCE4EC] text-[#9F1239]'
                        : 'bg-gray-100 text-gray-700'
                    ]"
                  >
                    {{ item.kategori }}
                  </span>
                </td>

                <td class="py-4 px-4 font-medium text-[#55464D]">
                  {{ item.checkIn }}
                </td>

                <td class="py-4 px-4">
                  <span
                    v-if="item.status === 'Sedang Dipanggil'"
                    class="bg-[#FFE4EE] text-[#9F1239] text-[11px] font-extrabold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5"
                  >
                    <span class="w-1.5 h-1.5 rounded-full bg-[#E11D48]"></span>
                    <span>Sedang Dipanggil</span>
                  </span>

                  <span
                    v-else-if="item.status === 'Menunggu'"
                    class="text-[#55464D] text-[12px] font-medium"
                  >
                    Menunggu
                  </span>

                  <span
                    v-else-if="item.status === 'Selesai'"
                    class="bg-[#DCFCE7] text-[#16A34A] text-[11px] font-bold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1"
                  >
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>Selesai</span>
                  </span>
                </td>

                <td class="py-4 px-4">
                  <div v-if="item.riwayatMedis.includes('AES-256')" class="flex items-center gap-1.5 text-[12px] text-[#6D314F] font-medium">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                      <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                    <span>{{ item.riwayatMedis }}</span>
                  </div>
                  <span v-else class="text-[12px] text-[#7E7379] italic font-medium">
                    {{ item.riwayatMedis }}
                  </span>
                </td>

                <td class="py-4 px-4 text-center">
                  <button
                    v-if="item.status !== 'Selesai'"
                    type="button"
                    @click="startExamination(item)"
                    :class="[
                      'px-3.5 py-1.5 rounded-[8px] font-bold text-[12px] transition-all cursor-pointer border-0 inline-flex items-center gap-1.5',
                      item.status === 'Sedang Dipanggil'
                        ? 'bg-[#6D314F] text-white hover:bg-[#55233C] shadow-xs'
                        : 'bg-[#FCEBF3] text-[#6D314F] hover:bg-[#FADCEB]'
                    ]"
                  >
                    <span>Periksa</span>
                    <span>&rarr;</span>
                  </button>

                  <span v-else class="text-[12px] text-gray-400 font-medium">
                    Arsip Final
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <!-- 3. Modal Form Pemeriksaan Medis Dokter -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isExamModalOpen"
          class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-3 md:p-4 backdrop-blur-[2px]"
          @click.self="isExamModalOpen = false"
        >
          <div class="w-full max-w-[620px] bg-white rounded-[22px] shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
            <!-- Header Modal -->
            <div class="p-[18px_22px] border-b border-[#F4E2EB] flex items-center justify-between bg-[#FFF7FA]">
              <div class="flex items-center gap-3">
                <div class="min-w-[58px] h-[44px] px-2.5 rounded-[12px] bg-[#6D314F] text-white flex items-center justify-center font-black text-[15px] shadow-xs">
                  {{ currentExamPatient?.noAntrean }}
                </div>
                <div>
                  <h3 class="text-[16px] font-extrabold text-[#1F1A1D] m-0 leading-tight">
                    Konsultasi &amp; Pemeriksaan Pasien
                  </h3>
                  <p class="text-[12px] text-[#7E7379] m-0 mt-0.5">
                    {{ currentExamPatient?.nama }} • {{ currentExamPatient?.usia }} ({{ currentExamPatient?.gender }})
                  </p>
                </div>
              </div>

              <button
                type="button"
                @click="isExamModalOpen = false"
                class="w-8 h-8 rounded-full bg-white border border-[#E5D7DF] flex items-center justify-center text-[#7E7379] hover:text-[#1F1A1D] cursor-pointer"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>

            <!-- Form Pemeriksaan Scrollable -->
            <form @submit.prevent="finishExamination" class="p-[20px] overflow-y-auto flex flex-col gap-4 text-[13px]">
              <!-- Anamnesis / Keluhan Awal Perawat -->
              <div class="bg-[#FFF2F7] rounded-[12px] p-3 border border-[#FADCEB]">
                <span class="text-[11px] font-bold text-[#6D314F] uppercase tracking-wide block mb-0.5">Anamnesis / Keluhan Awal</span>
                <p class="text-[12.5px] text-[#1F1A1D] m-0 italic">"{{ currentExamPatient?.keluhanAwal }}"</p>
              </div>

              <!-- Tanda-tanda Vital -->
              <div class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <label class="block text-[11px] font-bold text-[#7E7379] uppercase tracking-wider mb-2">Tanda-tanda Vital</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[10.5px] text-[#7E7379] block">Tekanan Darah</span>
                    <input v-model="examForm.tensi" type="text" class="w-full mt-1 p-1.5 border border-[#E5D7DF] rounded-[6px] font-extrabold text-[13px] text-[#1F1A1D] bg-white outline-none focus:border-[#6D314F]" />
                  </div>
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[10.5px] text-[#7E7379] block">Detak Nadi</span>
                    <input v-model="examForm.nadi" type="text" class="w-full mt-1 p-1.5 border border-[#E5D7DF] rounded-[6px] font-extrabold text-[13px] text-[#1F1A1D] bg-white outline-none focus:border-[#6D314F]" />
                  </div>
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[10.5px] text-[#7E7379] block">Suhu Tubuh</span>
                    <input v-model="examForm.suhu" type="text" class="w-full mt-1 p-1.5 border border-[#E5D7DF] rounded-[6px] font-extrabold text-[13px] text-[#1F1A1D] bg-white outline-none focus:border-[#6D314F]" />
                  </div>
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[10.5px] text-[#7E7379] block">Berat Badan</span>
                    <input v-model="examForm.bb" type="text" class="w-full mt-1 p-1.5 border border-[#E5D7DF] rounded-[6px] font-extrabold text-[13px] text-[#1F1A1D] bg-white outline-none focus:border-[#6D314F]" />
                  </div>
                </div>
              </div>

              <!-- 1. DIAGNOSA MEDIS DALAM BENTUK LIST (Kode ICD + Nama Diagnosa) -->
              <div class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <div class="flex items-center justify-between mb-2">
                  <label class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider">
                    Diagnosa Medis (List ICD-10)
                  </label>
                  <button
                    type="button"
                    @click="addDiagnosa"
                    class="text-[11.5px] font-bold text-[#6D314F] hover:underline flex items-center gap-1 cursor-pointer bg-transparent border-0 p-0"
                  >
                    <span>+ Tambah Diagnosa</span>
                  </button>
                </div>

                <div class="flex flex-col gap-2">
                  <div
                    v-for="(diag, idx) in examForm.diagnosaList"
                    :key="idx"
                    class="flex items-center gap-2"
                  >
                    <!-- Input Kode ICD -->
                    <div class="relative w-[85px] shrink-0">
                      <input
                        v-model="diag.kode"
                        type="text"
                        placeholder="ICD"
                        class="w-full bg-[#6D314F] text-white font-mono font-bold text-[11px] px-2 py-2 rounded-[8px] border-0 text-center uppercase outline-none"
                      />
                    </div>

                    <!-- Input Nama Diagnosa -->
                    <input
                      v-model="diag.nama"
                      type="text"
                      required
                      placeholder="Nama diagnosa klinis (contoh: Dispepsia Fungsional)"
                      class="flex-1 p-2 border border-[#E5D7DF] rounded-[8px] font-medium text-[#1F1A1D] text-[12.5px] outline-none focus:border-[#6D314F]"
                    />

                    <!-- Tombol Hapus Row -->
                    <button
                      type="button"
                      @click="removeDiagnosa(idx)"
                      :disabled="examForm.diagnosaList.length <= 1"
                      class="w-7 h-7 rounded-[6px] text-gray-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center cursor-pointer border-0 bg-transparent disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- 2. TINDAKAN MEDIS DALAM BENTUK LIST (Bullet Points) -->
              <div class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <div class="flex items-center justify-between mb-2">
                  <label class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider">
                    Tindakan Medis (List Prosedur)
                  </label>
                  <button
                    type="button"
                    @click="addTindakan"
                    class="text-[11.5px] font-bold text-[#6D314F] hover:underline flex items-center gap-1 cursor-pointer bg-transparent border-0 p-0"
                  >
                    <span>+ Tambah Tindakan</span>
                  </button>
                </div>

                <div class="flex flex-col gap-2">
                  <div
                    v-for="(tindakan, idx) in examForm.tindakanList"
                    :key="idx"
                    class="flex items-center gap-2"
                  >
                    <span class="text-[#6D314F] text-[16px] leading-none select-none">•</span>

                    <input
                      v-model="examForm.tindakanList[idx]"
                      type="text"
                      required
                      placeholder="Contoh: Palpasi abdomen epigastrium, edukasi pola diet lambung"
                      class="flex-1 p-2 border border-[#E5D7DF] rounded-[8px] font-medium text-[#1F1A1D] text-[12.5px] outline-none focus:border-[#6D314F]"
                    />

                    <button
                      type="button"
                      @click="removeTindakan(idx)"
                      :disabled="examForm.tindakanList.length <= 1"
                      class="w-7 h-7 rounded-[6px] text-gray-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center cursor-pointer border-0 bg-transparent disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- 3. RESEP OBAT DIGITAL DALAM BENTUK LIST (Nama Obat & Dosis / Aturan Pakai) -->
              <div class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <div class="flex items-center justify-between mb-2">
                  <label class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider">
                    Resep Obat &amp; Terapi (List Obat)
                  </label>
                  <button
                    type="button"
                    @click="addResep"
                    class="text-[11.5px] font-bold text-[#6D314F] hover:underline flex items-center gap-1 cursor-pointer bg-transparent border-0 p-0"
                  >
                    <span>+ Tambah Resep Obat</span>
                  </button>
                </div>

                <div class="flex flex-col gap-2.5">
                  <div
                    v-for="(obat, idx) in examForm.resepList"
                    :key="idx"
                    class="bg-[#FFF7FA] p-2.5 rounded-[10px] border border-[#F8D6E6] flex items-start gap-2"
                  >
                    <div class="flex-1 flex flex-col gap-1.5">
                      <!-- Nama Obat -->
                      <input
                        v-model="obat.nama"
                        type="text"
                        required
                        placeholder="Nama Obat & Sediaan (contoh: Omeprazole 20 mg)"
                        class="w-full p-2 border border-[#E5D7DF] rounded-[6px] font-bold text-[12.5px] text-[#6D314F] bg-white outline-none focus:border-[#6D314F]"
                      />
                      <!-- Aturan Minum / Dosis -->
                      <input
                        v-model="obat.aturan"
                        type="text"
                        required
                        placeholder="Aturan Pakai (contoh: 2 x 1 kapsul 30 menit sebelum makan)"
                        class="w-full p-1.5 border border-[#E5D7DF] rounded-[6px] font-medium text-[11.5px] text-[#55464D] bg-white outline-none focus:border-[#6D314F]"
                      />
                    </div>

                    <!-- Tombol Hapus Obat -->
                    <button
                      type="button"
                      @click="removeResep(idx)"
                      :disabled="examForm.resepList.length <= 1"
                      class="w-7 h-7 rounded-[6px] text-gray-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center cursor-pointer border-0 bg-transparent mt-1 disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- 4. CATATAN & EDUKASI DOKTER -->
              <div class="bg-[#FFF7FA] border border-[#F8D6E6] rounded-[14px] p-3.5">
                <label class="block text-[11px] font-bold text-[#6D314F] uppercase tracking-wider mb-1.5">
                  Catatan &amp; Edukasi Dokter
                </label>
                <textarea
                  v-model="examForm.catatan"
                  rows="2"
                  placeholder="Catatan edukasi untuk pasien..."
                  class="w-full p-2.5 border border-[#E5D7DF] rounded-[8px] font-medium text-[#1F1A1D] text-[12.5px] bg-white focus:border-[#6D314F] outline-none resize-none italic"
                ></textarea>
              </div>

              <!-- Footer Modal Action -->
              <div class="flex gap-2 pt-2 border-t border-[#F4E2EB]">
                <button
                  type="submit"
                  class="flex-1 bg-[#6D314F] text-white py-3 rounded-[10px] font-bold text-[13.5px] hover:bg-[#55233C] cursor-pointer border-0 flex items-center justify-center gap-2"
                >
                  <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  <span>Selesaikan &amp; Terbitkan Resep</span>
                </button>
                <button
                  type="button"
                  @click="isExamModalOpen = false"
                  class="bg-[#F8D6E6] text-[#6D314F] px-5 py-3 rounded-[10px] font-bold text-[13.5px] hover:bg-[#F2CEDD] cursor-pointer border-0"
                >
                  Batal
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Pop Up Toast Notifikasi Selesai Periksa & Perubahan Berhasil Disimpan -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-6 opacity-0 scale-95"
        enter-to-class="translate-y-0 opacity-100 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100 scale-100"
        leave-to-class="-translate-y-6 opacity-0 scale-95"
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

            <!-- Content -->
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
              class="w-6 h-6 rounded-full hover:bg-black/5 flex items-center justify-center text-gray-400 hover:text-gray-600 ml-auto shrink-0 transition-colors cursor-pointer border-0"
            >
              ✕
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Pop Up Konfirmasi Keluar dari Portal Dokter (Keluar atau Batal) -->
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
          v-if="showLogoutModal"
          class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-[2px] flex items-center justify-center p-4"
          @click.self="cancelLogout"
        >
          <div class="w-full max-w-[400px] bg-white rounded-[22px] border border-[#F2CEDD] p-6 shadow-[0_20px_50px_rgba(109,49,79,0.2)] text-center flex flex-col items-center box-border animate-in fade-in zoom-in-95 duration-200">
            <!-- Icon Keluar Rose / Merah -->
            <div class="w-14 h-14 rounded-full bg-rose-50 text-[#DC2626] border-2 border-rose-100 flex items-center justify-center mb-4 shadow-xs">
              <svg class="w-7 h-7 text-[#DC2626]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
              </svg>
            </div>

            <h3 class="text-[18px] font-extrabold text-[#1F1A1D] m-0">Keluar dari Portal Dokter?</h3>
            <p class="text-[13px] text-[#55464D] mt-2 mb-4 leading-relaxed">
              Apakah Anda yakin ingin keluar dari sesi kerja Portal Dokter Poliklinik? Anda harus masuk kembali untuk melanjutkan pelayanan konsultasi pasien.
            </p>

            <!-- Info Identitas Dokter Card -->
            <div class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[14px] p-3.5 mb-5 text-left flex items-center justify-between">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-full bg-[#6D314F] text-white flex items-center justify-center font-bold text-sm shrink-0">
                  {{ doctorInfo.nama.replace('dr. ', '').charAt(0) }}
                </div>
                <div class="min-w-0">
                  <span class="text-[13px] font-black text-[#1F1A1D] block leading-tight truncate">{{ doctorInfo.nama }}</span>
                  <span class="text-[11px] font-semibold text-[#8C4B6A] block mt-0.5">{{ doctorInfo.spesialis }}</span>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[10.5px] font-black bg-rose-100 text-[#DC2626] shrink-0">
                Dokter
              </span>
            </div>

            <!-- Tombol Pilihan: Batal / Keluar -->
            <div class="flex items-center gap-3 w-full">
              <button
                type="button"
                @click="cancelLogout"
                class="flex-1 py-2.5 px-4 rounded-[12px] border border-[#E5BED1] bg-white text-[#55464D] font-bold text-[13px] hover:bg-[#FFF7FA] cursor-pointer transition-colors"
              >
                Batal
              </button>
              <button
                type="button"
                @click="confirmLogout"
                class="flex-1 py-2.5 px-4 rounded-[12px] border border-transparent bg-[#DC2626] text-white font-bold text-[13px] hover:bg-[#B91C1C] cursor-pointer transition-colors shadow-sm"
              >
                Keluar
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
