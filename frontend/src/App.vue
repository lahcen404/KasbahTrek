<template>
  <div class="app-shell">
    <header class="topbar">
      <div class="brand">KasbahTrek</div>
      <nav class="nav">
        <a href="/">Home</a>
      </nav>
    </header>

    <main class="container">
      <RouterView />
    </main>
  </div>
</template>

<script setup lang="ts">
import { RouterView } from 'vue-router';
</script>

<style scoped>
.app-shell {
  min-height: 100vh;
  background: #0b1220;
  color: #e6e8ee;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica,
    Arial, "Apple Color Emoji", "Segoe UI Emoji";
}
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  position: sticky;
  top: 0;
  background: rgba(11, 18, 32, 0.9);
  backdrop-filter: blur(10px);
}
.brand {
  font-weight: 700;
  letter-spacing: 0.2px;
}
.nav a {
  color: #e6e8ee;
  text-decoration: none;
  opacity: 0.9;
}
.nav a:hover {
  opacity: 1;
}
.container {
  max-width: 960px;
  margin: 0 auto;
  padding: 28px 20px;
}
</style>

