<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminLogoutModal from '@/components/AdminLogoutModal.vue'
import logoKliniku from '@/assets/logo-kliniku.png'

const router = useRouter()
const showLogoutModal = ref(false)
const logoLoadError = ref(false)

// Data Admin Aktif
const adminUser = ref({
  nama: 'Siti Rahmawati',
  role: 'Admin Loket',
  statusLoket: 'Online',
  tanggalHariIni: 'Kamis, 24 Oktober 2024'
})

// Search & Filter State
const searchQuery = ref('')
const activeMenu = ref('beranda') // beranda, pendaftaran, jadwal, laporan
const selectedStatusTab = ref('semua') // semua, menunggu, dipanggil, selesai, dilewati

// Statistik Ringkasan Hari Ini
const stats = ref({
  totalPasien: 45,
  antreanMenunggu: 12,
  pasienSelesai: 30,
  dokterPraktik: 4
})

// Data Antrean di Loket
const queueList = ref([
  {
    id: 'ant-023',
    jam: '08:30 WIB',
    noTiket: 'A-023',
    nama: 'Clarissa Putri',
    nik: '3175085409890002',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Umum',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    status: 'Sedang Dipanggil',
    panggilanKe: 1
  },
  {
    id: 'ant-020',
    jam: '08:40 WIB',
    noTiket: 'A-020',
    nama: 'Bambang Sutrisno',
    nik: '3174021204620003',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Jantung & Pembuluh',
    dokter: 'dr. Farhan Malik, Sp.JP',
    status: 'Menunggu',
    panggilanKe: 0
  },
  {
    id: 'ant-004',
    jam: '08:42 WIB',
    noTiket: 'B-004',
    nama: 'Kayla Nadira',
    nik: '3174092215080002',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    status: 'Menunggu',
    panggilanKe: 0
  },
  {
    id: 'ant-021',
    jam: '08:45 WIB',
    noTiket: 'A-021',
    nama: 'Ratna Dewi',
    nik: '3275016208920005',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Gigi & Mulut',
    dokter: 'drg. Rina Permata, Sp.Ort',
    status: 'Menunggu',
    panggilanKe: 0
  },
  {
    id: 'ant-019',
    jam: '08:35 WIB',
    noTiket: 'A-019',
    nama: 'Siti Rahmawati',
    nik: '3175084902810001',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Penyakit Dalam',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-018',
    jam: '08:22 WIB',
    noTiket: 'A-018',
    nama: 'Marlina Sari',
    nik: '3173045610880007',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Kandungan',
    dokter: 'dr. Nurul Hidayati, Sp.OG',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-003',
    jam: '08:10 WIB',
    noTiket: 'B-003',
    nama: 'Alif Pratama',
    nik: '3175021903180004',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-014',
    jam: '07:55 WIB',
    noTiket: 'A-014',
    nama: 'Budi Santoso',
    nik: '3171012304770002',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-013',
    jam: '07:50 WIB',
    noTiket: 'A-013',
    nama: 'Rina Wati',
    nik: '3172054109850006',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Gigi & Mulut',
    dokter: 'drg. Rina Permata, Sp.Ort',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-002',
    jam: '07:45 WIB',
    noTiket: 'B-002',
    nama: 'Dimas Arya',
    nik: '3175081206190001',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-011',
    jam: '07:35 WIB',
    noTiket: 'A-011',
    nama: 'Sri Wahyuni',
    nik: '3174086302820008',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Kandungan',
    dokter: 'dr. Nurul Hidayati, Sp.OG',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-010',
    jam: '07:30 WIB',
    noTiket: 'A-010',
    nama: 'Hendra Saputra',
    nik: '3171052109800003',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Jantung & Pembuluh',
    dokter: 'dr. Farhan Malik, Sp.JP',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-001',
    jam: '07:25 WIB',
    noTiket: 'B-001',
    nama: 'Cika Ramadhani',
    nik: '3175025501170002',
    metodeDaftar: 'Online',
    tipePembayaran: 'Pendaftaran Online',
    poli: 'Poli Spesialis Anak',
    dokter: 'dr. Hendra Wijaya, Sp.A',
    status: 'Selesai',
    panggilanKe: 1
  },
  // Pasien Walk-in
  {
    id: 'ant-022',
    jam: '08:48 WIB',
    noTiket: 'A-022',
    nama: 'Ahmad Fauzi',
    nik: '3171092301850004',
    metodeDaftar: 'Walk-in',
    tipePembayaran: 'Pasien Walk-in',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    status: 'Menunggu',
    panggilanKe: 0
  },
  {
    id: 'ant-017',
    jam: '08:15 WIB',
    noTiket: 'A-017',
    nama: 'Hendra Gunawan',
    nik: '3172081105750003',
    metodeDaftar: 'Walk-in',
    tipePembayaran: 'Pasien Walk-in',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    status: 'Selesai',
    panggilanKe: 1
  },
  {
    id: 'ant-016',
    jam: '08:05 WIB',
    noTiket: 'A-016',
    nama: 'Joko Widodo Santoso',
    nik: '3171032107690001',
    metodeDaftar: 'Walk-in',
    tipePembayaran: 'Pasien Walk-in',
    poli: 'Poli Mata',
    dokter: 'dr. Anita Kusuma, Sp.M',
    status: 'Dilewati',
    panggilanKe: 3
  },
  {
    id: 'ant-015',
    jam: '08:00 WIB',
    noTiket: 'A-015',
    nama: 'Eka Yuliana',
    nik: '3174056209840008',
    metodeDaftar: 'Walk-in',
    tipePembayaran: 'Pasien Walk-in',
    poli: 'Poli Penyakit Dalam',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    status: 'Dilewati',
    panggilanKe: 3
  },
  {
    id: 'ant-012',
    jam: '07:40 WIB',
    noTiket: 'A-012',
    nama: 'Agus Priyono',
    nik: '3173091508710004',
    metodeDaftar: 'Walk-in',
    tipePembayaran: 'Pasien Walk-in',
    poli: 'Poli Penyakit Dalam',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    status: 'Selesai',
    panggilanKe: 1
  }
])

// Pasien yang Sedang Dipanggil Aktif
const activeCallingPatient = computed(() => {
  return queueList.value.find(q => q.status === 'Sedang Dipanggil') || null
})

// Antrean Berikutnya yang Siap Dipanggil (Status Menunggu, mengutamakan pendaftaran online)
const nextWaitingPatient = computed(() => {
  const waitingList = queueList.value.filter(q => q.status === 'Menunggu')
  // Prioritaskan yang daftar online terlebih dahulu
  const onlineWaiting = waitingList.find(q => q.metodeDaftar !== 'Walk-in')
  if (onlineWaiting) return onlineWaiting
  return waitingList.find(q => q.metodeDaftar === 'Walk-in') || null
})

// Tab Filter Antrean dengan Jumlah Real-time
const filterTabs = computed(() => [
  { key: 'semua', label: 'Semua', count: queueList.value.length },
  { key: 'menunggu', label: 'Menunggu', count: queueList.value.filter(q => q.status === 'Menunggu').length },
  { key: 'dipanggil', label: 'Dipanggil', count: queueList.value.filter(q => q.status === 'Sedang Dipanggil').length },
  { key: 'selesai', label: 'Selesai', count: queueList.value.filter(q => q.status === 'Selesai' || q.status === 'Terverifikasi').length },
  { key: 'dilewati', label: 'Dilewati', count: queueList.value.filter(q => q.status === 'Dilewati').length }
])

