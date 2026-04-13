<script setup lang="ts">
import { ref } from 'vue';

const logoUrl = '/kasbah-trek.png';

const newsletterEmail = ref('');

function onNewsletterSubmit() {
  newsletterEmail.value = '';
}
</script>

<template>
  <footer class="w-full bg-orange-50 dark:bg-slate-950 rounded-t-3xl mt-auto">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 px-8 py-16 w-full max-w-7xl mx-auto">
      <div class="space-y-4">
        <div class="flex items-center gap-2">
          <img :src="logoUrl" alt="" class="h-9 w-auto object-contain" />
          <div class="text-xl font-bold text-orange-900 dark:text-orange-500 font-headline">
            KASBAH TREK
          </div>
        </div>
        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed max-w-xs font-body">
          Curating high-end editorial adventures through the heart of the Maghreb since 2024.
        </p>
      </div>
      <div class="space-y-4">
        <h4
          class="text-sm font-bold uppercase tracking-widest text-orange-900 dark:text-orange-300 font-label"
        >
          Quick Links
        </h4>
        <ul class="space-y-2">
          <li>
            <a href="#" class="text-slate-500 hover:text-orange-700 transition-colors">Tours</a>
          </li>
          <li>
            <a href="#" class="text-slate-500 hover:text-orange-700 transition-colors">Guides</a>
          </li>
          <li>
            <a href="#" class="text-slate-500 hover:text-orange-700 transition-colors">Safety</a>
          </li>
        </ul>
      </div>
      <div class="space-y-4">
        <h4
          class="text-sm font-bold uppercase tracking-widest text-orange-900 dark:text-orange-300 font-label"
        >
          Social Media
        </h4>
        <ul class="space-y-2">
          <li>
            <a href="#" class="text-slate-500 hover:text-orange-700 transition-colors">Instagram</a>
          </li>
          <li>
            <a href="#" class="text-slate-500 hover:text-orange-700 transition-colors">Twitter</a>
          </li>
          <li>
            <a href="#" class="text-slate-500 hover:text-orange-700 transition-colors">Pinterest</a>
          </li>
        </ul>
      </div>
      <div class="space-y-4">
        <h4
          class="text-sm font-bold uppercase tracking-widest text-orange-900 dark:text-orange-300 font-label"
        >
          Newsletter
        </h4>
        <form class="flex gap-2" @submit.prevent="onNewsletterSubmit">
          <input
            v-model="newsletterEmail"
            class="bg-surface rounded-full px-4 py-2 text-sm border-none w-full focus:ring-1 focus:ring-primary text-on-surface"
            placeholder="Your email"
            type="email"
            autocomplete="email"
          />
          <button
            type="submit"
            class="bg-orange-900 text-white p-2 rounded-full hover:bg-primary transition-all shrink-0"
            aria-label="Subscribe"
          >
            <span class="material-symbols-outlined block text-xl">arrow_forward</span>
          </button>
        </form>
      </div>
    </div>
    <div
      class="max-w-7xl mx-auto px-8 py-6 flex flex-col md:flex-row justify-between items-center text-xs font-bold uppercase tracking-widest text-slate-400"
    >
      <span>© 2024 KASBAH TREK. All rights reserved.</span>
      <div class="flex gap-6 mt-4 md:mt-0">
        <a href="#" class="hover:text-orange-900 transition-colors">Privacy Policy</a>
        <a href="#" class="hover:text-orange-900 transition-colors">Terms of Service</a>
      </div>
    </div>
  </footer>
</template>
