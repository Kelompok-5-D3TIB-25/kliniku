<script setup>
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import AdminLogoutModal from '@/components/AdminLogoutModal.vue'
import logoKliniku from '@/assets/logo-kliniku.png'

const router = useRouter()
const showLogoutModal = ref(false)
const logoLoadError = ref(false)

// Data Resepsionis Aktif
const adminUser = ref({
  nama: 'Siti Rahmawati',
  role: 'Admin Loket & Resepsionis',
  statusLoket: 'Online',
  shift: 'Pagi (07:30 - 14:30 WIB)'
})

// Form Walk-in Pasien Baru
const walkinForm = ref({
  nama: '',
  nik: '',
  telepon: '',
  tipePasien: 'Umum / Mandiri',
  poli: 'Poli Umum',
  dokter: 'dr. Hendra Wijaya',
  keluhan: ''
})

// State Pasien Walk-in yang Terdaftar
const registeredPatient = ref({
  noAntreanPoli: 'A-025',
  poli: 'Poli Penyakit Dalam',
  lokasi: 'Ruang Tunggu Poli Lantai 1 (Gedung A)',
  namaLengkap: 'Rahmawati Soebandono',
  noRm: 'RM-2024-8841',
  nik: '3175084902810003',
  spesialis: 'Poli Penyakit Dalam (Sp.PD)',
  dokter: 'dr. Sarah Amanda, Sp.PD',
  jadwal: 'Hari ini (Walk-in Langsung)',
  email: 'rararungseng@gmail.com',
  telepon: '+62 812-1234-5678',
  waktuPendaftaran: '09:12 WIB'
})

const hasRegistered = ref(true)
const isSubmitting = ref(false)

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

// Submit Pasien Walk-in
const submitWalkin = () => {
  if (!walkinForm.value.nama || !walkinForm.value.nik) {
    triggerToast('Data Belum Lengkap', 'Harap lengkapi nama dan NIK pasien sebelum mendaftar!', 'Peringatan', 'warning')
    return
  }

  isSubmitting.value = true

  setTimeout(() => {
    isSubmitting.value = false
    const patientName = walkinForm.value.nama
    const randomTicket = `A-0${Math.floor(25 + Math.random() * 20)}`
    registeredPatient.value = {
      noAntreanPoli: randomTicket,
      poli: walkinForm.value.poli,
      lokasi: 'Ruang Tunggu Poli Lantai 1 (Gedung A)',
      namaLengkap: patientName,
      noRm: `RM-2024-${Math.floor(1000 + Math.random() * 9000)}`,
      nik: walkinForm.value.nik,
      spesialis: walkinForm.value.poli,
      dokter: walkinForm.value.dokter,
      jadwal: 'Hari ini (Walk-in Langsung)',
      email: `${patientName.toLowerCase().replace(/[^a-z0-9]/g, '')}@gmail.com`,
      telepon: walkinForm.value.telepon || '+62 812-9876-5432',
      waktuPendaftaran: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
    }
    hasRegistered.value = true

    // Simpan ke storage antrean walk-in agar tampil di Beranda
    try {
      const existingWalkins = JSON.parse(localStorage.getItem('kliniku_walkins') || '[]')
      existingWalkins.push({
        noTiket: randomTicket,
        nama: patientName,
        nik: walkinForm.value.nik,
        poli: walkinForm.value.poli,
        dokter: walkinForm.value.dokter,
        metodeDaftar: 'Walk-in',
        jam: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
      })
      localStorage.setItem('kliniku_walkins', JSON.stringify(existingWalkins))
    } catch (e) {
      console.warn('Gagal menyimpan antrean walkin:', e)
    }

    // Reset form
    walkinForm.value.nama = ''
    walkinForm.value.nik = ''
    walkinForm.value.telepon = ''
    walkinForm.value.keluhan = ''

    triggerToast(
      'Pendaftaran Walk-in Berhasil Disimpan!',
      `Berhasil menyimpan data untuk pendaftaran pasien walk-in (${patientName}). Nomor antrean ${randomTicket} diterbitkan.`,
      'Walk-in Berhasil'
    )
  }, 400)
}

const cetakTiket = () => {
  triggerToast(
    'Tiket Walk-in Berhasil Dicetak!',
    `Berhasil mencetak tiket untuk pasien walk-in (${registeredPatient.value.namaLengkap} - No. Antrean ${registeredPatient.value.noAntreanPoli}).`,
    'Cetak Tiket'
  )
}