// Filter Antrean berdasarkan Tab, Pencarian, & Prioritas Online (Walk-in di urutan terakhir)
const filteredQueue = computed(() => {
  let list = [...queueList.value]

  // Filter berdasarkan Tab Aktif
  if (selectedStatusTab.value === 'menunggu') {
    list = list.filter(q => q.status === 'Menunggu')
  } else if (selectedStatusTab.value === 'dipanggil') {
    list = list.filter(q => q.status === 'Sedang Dipanggil')
  } else if (selectedStatusTab.value === 'selesai') {
    list = list.filter(q => q.status === 'Selesai' || q.status === 'Terverifikasi')
  } else if (selectedStatusTab.value === 'dilewati') {
    list = list.filter(q => q.status === 'Dilewati')
  }

  // Filter berdasarkan input pencarian
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(item => {
      const metode = item.metodeDaftar ? item.metodeDaftar.toLowerCase() : ''
      return (
        item.nama.toLowerCase().includes(q) ||
        item.noTiket.toLowerCase().includes(q) ||
        item.nik.toLowerCase().includes(q) ||
        item.poli.toLowerCase().includes(q) ||
        metode.includes(q)
      )
    })
  }

  // Ranking Status:
  const getStatusRank = (status) => {
    if (status === 'Sedang Dipanggil') return 1
    if (status === 'Menunggu') return 2
    if (status === 'Dilewati') return 3
    if (status === 'Selesai' || status === 'Terverifikasi') return 4
    return 5
  }


  return list.sort((a, b) => {
    const rankA = getStatusRank(a.status)
    const rankB = getStatusRank(b.status)

    // Urutkan berdasarkan status: yang belum dipanggil di atas, selesai di paling bawah
    if (rankA !== rankB) {
      return rankA - rankB
    }

    // Jika status sama, prioritaskan metode pendaftaran: Online di atas, Walk-in di bawah
    const aIsWalkin = a.metodeDaftar === 'Walk-in'
    const bIsWalkin = b.metodeDaftar === 'Walk-in'

    if (!aIsWalkin && bIsWalkin) return -1
    if (aIsWalkin && !bIsWalkin) return 1

    return 0
  })
})

// Fungsi Bunyi Audio Panggilan
const playAnnouncement = (noTiket, nama, panggilanKe) => {
  if ('speechSynthesis' in window) {
    try {
      window.speechSynthesis.cancel()
      const text = `Nomor antrean, ${noTiket}, atas nama ${nama}, silakan menuju ke Loket `
      const utterance = new SpeechSynthesisUtterance(text)
      utterance.lang = 'id-ID'
      utterance.rate = 0.95
      window.speechSynthesis.speak(utterance)
    } catch (e) {
      console.warn('Speech synthesis error:', e)
    }
  }
}

// State Toast Notifikasi
const showToast = ref(false)
const toastTitle = ref('')
const toastMessage = ref('')
const toastTag = ref('')
const toastType = ref('success') // 'success' | 'warning' | 'info'
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

// State Modal Konfirmasi Lewati Pasien (Hangus)
const showSkipModal = ref(false)
const skipTargetPatient = ref(null)

const openSkipModal = (item) => {
  if (!item) return
  skipTargetPatient.value = item
  showSkipModal.value = true
}

const cancelSkip = () => {
  showSkipModal.value = false
  skipTargetPatient.value = null
}

const confirmSkip = () => {
  if (!skipTargetPatient.value) return
  const item = skipTargetPatient.value
  item.status = 'Dilewati'
  stats.value.antreanMenunggu = Math.max(0, stats.value.antreanMenunggu - 1)
  showSkipModal.value = false
  triggerToast(
    'Antrean Pasien Dilewati',
    `Nomor antrean ${item.noTiket} (${item.nama}) telah ditandai dilewati (hangus).`,
    'Dilewati',
    'warning'
  )
  skipTargetPatient.value = null
}

// Helper: Sinkronkan Panggilan ke Tampilan Layar TV Ruang Tunggu
const broadcastCallingQueue = (item, loket = 'Loket') => {
  if (!item) return
  const callData = {
    noTiket: item.noTiket || item.noAntrean || 'A-023',
    nama: item.nama || 'Pasien',
    poli: item.poli || 'Poli Umum',
    loket: loket,
    lokasi: 'POLIKLINIK 1 • LT. 1',
    panggilanKe: item.panggilanKe || 1,
    timestamp: Date.now()
  }
  try {
    localStorage.setItem('kliniku_calling_queue', JSON.stringify(callData))
    window.dispatchEvent(new Event('storage'))
  } catch (e) {
    console.warn('Gagal sinkronisasi TV:', e)
  }
}

// Aksi Panggil Ulang Pasien dari Card Pemanggilan Aktif
const panggilUlangAktif = () => {
  if (!activeCallingPatient.value) return
  const p = activeCallingPatient.value
  p.panggilanKe = (p.panggilanKe || 1) + 1
  playAnnouncement(p.noTiket, p.nama, p.panggilanKe)
  broadcastCallingQueue(p)
  triggerToast(
    'Panggilan Ulang Pasien',
    `Memanggil ulang ${p.nama} (${p.noTiket}) menuju Loket (Panggilan ke-${p.panggilanKe} dari maks. 3x).`,
    'Panggilan Ulang'
  )
}

// Aksi Panggil Pasien dari Tabel
const panggilAntrean = (item) => {
  queueList.value.forEach(q => {
    if (q.status === 'Sedang Dipanggil' && q.id !== item.id) {
      q.status = 'Menunggu'
    }
  })
  item.status = 'Sedang Dipanggil'
  item.panggilanKe = (item.panggilanKe || 0) + 1
  playAnnouncement(item.noTiket, item.nama, item.panggilanKe)
  broadcastCallingQueue(item)
  triggerToast(
    'Memanggil Pasien Antrean',
    `Nomor antrean ${item.noTiket} atas nama ${item.nama} dipanggil menuju Loket.`,
    'Panggilan Loket'
  )
}

// Aksi Panggil Ulang dari Baris Tabel (Dilewati / Lainnya)
const panggilUlangRow = (item) => {
  queueList.value.forEach(q => {
    if (q.status === 'Sedang Dipanggil' && q.id !== item.id) {
      q.status = 'Menunggu'
    }
  })
  item.status = 'Sedang Dipanggil'
  item.panggilanKe = (item.panggilanKe || 1) + 1
  playAnnouncement(item.noTiket, item.nama, item.panggilanKe)
  broadcastCallingQueue(item)
  triggerToast(
    'Panggilan Ulang Pasien',
    `Memanggil ulang ${item.nama} (${item.noTiket}) menuju Loket (Panggilan ke-${item.panggilanKe}).`,
    'Panggilan Ulang'
  )
}

