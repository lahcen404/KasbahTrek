<script setup lang="ts">
import { computed } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import AppFooter from './components/layout/AppFooter.vue';
import AppNavbar from './components/layout/AppNavbar.vue';

const route = useRoute();

const shelllessRoutes = new Set(['home']);
const showShell = computed(() => !shelllessRoutes.has(String(route.name)));
</script>

<template>
  <!-- Home is a full static landing (own nav + footer). Other routes use the app shell. -->
  <div
    v-if="showShell"
    class="bg-surface text-on-surface flex min-h-screen flex-col font-body"
  >
    <AppNavbar />
    <main class="flex min-h-0 flex-1 flex-col items-stretch pt-[72px]">
      <div class="flex min-h-0 flex-1 flex-col">
        <RouterView />
      </div>
    </main>
    <AppFooter class="mt-auto" />
  </div>
  <RouterView v-else />
</template>
