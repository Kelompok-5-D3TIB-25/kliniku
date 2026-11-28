<script setup>
import { ref, onMounted } from 'vue'
import PatientHeader from '@/components/PatientHeader.vue'
import PatientBottomNav from '@/components/PatientBottomNav.vue'

// State Modal Detail Rekam Medis
const selectedRecord = ref(null)
const isModalOpen = ref(false)
const showDownloadToast = ref(false)
const downloadToastMessage = ref('')

// Data Riwayat Kunjungan Pasien
const defaultVisits = [
  {
    id: 'vis-1',
    tanggal: '24 Okt 2026, 09:30 WIB',
    status: 'Selesai',
    poli: 'Poli Penyakit Dalam',
    dokter: 'dr. Sarah Amanda, Sp.PD',
    diagnosa: 'Dispepsia Fungsional & Gastritis Akut',
    noRm: 'RM-20261024-001',
    keluhan: 'Nyeri ulu hati terasa perih dan kembung sejak 3 hari yang lalu, disertai mual dan rasa tidak nyaman setelah makan.',
    tandaVital: {
      tekananDarah: '120/80 mmHg',
      nadi: '78 x/menit',
      suhu: '36.6 °C',
      beratBadan: '56 kg'
    },
    diagnosaDetail: [
      { kode: 'K29.7', nama: 'Gastritis, unspecified (Maag akut)' },
      { kode: 'K30', nama: 'Functional Dyspepsia' }
    ],
    tindakan: [
      'Pemeriksaan fisik abdomen (palpasi nyeri tekan epigastrium)',
      'Konsultasi dan edukasi diet lambung rendah asam'
    ],
    resepObat: [
      { nama: 'Omeprazole 20 mg', aturan: '2 x 1 kapsul (30 menit sebelum makan)' },
      { nama: 'Antasida Doen Suspensi', aturan: '3 x 1 sendok takar (1 jam sebelum makan)' },
      { nama: 'Sucralfate Sirup 500mg/5ml', aturan: '3 x 1 sendok makan' }
    ],
    catatanDokter: 'Hindari makanan pedas, asam, kafein, dan santan. Makan teratur dengan porsi kecil tetapi sering. Kontrol ulang jika dalam 5 hari nyeri ulu hati tidak membaik.'
  },
  {
    id: 'vis-2',
    tanggal: '24 Okt 2026, 09:30 WIB',
    status: 'Selesai',
    poli: 'Poli Gigi & Mulut',
    dokter: 'drg. Rina Permata, Sp.Ort',
    diagnosa: 'Pulpitis Revisibel & Scaling Kalkulus',
    noRm: 'RM-20261024-002',
    keluhan: 'Gigi geraham kanan bawah terasa ngilu tajam saat minum air dingin dan gusi sering berdarah saat menyikat gigi.',
    tandaVital: {
      tekananDarah: '118/76 mmHg',
      nadi: '74 x/menit',
      suhu: '36.5 °C',
      beratBadan: '56 kg'
    },
    diagnosaDetail: [
      { kode: 'K04.0', nama: 'Reversible Pulpitis (Gigi 46)' },
      { kode: 'K05.0', nama: 'Calculus Gingivitis Akut' }
    ],
    tindakan: [
      'Pembersihan karang gigi (Scaling supra & subgingiva RA & RB)',
      'Aplikasi topikal fluoride varnish desensitisasi',
      'Penambalan sementara cavity protection (temporary filling)'
    ],
    resepObat: [
      { nama: 'Asam Mefenamat 500 mg', aturan: '3 x 1 tablet sesudah makan (bila nyeri)' },
      { nama: 'Chlorhexidine Gluconate 0.2% Obat Kumur', aturan: '2 x sehari sesudah sikat gigi pagi dan malam' }
    ],
    catatanDokter: 'Gunakan sikat gigi berbulu halus (soft). Hindari minuman dingin atau terlalu panas selama 48 jam. Jadwal kontrol penambalan permanen 1 minggu lagi.'
  },
  {
    id: 'vis-3',
    tanggal: '24 Okt 2026, 09:30 WIB',
    status: 'Selesai',
    poli: 'Poli Umum',
    dokter: 'dr. Hendra Wijaya',
    diagnosa: 'Faringitis Akut & Influenza',
    noRm: 'RM-20261024-003',
    keluhan: 'Tenggorokan sakit perih saat menelan, demam sumeng sejak 2 hari, hidung tersumbat, batuk kering, dan badan lemas.',
    tandaVital: {
      tekananDarah: '115/78 mmHg',
      nadi: '82 x/menit',
      suhu: '37.8 °C',
      beratBadan: '56 kg'
    },
    diagnosaDetail: [
      { kode: 'J02.9', nama: 'Acute pharyngitis, unspecified' },
      { kode: 'J11.1', nama: 'Influenza with other respiratory manifestations' }
    ],
    tindakan: [
      'Pemeriksaan fisik orofaring (tampak faring hiperemis)',
      'Auskultasi paru (vesikuler normal tanpa ronki)',
      'Edukasi istirahat, hidrasi air hangat, & isolasi mandiri ringan'
    ],
    resepObat: [
      { nama: 'Paracetamol 500 mg', aturan: '3 x 1 tablet sesudah makan (bila demam/pusing)' },
      { nama: 'Cetirizine 10 mg', aturan: '1 x 1 tablet malam hari' },
      { nama: 'Tablet Hisap Dequalinium Chloride', aturan: '3 x 1 tablet hisap perlahan' },
      { nama: 'Vitamin C 500 mg', aturan: '1 x 1 tablet setelah sarapan' }
    ],
    catatanDokter: 'Istirahat tirah baring minimal 2 hari. Minum air putih hangat minimal 2 liter/hari. Gunakan masker saat berinteraksi dengan keluarga di rumah.'
  }
]

