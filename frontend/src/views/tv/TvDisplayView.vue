<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Helper format loket agar selalu bersih menjadi "Loket"
const formatLoketName = (val) => {
  if (!val) return 'Loket'
  const str = String(val).trim()
  if (/^loket\s*0?1$/i.test(str) || str.toLowerCase() === 'loket 01' || str.toLowerCase() === 'loket 1') {
    return 'Loket'
  }
  return str.replace(/loket\s*0?1/gi, 'Loket')
}

// Data Antrean Default TV
const defaultQueue = {
  noTiket: 'A-023',
  nama: 'Clarissa Putri',
  poli: 'Poli Umum',
  loket: 'Loket',
  lokasi: 'POLIKLINIK 1 • LT. 1',
  panggilanKe: 1,
  timestamp: Date.now()
}

const currentQueue = ref({ ...defaultQueue })
const isAudioEnabled = ref(true)
const isSpeaking = ref(false)
const isFullscreen = ref(false)
const tvContainer = ref(null)

// Jam Real-time format HH:mm WIB
const currentTime = ref('10:45')
let clockTimer = null

const updateClock = () => {
  const now = new Date()
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  currentTime.value = `${hours}:${minutes}`
}

// Ding-Dong Chime Sintetis Menggunakan Web Audio API
const playDingDongChime = () => {
  if (!isAudioEnabled.value) return Promise.resolve()

  return new Promise((resolve) => {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext
      if (!AudioCtx) {
        resolve()
        return
      }
      const ctx = new AudioCtx()

      // Nada 1: D5 (587.33 Hz)
      const osc1 = ctx.createOscillator()
      const gain1 = ctx.createGain()
      osc1.type = 'sine'
      osc1.frequency.setValueAtTime(587.33, ctx.currentTime)
      gain1.gain.setValueAtTime(0.3, ctx.currentTime)
      gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6)
      osc1.connect(gain1)
      gain1.connect(ctx.destination)
      osc1.start(ctx.currentTime)
      osc1.stop(ctx.currentTime + 0.6)

      // Nada 2: A4 (440 Hz) setelah 0.4 detik
      setTimeout(() => {
        try {
          const osc2 = ctx.createOscillator()
          const gain2 = ctx.createGain()
          osc2.type = 'sine'
          osc2.frequency.setValueAtTime(440, ctx.currentTime)
          gain2.gain.setValueAtTime(0.35, ctx.currentTime)
          gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.8)
          osc2.connect(gain2)
          gain2.connect(ctx.destination)
          osc2.start(ctx.currentTime)
          osc2.stop(ctx.currentTime + 0.8)

          setTimeout(() => {
            ctx.close()
            resolve()
          }, 850)
        } catch (err) {
          resolve()
        }
      }, 350)
    } catch (e) {
      console.warn('Web Audio error:', e)
      resolve()
    }
  })
}

// Pengumuman Suara (SpeechSynthesis)
const speakAnnouncement = (queue) => {
  if (!isAudioEnabled.value) return

  isSpeaking.value = true
  playDingDongChime().then(() => {
    if (!('speechSynthesis' in window)) {
      setTimeout(() => { isSpeaking.value = false }, 3000)
      return
    }

    try {
      window.speechSynthesis.cancel()

      // Teks pengumuman
      const loketText = formatLoketName(queue.loket)
      const poliText = queue.poli || 'Poli Umum'
      const text = `Nomor antrean, ${queue.noTiket}, silakan menuju ${loketText}, ${poliText}`

      const utterance = new SpeechSynthesisUtterance(text)
      utterance.lang = 'id-ID'
      utterance.rate = 0.92

      utterance.onend = () => {
        isSpeaking.value = false
      }
      utterance.onerror = () => {
        isSpeaking.value = false
      }

      window.speechSynthesis.speak(utterance)
    } catch (err) {
      console.warn('SpeechSynthesis error:', err)
      isSpeaking.value = false
    }
  })
}

