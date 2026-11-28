<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[430px] h-[64px] bg-white border-t border-[#F0D9E5] flex justify-around items-center z-50 box-border">
    <!-- Menu Beranda -->
    <RouterLink
      to="/beranda"
      class="flex flex-col items-center justify-center gap-1 no-underline text-[#7E7379] text-[11px] font-medium transition-colors duration-150 w-1/3 h-full hover:text-[#6D314F] [&.router-link-active]:text-[#6D314F] [&.router-link-active]:font-extrabold [&.router-link-active_svg]:scale-105"
    >
      <svg viewBox="0 0 24 24" class="w-[22px] h-[22px] transition-transform duration-150" fill="currentColor">
        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
      </svg>
      <span>Beranda</span>
    </RouterLink>

    <!-- Menu Riwayat -->
    <RouterLink
      to="/riwayat"
      class="flex flex-col items-center justify-center gap-1 no-underline text-[#7E7379] text-[11px] font-medium transition-colors duration-150 w-1/3 h-full hover:text-[#6D314F] [&.router-link-active]:text-[#6D314F] [&.router-link-active]:font-extrabold [&.router-link-active_svg]:scale-105"
    >
      <svg
        viewBox="0 0 24 24"
        class="w-[22px] h-[22px] transition-transform duration-150"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="M4 20h7M4 4h5l2 3h9a2 2 0 0 1 2 2v2" />
        <path d="M4 4v16" />
        <circle cx="16.5" cy="16.5" r="4.5" />
        <polyline points="16.5 14 16.5 16.5 18 18" />
      </svg>
      <span>Riwayat</span>
    </RouterLink>

    <!-- Menu Profil -->
    <RouterLink
      to="/profil"
      class="flex flex-col items-center justify-center gap-1 no-underline text-[#7E7379] text-[11px] font-medium transition-colors duration-150 w-1/3 h-full hover:text-[#6D314F] [&.router-link-active]:text-[#6D314F] [&.router-link-active]:font-extrabold [&.router-link-active_svg]:scale-105"
    >
      <svg
        viewBox="0 0 24 24"
        class="w-[22px] h-[22px] transition-transform duration-150"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <circle cx="12" cy="12" r="10" />
        <circle cx="12" cy="9" r="3.2" />
        <path d="M6.3 18.7c1.4-2.5 3.5-3.7 5.7-3.7s4.3 1.2 5.7 3.7" />
      </svg>
      <span>Profil</span>
    </RouterLink>
  </nav>
</template>
