<script setup>
defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  adminName: {
    type: String,
    default: 'Siti Rahmawati'
  }
})

defineEmits(['close', 'confirm'])
</script>

<template>
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
        v-if="isOpen"
        class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-[2px] flex items-center justify-center p-4"
        @click.self="$emit('close')"
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

          <h3 class="text-[18px] font-extrabold text-[#1F1A1D] m-0">Keluar dari Portal Admin?</h3>
          <p class="text-[13px] text-[#55464D] mt-2 mb-4 leading-relaxed">
            Apakah Anda yakin ingin keluar dari sesi kerja Loket Pendaftaran? Anda harus masuk kembali untuk mengelola antrean pasien.
          </p>

          <!-- Info Admin Card -->
          <div class="w-full bg-[#FFF7FA] border border-[#F2CEDD] rounded-[14px] p-3.5 mb-5 text-left flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-full bg-[#6D314F] text-white flex items-center justify-center font-bold text-sm shrink-0">
                {{ adminName ? adminName.charAt(0) : 'A' }}
              </div>
              <div class="min-w-0">
                <span class="text-[13px] font-black text-[#1F1A1D] block leading-tight truncate">{{ adminName || 'Siti Rahmawati' }}</span>
                <span class="text-[11px] font-semibold text-[#8C4B6A] block mt-0.5">Petugas Admin Loket</span>
              </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10.5px] font-black bg-rose-100 text-[#DC2626] shrink-0">
              Keluar
            </span>
          </div>

          <!-- Tombol Pilihan: Yakin / Tidak -->
          <div class="flex items-center gap-3 w-full">
            <button
              type="button"
              @click="$emit('close')"
              class="flex-1 py-2.5 px-4 rounded-[12px] border border-[#E5BED1] bg-white text-[#55464D] font-bold text-[13px] hover:bg-[#FFF7FA] cursor-pointer transition-colors"
            >
              Batal
            </button>
            <button
              type="button"
              @click="$emit('confirm')"
              class="flex-1 py-2.5 px-4 rounded-[12px] border border-transparent bg-[#DC2626] text-white font-bold text-[13px] hover:bg-[#B91C1C] cursor-pointer transition-colors shadow-sm"
            >
              Keluar
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
