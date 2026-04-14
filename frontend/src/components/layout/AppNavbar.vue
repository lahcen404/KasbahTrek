<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

// logo url
const logoUrl = '/kasbah-trek.png';

const route = useRoute();
const isHome = computed(() => route.name === 'home');
const isTours = computed(() => route.name === 'tours');

const mobileMenuOpen = ref(false);

watch(
  () => route.fullPath,
  () => {
    mobileMenuOpen.value = false;
  },
);
</script>

<template>
  <nav
    class="fixed left-0 right-0 top-0 z-50 bg-surface/80 shadow-[0_20px_50px_rgba(30,42,47,0.06)] backdrop-blur-md"
  >
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
      <RouterLink
        to="/"
        class="flex shrink-0 items-center gap-3"
      >
        <img
          :src="logoUrl"
          alt="Kasbah Trek"
          class="h-10 w-auto object-contain"
        />
        <span
          class="font-headline text-2xl font-bold uppercase tracking-tight text-orange-900 dark:text-orange-500"
        >
          KASBAH TREK
        </span>
      </RouterLink>

      <div class="hidden items-center gap-8 md:flex">
        <RouterLink
          :to="{ name: 'home' }"
          :class="
            isHome
              ? 'border-b-2 border-orange-800 pb-1 font-bold text-orange-800 dark:border-orange-400 dark:text-orange-400'
              : 'text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400'
          "
          >Home</RouterLink
        >
        <RouterLink
          :to="{ name: 'tours' }"
          :class="
            isTours
              ? 'border-b-2 border-orange-800 pb-1 font-bold text-orange-800 dark:border-orange-400 dark:text-orange-400'
              : 'text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400'
          "
          >Tours</RouterLink
        >
        <a
          class="text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400"
          href="#"
        >How it works</a>
        <a
          class="text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400"
          href="#"
        >Guides</a>
        <a
          class="text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400"
          href="#"
        >About</a>
      </div>

      <div class="flex items-center gap-4">
        <button
          type="button"
          class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-outline-variant/30 bg-surface-container-lowest text-slate-700 transition-colors hover:bg-surface-container-low md:hidden"
          :aria-expanded="mobileMenuOpen ? 'true' : 'false'"
          aria-controls="mobile-nav"
          aria-label="Open menu"
          @click="mobileMenuOpen = !mobileMenuOpen"
        >
          <span class="material-symbols-outlined">
            {{ mobileMenuOpen ? 'close' : 'menu' }}
          </span>
        </button>
        <RouterLink
          :to="{ name: 'login' }"
          class="hidden px-4 py-2 font-semibold text-slate-600 transition-colors hover:text-orange-800 md:block"
        >
          Login
        </RouterLink>
        <RouterLink
          :to="{ name: 'register' }"
          class="hidden rounded-full bg-primary px-8 py-3 font-bold text-on-primary transition-all hover:brightness-110 hover:shadow-lg active:scale-95 md:inline-flex"
        >
          Register
        </RouterLink>
      </div>
    </div>

    <!-- Mobile menu -->
    <div
      id="mobile-nav"
      class="md:hidden"
      v-show="mobileMenuOpen"
    >
      <div class="mx-auto w-full max-w-7xl px-6 pb-4">
        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-4 shadow-sm">
          <div class="flex flex-col gap-3">
            <RouterLink
              :to="{ name: 'home' }"
              class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low"
              >Home</RouterLink
            >
            <RouterLink
              :to="{ name: 'tours' }"
              class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low"
              >Tours</RouterLink
            >
            <a class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low" href="#"
              >How it works</a
            >
            <a class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low" href="#"
              >Guides</a
            >
            <a class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low" href="#"
              >About</a
            >
          </div>

          <div class="mt-4 grid grid-cols-2 gap-3">
            <RouterLink
              :to="{ name: 'login' }"
              class="rounded-full border border-outline-variant/30 bg-surface px-4 py-3 text-center font-bold text-slate-700 transition-colors hover:bg-surface-container-low active:scale-95"
            >
              Login
            </RouterLink>
            <RouterLink
              :to="{ name: 'register' }"
              class="rounded-full bg-primary px-4 py-3 text-center font-bold text-on-primary transition-all hover:brightness-110 hover:shadow-lg active:scale-95"
            >
              Register
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
