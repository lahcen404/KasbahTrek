<script setup lang="ts">
const logoUrl = '/kasbah-trek.png';
</script>

<template>
  <footer class="w-full rounded-t-3xl bg-orange-50 dark:bg-slate-950">
    <div class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-8 px-8 py-16 md:grid-cols-4">
      <div class="space-y-6">
        <div class="flex items-center gap-2">
          <img
            :src="logoUrl"
            alt=""
            class="h-9 w-auto object-contain"
          />
          <div class="font-headline text-xl font-bold uppercase text-orange-900 dark:text-orange-500">
            KASBAH TREK
          </div>
        </div>
        <p class="font-noto-serif text-sm leading-relaxed text-slate-500 dark:text-slate-400">
          Curating the finest nomadic experiences across the Moroccan landscape since 2012.
        </p>
        <div class="flex gap-4">
          <a
            class="text-slate-500 transition-colors hover:text-orange-900"
            href="#"
          ><span class="material-symbols-outlined">public</span></a>
          <a
            class="text-slate-500 transition-colors hover:text-orange-900"
            href="#"
          ><span class="material-symbols-outlined">mail</span></a>
          <a
            class="text-slate-500 transition-colors hover:text-orange-900"
            href="#"
          ><span class="material-symbols-outlined">camera</span></a>
        </div>
      </div>
      <div class="space-y-6">
        <h6 class="font-headline font-semibold text-orange-900 dark:text-orange-300">Quick Links</h6>
        <ul class="space-y-4">
          <li>
            <a
              class="text-sm text-slate-500 transition-colors hover:text-orange-700 dark:text-slate-400 dark:hover:text-orange-400"
              href="#"
            >Destinations</a>
          </li>
          <li>
            <a
              class="text-sm text-slate-500 transition-colors hover:text-orange-700 dark:text-slate-400 dark:hover:text-orange-400"
              href="#"
            >Our Story</a>
          </li>
          <li>
            <a
              class="text-sm text-slate-500 transition-colors hover:text-orange-700 dark:text-slate-400 dark:hover:text-orange-400"
              href="#"
            >Sustainable Travel</a>
          </li>
          <li>
            <a
              class="text-sm text-slate-500 transition-colors hover:text-orange-700 dark:text-slate-400 dark:hover:text-orange-400"
              href="#"
            >Contact Us</a>
          </li>
        </ul>
      </div>
      <div class="space-y-6">
        <h6 class="font-headline font-semibold text-orange-900 dark:text-orange-300">Newsletter</h6>
        <p class="font-noto-serif text-sm text-slate-500 dark:text-slate-400">
          Join our nomad community for monthly travel inspiration.
        </p>
        <div class="flex overflow-hidden rounded-full bg-surface-container-high p-1">
          <input
            class="w-full border-none bg-transparent px-4 py-2 text-sm focus:ring-0"
            placeholder="Your email"
            type="email"
          />
          <button
            type="button"
            class="rounded-full bg-orange-900 p-2 text-white"
            aria-label="Subscribe"
          >
            <span class="material-symbols-outlined text-sm">send</span>
          </button>
        </div>
      </div>
      <div class="space-y-6">
        <h6 class="font-headline font-semibold text-orange-900 dark:text-orange-300">
          Language Selector
        </h6>
        <div
          class="flex cursor-pointer items-center gap-2 text-sm text-slate-500 hover:text-orange-900"
        >
          <span class="material-symbols-outlined text-sm">language</span>
          <span>English (US)</span>
          <span class="material-symbols-outlined text-sm">expand_more</span>
        </div>
      </div>
    </div>
    <div
      class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 border-t border-orange-200/30 px-8 py-8 md:flex-row"
    >
      <p class="font-label text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400">
        © 2024 KASBAH TREK. All rights reserved.
      </p>
      <div class="flex gap-8">
        <a
          class="font-label text-xs uppercase tracking-widest text-slate-400 hover:text-orange-900"
          href="#"
        >Privacy</a>
        <a
          class="font-label text-xs uppercase tracking-widest text-slate-400 hover:text-orange-900"
          href="#"
        >Terms</a>
      </div>
    </div>
  </footer>
</template>
