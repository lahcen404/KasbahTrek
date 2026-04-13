<script setup lang="ts">
import { RouterView } from 'vue-router';
import AppFooter from './components/layout/AppFooter.vue';
import AppNavbar from './components/layout/AppNavbar.vue';
</script>

<template>
  <div class="bg-surface text-on-surface min-h-screen flex flex-col font-body">
    <AppNavbar />
    <!-- Same vertical rhythm as reference: pt-16 (64px) under fixed navbar -->
    <main class="flex min-h-0 flex-1 flex-col items-stretch pt-16">
      <div class="flex min-h-0 flex-1 flex-col">
        <RouterView />
      </div>
    </main>
    <AppFooter />
  </div>
</template>
