<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="min-h-screen w-full bg-[#f3e8ee] flex justify-center max-[480px]:bg-[#fff7fa]">
    <div class="w-full max-w-[430px] min-h-screen bg-[#fff7fa] shadow-[0_0_32px_rgba(109,49,79,0.08)] max-[480px]:shadow-none relative flex flex-col">
      <RouterView />
    </div>
  </div>
</template>
