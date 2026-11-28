<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PatientHeader from '@/components/PatientHeader.vue'
import PatientBottomNav from '@/components/PatientBottomNav.vue'

const router = useRouter()

// Data Profil Pasien
const profil = ref({
  namaLengkap: 'Rahmawati Soebandono',
  nik: '3172345678987',
  whatsapp: '+62 812-1234-5678',
  email: 'rahmawati.soebandono@gmail.com',
  tanggalLahir: '14 Agustus 1990',
  usia: '34 Tahun',
  alamat: 'Jl. Sudirman No. 45, RT 04 / RW 02, Jakarta Selatan',
  foto: ''
})

// State File Upload Foto Profil
const fileInputRef = ref(null)

// State Toast Notifikasi
const showToast = ref(false)
const toastTitle = ref('Perubahan Berhasil Disimpan!')
const toastMessage = ref('')
const toastTag = ref('Profil')

const triggerToast = (title, message, tag = 'Profil') => {
  toastTitle.value = title
  toastMessage.value = message
  toastTag.value = tag
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 4500)
}

// State Modal Konfirmasi Keluar Akun (Logout)
const isLogoutModalOpen = ref(false)

const handleLogout = () => {
  isLogoutModalOpen.value = true
}

const cancelLogout = () => {
  isLogoutModalOpen.value = false
}

const confirmLogout = () => {
  isLogoutModalOpen.value = false
  localStorage.removeItem('kliniku_active_user')
  router.push('/login')
}

const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click()
  }
}

const handlePhotoUpload = (e) => {
  const file = e.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('Harap pilih file gambar (JPG, PNG, WebP)!')
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    alert('Ukuran gambar maksimal 5MB!')
    return
  }

  const reader = new FileReader()
  reader.onload = (event) => {
    const base64Data = event.target?.result
    if (base64Data) {
      profil.value.foto = base64Data
      try {
        localStorage.setItem('kliniku_patient_profile', JSON.stringify(profil.value))
        window.dispatchEvent(new Event('kliniku-profile-updated'))
      } catch (err) {
        console.error('Gagal menyimpan foto profil:', err)
      }
      triggerToast('Perubahan Berhasil Disimpan!', 'Foto profil akun Anda telah berhasil diperbarui.', 'Foto Profil')
    }
  }
  reader.readAsDataURL(file)
  e.target.value = ''
}

const removePhoto = () => {
  profil.value.foto = ''
  try {
    localStorage.setItem('kliniku_patient_profile', JSON.stringify(profil.value))
    window.dispatchEvent(new Event('kliniku-profile-updated'))
  } catch (err) {
    console.error('Gagal menghapus foto profil:', err)
  }
  triggerToast('Perubahan Berhasil Disimpan!', 'Foto profil telah dihapus dan dikembalikan ke avatar default.', 'Foto Profil')
}

// State Modal Ubah Data
const isEditModalOpen = ref(false)
const editForm = ref({ ...profil.value })

// State Modal Keamanan Akun
const isSecurityModalOpen = ref(false)
const passwordForm = ref({
  passwordLama: '',
  passwordBaru: '',
  konfirmasiPassword: ''
})

onMounted(() => {
  try {
    const saved = localStorage.getItem('kliniku_patient_profile')
    if (saved) {
      profil.value = { ...profil.value, ...JSON.parse(saved) }
    } else {
      const activeUser = localStorage.getItem('kliniku_active_user')
      if (activeUser) {
        const parsed = JSON.parse(activeUser)
        if (parsed.namaLengkap) profil.value.namaLengkap = parsed.namaLengkap
        if (parsed.nik) profil.value.nik = parsed.nik
        if (parsed.email) profil.value.email = parsed.email
        if (parsed.alamat) profil.value.alamat = parsed.alamat
      }
    }
  } catch (e) {
    console.error('Gagal memuat data profil:', e)
  }
})

// Buka Modal Edit
const openEditModal = () => {
  editForm.value = { ...profil.value }
  isEditModalOpen.value = true
}

