<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import logoKliniku from '@/assets/logo-kliniku.png'

const logoLoadError = ref(false)
const patientAvatar = ref('')

const loadAvatar = () => {
  try {
    const saved = localStorage.getItem('kliniku_patient_profile')
    if (saved) {
      const parsed = JSON.parse(saved)
      if (parsed.foto) {
        patientAvatar.value = parsed.foto
        return
      }
    }
    const activeUser = localStorage.getItem('kliniku_active_user')
    if (activeUser) {
      const parsed = JSON.parse(activeUser)
      if (parsed.foto) {
        patientAvatar.value = parsed.foto
        return
      }
    }
    patientAvatar.value = ''
  } catch (e) {
    patientAvatar.value = ''
  }
}

onMounted(() => {
  loadAvatar()
  window.addEventListener('kliniku-profile-updated', loadAvatar)
  window.addEventListener('storage', loadAvatar)
})

onUnmounted(() => {
  window.removeEventListener('kliniku-profile-updated', loadAvatar)
  window.removeEventListener('storage', loadAvatar)
})
</script>

<template>
  <header class="sticky top-0 left-0 w-full bg-white border-b border-[#F2CEDD] z-40 flex justify-between items-center p-[12px_18px] box-border">
    <div class="flex items-center gap-2">
      <!-- Logo KLINIKU -->
      <img
        v-if="!logoLoadError"
        :src="logoKliniku"
        alt="Logo KLINIKU"
        class="h-[26px] w-auto object-contain"
        @error="logoLoadError = true"
      />
      <!-- Fallback SVG jika logo terkendala -->
      <svg
        v-else
        class="w-[26px] h-[26px]"
        viewBox="0 0 32 32"
        fill="none"
      >
        <rect width="32" height="32" rx="8" fill="#F8D6E6" />
        <path d="M16 6v20M6 16h20" stroke="#6D314F" stroke-width="4" stroke-linecap="round" />
      </svg>
      <span class="text-[16px] font-extrabold text-[#1F1A1D] tracking-[0.5px]">KLINIKU</span>
    </div>

    <div class="flex items-center gap-[14px]">
      <!-- Lingkaran Avatar Pengguna -->
      <router-link to="/profil" class="block">
        <div class="w-8 h-8 rounded-full bg-[#D9D9D9] shrink-0 overflow-hidden border border-[#F2CEDD] flex items-center justify-center cursor-pointer hover:ring-2 hover:ring-[#6D314F]/30 transition-all" aria-label="Avatar Pasien">
          <img v-if="patientAvatar" :src="patientAvatar" alt="Avatar Pasien" class="w-full h-full object-cover" />
          <svg v-else class="w-5 h-5 text-[#9E9E9E]" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
        </div>
      </router-link>
    </div>
  </header>
</template>