// Logout
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
          <!-- Beranda -->
          <RouterLink
            to="/admin"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all no-underline text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]"
          >
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
              <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
            </svg>
            <span>Beranda</span>
          </RouterLink>

          <!-- Loket Pendaftaran (Aktif) -->
          <button
            type="button"
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-bold text-left transition-all cursor-default border-0 bg-[#6D314F] text-white shadow-sm"
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
            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-[12px] text-[13px] font-semibold text-left transition-all cursor-pointer border-0 bg-transparent text-[#55464D] hover:bg-[#FCE4EC] hover:text-[#6D314F]"
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
    <!-- 2. KONTEN UTAMA LOKET PENDAFTARAN               -->
    <!-- ============================================== -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- 2.1 Top Bar Header: Profil Resepsionis -->
      <header class="w-full bg-transparent px-6 lg:px-8 py-4 flex items-center justify-end box-border">
        <div class="flex items-center gap-2.5 bg-white border border-[#F2CEDD] rounded-full px-3 py-1.5 shadow-xs">
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
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h1 class="text-[22px] md:text-[24px] font-black text-[#1F1A1D] m-0 tracking-tight">
              Loket Pendaftaran Pasien Walk-in
            </h1>
          </div>
        </div>

        <!-- FORMULIR PENDAFTARAN PASIEN WALK-IN -->
        <section class="bg-white rounded-[18px] border border-[#F4E2EB] p-6 shadow-[0_2px_12px_rgba(109,49,79,0.03)] max-w-4xl">
          <div class="flex items-center gap-2.5 pb-3 mb-4 border-b border-[#F8E9F0]">
            <div class="w-8 h-8 rounded-xl bg-[#FCE4EC] text-[#6D314F] flex items-center justify-center shrink-0">
              <svg class="w-4.5 h-4.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <line x1="19" y1="8" x2="19" y2="14" />
                <line x1="22" y1="11" x2="16" y2="11" />
              </svg>
            </div>
            <div>
              <h2 class="text-[16px] font-black text-[#1F1A1D] m-0">Formulir Pendaftaran Pasien Walk-in (Daftar Langsung)</h2>
              <p class="text-[12px] text-[#7E7379] m-0 mt-0.5">Masukkan data pasien umum atau pasien baru yang datang langsung tanpa reservasi daring.</p>
            </div>
          </div>

          <form @submit.prevent="submitWalkin" class="flex flex-col gap-4 text-[13px]">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nama Pasien -->
              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">
                  Nama Lengkap Pasien <span class="text-[#DC2626]">*</span>
                </label>
                <input
                  v-model="walkinForm.nama"
                  type="text"
                  required
                  placeholder="Contoh: Siti Rahmawati / Bambang Sutrisno"
                  class="w-full px-3 py-2.5 rounded-[10px] border border-[#E5D7DF] font-medium outline-none focus:border-[#6D314F] text-[#1F1A1D]"
                />
              </div>

              <!-- NIK KTP -->
              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">
                  Nomor Induk Kependudukan (NIK) <span class="text-[#DC2626]">*</span>
                </label>
                <input
                  v-model="walkinForm.nik"
                  type="text"
                  maxlength="16"
                  required
                  placeholder="16 digit NIK KTP"
                  class="w-full px-3 py-2.5 rounded-[10px] border border-[#E5D7DF] font-mono font-medium outline-none focus:border-[#6D314F] text-[#1F1A1D]"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Nomor Telepon / WhatsApp -->
              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">
                  Nomor WhatsApp / Telepon Aktif
                </label>
                <input
                  v-model="walkinForm.telepon"
                  type="text"
                  placeholder="Contoh: 0812xxxxxxx"
                  class="w-full px-3 py-2.5 rounded-[10px] border border-[#E5D7DF] font-medium outline-none focus:border-[#6D314F] text-[#1F1A1D]"
                />
              </div>

              <!-- Poli Tujuan -->
              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">
                  Poli Tujuan <span class="text-[#DC2626]">*</span>
                </label>
                <div class="relative flex items-center">
                  <select
                    v-model="walkinForm.poli"
                    class="w-full appearance-none pl-3.5 pr-10 py-2.5 rounded-[10px] border border-[#E5D7DF] font-medium outline-none focus:border-[#6D314F] bg-white text-[#1F1A1D] cursor-pointer"
                  >
                    <option value="Poli Umum">Poli Umum</option>
                    <option value="Poli Gigi & Mulut">Poli Gigi &amp; Mulut</option>
                    <option value="Poli Penyakit Dalam">Poli Penyakit Dalam</option>
                    <option value="Poli Spesialis Anak">Poli Spesialis Anak</option>
                    <option value="Poli Mata">Poli Mata</option>
                    <option value="Poli Kandungan">Poli Kandungan</option>
                  </select>
                  <div class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="6 9 12 15 18 9" />
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Dokter Praktik -->
              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">
                  Dokter Praktik <span class="text-[#DC2626]">*</span>
                </label>
                <div class="relative flex items-center">
                  <select
                    v-model="walkinForm.dokter"
                    class="w-full appearance-none pl-3.5 pr-10 py-2.5 rounded-[10px] border border-[#E5D7DF] font-medium outline-none focus:border-[#6D314F] bg-white text-[#1F1A1D] cursor-pointer"
                  >
                    <option value="dr. Hendra Wijaya">dr. Hendra Wijaya (Poli Umum / Anak)</option>
                    <option value="drg. Rina Permata, Sp.Ort">drg. Rina Permata, Sp.Ort</option>
                    <option value="dr. Sarah Amanda, Sp.PD">dr. Sarah Amanda, Sp.PD</option>
                    <option value="dr. Anita Kusuma, Sp.M">dr. Anita Kusuma, Sp.M</option>
                    <option value="dr. Nurul Hidayati, Sp.OG">dr. Nurul Hidayati, Sp.OG</option>
                  </select>
                  <div class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center text-[#6D314F]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="6 9 12 15 18 9" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Keluhan Singkat -->
            <div>
              <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">
                Keluhan Utama / Gejala Singkat Pasien
              </label>
              <textarea
                v-model="walkinForm.keluhan"
                rows="2"
                placeholder="Tuliskan keluhan atau gejala pasien untuk catatan triase awal..."
                class="w-full px-3 py-2.5 rounded-[10px] border border-[#E5D7DF] font-medium outline-none focus:border-[#6D314F] resize-none text-[#1F1A1D]"
              ></textarea>
            </div>

            <!-- Tombol Submit -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <button
                type="submit"
                :disabled="isSubmitting"
                class="w-full sm:w-auto px-6 py-3 bg-[#6D314F] text-white font-extrabold rounded-[12px] text-[13.5px] hover:bg-[#55233C] cursor-pointer border-0 shadow-sm transition-all flex items-center justify-center gap-2"
              >
                <svg v-if="!isSubmitting" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <polyline points="16 11 18 13 22 9" />
                </svg>
                <span v-if="!isSubmitting">Daftarkan Pasien &amp; Terbitkan Nomor Antrean</span>
                <span v-else>Memproses Pendaftaran...</span>
              </button>
            </div>
          </form>
        </section>

        <!-- ============================================== -->
        <!-- 3. RINGKASAN PASIEN TERDAFTAR DI LOKET         -->
        <!-- ============================================== -->
        <section v-if="hasRegistered" class="bg-white rounded-[18px] border border-[#F4E2EB] p-5 shadow-[0_2px_12px_rgba(109,49,79,0.03)] flex flex-col gap-4 max-w-4xl">
          <!-- Header Ringkasan -->
          <div class="flex items-center justify-between pb-3 border-b border-dashed border-[#F2CEDD]">
            <div class="flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-[#FCE4EC] text-[#6D314F] flex items-center justify-center">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                  <polyline points="9 12 11 14 15 10" />
                </svg>
              </div>
              <div class="flex flex-col">
                <h2 class="text-[14.5px] font-black text-[#1F1A1D] m-0">Ringkasan Pasien Terdaftar di Loket</h2>
                <span class="text-[11px] text-[#7E7379]">Waktu Pendaftaran: {{ registeredPatient.waktuPendaftaran }}</span>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <!-- Tombol Cetak Tiket -->
              <button
                type="button"
                @click="cetakTiket"
                class="px-3 py-1 rounded-[8px] bg-white border border-[#D8A3BC] hover:bg-[#FDEBF3] text-[#6D314F] text-[11.5px] font-bold cursor-pointer transition-colors shadow-2xs flex items-center gap-1.5"
              >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 6 2 18 2 18 9" />
                  <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                  <rect x="6" y="14" width="12" height="8" />
                </svg>
                <span>Cetak Tiket</span>
              </button>

              <!-- Badge Terdaftar di Loket -->
              <span class="bg-[#FFE4EE] text-[#9F1239] text-[11.5px] font-extrabold px-3 py-1 rounded-full inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#E11D48]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Terdaftar di Loket</span>
              </span>
            </div>
          </div>

          <!-- Banner Merah Muda: Nomor Antrean Poliklinik & Ruang Tunggu -->
          <div class="bg-[#FCEEF4] rounded-[14px] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border border-[#F8D6E6]">
            <div>
              <span class="text-[10px] font-bold text-[#6D314F] uppercase tracking-wider block mb-0.5">
                Nomor Antrean Poliklinik Diterbitkan
              </span>
              <div class="flex items-baseline gap-2">
                <span class="text-[32px] font-black text-[#6D314F] leading-none">{{ registeredPatient.noAntreanPoli }}</span>
                <span class="text-[14px] font-extrabold text-[#1F1A1D]">/ {{ registeredPatient.poli }}</span>
              </div>
            </div>

            <div class="sm:text-right">
              <span class="text-[10px] font-bold text-[#7E7379] uppercase tracking-wider block mb-0.5">
                Lokasi Ruang Tunggu
              </span>
              <div class="flex items-center sm:justify-end gap-1.5 text-[13px] font-bold text-[#1F1A1D]">
                <svg class="w-4 h-4 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 20V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14" />
                  <path d="M2 20h20" />
                </svg>
                <span>{{ registeredPatient.lokasi }}</span>
              </div>
            </div>
          </div>

          <!-- Grid Rincian Detail Informasi Pasien -->
          <div class="bg-[#FFF7FA] rounded-[14px] p-4 border border-[#F4E2EB]">
            <span class="text-[10.5px] font-bold text-[#7E7379] uppercase tracking-wider block mb-3">
              Detail Informasi Pasien
            </span>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-3 gap-x-4 text-[12.5px]">
              <!-- Kolom 1 -->
              <div class="flex flex-col gap-2">
                <div>
                  <span class="text-[#7E7379] text-[11px] block">Nama Lengkap Pasien</span>
                  <strong class="text-[#1F1A1D] font-extrabold text-[13.5px]">{{ registeredPatient.namaLengkap }}</strong>
                </div>
                <div>
                  <span class="text-[#7E7379] text-[11px] block">Poliklinik &amp; Dokter</span>
                  <p class="text-[#1F1A1D] font-bold m-0 leading-tight">{{ registeredPatient.spesialis }}</p>
                  <span class="text-[#55464D] text-[11.5px]">{{ registeredPatient.dokter }}</span>
                </div>
              </div>

              <!-- Kolom 2 -->
              <div class="flex flex-col gap-2">
                <div>
                  <span class="text-[#7E7379] text-[11px] block">Nomor Rekam Medis (No. RM)</span>
                  <strong class="text-[#1F1A1D] font-extrabold font-mono text-[13.5px]">{{ registeredPatient.noRm }}</strong>
                </div>
                <div>
                  <span class="text-[#7E7379] text-[11px] block">Jadwal &amp; Sesi Praktik</span>
                  <strong class="text-[#1F1A1D] font-bold">{{ registeredPatient.jadwal }}</strong>
                </div>
              </div>

              <!-- Kolom 3 -->
              <div class="flex flex-col gap-2">
                <div>
                  <span class="text-[#7E7379] text-[11px] block">Nomor NIK KTP</span>
                  <strong class="text-[#1F1A1D] font-extrabold font-mono text-[13.5px]">{{ registeredPatient.nik }}</strong>
                </div>
                <div>
                  <span class="text-[#7E7379] text-[11px] block">Kontak Pasien</span>
                  <div class="flex items-center gap-1.5 text-[#55464D]">
                    <svg class="w-3.5 h-3.5 text-[#6D314F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    <span class="truncate font-medium">{{ registeredPatient.telepon }}</span>
                  </div>
                </div>
              </div>
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