// Simpan Perubahan Data
const saveProfile = () => {
  profil.value = { ...editForm.value }
  try {
    localStorage.setItem('kliniku_patient_profile', JSON.stringify(profil.value))
    // Sinkronkan juga active_user jika ada perubahan nama/nik/alamat/email
    const active = JSON.parse(localStorage.getItem('kliniku_active_user') || '{}')
    active.namaLengkap = profil.value.namaLengkap
    active.nik = profil.value.nik
    active.email = profil.value.email
    active.alamat = profil.value.alamat
    localStorage.setItem('kliniku_active_user', JSON.stringify(active))
  } catch (e) {
    console.error('Gagal menyimpan profil:', e)
  }
  isEditModalOpen.value = false
  triggerToast('Perubahan Berhasil Disimpan!', 'Data pribadi akun Anda telah berhasil diperbarui.', 'Data Pribadi')
}

// Buka Modal Keamanan
const openSecurityModal = () => {
  passwordForm.value = { passwordLama: '', passwordBaru: '', konfirmasiPassword: '' }
  isSecurityModalOpen.value = true
}

// Simpan Sandi Baru
const saveSecurity = () => {
  if (!passwordForm.value.passwordLama || !passwordForm.value.passwordBaru) {
    alert('Harap isi kata sandi dengan lengkap!')
    return
  }
  if (passwordForm.value.passwordBaru !== passwordForm.value.konfirmasiPassword) {
    alert('Konfirmasi kata sandi tidak cocok!')
    return
  }
  isSecurityModalOpen.value = false
  triggerToast('Perubahan Berhasil Disimpan!', 'Kata sandi akun Anda telah berhasil diperbarui.', 'Keamanan')
}

// Hubungi Bantuan Klinik
const handleCallClinic = () => {
  if (confirm('Hubungi Layanan Bantuan KLINIKU di 0800-1-KLINIK?')) {
    window.location.href = 'tel:08001554645'
  }
}
</script>