// Muat Antrean Aktif dari LocalStorage
const loadActiveQueue = (triggerSound = false) => {
  try {
    const raw = localStorage.getItem('kliniku_calling_queue')
    if (raw) {
      const parsed = JSON.parse(raw)
      const cleanLoket = formatLoketName(parsed.loket)
      const hasChanged = currentQueue.value.noTiket !== parsed.noTiket ||
                         currentQueue.value.loket !== cleanLoket ||
                         currentQueue.value.timestamp !== parsed.timestamp

      currentQueue.value = {
        noTiket: parsed.noTiket || 'A-023',
        nama: parsed.nama || 'Clarissa Putri',
        poli: parsed.poli || 'Poli Umum',
        loket: cleanLoket,
        lokasi: parsed.lokasi || 'POLIKLINIK 1 • LT. 1',
        panggilanKe: parsed.panggilanKe || 1,
        timestamp: parsed.timestamp || Date.now()
      }

      // Bersihkan jika data tersimpan di browser masih menyimpan 'Loket 01'
      if (parsed.loket !== cleanLoket) {
        parsed.loket = cleanLoket
        localStorage.setItem('kliniku_calling_queue', JSON.stringify(parsed))
      }

      if (triggerSound && hasChanged) {
        speakAnnouncement(currentQueue.value)
      }
    } else {
      // Set default jika belum ada data di localStorage
      localStorage.setItem('kliniku_calling_queue', JSON.stringify(defaultQueue))
    }
  } catch (e) {
    console.warn('Gagal membaca antrean aktif:', e)
  }
}

// Toggle Fullscreen
const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    if (tvContainer.value?.requestFullscreen) {
      tvContainer.value.requestFullscreen()
      isFullscreen.value = true
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen()
      isFullscreen.value = false
    }
  }
}

// Simulasi Coba Panggil Tiket Lain (Untuk Pengujian Langsung di Layar TV)
const quickTestCalls = [
  { noTiket: 'A-023', nama: 'Clarissa Putri', poli: 'Poli Umum', loket: 'Loket' },
  { noTiket: 'A-020', nama: 'Bambang Sutrisno', poli: 'Poli Jantung & Pembuluh', loket: 'Loket' },
  { noTiket: 'B-004', nama: 'Kayla Nadira', poli: 'Poli Spesialis Anak', loket: 'Loket' },
  { noTiket: 'A-021', nama: 'Ratna Dewi', poli: 'Poli Gigi & Mulut', loket: 'Loket' }
]

const testCall = (item) => {
  const data = {
    ...item,
    lokasi: 'POLIKLINIK 1 • LT. 1',
    panggilanKe: 1,
    timestamp: Date.now()
  }
  localStorage.setItem('kliniku_calling_queue', JSON.stringify(data))
  currentQueue.value = data
  speakAnnouncement(data)
}

// Teks teks pengumuman yang ditampilkan pada bar suara
const announcementText = computed(() => {
  const loket = formatLoketName(currentQueue.value.loket)
  const poli = currentQueue.value.poli || 'Poli Umum'
  return `"Ding-dong! Nomor antrean ${currentQueue.value.noTiket}, silakan menuju ${loket} (${poli})..."`
})

// Listen storage event untuk multi-tab sync dari Portal Admin
const handleStorageChange = (e) => {
  if (!e || e.key === 'kliniku_calling_queue' || e.key === null) {
    loadActiveQueue(true)
  }
}

onMounted(() => {
  updateClock()
  clockTimer = setInterval(updateClock, 1000)

  // Baca antrean tersimpan awal
  loadActiveQueue(false)

  // Pasang listener sinkronisasi real-time
  window.addEventListener('storage', handleStorageChange)

  // Listener fullscreen change
  document.addEventListener('fullscreenchange', () => {
    isFullscreen.value = !!document.fullscreenElement
  })
})

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer)
  window.removeEventListener('storage', handleStorageChange)
  if ('speechSynthesis' in window) {
    window.speechSynthesis.cancel()
  }
})
</script>