// Helper: Sinkronisasi Status Selesai ke LocalStorage & Sisi Pasien
const markPatientCompleted = (patient) => {
  if (!patient) return
  patient.status = 'Selesai'

  try {
    // 1. Simpan tiket ke daftar tiket yang sudah selesai
    const completedTickets = JSON.parse(localStorage.getItem('kliniku_completed_tickets') || '[]')
    if (!completedTickets.includes(patient.noTiket)) {
      completedTickets.push(patient.noTiket)
      localStorage.setItem('kliniku_completed_tickets', JSON.stringify(completedTickets))
    }

    // 2. Simpan status spesifik per nomor tiket
    localStorage.setItem(`kliniku_queue_status_${patient.noTiket}`, 'Selesai')

    // 3. Update status di array kliniku_bookings jika pasien ini daftar online
    const now = new Date()
    const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
    const savedBookings = JSON.parse(localStorage.getItem('kliniku_bookings') || '[]')
    let bookingUpdated = false
    savedBookings.forEach(b => {
      if (b.nomorAntrean === patient.noTiket || b.pasienNama === patient.nama) {
        b.status = 'Selesai'
        b.tanggalSelesai = todayStr
        b.selesaiPada = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
        bookingUpdated = true
      }
    })
    if (bookingUpdated) {
      localStorage.setItem('kliniku_bookings', JSON.stringify(savedBookings))
    }

    // 4. Update flag antrean aktif selesai untuk sesi pasien saat ini beserta tanggal
    localStorage.setItem('kliniku_active_queue_completed', 'true')
    localStorage.setItem('kliniku_last_completed_ticket', patient.noTiket)
    localStorage.setItem('kliniku_last_completed_patient', patient.nama)
    localStorage.setItem('kliniku_last_completed_date', todayStr)

    // 5. Tambahkan ke riwayat kunjungan selesai di sisi pasien
    const completedVisits = JSON.parse(localStorage.getItem('kliniku_completed_visits') || '[]')
    const existsVisit = completedVisits.some(v => v.noAntrean === patient.noTiket)
    if (!existsVisit) {
      completedVisits.unshift({
        id: `vis-${Date.now()}`,
        tanggal: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ', ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB',
        status: 'Selesai',
        poli: patient.poli || 'Poli Umum',
        dokter: patient.dokter || 'dr. Sarah Amanda, Sp.PD',
        diagnosa: 'Pemeriksaan Selesai & Terverifikasi',
        noRm: `RM-2026-${patient.noTiket ? patient.noTiket.replace(/[^0-9]/g, '') : '010'}`,
        keluhan: 'Pemeriksaan dan konsultasi poliklinik telah selesai dilaksanakan.',
        tandaVital: {
          tekananDarah: '120/80 mmHg',
          nadi: '78 x/menit',
          suhu: '36.6 °C',
          beratBadan: '56 kg'
        },
        diagnosaDetail: [
          { kode: 'Z00.0', nama: 'General medical examination (Konsultasi Selesai)' }
        ],
        tindakan: ['Pemeriksaan klinis dan verifikasi loket selesai'],
        resepObat: [],
        catatanDokter: 'Pelayanan telah selesai. Pasien dapat melanjutkan aktivitas normal.'
      })
      localStorage.setItem('kliniku_completed_visits', JSON.stringify(completedVisits))
    }

    // 6. Broadcast event agar tab Pasien terupdate secara instan real-time
    window.dispatchEvent(new Event('storage'))
  } catch (e) {
    console.warn('Gagal sinkronisasi status selesai:', e)
  }
}

// Aksi Verifikasi Hadir (Pasien Datang ke Loket)
const verifikasiHadir = (item) => {
  if (!item) return
  markPatientCompleted(item)
  stats.value.antreanMenunggu = Math.max(0, stats.value.antreanMenunggu - 1)
  stats.value.pasienSelesai += 1
  triggerToast(
    'Verifikasi Kehadiran Berhasil',
    `Kehadiran ${item.nama} (${item.noTiket}) berhasil diverifikasi di Loket! Pasien siap diarahkan ke ${item.poli}.`,
    'Kehadiran'
  )
}

// Aksi Selesaikan Pelayanan Langsung dari Admin
const selesaikanPelayanan = (patient) => {
  if (!patient) return
  const prevStatus = patient.status
  markPatientCompleted(patient)
  if (prevStatus === 'Menunggu') {
    stats.value.antreanMenunggu = Math.max(0, stats.value.antreanMenunggu - 1)
  }
  stats.value.pasienSelesai += 1
  triggerToast(
    'Pelayanan Pasien Selesai!',
    `Nomor antrean ${patient.noTiket} (${patient.nama}) telah dinyatakan Selesai. Antrean di sisi pasien otomatis ditandai Selesai.`,
    'Selesai'
  )
}

// Aksi Lewati Pasien
const lewatiPasien = (item) => {
  openSkipModal(item)
}

// Aksi Panggil Berikutnya
const panggilBerikutnya = () => {
  if (!nextWaitingPatient.value) {
    triggerToast('Antrean Selesai', 'Tidak ada antrean menunggu berikutnya di loket!', 'Info', 'warning')
    return
  }
  const next = nextWaitingPatient.value
  // Selesaikan yang sedang dipanggil jika ada
  if (activeCallingPatient.value) {
    markPatientCompleted(activeCallingPatient.value)
    stats.value.pasienSelesai += 1
  }
  next.status = 'Sedang Dipanggil'
  next.panggilanKe = 1
  playAnnouncement(next.noTiket, next.nama, next.panggilanKe)
  broadcastCallingQueue(next)
  triggerToast(
    'Panggilan Berikutnya',
    `Nomor antrean ${next.noTiket} atas nama ${next.nama} dipanggil menuju Loket.`,
    'Panggilan Berikutnya'
  )
}

// Aksi Lihat Detail Pasien Selesai
const selectedDetail = ref(null)
const showDetailModal = ref(false)
const lihatDetail = (item) => {
  selectedDetail.value = item
  showDetailModal.value = true
}

// State & Fungsi Card Verifikasi Kehadiran (Scan QR & Input Manual di Dashboard Beranda)
const attendanceCode = ref('A-023')
const isScanningAttendance = ref(false)
const attendanceError = ref('')
const verifiedAttendancePatient = ref(null)

const verifikasiKehadiran = (codeToVerify) => {
  const code = (codeToVerify || attendanceCode.value || '').trim().toUpperCase()
  if (!code) {
    attendanceError.value = 'Harap masukkan nomor antrean atau kode booking pasien!'
    return
  }

  attendanceError.value = ''

  // Cari pasien di queueList berdasarkan noTiket (case-insensitive)
  let patient = queueList.value.find(q => q.noTiket.toUpperCase() === code)

  // Cek juga dari kliniku_bookings jika pasien mendaftar online
  if (!patient) {
    try {
      const savedBookings = JSON.parse(localStorage.getItem('kliniku_bookings') || '[]')
      const foundB = savedBookings.find(b => b.nomorAntrean && b.nomorAntrean.toUpperCase() === code)
      if (foundB) {
        patient = {
          id: `ant-${foundB.nomorAntrean}`,
          jam: '09:25 WIB',
          noTiket: foundB.nomorAntrean,
          nama: foundB.pasienNama || 'Pasien Online',
          nik: foundB.nik || '3172345678987',
          metodeDaftar: 'Online',
          tipePembayaran: 'Pendaftaran Online',
          poli: foundB.poli || 'Poli Umum',
          dokter: foundB.dokter || 'dr. Hendra Wijaya',
          status: 'Menunggu',
          panggilanKe: 0
        }
        queueList.value.unshift(patient)
      }
    } catch (e) {}
  }

  // Jika tidak ditemukan noTiket persis, cek apakah kode booking B-024 atau cocokkan dengan pasien aktif
  if (!patient && code === 'B-024') {
    patient = queueList.value.find(q => q.noTiket === 'B-004' || q.status === 'Sedang Dipanggil')
    if (patient) {
      patient.noTiket = 'B-024'
      patient.nama = 'Rahmawati Soebandono'
    }
  }

  // Jika masih belum ditemukan, fallback ke activeCallingPatient atau pasien pertama yang Menunggu
  if (!patient) {
    patient = activeCallingPatient.value || queueList.value.find(q => q.status === 'Menunggu')
  }

  if (patient) {
    const prevStatus = patient.status
    markPatientCompleted(patient)
    if (prevStatus === 'Menunggu') {
      stats.value.antreanMenunggu = Math.max(0, stats.value.antreanMenunggu - 1)
    }
    stats.value.pasienSelesai += 1

    verifiedAttendancePatient.value = {
      nama: patient.nama,
      noTiket: patient.noTiket,
      nik: patient.nik,
      poli: patient.poli,
      dokter: patient.dokter,
      waktu: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
    }

    // Jika ada antrean berikutnya yang menunggu, otomatis siapkan kode berikutnya
    if (nextWaitingPatient.value) {
      attendanceCode.value = nextWaitingPatient.value.noTiket
    }

    triggerToast(
      'Verifikasi Kehadiran Berhasil!',
      `Kehadiran pasien ${patient.nama} (${patient.noTiket}) berhasil diverifikasi dan ditandai Selesai.`,
      'Verifikasi Hadir'
    )
  } else {
    attendanceError.value = `Antrean dengan kode ${code} tidak ditemukan.`
    triggerToast(
      'Verifikasi Gagal',
      attendanceError.value,
      'Gagal',
      'warning'
    )
  }
}

