<script setup lang="ts">
import { ref } from 'vue';

const result = ref<string>('');

async function ping() {
  try {
    const res = await fetch('/api/up');
    result.value = `GET /api/up -> ${res.status} ${res.statusText}`;
  } catch (e) {
    result.value = `Error calling /api: ${String(e)}`;
  }
}
</script>

<template>
  <section class="hero">
    <h1>Vue frontend is running</h1>
    <p>
      This is a fresh Vue 3 + Vite app in Docker. Next step: connect to your backend APIs
      under <code>/api</code>.
    </p>
    <div class="actions">
      <button class="btn" @click="ping">Ping API</button>
    </div>
    <pre v-if="result" class="output">{{ result }}</pre>
  </section>
</template>

<style scoped>
.hero {
  padding: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.03);
}
h1 {
  margin: 0 0 10px;
  font-size: 28px;
}
p {
  margin: 0 0 18px;
  opacity: 0.9;
  line-height: 1.5;
}
code {
  background: rgba(255, 255, 255, 0.08);
  padding: 2px 6px;
  border-radius: 8px;
}
.actions {
  display: flex;
  gap: 10px;
  align-items: center;
}
.btn {
  cursor: pointer;
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.06);
  color: inherit;
  padding: 10px 12px;
  border-radius: 10px;
}
.btn:hover {
  background: rgba(255, 255, 255, 0.09);
}
.output {
  margin-top: 14px;
  padding: 12px;
  border-radius: 10px;
  background: rgba(0, 0, 0, 0.35);
  overflow: auto;
}
</style>