// State List Kunjungan
const visitsList = ref([])

onMounted(() => {
  // Ambil data kunjungan yang selesai dari default, ditambah kunjungan yang tersimpan di localStorage jika ada
  try {
    const savedCompleted = JSON.parse(localStorage.getItem('kliniku_completed_visits') || '[]')
    if (savedCompleted.length > 0) {
      visitsList.value = [...savedCompleted, ...defaultVisits]
    } else {
      visitsList.value = [...defaultVisits]
    }
  } catch (e) {
    visitsList.value = [...defaultVisits]
  }
})

// Buka Modal Rincian Rekam Medis
const openDetail = (visit) => {
  selectedRecord.value = visit
  isModalOpen.value = true
  document.body.style.overflow = 'hidden'
}

// Tutup Modal
const closeModal = () => {
  isModalOpen.value = false
  selectedRecord.value = null
  document.body.style.overflow = ''
}

// Simulasi Unduh Resume Medis Elektronik
const downloadResume = () => {
  const rmNo = selectedRecord.value?.noRm || 'RM-KLINIKU'
  downloadToastMessage.value = `Resume Medis Elektronik (${rmNo}) berhasil diunduh dalam format PDF!`
  showDownloadToast.value = true
  setTimeout(() => {
    showDownloadToast.value = false
  }, 4500)
}
</script>

