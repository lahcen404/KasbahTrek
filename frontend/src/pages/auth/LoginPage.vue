<script setup lang="ts">
import type { AxiosError } from 'axios';
import { ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const heroImageUrl =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuBijAFJemguYAboRkJQRvY8fpXaXuCFmU3MU8vdtvuO6T0RLZUaU-NDuW90lFNSjNXJDjk3KkZClyH7jeQcB5DvdZIQdLMEQh29Urw641FMyl9pXX6cDD97jYGEkszILa1E54x9i-OaCpZigjY6RxNYz1_M7weLfK7IbjZxnTO6_caCWgTLS0Ml9Z9lhLyxDbH-N9hXSScgvo8HCR0S4KOf9biOa3O8DxkRyi03HkkJbm7lSnPA_RtTt_HuvXJJpEYCD5LLpCOBzrw';

const email = ref('');
const password = ref('');
// "Remember me" is UI only for now; the token always uses localStorage (see api/client.ts).
const rememberMe = ref(true);
const loading = ref(false);
const errorMsg = ref('');

async function onSubmit() {
  errorMsg.value = '';
  loading.value = true;
  try {
    await authStore.login(email.value.trim(), password.value);
    const redirectTarget =
      typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
        ? route.query.redirect
        : authStore.role === 'ADMIN'
          ? '/admin/dashboard'
          : authStore.role === 'GUIDE'
            ? '/guide/dashboard'
            : '/traveler/profile';
    await router.push(redirectTarget);
  } catch (e) {
    const err = e as AxiosError<{
      message?: string;
      errors?: Record<string, string[]>;
    }>;
    const data = err.response?.data;
    if (data?.errors) {
      const first = Object.values(data.errors)[0];
      errorMsg.value = first?.[0] ?? 'Login failed.';
    } else {
      errorMsg.value = data?.message ?? 'Login failed.';
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <!-- Split layout: matches reference min-h-[calc(100vh-64px)] -->
  <div class="flex w-full min-h-[calc(100vh-72px)] flex-1 flex-col md:flex-row">
    <!-- Visual side (desktop only) -->
    <div class="relative hidden overflow-hidden md:block md:w-1/2">
      <img
        :src="heroImageUrl"
        alt="Moroccan Landscape"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div
        class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-orange-900/60 via-transparent to-transparent p-16"
      >
        <h2 class="mb-4 text-5xl font-bold leading-tight tracking-tight text-white">
          The Atlas awaits your next story.
        </h2>
        <p class="max-w-md font-body text-xl leading-relaxed text-orange-50/90">
          Join our curated community of explorers and discover the hidden gems of the Maghreb.
        </p>
      </div>
    </div>

    <!-- Form side -->
    <div class="flex w-full items-center justify-center bg-surface p-6 sm:p-8 md:w-1/2">
      <div class="w-full max-w-md space-y-10">
        <header class="space-y-3">
          <h1 class="text-4xl font-bold tracking-tight text-on-surface">
            Welcome back
          </h1>
          <p class="text-slate-500 font-body">
            Please enter your details to access your dashboard.
          </p>
          <p
            v-if="route.query.registered"
            class="rounded-2xl bg-secondary-container/80 px-4 py-3 text-sm font-medium text-on-secondary-container"
          >
            Account created. You can sign in now.
          </p>
        </header>

        <form class="space-y-6" @submit.prevent="onSubmit">
          <p
            v-if="errorMsg"
            class="text-sm font-medium text-error bg-error-container/80 rounded-2xl px-4 py-3"
            role="alert"
          >
            {{ errorMsg }}
          </p>

          <div class="space-y-2">
            <label
              class="text-sm font-bold uppercase tracking-widest text-slate-600 font-label"
              for="login-email"
              >Email Address</label
            >
            <input
              id="login-email"
              v-model="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="w-full bg-surface-container-high border-none rounded-2xl py-4 px-6 text-on-surface focus:ring-2 focus:ring-primary/50 placeholder:text-slate-400"
              placeholder="email@kasbah-trek.com"
            />
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center gap-2">
              <label
                class="text-sm font-bold uppercase tracking-widest text-slate-600 font-label"
                for="login-password"
                >Password</label
              >
              <a
                href="#"
                class="text-sm font-semibold text-primary hover:text-orange-700 transition-colors shrink-0"
                >Forgot Password?</a
              >
            </div>
            <input
              id="login-password"
              v-model="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              minlength="6"
              class="w-full bg-surface-container-high border-none rounded-2xl py-4 px-6 text-on-surface focus:ring-2 focus:ring-primary/50"
              placeholder="••••••••"
            />
          </div>

          <div class="flex items-center gap-3">
            <input
              id="remember"
              v-model="rememberMe"
              name="remember"
              type="checkbox"
              class="w-5 h-5 rounded border-outline-variant/50 text-primary focus:ring-primary/50 bg-surface-container-high"
            />
            <label class="text-sm font-medium text-slate-600 cursor-pointer" for="remember">
              Remember me for 30 days
            </label>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-full bg-primary py-4 font-bold text-white shadow-lg shadow-primary/20 transition-all hover:brightness-110 hover:shadow-xl hover:shadow-primary/30 active:scale-95 disabled:pointer-events-none disabled:opacity-60"
          >
            {{ loading ? 'Signing in…' : 'Login' }}
          </button>

          <div class="relative py-4">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-surface-container-highest" />
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="bg-surface px-4 text-slate-400 font-medium">Or continue with</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <button
              type="button"
              class="flex items-center justify-center gap-2 py-3 px-4 border border-outline-variant/30 rounded-full hover:bg-surface-container-low transition-colors font-semibold text-slate-700 active:scale-95"
            >
              <span class="material-symbols-outlined text-xl">google</span>
              <span>Google</span>
            </button>
            <button
              type="button"
              class="flex items-center justify-center gap-2 py-3 px-4 border border-outline-variant/30 rounded-full hover:bg-surface-container-low transition-colors font-semibold text-slate-700 active:scale-95"
            >
              <span class="material-symbols-outlined text-xl">social_leaderboard</span>
              <span>Facebook</span>
            </button>
          </div>
        </form>

        <footer class="text-center">
          <p class="text-slate-500">
            Don't have an account?
            <RouterLink
              :to="{ name: 'register' }"
              class="ml-1 font-bold text-primary transition-colors hover:text-orange-700"
            >
              Register
            </RouterLink>
          </p>
        </footer>
      </div>
    </div>
  </div>
</template>
