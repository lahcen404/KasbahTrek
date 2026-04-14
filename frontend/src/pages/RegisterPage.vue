<script setup lang="ts">
import type { AxiosError } from 'axios';
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { registerAccount, type RegisterRole } from '../api/auth';

const router = useRouter();

const editorialImageUrl =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuCRuo_eEC1FqnnrqaqzhHJ57ILoAzeBvtyHD0494bsQEucWMevwEg7ybcp3Gah3OPS9eLpSVscbeyknMgCHwHVGzwuTbLAYrpOI_ggEuH9WzzFHZyKjZYw5hnP3Wv3NsO2gIr5hwfWprA0nfA5xo3pJgh2CG4Fr6vtflCzmuC2t9_olZmyi1NQBHhIFkK0r4tzgpB1NtGoDsHdsRoZ2MqhtHkXZeBcTGlLvClvUFQSyrSDytdx-SQejgNJPpAjtucQRUZHyMeEZeF0';

const role = ref<RegisterRole>('TRAVELER');
const fullname = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const termsAccepted = ref(false);

const loading = ref(false);
const errorMsg = ref('');

async function onSubmit() {
  errorMsg.value = '';
  if (!termsAccepted.value) {
    errorMsg.value = 'Please accept the Terms of Service and Privacy Policy!!!';
    return;
  }
  if (password.value !== passwordConfirmation.value) {
    errorMsg.value = 'Passwords do not match.';
    return;
  }

  loading.value = true;
  try {
    await registerAccount({
      fullname: fullname.value.trim(),
      email: email.value.trim(),
      password: password.value,
      password_confirmation: passwordConfirmation.value,
      role: role.value,
    });
    await router.push({ name: 'login', query: { registered: '1' } });
  } catch (e) {
    const err = e as AxiosError<{
      message?: string;
      errors?: Record<string, string[]>;
    }>;
    const data = err.response?.data;
    if (data?.errors) {
      const first = Object.values(data.errors).flat()[0];
      errorMsg.value = first ?? 'Registration failed.';
    } else {
      errorMsg.value = data?.message ?? 'Registration failed.';
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div
    class="relative z-0 flex min-h-[calc(100vh-72px)] flex-1 flex-col items-center justify-center overflow-hidden bg-background px-6 py-12 font-body text-on-background selection:bg-primary-container selection:text-on-primary-container zellige-pattern"
  >
    <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/10 blur-3xl" aria-hidden="true" />
    <div
      class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-secondary/10 blur-3xl"
      aria-hidden="true"
    />

    <div class="relative z-10 mx-auto grid w-full max-w-6xl items-center gap-12 md:grid-cols-2">
      <!-- Editorial (md+) -->
      <div class="hidden flex-col gap-8 pr-12 md:flex">
        <div class="relative">
          <span
            class="mb-4 inline-block rounded-full bg-secondary-container px-4 py-1 text-xs font-bold uppercase tracking-widest text-on-secondary-fixed-variant"
          >
            The Nomad Journey
          </span>
          <h1
            class="font-headline text-5xl font-bold leading-[1.1] tracking-tight text-orange-900 lg:text-7xl"
          >
            Begin your <br />
            <span class="italic text-primary">Atlas</span> story.
          </h1>
        </div>
        <p class="max-w-md text-lg leading-relaxed text-on-surface-variant">
          Join a community of elite nomads and local experts. Discover hidden oases, golden dunes, and
          the soul of Morocco.
        </p>
        <div class="relative aspect-[4/3] w-full overflow-hidden rounded-3xl shadow-2xl">
          <img
            :src="editorialImageUrl"
            alt="Moroccan Kasbah at sunset"
            class="h-full w-full object-cover"
          />
          <div
            class="absolute inset-0 bg-gradient-to-t from-orange-900/40 to-transparent"
            aria-hidden="true"
          />
          <div class="absolute bottom-6 left-6 text-white">
            <p class="font-headline text-xl font-semibold">Ait Benhaddou</p>
            <p class="text-sm opacity-80">Heritage Trek 2024</p>
          </div>
        </div>
      </div>

      <!-- Form card -->
      <div
        class="relative rounded-xl border border-outline-variant/10 bg-surface-container-lowest p-8 shadow-[0_20px_50px_rgba(30,42,47,0.06)] md:p-12"
      >
        <div class="mb-8">
          <h2 class="mb-2 font-headline text-3xl font-semibold text-on-background">Create Account</h2>
          <p class="text-on-surface-variant">Fill in the details to start your adventure.</p>
        </div>

        <form class="flex flex-col gap-6" @submit.prevent="onSubmit">
          <p
            v-if="errorMsg"
            class="rounded-2xl bg-error-container/90 px-4 py-3 text-sm font-medium text-error"
            role="alert"
          >
            {{ errorMsg }}
          </p>

          <div class="flex flex-col gap-3">
            <label class="text-xs font-bold uppercase tracking-widest text-on-surface-variant"
              >I am a...</label
            >
            <div class="grid grid-cols-2 gap-4">
              <label
                class="group relative flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-transparent bg-surface-container-high p-4 transition-all hover:bg-orange-50 has-[:checked]:border-primary has-[:checked]:bg-orange-50"
              >
                <input v-model="role" class="sr-only" name="role" type="radio" value="TRAVELER" />
                <span class="material-symbols-outlined text-3xl text-primary">hiking</span>
                <span class="text-sm font-bold text-orange-900">Traveler</span>
              </label>
              <label
                class="group relative flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-transparent bg-surface-container-high p-4 transition-all hover:bg-orange-50 has-[:checked]:border-primary has-[:checked]:bg-orange-50"
              >
                <input v-model="role" class="sr-only" name="role" type="radio" value="GUIDE" />
                <span class="material-symbols-outlined text-3xl text-secondary">camping</span>
                <span class="text-sm font-bold text-orange-900">Guide</span>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4">
            <div class="flex flex-col gap-1">
              <label class="pl-1 text-xs font-bold text-on-surface-variant">Full Name</label>
              <input
                v-model="fullname"
                class="rounded-md border-none bg-surface-container-high p-4 text-on-background transition-all placeholder:text-outline-variant focus:ring-2 focus:ring-primary/50"
                placeholder="Omar Al-Khattab"
                type="text"
                name="fullname"
                autocomplete="name"
                required
              />
            </div>
            <div class="flex flex-col gap-1">
              <label class="pl-1 text-xs font-bold text-on-surface-variant">Email Address</label>
              <input
                v-model="email"
                class="rounded-md border-none bg-surface-container-high p-4 text-on-background transition-all placeholder:text-outline-variant focus:ring-2 focus:ring-primary/50"
                placeholder="nomad@kasbahtrek.com"
                type="email"
                name="email"
                autocomplete="email"
                required
              />
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="flex flex-col gap-1">
                <label class="pl-1 text-xs font-bold text-on-surface-variant">Password</label>
                <input
                  v-model="password"
                  class="rounded-md border-none bg-surface-container-high p-4 text-on-background transition-all placeholder:text-outline-variant focus:ring-2 focus:ring-primary/50"
                  placeholder="••••••••"
                  type="password"
                  name="password"
                  autocomplete="new-password"
                  required
                  minlength="6"
                />
              </div>
              <div class="flex flex-col gap-1">
                <label class="pl-1 text-xs font-bold text-on-surface-variant">Confirm Password</label>
                <input
                  v-model="passwordConfirmation"
                  class="rounded-md border-none bg-surface-container-high p-4 text-on-background transition-all placeholder:text-outline-variant focus:ring-2 focus:ring-primary/50"
                  placeholder="••••••••"
                  type="password"
                  name="password_confirmation"
                  autocomplete="new-password"
                  required
                  minlength="6"
                />
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 px-1">
            <input
              id="terms"
              v-model="termsAccepted"
              class="rounded border-outline-variant text-primary focus:ring-primary/30"
              type="checkbox"
            />
            <label class="text-sm text-on-surface-variant" for="terms">
              I agree to the
              <a class="font-semibold text-primary hover:underline" href="#">Terms of Service</a>
              and
              <a class="font-semibold text-primary hover:underline" href="#">Privacy Policy</a>.
            </label>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="group flex items-center justify-center gap-2 rounded-full bg-primary px-8 py-5 text-lg font-bold text-on-primary shadow-xl transition-all hover:brightness-110 hover:shadow-lg disabled:opacity-60"
          >
            {{ loading ? 'Creating…' : 'Create Account' }}
            <span
              class="material-symbols-outlined transition-transform group-hover:translate-x-1"
              aria-hidden="true"
              >arrow_forward</span
            >
          </button>

          <div class="mt-4 text-center">
            <p class="text-on-surface-variant">
              Already have an account?
              <RouterLink
                :to="{ name: 'login' }"
                class="ml-1 font-bold text-primary hover:underline"
              >
                Login
              </RouterLink>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
