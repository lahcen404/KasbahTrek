import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => ({
  plugins: [vue()],
  server: {
    port: 5173,
    strictPort: true,
    proxy: {
      // Local dev (outside Docker): point to localhost:8080
      // Docker dev: we override with VITE_API_TARGET=http://nginx:80
      '/api': {
        target: process.env.VITE_API_TARGET ?? 'http://localhost:8080',
        changeOrigin: true,
      },
      '/storage': {
        target: process.env.VITE_API_TARGET ?? 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
}));