// Simulasi Scan Optik QR Code
const triggerAttendanceScan = () => {
  isScanningAttendance.value = true
  setTimeout(() => {
    isScanningAttendance.value = false
    const targetCode = activeCallingPatient.value ? activeCallingPatient.value.noTiket : (nextWaitingPatient.value ? nextWaitingPatient.value.noTiket : 'A-023')
    attendanceCode.value = targetCode
    verifikasiKehadiran(targetCode)
  }, 700)
}

// Refresh Live Data
const isRefreshing = ref(false)
const refreshData = () => {
  isRefreshing.value = true
  setTimeout(() => {
    isRefreshing.value = false
    triggerToast(
      'Data Antrean Disinkronisasi!',
      'Data antrean loket berhasil disinkronisasi secara real-time ke Sistem Rekam Medis.',
      'Real-Time Sync'
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

// Sinkronisasi data booking lokal & walk-in jika ada
onMounted(() => {
  try {
    const completedTickets = JSON.parse(localStorage.getItem('kliniku_completed_tickets') || '[]')
    
    // Perbarui status antrean bawaan jika sudah pernah diselesaikan
    queueList.value.forEach(q => {
      if (completedTickets.includes(q.noTiket)) {
        q.status = 'Selesai'
      }
    })

    const savedBookings = JSON.parse(localStorage.getItem('kliniku_bookings') || '[]')
    if (savedBookings.length > 0) {
      savedBookings.forEach(b => {
        const exists = queueList.value.some(q => q.noTiket === b.nomorAntrean)
        const isDone = b.status === 'Selesai' || completedTickets.includes(b.nomorAntrean)
        if (!exists) {
          queueList.value.unshift({
            id: `ant-${b.nomorAntrean || Date.now()}`,
            jam: '09:25 WIB',
            noTiket: b.nomorAntrean || 'A-029',
            nama: b.pasienNama || 'Pasien Online',
            nik: b.nik || '3172345678987',
            metodeDaftar: 'Online',
            tipePembayaran: 'Pendaftaran Online',
            poli: b.poli || 'Poli Umum',
            dokter: b.dokter || 'dr. Hendra Wijaya',
            status: isDone ? 'Selesai' : (b.status || 'Menunggu'),
            panggilanKe: isDone ? 1 : 0
          })
        } else {
          const item = queueList.value.find(q => q.noTiket === b.nomorAntrean)
          if (item && isDone) {
            item.status = 'Selesai'
          }
        }
      })
    }

    const savedWalkins = JSON.parse(localStorage.getItem('kliniku_walkins') || '[]')
    if (savedWalkins.length > 0) {
      savedWalkins.forEach(w => {
        const exists = queueList.value.some(q => q.noTiket === w.noTiket)
        if (!exists) {
          queueList.value.push({
            id: `ant-${w.noTiket || Date.now()}`,
            jam: w.jam || '09:30 WIB',
            noTiket: w.noTiket || 'A-030',
            nama: w.nama || 'Pasien Walk-in',
            nik: w.nik || '3171000000000001',
            metodeDaftar: 'Walk-in',
            tipePembayaran: 'Pasien Walk-in',
            poli: w.poli || 'Poli Umum',
            dokter: w.dokter || 'dr. Hendra Wijaya',
            status: 'Menunggu',
            panggilanKe: 0
          })
        }
      })
    }
    // Sinkronkan antrean pemanggilan aktif ke tampilan TV
    if (activeCallingPatient.value) {
      broadcastCallingQueue(activeCallingPatient.value)
    }
  } catch (e) {
    console.error('Gagal sinkron data booking:', e)
  }
})
</script>

<template>
  <div class="min-h-screen w-full bg-[#FFF7FA] text-[#1F1A1D] flex font-sans">
    
    <!-- 1. SIDEBAR KIRI ADMIN -->
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

        <!-- Status Loket (Online) -->
        <div class="flex items-center justify-between px-1 text-[12px] text-[#55464D]">
          <span>Status Loket</span>
          <span class="inline-flex items-center gap-1.5 font-bold text-[#1F1A1D]">
            <span class="w-2 h-2 rounded-full bg-[#6D314F]"></span>
            <span>{{ adminUser.statusLoket }}</span>
          </span>
        </div>

        <!-- Menu Navigasi Utama -->
        <nav class="flex flex-col gap-1.5">
          <!-- Beranda (Aktif) -->
          <button
            type="button"
            @click="activeMenu = 'beranda'"
            :class="[
              'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-bold text-left transition-all cursor-pointer border-0',
              activeMenu === 'beranda'
                ? 'bg-[#6D314F] text-white shadow-sm'
                : 'bg-transparent text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]'
            ]"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
              <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
            </svg>
            <span>Beranda</span>
          </button>

          <!-- Loket Pendaftaran -->
          <button
            type="button"
            @click="router.push('/admin/pendaftaran')"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all cursor-pointer border-0 bg-transparent text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <line x1="19" y1="8" x2="19" y2="14" />
              <line x1="22" y1="11" x2="16" y2="11" />
            </svg>
            <span>Loket Pendaftaran Pasien Walk-In</span>
          </button>

          <!-- Jadwal & Kuota Dokter -->
          <button
            type="button"
            @click="router.push('/admin/jadwal')"
            :class="[
              'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all cursor-pointer border-0',
              activeMenu === 'jadwal'
                ? 'bg-[#6D314F] text-white shadow-sm'
                : 'bg-transparent text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]'
            ]"
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

    <!-- 2. KONTEN UTAMA DASHBOARD ADMIN -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- 2.1 Top Bar Header: Profil Admin -->
      <header class="w-full bg-transparent px-6 lg:px-8 py-4 flex items-center justify-end box-border">
        <!-- Profil Admin Pill -->
        <div class="flex items-center gap-2.5 bg-white border border-[#F2CEDD] rounded-full px-3 py-1.5 shadow-xs shrink-0">
          <div class="w-7 h-7 rounded-full bg-[#6D314F] text-white flex items-center justify-center font-bold text-[11px]">
            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
          </div>
          <span class="text-[13px] font-extrabold text-[#1F1A1D] pr-1">{{ adminUser.nama }}</span>
        </div>
      </header>

      <!-- 2.2 Area Utama Halaman -->
      <main class="flex-1 px-6 lg:px-8 pb-8 flex flex-col gap-5 box-border">
        <!-- Header Sambutan: Selamat Bekerja, Siti Rahmawati! -->
        <section class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h1 class="text-[22px] md:text-[24px] font-black text-[#1F1A1D] m-0 tracking-tight">
            Selamat Bekerja, <span class="text-[#6D314F]">{{ adminUser.nama }}!</span>
          </h1>

          <!-- Tanggal & Tombol Refresh Live -->
          <div class="flex items-center gap-2.5">
            <button
              type="button"
              @click="refreshData"
              title="Perbarui Data Antrean"
              class="w-8 h-8 rounded-lg bg-white border border-[#F2CEDD] flex items-center justify-center text-[#6D314F] hover:bg-[#FDEBF3] transition-colors cursor-pointer shadow-xs"
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
          </div>
        </section>

        <!-- 4 Kartu Statistik Metrik Utama -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Card 1: Total Pasien Hari Ini -->
          <div class="bg-white rounded-[16px] p-4 border border-[#F4E2EB] shadow-[0_2px_10px_rgba(109,49,79,0.03)] flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[12px] font-medium text-[#7E7379] leading-tight">Total Pasien Hari Ini</span>
              <span class="text-[26px] font-extrabold text-[#1F1A1D] leading-none mt-2">{{ stats.totalPasien }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#FCE4EC] text-[#6D314F] flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
            </div>
          </div>

          <!-- Card 2: Antrean Menunggu -->
          <div class="bg-white rounded-[16px] p-4 border border-[#F4E2EB] shadow-[0_2px_10px_rgba(109,49,79,0.03)] flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[12px] font-medium text-[#7E7379] leading-tight">Antrean Menunggu</span>
              <span class="text-[26px] font-extrabold text-[#1F1A1D] leading-none mt-2">{{ stats.antreanMenunggu }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#FCE4EC] text-[#6D314F] flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                <line x1="9" y1="12" x2="11" y2="12" />
                <line x1="9" y1="16" x2="15" y2="16" />
              </svg>
            </div>
          </div>

          <!-- Card 3: Pasien Selesai -->
          <div class="bg-white rounded-[16px] p-4 border border-[#F4E2EB] shadow-[0_2px_10px_rgba(109,49,79,0.03)] flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[12px] font-medium text-[#7E7379] leading-tight">Pasien Selesai</span>
              <span class="text-[26px] font-extrabold text-[#1F1A1D] leading-none mt-2">{{ stats.pasienSelesai }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#FCE4EC] text-[#6D314F] flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
            </div>
          </div>

          <!-- Card 4: Dokter Praktik Hari Ini -->
          <div class="bg-white rounded-[16px] p-4 border border-[#F4E2EB] shadow-[0_2px_10px_rgba(109,49,79,0.03)] flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-[12px] font-medium text-[#7E7379] leading-tight">Dokter Praktik Hari Ini</span>
              <span class="text-[26px] font-extrabold text-[#1F1A1D] leading-none mt-2">{{ stats.dokterPraktik }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#FCE4EC] text-[#6D314F] flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" />
                <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" />
                <circle cx="20" cy="10" r="2" />
              </svg>
            </div>
          </div>
        </section>

        
        <!-- 3. CARD PEMANGGILAN AKTIF (Live Call Banner) -->
        <section
          v-if="activeCallingPatient"
          class="bg-white rounded-[18px] p-5 md:p-6 border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] flex flex-col gap-4"
        >
          <!-- Label Atas: PEMANGGILAN AKTIF -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-[#E11D48] animate-ping"></span>
              <span class="text-[11px] font-black uppercase tracking-wider text-[#6D314F]">PEMANGGILAN AKTIF</span>
            </div>
            <!-- Shortcut ke Simulasi TV Ruang Tunggu -->
            <a
              href="/tv"
              target="_blank"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#261720] text-[#E288A3] hover:text-white hover:bg-[#3D2534] border border-[#4D2F41] transition-all shadow-xs"
              title="Buka Simulasi Layar TV Ruang Tunggu di tab baru"
            >
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
              </svg>
              <span>Layar TV Antrean</span>
              <svg class="w-3 h-3 text-[#A88E9E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
              </svg>
            </a>
          </div>

          <!-- Baris Utama Info Pasien yang Sedang Dipanggil -->
          <div class="flex flex-col sm:flex-row sm:items-center gap-4 md:gap-5">
            <!-- Kotak Besar No. Antrean Ungu Gelap -->
            <div class="bg-[#6D314F] text-white rounded-[14px] px-5 py-3.5 flex flex-col items-center justify-center min-w-[110px] shrink-0 shadow-sm">
              <span class="text-[9.5px] font-bold uppercase tracking-wider text-rose-200">NO. ANTREAN</span>
              <span class="text-[28px] md:text-[32px] font-black leading-tight mt-0.5">{{ activeCallingPatient.noTiket }}</span>
            </div>

            <!-- Nama, NIK, dan Label Dokter & Jumlah Panggilan -->
            <div class="flex flex-col gap-1.5 flex-1 min-w-0">
              <h2 class="text-[19px] md:text-[21px] font-black text-[#1F1A1D] m-0 tracking-tight truncate">
                {{ activeCallingPatient.nama }}
              </h2>
              <p class="text-[12px] text-[#7E7379] font-medium m-0">
                NIK: <span class="font-mono text-[#55464D]">{{ activeCallingPatient.nik }}</span> • <span class="text-[#6D314F] font-semibold">{{ activeCallingPatient.poli }}</span>
              </p>

              <!-- Pill Tag Dokter & Status Panggilan -->
              <div class="flex flex-wrap items-center gap-2 mt-1">
                <!-- Pill Dokter -->
                <span class="inline-flex items-center gap-1.5 bg-white border border-[#F2CEDD] rounded-full px-2.5 py-1 text-[11px] font-medium text-[#55464D] shadow-2xs">
                  <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" />
                    <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" />
                    <circle cx="20" cy="10" r="2" />
                  </svg>
                  <span>{{ activeCallingPatient.dokter }}</span>
                </span>

                <!-- Pill Counter Panggilan (Panggilan ke-1 dari maks. 3x) -->
                <span class="inline-flex items-center gap-1 bg-[#FFF0F5] border border-[#FCE4EC] rounded-full px-2.5 py-1 text-[11px] font-semibold text-[#6D314F]">
                  <span>Panggilan ke-{{ activeCallingPatient.panggilanKe || 1 }} dari maks. 3x</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Kotak Aksi Verifikasi & Panggilan Ulang di Loket -->
          <div class="bg-[#FFF7FA] rounded-[14px] p-4 border border-[#F8E2EC] flex flex-col gap-3">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <polyline points="16 11 18 13 22 9" />
              </svg>
              <h4 class="text-[13px] font-bold text-[#1F1A1D] m-0">Verifikasi Kehadiran di Loket</h4>
            </div>
            <p class="text-[12px] text-[#7E7379] m-0 -mt-1">
              Apakah pasien telah hadir dan siap dilayani di meja loket pendaftaran?
            </p>

            <!-- Tombol-tombol Aksi di dalam Card -->
            <div class="flex items-center gap-2.5 flex-wrap pt-1">
              <!-- Tombol Selesai Dilayani (Fitur langsung menyelesaikan antrean) -->
              <button
                type="button"
                @click="selesaikanPelayanan(activeCallingPatient)"
                class="bg-[#10B981] hover:bg-[#059669] text-white px-3.5 py-1.5 rounded-[9px] text-[12px] font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-xs border-0"
              >
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Tandai Selesai</span>
              </button>

              <!-- Tombol Panggil Ulang Pasien -->
              <button
                type="button"
                @click="panggilUlangAktif"
                class="bg-white hover:bg-[#FFF0F5] text-[#6D314F] border border-[#F2CEDD] px-3.5 py-1.5 rounded-[9px] text-[12px] font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-xs"
              >
                <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="1 4 1 10 7 10" />
                  <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                </svg>
                <span>Panggil Ulang Pasien</span>
              </button>

              <!-- Tombol Lewati Pasien (Hangus) -->
              <button
                type="button"
                @click="lewatiPasien(activeCallingPatient)"
                class="bg-[#FFF0F5] hover:bg-[#FCE4EC] text-[#BE123C] border border-[#FBCFE8] px-3.5 py-1.5 rounded-[9px] text-[12px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-xs"
              >
                <svg class="w-3.5 h-3.5 text-[#BE123C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
                <span>Lewati Pasien (Hangus)</span>
              </button>
            </div>
          </div>

          <!-- Baris Bawah Card: Antrean Berikutnya & Tombol Panggil Berikutnya -->
          <div class="border-t border-[#F8E9F0] pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[12px]">
            <div class="text-[#55464D]">
              <span class="text-[#7E7379]">Antrean berikutnya siap:</span>
              <strong class="text-[#1F1A1D] ml-1.5 font-bold" v-if="nextWaitingPatient">
                {{ nextWaitingPatient.noTiket }} ({{ nextWaitingPatient.nama }})
              </strong>
              <span class="text-[#7E7379] italic ml-1.5" v-else>Tidak ada antrean menunggu</span>
            </div>

            <button
              type="button"
              @click="panggilBerikutnya"
              :disabled="!nextWaitingPatient"
              :class="[
                'px-3.5 py-1 rounded-[8px] font-bold text-[11.5px] transition-all flex items-center gap-1.5 border',
                nextWaitingPatient
                  ? 'bg-[#FFF7FA] border-[#F2CEDD] text-[#6D314F] hover:bg-[#6D314F] hover:text-white cursor-pointer shadow-xs'
                  : 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed'
              ]"
            >
              <span>Panggil Berikutnya</span>
              <span class="font-bold text-[13px] leading-none ml-0.5">&gt;</span>
            </button>
          </div>
        </section>

        <!-- 3.5 CARD VERIFIKASI KEHADIRAN (Scan QR & Input Manual) -->
        <section class="bg-white rounded-[18px] p-5 md:p-6 border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] flex flex-col gap-4">
          <!-- Header Card: Verifikasi Kehadiran -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#F8E9F0]">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-[#6D314F] text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4.5 h-4.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <polyline points="16 11 18 13 22 9" />
                </svg>
              </div>
              <div>
                <h3 class="text-[17px] font-black text-[#1F1A1D] m-0 tracking-tight">Verifikasi Kehadiran</h3>
                <p class="text-[12px] text-[#7E7379] m-0 mt-0.5">
                  Pindai QR code e-tiket pasien atau masukkan kode antrean secara manual untuk menandai data pasien di dashboard menjadi <strong class="text-[#6D314F]">Selesai</strong>.
                </p>
              </div>
            </div>
          </div>

          <!-- Grid 2 Kolom: Scanner Optik QR Code & Input Kode Antrean Manual -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Kolom 1: Scanner Optik QR Code -->
            <div class="bg-[#FFF7FA] rounded-[16px] p-4 border border-[#F8E2EC] flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[13.5px] font-extrabold text-[#1F1A1D] flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M4 7V4h3M20 7V4h-3M4 17v3h3M20 17v3h-3" />
                    </svg>
                    Scanner QR Code
                  </span>
                  
                </div>
                <p class="text-[11.5px] text-[#7E7379] m-0 mb-3">
                  Arahkan barcode scanner fisik ke lembar cetak atau layar ponsel pasien.
                </p>
              </div>

              <!-- Viewfinder Scanner -->
              <div
                @click="triggerAttendanceScan"
                class="w-full bg-[#FCEEF4] rounded-[14px] p-5 flex flex-col items-center justify-center relative cursor-pointer group border border-[#F8D6E6] hover:bg-[#FADCEB] transition-colors min-h-[170px]"
                title="Klik area untuk simulasi pemindaian QR code"
              >
                <!-- Sudut Framing QR -->
                <div class="w-[160px] h-[110px] relative flex items-center justify-center">
                  <div class="absolute top-0 left-0 w-5 h-5 border-t-2 border-l-2 border-[#6D314F]"></div>
                  <div class="absolute top-0 right-0 w-5 h-5 border-t-2 border-r-2 border-[#6D314F]"></div>
                  <div class="absolute bottom-0 left-0 w-5 h-5 border-b-2 border-l-2 border-[#6D314F]"></div>
                  <div class="absolute bottom-0 right-0 w-5 h-5 border-b-2 border-r-2 border-[#6D314F]"></div>

                  <!-- Ikon QR di Tengah -->
                  <div class="w-14 h-14 rounded-xl bg-white/80 flex items-center justify-center text-[#6D314F] shadow-xs">
                    <svg class="w-8 h-8 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                      <rect x="3" y="3" width="7" height="7" />
                      <rect x="14" y="3" width="7" height="7" />
                      <rect x="14" y="14" width="7" height="7" />
                      <rect x="3" y="14" width="7" height="7" />
                    </svg>
                  </div>

                  <!-- Garis Laser Scan Animasi -->
                  <div class="absolute w-full h-[2px] bg-red-500 shadow-[0_0_8px_red] animate-pulse"></div>
                </div>

                <span class="text-[11px] font-bold text-[#6D314F] mt-2.5 flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#E11D48] animate-ping"></span>
                  <span v-if="isScanningAttendance">Memindai QR code pasien...</span>
                  <span v-else>Menunggu pembacaan sensor otomatis... (Klik untuk scan)</span>
                </span>
              </div>
            </div>

            <!-- Kolom 2: Input Kode Antrean Manual -->
            <div class="bg-[#FFF7FA] rounded-[16px] p-4 border border-[#F8E2EC] flex flex-col justify-between">
              <div>
                <span class="text-[13.5px] font-extrabold text-[#1F1A1D] flex items-center gap-1.5 mb-1">
                  <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                  </svg>
                  Input Kode Antrean Manual
                </span>
                <p class="text-[11.5px] text-[#7E7379] m-0 mb-3">
                  Gunakan opsi ini jika QR code di ponsel pasien buram atau kendala pada scanner optik.
                </p>

                <!-- Input Field Kode -->
                <div class="mb-3">
                  <label class="block text-[11.5px] font-bold text-[#1F1A1D] mb-1">
                    Kode Booking / No. Antrean Pasien <span class="text-[#E11D48]">*</span>
                  </label>
                  <div class="relative">
                    <input
                      v-model="attendanceCode"
                      type="text"
                      placeholder="Contoh: A-023, A-020, B-004, B-024"
                      class="w-full bg-white border border-[#F2CEDD] rounded-xl pl-10 pr-4 py-2.5 text-[13px] font-bold text-[#1F1A1D] outline-none uppercase focus:border-[#6D314F] shadow-2xs"
                      @keyup.enter="verifikasiKehadiran()"
                    />
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[#6D314F]">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                      </svg>
                    </div>
                  </div>
                  <span v-if="attendanceError" class="text-[11px] text-[#DC2626] font-semibold mt-1 block">
                    {{ attendanceError }}
                  </span>
                </div>
              </div>

              <!-- Tombol Verifikasi & Tandai Selesai -->
              <button
                type="button"
                @click="verifikasiKehadiran()"
                class="w-full bg-[#6D314F] hover:bg-[#58253E] text-white py-2.5 px-4 rounded-xl text-[13px] font-bold flex items-center justify-center gap-2 shadow-sm transition-all transform active:scale-98 cursor-pointer border-0 mt-2"
              >
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                  <polyline points="9 12 11 14 15 10" />
                </svg>
                <span>Verifikasi Kehadiran &amp; Tandai Selesai</span>
              </button>
            </div>
          </div>

          <!-- Banner Hasil Verifikasi Pasien Terakhir -->
          <div
            v-if="verifiedAttendancePatient"
            class="bg-emerald-50 border border-emerald-200 rounded-[14px] p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-[12.5px] transition-all"
          >
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
              </div>
              <div>
                <p class="font-extrabold text-emerald-900 m-0">
                  Kehadiran Terverifikasi: {{ verifiedAttendancePatient.nama }} ({{ verifiedAttendancePatient.noTiket }})
                </p>
                <p class="text-[11.5px] text-emerald-700 m-0 mt-0.5">
                  Tujuan: {{ verifiedAttendancePatient.poli }} • {{ verifiedAttendancePatient.dokter }} — <strong class="text-emerald-800">Status antrean di dashboard ditandai Selesai</strong> pada {{ verifiedAttendancePatient.waktu }}.
                </p>
              </div>
            </div>

            <span class="inline-flex items-center gap-1 bg-emerald-600 text-white font-bold text-[11px] px-3 py-1 rounded-full shadow-2xs shrink-0 self-start sm:self-auto">
              ✓ Status: Selesai
            </span>
          </div>
        </section>

        <!-- 4. FILTER TABS (Semua, Menunggu, Dipanggil, Selesai, Dilewati) -->
        <section class="flex items-center gap-2 overflow-x-auto pb-1">
          <button
            v-for="tab in filterTabs"
            :key="tab.key"
            type="button"
            @click="selectedStatusTab = tab.key"
            :class="[
              'px-3.5 py-1.5 rounded-full text-[12px] font-bold transition-all cursor-pointer whitespace-nowrap border',
              selectedStatusTab === tab.key
                ? 'bg-[#6D314F] text-white border-[#6D314F] shadow-xs'
                : 'bg-white border-[#F2CEDD] text-[#55464D] hover:bg-[#FFF7FA] hover:text-[#6D314F]'
            ]"
          >
            {{ tab.label }} ({{ tab.count }})
          </button>
        </section>


        <!-- 5. TABEL ANTREAN TERBARU DI LOKET (LIVE REAL-TIME) -->
        <section class="bg-white rounded-[18px] border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] overflow-hidden flex flex-col">
          <!-- Header Tabel (Card Data Pasien) -->
          <div class="p-5 border-b border-[#F4E2EB] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-[17px] font-black text-[#1F1A1D] m-0">Antrean Pasien di Loket (Live Real-Time)</h2>
                <span class="w-2.5 h-2.5 rounded-full bg-[#E11D48] animate-ping"></span>
              </div>
            </div>

            <!-- Bagian Kanan: Cari Pasien & Total Antrean -->
            <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
              <!-- Input Pencarian Pasien di Card Data Pasien -->
              <div class="relative w-full sm:w-[280px]">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[#7E7379]">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                  </svg>
                </span>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari No. RM, Pasien, atau Antrean.."
                  class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-full pl-9 pr-4 py-2 text-[12px] text-[#1F1A1D] outline-none shadow-2xs focus:border-[#6D314F] focus:bg-white transition-colors"
                />
              </div>

              <!-- Total antrean loket -->
              <div class="text-[12px] text-[#7E7379] font-medium bg-[#FFF7FA] border border-[#F4E2EB] px-3.5 py-2 rounded-full whitespace-nowrap shrink-0">
                Total antrean: <strong class="text-[#6D314F] font-extrabold">{{ queueList.length }}</strong>
              </div>
            </div>
          </div>

          <!-- Tabel Responsif dengan Scroll Vertikal Khusus Antrean -->
          <div class="w-full overflow-x-auto max-h-[460px] overflow-y-auto">
            <table class="w-full text-left border-collapse min-w-[850px]">
              <thead class="sticky top-0 bg-[#FFF7FA] z-10 shadow-[0_1px_4px_rgba(109,49,79,0.04)]">
                <tr class="text-[#7E7379] text-[11px] font-bold uppercase tracking-wider border-b border-[#F4E2EB]">
                  <th class="py-3.5 px-4 bg-[#FFF7FA]">Jam Kedatangan</th>
                  <th class="py-3.5 px-4 bg-[#FFF7FA]">No. Tiket</th>
                  <th class="py-3.5 px-4 bg-[#FFF7FA]">Identitas Pasien</th>
                  <th class="py-3.5 px-4 bg-[#FFF7FA]">Poli &amp; Dokter Tujuan</th>
                  <th class="py-3.5 px-4 bg-[#FFF7FA]">Status</th>
                  <th class="py-3.5 px-4 text-center bg-[#FFF7FA]">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#F8E9F0] text-[13px]">
                <tr
                  v-for="row in filteredQueue"
                  :key="row.id"
                  class="hover:bg-[#FFF9FB] transition-colors"
                >
                  <!-- 1. Jam Kedatangan -->
                  <td class="py-3.5 px-4 font-semibold text-[#55464D] whitespace-nowrap">
                    {{ row.jam }}
                  </td>

                  <!-- 2. No. Tiket -->
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <!-- Jika Sedang Dipanggil: Kotak Plum Teks Putih -->
                    <span
                      v-if="row.status === 'Sedang Dipanggil'"
                      class="inline-block bg-[#6D314F] text-white font-black text-[13px] px-3 py-1 rounded-[8px] shadow-xs tracking-tight"
                    >
                      {{ row.noTiket }}
                    </span>
                    <!-- Jika Dilewati: Kotak Rose Teks Merah Muda -->
                    <span
                      v-else-if="row.status === 'Dilewati'"
                      class="inline-block bg-[#FFF0F5] border border-[#FBCFE8] text-[#BE123C] font-extrabold text-[13px] px-3 py-1 rounded-[8px]"
                    >
                      {{ row.noTiket }}
                    </span>
                    <!-- Jika Menunggu/Selesai: Kotak Pink Soft -->
                    <span
                      v-else
                      class="inline-block bg-[#FDEBF3] text-[#6D314F] font-extrabold text-[13px] px-3 py-1 rounded-[8px]"
                    >
                      {{ row.noTiket }}
                    </span>
                  </td>

                  <!-- 3. Identitas Pasien -->
                  <td class="py-3.5 px-4">
                    <div class="flex flex-col">
                      <span class="font-bold text-[13.5px] text-[#1F1A1D]">
                        {{ row.nama }}
                      </span>
                      <span class="text-[11.5px] text-[#7E7379] mt-0.5">
                        NIK: {{ row.nik }} · <span :class="row.metodeDaftar === 'Walk-in' ? 'text-amber-700 font-bold' : 'text-[#6D314F] font-bold'">{{ row.metodeDaftar === 'Walk-in' ? 'Pasien Walk-in' : 'Pendaftaran Online' }}</span>
                      </span>
                    </div>
                  </td>

                  <!-- 4. Poli & Dokter Tujuan -->
                  <td class="py-3.5 px-4">
                    <div class="flex flex-col">
                      <span class="font-semibold text-[13px] text-[#1F1A1D]">
                        {{ row.poli }}
                      </span>
                      <span class="text-[11.5px] text-[#7E7379] mt-0.5">
                        {{ row.dokter }}
                      </span>
                    </div>
                  </td>

                  <!-- 5. Status -->
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <!-- Sedang Dipanggil -->
                    <span
                      v-if="row.status === 'Sedang Dipanggil'"
                      class="bg-[#FFE4EE] text-[#9F1239] text-[11px] font-extrabold px-3 py-1 rounded-full inline-flex items-center gap-1.5"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-[#E11D48] animate-ping"></span>
                      <span>Sedang Dipanggil</span>
                    </span>

                    <!-- Selesai / Terverifikasi -->
                    <span
                      v-else-if="row.status === 'Selesai' || row.status === 'Terverifikasi'"
                      class="bg-[#F3F4F6] text-[#4B5563] text-[11px] font-semibold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1"
                    >
                      <span>Selesai</span>
                    </span>

                    <!-- Dilewati -->
                    <span
                      v-else-if="row.status === 'Dilewati'"
                      class="bg-[#FFE4E6] text-[#BE123C] text-[11px] font-bold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1"
                    >
                      <span>Dilewati</span>
                    </span>

                    <!-- Menunggu -->
                    <span
                      v-else
                      class="text-[#55464D] text-[12px] font-medium"
                    >
                      Menunggu
                    </span>
                  </td>

                  <!-- 6. Aksi Tindakan -->
                  <td class="py-3.5 px-4 text-center whitespace-nowrap">
                    <!-- Aksi 1: Panggil (Jika status Menunggu) -->
                    <button
                      v-if="row.status === 'Menunggu'"
                      type="button"
                      @click="panggilAntrean(row)"
                      class="bg-[#FFF0F5] hover:bg-[#FADCEB] text-[#6D314F] border border-[#F2CEDD] font-bold text-[12px] px-3.5 py-1.5 rounded-[8px] inline-flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs"
                    >
                      <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 5L6 9H2v6h4l5 4V5z" />
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07" />
                      </svg>
                      <span>Panggil</span>
                    </button>

                    <!-- Aksi 2: Sedang Dipanggil (Bisa Panggil Ulang atau Tandai Selesai) -->
                    <div v-else-if="row.status === 'Sedang Dipanggil'" class="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="panggilUlangRow(row)"
                        class="bg-[#FFF0F5] hover:bg-[#FCE4EC] text-[#6D314F] border border-[#F2CEDD] font-bold text-[12px] px-3 py-1.5 rounded-[8px] inline-flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs"
                      >
                        <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="1 4 1 10 7 10" />
                          <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                        </svg>
                        <span>Panggil Ulang</span>
                      </button>

                      <button
                        type="button"
                        @click="selesaikanPelayanan(row)"
                        class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-[12px] px-2.5 py-1.5 rounded-[8px] inline-flex items-center gap-1 transition-all cursor-pointer shadow-2xs"
                        title="Tandai pasien ini telah selesai dilayani"
                      >
                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                          <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span>Selesai</span>
                      </button>
                    </div>

                    <!-- Aksi 2.5: Panggil Ulang jika Dilewati -->
                    <button
                      v-else-if="row.status === 'Dilewati'"
                      type="button"
                      @click="panggilUlangRow(row)"
                      class="bg-[#FFF0F5] hover:bg-[#FCE4EC] text-[#6D314F] border border-[#F2CEDD] font-bold text-[12px] px-3.5 py-1.5 rounded-[8px] inline-flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs"
                    >
                      <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="1 4 1 10 7 10" />
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                      </svg>
                      <span>Panggil Ulang</span>
                    </button>

                    <!-- Aksi 3: Detail (Jika status Selesai) -->
                    <button
                      v-else-if="row.status === 'Selesai' || row.status === 'Terverifikasi'"
                      type="button"
                      @click="lihatDetail(row)"
                      class="bg-transparent hover:bg-[#FFF0F5] text-[#7E7379] hover:text-[#6D314F] font-semibold text-[12px] px-3 py-1 rounded-[8px] inline-flex items-center gap-1 transition-all cursor-pointer border border-transparent hover:border-[#F2CEDD]"
                    >
                      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      <span>Detail</span>
                    </button>
                  </td>
                </tr>

                <!-- Baris Kosong jika filter tidak menemukan data -->
                <tr v-if="filteredQueue.length === 0">
                  <td colspan="6" class="text-center py-8 text-[#7E7379] text-[13px]">
                    Tidak ada data antrean untuk kategori atau pencarian ini.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

  
    <!-- MODAL DETAIL PASIEN (SELESAI)                  -->
    <div
      v-if="showDetailModal && selectedDetail"
      class="fixed inset-0 bg-black/40 backdrop-blur-xs z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-[20px] max-w-md w-full p-6 border border-[#F2CEDD] shadow-xl flex flex-col gap-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#F8E9F0]">
          <div>
            <h3 class="text-[17px] font-bold text-[#1F1A1D] m-0">Detail Antrean Pasien</h3>
            <span class="text-[12px] text-[#7E7379]">No. Tiket: {{ selectedDetail.noTiket }}</span>
          </div>
          <button
            type="button"
            @click="showDetailModal = false"
            class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer border-0"
          >
            ✕
          </button>
        </div>

        <div class="flex flex-col gap-2.5 text-[13px]">
          <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Nama Lengkap</span>
            <strong class="text-gray-800">{{ selectedDetail.nama }}</strong>
          </div>
          <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Nomor NIK</span>
            <strong class="font-mono text-gray-800">{{ selectedDetail.nik }}</strong>
          </div>
          <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Poli Tujuan</span>
            <strong class="text-[#6D314F]">{{ selectedDetail.poli }}</strong>
          </div>
          <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Dokter Pemeriksa</span>
            <span class="text-gray-800">{{ selectedDetail.dokter }}</span>
          </div>
          <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Jam Kedatangan</span>
            <span class="text-gray-800">{{ selectedDetail.jam }}</span>
          </div>
          <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-500">Metode Pendaftaran</span>
            <span :class="selectedDetail.metodeDaftar === 'Walk-in' ? 'text-amber-700 font-bold' : 'text-[#6D314F] font-bold'">
              {{ selectedDetail.metodeDaftar === 'Walk-in' ? 'Pasien Walk-in' : 'Pendaftaran Online' }}
            </span>
          </div>
          <div class="flex justify-between py-1">
            <span class="text-gray-500">Status</span>
            <span class="bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full text-[11px] font-bold">
              {{ selectedDetail.status }}
            </span>
          </div>
        </div>

        <button
          type="button"
          @click="showDetailModal = false"
          class="w-full mt-2 bg-[#6D314F] text-white py-2.5 rounded-[10px] font-bold text-[13px] hover:bg-[#55233C] transition-colors border-0 cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>

    <!-- POP UP PILIHAN YAKIN / TIDAK UNTUK MELEWATI PASIEN (HANGUS) -->
    <Teleport to="body">
      <div
        v-if="showSkipModal && skipTargetPatient"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-xs p-4 animate-in fade-in duration-200"
      >
        <div class="bg-white rounded-[24px] max-w-[420px] w-full p-6 shadow-2xl border border-[#F2CEDD] flex flex-col items-center text-center">
          <!-- Icon Peringatan -->
          <div class="w-14 h-14 rounded-full bg-rose-50 text-[#BE123C] border-2 border-rose-100 flex items-center justify-center mb-4 shadow-xs">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
          </div>

          <h3 class="text-[18px] font-extrabold text-[#1F1A1D] m-0">Lewati Antrean Pasien?</h3>
          <p class="text-[13px] text-[#55464D] mt-2 mb-4 leading-relaxed">
            Apakah Anda yakin ingin melewati (hanguskan) antrean pasien ini? Pasien tidak akan dipanggil lagi pada sesi pemanggilan aktif saat ini.
          </p>

          <!-- Info Pasien Card -->
          <div class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[14px] p-3 mb-5 text-left flex items-center justify-between">
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-[#7E7379] block">Nomor &amp; Nama Pasien</span>
              <span class="text-[14px] font-black text-[#6D314F] block mt-0.5">
                {{ skipTargetPatient.noTiket }} - {{ skipTargetPatient.nama }}
              </span>
              <span class="text-[11.5px] text-[#7E7379] block mt-0.5">
                {{ skipTargetPatient.poli }} &bull; {{ skipTargetPatient.dokter }}
              </span>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10.5px] font-black bg-rose-100 text-[#BE123C]">
              Hangus
            </span>
          </div>

          <!-- Tombol Pilihan: Yakin / Tidak -->
          <div class="flex items-center gap-3 w-full">
            <button
              type="button"
              @click="cancelSkip"
              class="flex-1 py-2.5 px-4 rounded-[12px] border border-[#E5BED1] bg-white text-[#55464D] font-bold text-[13px] hover:bg-[#FFF7FA] cursor-pointer transition-colors"
            >
              Tidak, Batalkan
            </button>
            <button
              type="button"
              @click="confirmSkip"
              class="flex-1 py-2.5 px-4 rounded-[12px] border border-transparent bg-[#BE123C] text-white font-bold text-[13px] hover:bg-[#9F1239] cursor-pointer transition-colors shadow-sm"
            >
              Ya, Yakin Lewati
            </button>
          </div>
        </div>
      </div>
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

<style scoped>
/* Scrollbar kustom untuk tabel antrean */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #FFF7FA;
  border-radius: 8px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #E5BED1;
  border-radius: 8px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #6D314F;
}
</style>
