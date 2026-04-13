<script setup lang="ts">
import { RouterLink } from 'vue-router';

// File must live in public/ (or Docker mount: kasbah-trek.png -> /app/public/kasbah-trek.png)
const logoUrl = '/kasbah-trek.png';
</script>

<template>
  <nav
    class="fixed top-0 left-0 right-0 z-50 bg-surface/80 backdrop-blur-md border-b border-surface-container-highest/60"
  >
    <div class="flex justify-between items-center w-full px-6 py-4 max-w-7xl mx-auto">
      <RouterLink to="/" class="flex items-center gap-3 shrink-0">
        <img :src="logoUrl" alt="Kasbah Trek" class="h-10 w-auto object-contain" />
        <span class="text-xl md:text-2xl font-bold tracking-tight text-orange-900 font-headline"
          >KASBAH TREK</span
        >
      </RouterLink>
      <div class="flex items-center gap-4 md:gap-8">
        <div class="flex items-center gap-3 md:hidden">
          <RouterLink
            :to="{ name: 'register' }"
            class="text-slate-600 font-semibold text-sm hover:text-orange-800 transition-colors"
            >Register</RouterLink
          >
          <RouterLink
            :to="{ name: 'login' }"
            class="text-primary font-bold hover:text-orange-700 transition-colors text-sm font-semibold"
            >Login</RouterLink
          >
        </div>
        <div class="hidden md:flex items-center gap-8">
          <RouterLink
            :to="{ name: 'home' }"
            class="text-slate-600 hover:text-orange-800 transition-colors text-sm font-semibold"
            >Home</RouterLink
          >
          <a href="#" class="text-slate-600 hover:text-orange-800 transition-colors text-sm font-semibold"
            >Tours</a
          >
          <a href="#" class="text-slate-600 hover:text-orange-800 transition-colors text-sm font-semibold"
            >About</a
          >
          <RouterLink
            :to="{ name: 'register' }"
            class="text-slate-600 font-semibold text-sm hover:text-orange-800 transition-colors"
            >Register</RouterLink
          >
          <RouterLink
            :to="{ name: 'login' }"
            class="text-primary font-bold hover:text-orange-700 transition-colors text-sm font-semibold"
            >Login</RouterLink
          >
        </div>
      </div>
    </div>
  </nav>
</template>