<template>
  <div class="w-full min-h-screen bg-[#FFF7FA] flex flex-col relative">
    <!-- Pop Up Toast Notifikasi Resume Medis Elektronik Berhasil Diunduh (Teleport to body agar berada di atas modal) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="-translate-y-6 opacity-0 scale-95"
        leave-active-class="transition-all duration-200 ease-in"
        leave-to-class="-translate-y-6 opacity-0 scale-95"
      >
        <div
          v-if="showDownloadToast"
          class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-fit max-w-[calc(100vw-32px)] sm:max-w-md bg-white border border-[#A7F3D0] rounded-[16px] p-[10px_16px] shadow-[0_14px_36px_rgba(0,0,0,0.22),0_4px_12px_rgba(16,185,129,0.18)] flex items-center justify-between gap-3 box-border"
          role="alert"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-full bg-[#EAFBF3] border border-[#A7F3D0] text-[#10B981] flex items-center justify-center shrink-0 shadow-xs">
              <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
              </svg>
            </div>
            <div class="flex flex-col min-w-0 text-left">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[12.5px] font-extrabold text-[#1F1A1D] whitespace-nowrap">Unduh Dokumen Berhasil!</span>
                <span class="px-2 py-0.2 bg-[#EAFBF3] text-[#059669] text-[9.5px] font-bold rounded-full whitespace-nowrap">PDF RME</span>
              </div>
              <span class="text-[11.5px] text-[#5C5257] leading-tight mt-0.5 break-words">{{ downloadToastMessage }}</span>
            </div>
          </div>

          <button
            type="button"
            @click="showDownloadToast = false"
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

    <!-- Konten Utama Riwayat Kunjungan -->
    <main class="p-[20px_18px_90px] flex flex-col box-border">
      <!-- Judul Halaman -->
      <h1 class="text-[20px] font-extrabold text-[#1F1A1D] mb-4 tracking-[-0.2px]">
        Riwayat Kunjungan
      </h1>

      <!-- Daftar Kartu Kunjungan Selesai -->
      <div class="flex flex-col gap-4">
        <article
          v-for="visit in visitsList"
          :key="visit.id"
          class="bg-white rounded-[16px] border border-[#F4E2EB] shadow-[0_2px_12px_rgba(109,49,79,0.03)] p-4 flex flex-col gap-3 transition-all duration-150 hover:shadow-[0_4px_16px_rgba(109,49,79,0.07)]"
        >
          <!-- 1. Header Kartu: Tanggal Kunjungan & Status Selesai -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-[#2D2428] text-[13px] font-medium">
              <!-- Calendar Icon -->
              <svg
                class="w-4 h-4 text-[#4A3B42] shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
              <span>{{ visit.tanggal }}</span>
            </div>

            <!-- Badge Status Hijau Selesai -->
            <span class="bg-[#DCFCE7] text-[#16A34A] text-[11.5px] font-bold px-2.5 py-0.5 rounded-md leading-normal tracking-wide">
              {{ visit.status }}
            </span>
          </div>

          <!-- 2. Sub-Card Merah Muda: Poli & Dokter -->
          <div class="bg-[#FDEBF3] rounded-[12px] p-3 flex items-center gap-3">
            <!-- Stethoscope Icon Box -->
            <div class="w-10 h-10 rounded-[10px] bg-white flex items-center justify-center shrink-0 shadow-[0_1px_4px_rgba(109,49,79,0.06)]">
              <svg
                class="w-5 h-5 text-[#6D314F]"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3" />
                <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4" />
                <circle cx="20" cy="10" r="2" />
              </svg>
            </div>

            <div class="flex flex-col min-w-0">
              <h2 class="text-[#1F1A1D] text-[14.5px] font-extrabold leading-tight truncate">
                {{ visit.poli }}
              </h2>
              <span class="text-[#65585E] text-[12px] font-medium leading-normal mt-0.5 truncate">
                {{ visit.dokter }}
              </span>
            </div>
          </div>

          <!-- 3. Diagnosa Ringkas -->
          <div class="flex flex-col gap-0.5 pt-0.5">
            <span class="text-[11px] font-bold text-[#7E7379] tracking-wider uppercase">
              DIAGNOSA RINGKAS
            </span>
            <p class="text-[13.5px] font-bold text-[#1F1A1D] m-0 leading-snug">
              {{ visit.diagnosa }}
            </p>
          </div>

          <!-- 4. Tombol Aksi: Lihat Rincian Rekam Medis -->
          <div class="pt-2.5 border-t border-[#F8E9F0]">
            <button
              type="button"
              @click="openDetail(visit)"
              class="w-full flex items-center justify-between text-[#6D314F] hover:text-[#55233C] transition-colors cursor-pointer group bg-transparent border-0 p-0"
            >
              <div class="flex items-center gap-2 text-[12.5px] font-bold">
                <!-- Lock Icon -->
                <svg
                  class="w-3.5 h-3.5 text-[#6D314F]"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                <span>Lihat Rincian Rekam Medis</span>
              </div>

              <!-- Chevron Right -->
              <svg
                class="w-4 h-4 text-[#6D314F] transition-transform duration-150 group-hover:translate-x-0.5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </button>
          </div>
        </article>
      </div>
    </main>

    <!-- Modal Popup / Bottom Sheet Rincian Rekam Medis -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isModalOpen"
          class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 backdrop-blur-[2px]"
          @click.self="closeModal"
        >
          <div class="w-full max-w-[430px] bg-white rounded-t-[24px] sm:rounded-[20px] max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Header Modal -->
            <div class="p-[18px_20px] border-b border-[#F4E2EB] flex items-center justify-between bg-[#FFF7FA]">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-[#FCE4EC] flex items-center justify-center text-[#6D314F]">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-[15px] font-extrabold text-[#1F1A1D] leading-tight">Rincian Rekam Medis</h3>
                  <p class="text-[11.5px] text-[#7E7379] m-0">{{ selectedRecord?.noRm }}</p>
                </div>
              </div>

              <!-- Tombol Tutup X -->
              <button
                type="button"
                @click="closeModal"
                class="w-8 h-8 rounded-full bg-white border border-[#E5D7DF] flex items-center justify-center text-[#7E7379] hover:text-[#1F1A1D] hover:bg-[#FDE8EF] transition-colors cursor-pointer"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>

            <!-- Isi Modal Scrollable -->
            <div class="p-[20px] overflow-y-auto flex flex-col gap-4 text-[13px] text-[#2D2428]">
              <!-- Info Dokter & Poli -->
              <div class="bg-[#FDEBF3] rounded-[14px] p-3.5 flex items-center justify-between">
                <div>
                  <span class="text-[11px] font-bold text-[#6D314F] uppercase tracking-wide">Pemeriksaan Selesai</span>
                  <h4 class="text-[14px] font-extrabold text-[#1F1A1D] mt-0.5 mb-0.5">{{ selectedRecord?.poli }}</h4>
                  <p class="text-[12px] text-[#55464D] m-0">{{ selectedRecord?.dokter }}</p>
                </div>
                <div class="text-right">
                  <span class="inline-block bg-[#DCFCE7] text-[#16A34A] text-[11px] font-bold px-2 py-0.5 rounded">
                    Terverifikasi
                  </span>
                  <p class="text-[11px] text-[#7E7379] mt-1 m-0">{{ selectedRecord?.tanggal }}</p>
                </div>
              </div>

              <!-- Tanda-tanda Vital -->
              <div v-if="selectedRecord?.tandaVital" class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider block mb-2">Tanda-tanda Vital</span>
                <div class="grid grid-cols-2 gap-2 text-[12px]">
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[#7E7379] text-[11px] block">Tekanan Darah</span>
                    <strong class="text-[#1F1A1D] font-extrabold text-[13px]">{{ selectedRecord.tandaVital.tekananDarah }}</strong>
                  </div>
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[#7E7379] text-[11px] block">Detak Nadi</span>
                    <strong class="text-[#1F1A1D] font-extrabold text-[13px]">{{ selectedRecord.tandaVital.nadi }}</strong>
                  </div>
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[#7E7379] text-[11px] block">Suhu Tubuh</span>
                    <strong class="text-[#1F1A1D] font-extrabold text-[13px]">{{ selectedRecord.tandaVital.suhu }}</strong>
                  </div>
                  <div class="bg-[#FFF7FA] p-2 rounded-lg">
                    <span class="text-[#7E7379] text-[11px] block">Berat Badan</span>
                    <strong class="text-[#1F1A1D] font-extrabold text-[13px]">{{ selectedRecord.tandaVital.beratBadan }}</strong>
                  </div>
                </div>
              </div>

              <!-- Anamnesis / Keluhan Utama -->
              <div class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider block mb-1">Keluhan Utama (Anamnesis)</span>
                <p class="text-[12.5px] text-[#2D2428] leading-[1.5] m-0">{{ selectedRecord?.keluhan }}</p>
              </div>

              <!-- Diagnosa Lengkap (ICD-10) -->
              <div class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider block mb-2">Diagnosa Medis</span>
                <div class="flex flex-col gap-1.5">
                  <div
                    v-for="(diag, dIdx) in selectedRecord?.diagnosaDetail"
                    :key="dIdx"
                    class="flex items-center gap-2 text-[12px]"
                  >
                    <span class="bg-[#6D314F] text-white text-[10.5px] font-bold px-1.5 py-0.5 rounded font-mono">
                      {{ diag.kode }}
                    </span>
                    <span class="font-medium text-[#1F1A1D]">{{ diag.nama }}</span>
                  </div>
                </div>
              </div>

              <!-- Tindakan Medis yang Diberikan -->
              <div v-if="selectedRecord?.tindakan" class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider block mb-2">Tindakan Medis</span>
                <ul class="list-disc list-inside text-[12px] text-[#2D2428] space-y-1 m-0 p-0">
                  <li v-for="(act, aIdx) in selectedRecord.tindakan" :key="aIdx">
                    {{ act }}
                  </li>
                </ul>
              </div>

              <!-- Resep Obat & Aturan Pakai -->
              <div v-if="selectedRecord?.resepObat" class="bg-white border border-[#F4E2EB] rounded-[14px] p-3.5">
                <span class="text-[11px] font-bold text-[#7E7379] uppercase tracking-wider block mb-2">Resep Obat &amp; Terapi</span>
                <div class="flex flex-col gap-2">
                  <div
                    v-for="(obat, oIdx) in selectedRecord.resepObat"
                    :key="oIdx"
                    class="bg-[#FFF7FA] p-2.5 rounded-[10px] flex flex-col gap-0.5"
                  >
                    <span class="font-extrabold text-[12.5px] text-[#6D314F]">{{ obat.nama }}</span>
                    <span class="text-[11.5px] text-[#55464D]">{{ obat.aturan }}</span>
                  </div>
                </div>
              </div>

              <!-- Catatan & Edukasi Dokter -->
              <div v-if="selectedRecord?.catatanDokter" class="bg-[#FFF7FA] border border-[#F8D6E6] rounded-[14px] p-3.5">
                <span class="text-[11px] font-bold text-[#6D314F] uppercase tracking-wider block mb-1">Catatan &amp; Edukasi Dokter</span>
                <p class="text-[12px] text-[#4A3B42] leading-[1.5] italic m-0">"{{ selectedRecord.catatanDokter }}"</p>
              </div>
            </div>

            <!-- Footer Modal Action -->
            <div class="p-[14px_20px] border-t border-[#F4E2EB] bg-white flex gap-2">
              <button
                type="button"
                @click="downloadResume"
                class="flex-1 bg-[#6D314F] text-white font-bold py-2.5 px-4 rounded-[12px] text-[13px] flex items-center justify-center gap-1.5 hover:bg-[#55233C] transition-colors cursor-pointer"
              >
                <!-- Download SVG Icon -->
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="7 10 12 15 17 10" />
                  <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span>Unduh Resume Medis</span>
              </button>

              <button
                type="button"
                @click="closeModal"
                class="bg-[#F8D6E6] text-[#6D314F] font-bold py-2.5 px-4 rounded-[12px] text-[13px] hover:bg-[#F2CEDD] transition-colors cursor-pointer"
              >
                Tutup
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Bottom Navigation Bar (Active di Riwayat) -->
    <PatientBottomNav />
  </div>
</template>