<template>
  <div class="w-full min-h-screen bg-[#FFF7FA] flex flex-col relative">
    <!-- Pop Up Toast Notifikasi Perubahan Berhasil Disimpan di Profil (Teleport to body agar berada di atas semua modal) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-6 opacity-0 scale-95"
        leave-active-class="transition-all duration-200 ease-in"
        leave-to-class="-translate-y-6 opacity-0 scale-95"
      >
        <div
          v-if="showToast"
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
                <span class="text-[12.5px] font-extrabold text-[#1F1A1D] whitespace-nowrap">{{ toastTitle }}</span>
                <span class="px-2 py-0.2 bg-[#EAFBF3] text-[#059669] text-[9.5px] font-bold rounded-full whitespace-nowrap">{{ toastTag }}</span>
              </div>
              <span class="text-[11.5px] text-[#5C5257] leading-tight mt-0.5 break-words">{{ toastMessage }}</span>
            </div>
          </div>

          <button
            type="button"
            @click="showToast = false"
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

    <!-- Top Sticky Header KLINIKU -->
    <PatientHeader />

    <!-- Konten Utama Halaman Profil Pasien -->
    <main class="p-[20px_18px_90px] flex flex-col items-center box-border">
      <!-- 1. Lingkaran Foto Avatar Profil dengan Aksi Ubah / Tambah Foto -->
      <div class="relative w-[114px] h-[114px] mb-2 group">
        <!-- Avatar Preview Circle -->
        <div
          @click="triggerFileInput"
          class="w-[114px] h-[114px] rounded-full bg-[#D9D9D9] shadow-[0_2px_8px_rgba(0,0,0,0.06)] overflow-hidden cursor-pointer border-2 border-white flex items-center justify-center transition-all hover:ring-2 hover:ring-[#6D314F]/40"
          title="Klik untuk mengubah atau menambahkan foto profil"
          role="button"
          tabindex="0"
          aria-label="Ubah foto profil"
        >
          <img
            v-if="profil.foto"
            :src="profil.foto"
            alt="Foto Profil Pasien"
            class="w-full h-full object-cover"
          />
          <svg
            v-else
            class="w-14 h-14 text-[#BDBDBD]"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
        </div>

        <!-- Tombol Aksi Kamera di Sudut Kanan Bawah Avatar -->
        <button
          type="button"
          @click="triggerFileInput"
          class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#6D314F] hover:bg-[#58253E] text-white flex items-center justify-center shadow-md border-2 border-white transition-all transform active:scale-90 cursor-pointer"
          title="Ubah / Tambah Foto Profil"
          aria-label="Tombol ubah foto profil"
        >
          <!-- Camera Icon -->
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
            <circle cx="12" cy="13" r="4" />
          </svg>
        </button>

        <!-- Hidden File Input -->
        <input
          ref="fileInputRef"
          type="file"
          accept="image/png, image/jpeg, image/jpg, image/webp"
          class="hidden"
          @change="handlePhotoUpload"
        />
      </div>

      <!-- Aksi Hapus Foto jika sudah ada foto tersimpan -->
      <div v-if="profil.foto" class="mb-2">
        <button
          type="button"
          @click="removePhoto"
          class="text-[11.5px] text-[#A64B74] hover:text-[#6D314F] font-semibold flex items-center gap-1 cursor-pointer bg-transparent border-0 underline"
        >
          <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
          </svg>
          <span>Hapus Foto Profil</span>
        </button>
      </div>

      <!-- 2. Nama Lengkap Pasien -->
      <h1 class="text-[20px] font-extrabold text-[#1F1A1D] mb-5 tracking-[-0.2px] text-center">
        {{ profil.namaLengkap }}
      </h1>

      <div class="w-full flex flex-col gap-4">
        <!-- 3. Kartu: Data Pribadi -->
        <section class="bg-white rounded-[18px] border border-[#F5E2EC] shadow-[0_2px_12px_rgba(109,49,79,0.03)] p-4 flex flex-col gap-3">
          <!-- Header Kartu Data Pribadi -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-[#1F1A1D]">
              <!-- User Outline Icon -->
              <svg class="w-[18px] h-[18px] text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4" />
                <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2" />
              </svg>
              <h2 class="text-[15px] font-extrabold m-0 leading-none">Data Pribadi</h2>
            </div>

            <!-- Tombol Ubah Data -->
            <button
              type="button"
              @click="openEditModal"
              class="bg-[#FDEBF3] text-[#6D314F] px-3 py-1 rounded-full text-[11.5px] font-bold flex items-center gap-1.5 hover:bg-[#FADCEB] transition-colors cursor-pointer border-0"
            >
              <!-- Pencil Icon -->
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
              </svg>
              <span>Ubah Data</span>
            </button>
          </div>

          <!-- Daftar Informasi Data Pribadi -->
          <div class="flex flex-col gap-2.5">
            <!-- Item 1: NIK -->
            <div class="bg-[#FDEEF4] rounded-[12px] p-[10px_12px] flex items-center gap-3">
              <div class="w-10 h-10 rounded-[10px] bg-[#E59ABF] flex items-center justify-center shrink-0 text-white shadow-sm">
                <!-- ID Card Icon -->
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="5" width="20" height="14" rx="2" />
                  <line x1="2" y1="10" x2="22" y2="10" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-[11.5px] font-bold text-[#1F1A1D] leading-tight">Nomor Induk Kependudukan (NIK)</span>
                <span class="text-[13px] font-medium text-[#1F1A1D] leading-normal mt-0.5">{{ profil.nik }}</span>
              </div>
            </div>

            <!-- Item 2: Nomor WhatsApp -->
            <div class="bg-[#FDEEF4] rounded-[12px] p-[10px_12px] flex items-center gap-3">
              <div class="w-10 h-10 rounded-[10px] bg-[#E59ABF] flex items-center justify-center shrink-0 text-white shadow-sm">
                <!-- Chat Message Icon -->
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                  <line x1="8" y1="9" x2="16" y2="9" />
                  <line x1="8" y1="13" x2="14" y2="13" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-[11.5px] font-bold text-[#1F1A1D] leading-tight">Nomor WhatsApp Aktif</span>
                <span class="text-[13px] font-medium text-[#1F1A1D] leading-normal mt-0.5">{{ profil.whatsapp }}</span>
              </div>
            </div>

            <!-- Item 3: Email Aktif -->
            <div class="bg-[#FDEEF4] rounded-[12px] p-[10px_12px] flex items-center gap-3">
              <div class="w-10 h-10 rounded-[10px] bg-[#E59ABF] flex items-center justify-center shrink-0 text-white shadow-sm">
                <!-- Mail Icon -->
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="4" width="20" height="16" rx="2" />
                  <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-[11.5px] font-bold text-[#1F1A1D] leading-tight">Email Aktif</span>
                <span class="text-[13px] font-medium text-[#1F1A1D] leading-normal mt-0.5 truncate">{{ profil.email }}</span>
              </div>
            </div>

            <!-- Item 4: Tanggal Lahir & Usia -->
            <div class="bg-[#FDEEF4] rounded-[12px] p-[10px_12px] flex items-center gap-3">
              <div class="w-10 h-10 rounded-[10px] bg-[#E59ABF] flex items-center justify-center shrink-0 text-white shadow-sm">
                <!-- Calendar Icon -->
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                  <line x1="16" y1="2" x2="16" y2="6" />
                  <line x1="8" y1="2" x2="8" y2="6" />
                  <line x1="3" y1="10" x2="21" y2="10" />
                  <circle cx="8" cy="14" r="1" fill="currentColor" />
                  <circle cx="12" cy="14" r="1" fill="currentColor" />
                  <circle cx="16" cy="14" r="1" fill="currentColor" />
                  <circle cx="8" cy="18" r="1" fill="currentColor" />
                  <circle cx="12" cy="18" r="1" fill="currentColor" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-[11.5px] font-bold text-[#1F1A1D] leading-tight">Tanggal Lahir &amp; Usia</span>
                <span class="text-[13px] font-medium text-[#1F1A1D] leading-normal mt-0.5">{{ profil.tanggalLahir }} ({{ profil.usia }})</span>
              </div>
            </div>

            <!-- Item 4: Alamat Domisili -->
            <div class="bg-[#FDEEF4] rounded-[12px] p-[10px_12px] flex items-center gap-3">
              <div class="w-10 h-10 rounded-[10px] bg-[#E59ABF] flex items-center justify-center shrink-0 text-white shadow-sm">
                <!-- Map Pin Icon -->
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-[11.5px] font-bold text-[#1F1A1D] leading-tight">Alamat Domisili</span>
                <span class="text-[12.5px] font-medium text-[#1F1A1D] leading-snug mt-0.5">{{ profil.alamat }}</span>
              </div>
            </div>
          </div>
        </section>

        <!-- 4. Kartu: Pengaturan & Bantuan -->
        <section class="bg-white rounded-[18px] border border-[#F5E2EC] shadow-[0_2px_12px_rgba(109,49,79,0.03)] p-4 flex flex-col gap-3">
          <!-- Header Kartu Pengaturan & Bantuan -->
          <div class="flex items-center gap-2 text-[#1F1A1D]">
            <!-- Sliders / Settings Icon -->
            <svg class="w-[18px] h-[18px] text-[#1F1A1D]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
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
            <h2 class="text-[15px] font-extrabold m-0 leading-none">Pengaturan &amp; Bantuan</h2>
          </div>

          <!-- Daftar Menu Pengaturan & Bantuan -->
          <div class="flex flex-col gap-2.5">
            <!-- Menu 1: Keamanan Akun & Sandi -->
            <button
              type="button"
              @click="openSecurityModal"
              class="w-full bg-[#FDEEF4] rounded-[12px] p-[10px_12px] flex items-center gap-3 text-left border-0 cursor-pointer hover:bg-[#FADCEB] transition-colors"
            >
              <div class="w-10 h-10 rounded-[10px] bg-[#E59ABF] flex items-center justify-center shrink-0 text-white shadow-sm">
                <!-- Padlock Icon -->
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-[12px] font-bold text-[#1F1A1D] leading-tight">Keamanan Akun &amp; Sandi</span>
                <span class="text-[11.5px] font-medium text-[#55464D] leading-normal mt-0.5">PIN Transaksi &amp; Kata Sandi</span>
              </div>
            </button>

            <!-- Menu 2: Bantuan & Hubungi Klinik -->
            <button
              type="button"
              @click="handleCallClinic"
              class="w-full bg-[#FDEEF4] rounded-[12px] p-[10px_12px] flex items-center gap-3 text-left border-0 cursor-pointer hover:bg-[#FADCEB] transition-colors"
            >
              <div class="w-10 h-10 rounded-[10px] bg-[#E59ABF] flex items-center justify-center shrink-0 text-white shadow-sm">
                <!-- Headset Customer Support Icon -->
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
                  <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
                  <path d="M16 19a4 4 0 0 1-4 4h-1" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-[12px] font-bold text-[#1F1A1D] leading-tight">Bantuan &amp; Hubungi Klinik</span>
                <span class="text-[11.5px] font-medium text-[#55464D] leading-normal mt-0.5">0800-1-KLINIK (24 Jam)</span>
              </div>
            </button>
          </div>
        </section>

        <!-- 5. Tombol Keluar dari Akun (Logout) -->
        <button
          type="button"
          @click="handleLogout"
          class="w-full bg-[#6D314F] text-white border-0 rounded-[12px] p-[13px_18px] text-[15px] font-bold cursor-pointer flex items-center justify-center gap-2.5 transition-all duration-200 hover:bg-[#55233C] active:scale-[0.985] shadow-[0_2px_8px_rgba(109,49,79,0.15)] mt-1"
        >
          <!-- Exit Door Icon -->
          <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
          </svg>
          <span>Keluar dari Akun</span>
        </button>
      </div>
    </main>

    <!-- Modal Ubah Data Pribadi -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isEditModalOpen"
          class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-[2px]"
          @click.self="isEditModalOpen = false"
        >
          <div class="w-full max-w-[400px] bg-white rounded-[20px] shadow-2xl overflow-hidden flex flex-col animate-scale-up">
            <div class="p-[16px_20px] border-b border-[#F4E2EB] flex items-center justify-between bg-[#FFF7FA]">
              <h3 class="text-[15px] font-extrabold text-[#1F1A1D] m-0">Ubah Data Pribadi</h3>
              <button
                type="button"
                @click="isEditModalOpen = false"
                class="w-8 h-8 rounded-full bg-white border border-[#E5D7DF] flex items-center justify-center text-[#7E7379] hover:text-[#1F1A1D] cursor-pointer"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveProfile" class="p-[20px] flex flex-col gap-3 text-[13px]">
              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Nama Lengkap</label>
                <input
                  v-model="editForm.namaLengkap"
                  type="text"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Nomor Induk Kependudukan (NIK)</label>
                <input
                  v-model="editForm.nik"
                  type="text"
                  maxlength="16"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Nomor WhatsApp Aktif</label>
                <input
                  v-model="editForm.whatsapp"
                  type="text"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Email Aktif</label>
                <input
                  v-model="editForm.email"
                  type="email"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Tanggal Lahir</label>
                <input
                  v-model="editForm.tanggalLahir"
                  type="text"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Alamat Domisili</label>
                <textarea
                  v-model="editForm.alamat"
                  rows="2"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F] resize-none"
                ></textarea>
              </div>

              <div class="flex gap-2 mt-2">
                <button
                  type="submit"
                  class="flex-1 bg-[#6D314F] text-white py-2.5 rounded-[10px] font-bold text-[13px] hover:bg-[#55233C] cursor-pointer border-0"
                >
                  Simpan Perubahan
                </button>
                <button
                  type="button"
                  @click="isEditModalOpen = false"
                  class="bg-[#F8D6E6] text-[#6D314F] px-4 py-2.5 rounded-[10px] font-bold text-[13px] hover:bg-[#F2CEDD] cursor-pointer border-0"
                >
                  Batal
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Modal Keamanan Akun & Sandi -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isSecurityModalOpen"
          class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-[2px]"
          @click.self="isSecurityModalOpen = false"
        >
          <div class="w-full max-w-[400px] bg-white rounded-[20px] shadow-2xl overflow-hidden flex flex-col animate-scale-up">
            <div class="p-[16px_20px] border-b border-[#F4E2EB] flex items-center justify-between bg-[#FFF7FA]">
              <h3 class="text-[15px] font-extrabold text-[#1F1A1D] m-0">Keamanan Akun &amp; Sandi</h3>
              <button
                type="button"
                @click="isSecurityModalOpen = false"
                class="w-8 h-8 rounded-full bg-white border border-[#E5D7DF] flex items-center justify-center text-[#7E7379] hover:text-[#1F1A1D] cursor-pointer"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveSecurity" class="p-[20px] flex flex-col gap-3 text-[13px]">
              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Kata Sandi Saat Ini</label>
                <input
                  v-model="passwordForm.passwordLama"
                  type="password"
                  placeholder="Masukkan kata sandi lama"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Kata Sandi Baru</label>
                <input
                  v-model="passwordForm.passwordBaru"
                  type="password"
                  placeholder="Minimal 8 karakter"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div>
                <label class="block text-[11.5px] font-bold text-[#55464D] mb-1">Konfirmasi Kata Sandi Baru</label>
                <input
                  v-model="passwordForm.konfirmasiPassword"
                  type="password"
                  placeholder="Ulangi kata sandi baru"
                  required
                  class="w-full p-2.5 rounded-[10px] border border-[#E5D7DF] bg-[#FFF7FA] text-[#1F1A1D] font-medium outline-none focus:border-[#6D314F]"
                />
              </div>

              <div class="flex gap-2 mt-2">
                <button
                  type="submit"
                  class="flex-1 bg-[#6D314F] text-white py-2.5 rounded-[10px] font-bold text-[13px] hover:bg-[#55233C] cursor-pointer border-0"
                >
                  Ubah Kata Sandi
                </button>
                <button
                  type="button"
                  @click="isSecurityModalOpen = false"
                  class="bg-[#F8D6E6] text-[#6D314F] px-4 py-2.5 rounded-[10px] font-bold text-[13px] hover:bg-[#F2CEDD] cursor-pointer border-0"
                >
                  Batal
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Pop Up Modal Konfirmasi Keluar dari Akun (Logout) -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isLogoutModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-[2px]"
        @click.self="cancelLogout"
      >
        <div class="bg-white rounded-[24px] border border-[#F2CEDD] p-6 w-full max-w-[340px] shadow-[0_16px_40px_rgba(109,49,79,0.25)] flex flex-col items-center text-center box-border transform transition-all">
          <!-- Lingkaran Icon Logout Merah / Plum -->
          <div class="w-14 h-14 rounded-full bg-[#FEE2E2] border-[3px] border-[#FECACA] text-[#DC2626] flex items-center justify-center mb-3.5 shadow-sm">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
          </div>

          <h3 class="text-[19px] font-extrabold text-[#1F1A1D] mb-1.5 tracking-[-0.2px]">
            Keluar dari Akun?
          </h3>

          <p class="text-[13px] text-[#5C5257] leading-relaxed mb-5">
            Apakah Anda yakin ingin keluar dari akun KLINIKU? Anda perlu masuk kembali untuk mengakses tiket antrean dan rekam medis.
          </p>

          <div class="grid grid-cols-2 gap-3 w-full">
            <!-- Tombol Batal -->
            <button
              type="button"
              @click="cancelLogout"
              class="w-full py-2.5 px-3 rounded-[12px] border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-bold text-[13.5px] cursor-pointer transition-colors"
            >
              Batal
            </button>

            <!-- Tombol Ya / Keluar -->
            <button
              type="button"
              @click="confirmLogout"
              class="w-full py-2.5 px-3 rounded-[12px] bg-[#DC2626] hover:bg-[#B91C1C] text-white font-bold text-[13.5px] cursor-pointer shadow-sm transition-all transform active:scale-95 border-0"
            >
              Ya
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Bottom Navigation Bar (Active di Profil) -->
    <PatientBottomNav />
  </div>
</template>