<template>
  <div
    ref="tvContainer"
    class="min-h-screen bg-[#0C080B] text-white flex flex-col items-center justify-center p-3 sm:p-6 md:p-10 select-none relative overflow-x-hidden font-sans"
  >
    <!-- Background Ambient Glow -->
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-[#6D2D49]/15 blur-[120px] rounded-full pointer-events-none"></div>

    <!-- MAIN TV FRAME -->
    <div
      class="w-full max-w-4xl bg-[#1C1318] border border-[#35212E] rounded-[28px] sm:rounded-[32px] p-4 sm:p-7 md:p-8 shadow-2xl relative transition-all duration-300"
    >
      <!-- HEADER TV -->
      <div class="flex items-center justify-between pb-4 sm:pb-6 px-1">
        <!-- Title & TV Icon -->
        <div class="flex items-center gap-2.5 sm:gap-3 text-white">
          <!-- Monitor TV Icon -->
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
          </svg>
          <h1 class="text-xs sm:text-sm md:text-base font-bold tracking-wider text-white uppercase">
            SIMULASI TV RUANG TUNGGU
          </h1>
        </div>

        <!-- Live Sync Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1 sm:py-1.5 rounded-full bg-[#0E3524] border border-[#10B981]/40 text-[#34D399] text-[11px] sm:text-xs font-bold tracking-wider">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#10B981] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#10B981]"></span>
          </span>
          <span>LIVE SYNC</span>
        </div>
      </div>

      <!-- INNER SCREEN CARD -->
      <div class="bg-[#281A23] border border-[#3E2535] rounded-2xl sm:rounded-[24px] p-5 sm:p-8 md:p-10 flex flex-col items-center justify-center text-center shadow-inner relative overflow-hidden">
        
        <!-- Screen Subheader (Lokasi & Jam) -->
        <div class="w-full flex items-center justify-between text-[#B399A8] text-xs sm:text-sm font-semibold tracking-widest pb-4 sm:pb-6">
          <span class="uppercase">{{ currentQueue.lokasi || 'POLIKLINIK 1 • LT. 1' }}</span>
          <span class="font-mono tracking-wider">{{ currentTime }} WIB</span>
        </div>

        <!-- NOMOR ANTREAN TITLE -->
        <div class="mt-1 sm:mt-2">
          <h2 class="text-xs sm:text-sm md:text-base font-bold tracking-[0.25em] text-[#D8BCCB] uppercase">
            NOMOR ANTREAN
          </h2>
        </div>

        <!-- GIANT TICKET NUMBER -->
        <div class="my-2 sm:my-4 transition-transform duration-300 hover:scale-[1.02]">
          <span class="text-6xl sm:text-7xl md:text-8xl lg:text-9xl font-black font-sans tracking-tight text-white drop-shadow-sm select-all">
            {{ currentQueue.noTiket || 'A-023' }}
          </span>
        </div>

        <!-- DESTINATION PILL BADGE -->
        <div class="mb-5 sm:mb-7">
          <div class="inline-flex items-center justify-center bg-[#6E2A47] hover:bg-[#7D3052] border border-[#8C385C] text-white font-bold text-xs sm:text-sm md:text-base px-5 sm:px-8 py-2.5 sm:py-3 rounded-full shadow-lg transition-colors">
            <span>MENUJU {{ formatLoketName(currentQueue.loket).toUpperCase() }} ({{ (currentQueue.poli || 'POLI UMUM').toUpperCase() }})</span>
          </div>
        </div>

        <!-- ANNOUNCEMENT SOUND BAR -->
        <div class="w-full max-w-xl bg-[#191016] border border-[#392231] rounded-full px-4 sm:px-6 py-2.5 sm:py-3.5 flex items-center gap-3 sm:gap-4 shadow-sm">
          <!-- Animated Soundwave Icon -->
          <div class="flex items-center gap-1 h-5 flex-shrink-0" :class="{ 'opacity-100': isSpeaking, 'opacity-75': !isSpeaking }">
            <span class="w-1 bg-[#E288A3] rounded-full h-2.5 transition-all duration-200" :class="{ 'animate-bounce': isSpeaking }"></span>
            <span class="w-1 bg-[#E288A3] rounded-full h-4 transition-all duration-200" :class="{ 'animate-pulse': isSpeaking }"></span>
            <span class="w-1 bg-[#E288A3] rounded-full h-5 transition-all duration-200" :class="{ 'animate-bounce': isSpeaking }"></span>
            <span class="w-1 bg-[#E288A3] rounded-full h-3.5 transition-all duration-200" :class="{ 'animate-pulse': isSpeaking }"></span>
            <span class="w-1 bg-[#E288A3] rounded-full h-2 transition-all duration-200" :class="{ 'animate-bounce': isSpeaking }"></span>
          </div>

          <!-- Announcement Text -->
          <div class="text-left text-xs sm:text-sm text-gray-200 truncate font-medium">
            <span class="italic">{{ announcementText }}</span>
          </div>
        </div>

      </div>

      <!-- FOOTER TV FRAME -->
      <div class="flex flex-col sm:flex-row items-center justify-between pt-4 sm:pt-5 px-1 gap-3 text-xs text-[#8A7382]">
        <!-- Display ID -->
        <div class="font-mono flex items-center gap-2">
          <span>Display ID: TV-WAITING-01</span>
          <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        </div>

        <!-- Utility Buttons -->
        <div class="flex items-center gap-2">
          <!-- Tombol Panggil Suara Ulang -->
          <button
            @click="speakAnnouncement(currentQueue)"
            class="px-2.5 py-1 rounded-lg bg-[#281A23] hover:bg-[#382331] text-[#D8BCCB] hover:text-white transition-colors flex items-center gap-1.5 border border-[#3E2535]"
            title="Putar suara panggilan sekarang"
          >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
              <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
            </svg>
            <span>Putar Suara</span>
          </button>

          <!-- Toggle Suara Mute / Unmute -->
          <button
            @click="isAudioEnabled = !isAudioEnabled"
            class="px-2.5 py-1 rounded-lg bg-[#281A23] hover:bg-[#382331] text-[#D8BCCB] hover:text-white transition-colors flex items-center gap-1.5 border border-[#3E2535]"
            :title="isAudioEnabled ? 'Matikan Suara TV' : 'Aktifkan Suara TV'"
          >
            <span v-if="isAudioEnabled">🔊 Audio ON</span>
            <span v-else class="text-rose-400">🔇 Audio OFF</span>
          </button>

          <!-- Fullscreen Toggle -->
          <button
            @click="toggleFullscreen"
            class="px-2.5 py-1 rounded-lg bg-[#281A23] hover:bg-[#382331] text-[#D8BCCB] hover:text-white transition-colors flex items-center gap-1.5 border border-[#3E2535]"
            title="Layar Penuh TV"
          >
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path v-if="!isFullscreen" d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
              <path v-else d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
            </svg>
            <span>{{ isFullscreen ? 'Keluar Fullscreen' : 'Layar Penuh' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- QUICK SIMULATION & NAVIGATION BAR DI BAWAH TV -->
    <div class="w-full max-w-4xl mt-5 sm:mt-6 bg-[#160E13] border border-[#2B1B26] rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
      <div class="flex items-center gap-2 text-gray-400">
        <span class="font-medium text-gray-300">Tes Cepat Panggilan TV:</span>
        <div class="flex items-center gap-1.5 flex-wrap">
          <button
            v-for="test in quickTestCalls"
            :key="test.noTiket"
            @click="testCall(test)"
            class="px-2.5 py-1 rounded-md font-mono font-bold transition-colors"
            :class="currentQueue.noTiket === test.noTiket ? 'bg-[#6E2A47] text-white border border-[#8C385C]' : 'bg-[#21151D] hover:bg-[#2F1C29] text-gray-300 border border-[#372130]'"
          >
            {{ test.noTiket }}
          </button>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <router-link
          to="/admin"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#2A1B24] hover:bg-[#3D2534] text-[#E288A3] hover:text-white font-medium transition-colors border border-[#3E2535]"
        >
          <span>Buka Portal Admin</span>
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </router-link>
      </div>
    </div>

  </div>
</template>
