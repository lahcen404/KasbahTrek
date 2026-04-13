<script setup lang="ts">
import { RouterView } from 'vue-router';
import AppFooter from '../components/layout/AppFooter.vue';
import AppNavbar from '../components/layout/AppNavbar.vue';
</script>

<template>
  <div class="bg-surface text-on-surface min-h-screen flex flex-col font-body">
    <AppNavbar />
    <main class="flex-1 flex flex-col pt-[72px]">
      <RouterView />
    </main>
    <AppFooter />
  </div>
</template>
