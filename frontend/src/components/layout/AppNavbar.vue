<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { normalizeAppRole } from '../../api/auth';
import { useAuthStore } from '../../stores/auth';

// logo url
const logoUrl = '/kasbah-trek.png';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const isHome = computed(() => route.name === 'home');
const isTours = computed(() => route.name === 'tours');
const isGuideDashboard = computed(() => route.name === 'guide-dashboard');
const isAdminDashboard = computed(() => route.name === 'admin-dashboard');
const isTravelerProfile = computed(() => route.name === 'traveler-profile');
const isGuideSection = computed(() => String(route.path).startsWith('/guide'));
const isTravelerSection = computed(() => String(route.path).startsWith('/traveler'));
const isTravelerFavorites = computed(() => route.name === 'traveler-favorites');

const normalizedRole = computed(() => normalizeAppRole(authStore.role));
const isAuthenticated = computed(() => authStore.isAuthenticated || Boolean(authStore.role));

const showDashboardLink = computed(() => {
  if (!isAuthenticated.value) {
    return false;
  }

  return true;
});

const showFavoritesLink = computed(() => {
  if (!isAuthenticated.value) {
    return false;
  }

  return normalizedRole.value === 'TRAVELER' || isTravelerSection.value;
});

const dashboardRoute = computed(() => {
  if (normalizedRole.value === 'ADMIN' || isAdminDashboard.value) {
    return { name: 'admin-dashboard' as const };
  }

  if (normalizedRole.value === 'GUIDE' || isGuideSection.value) {
    return { name: 'guide-dashboard' as const };
  }

  return { name: 'traveler-profile' as const };
});

const dashboardLabel = computed(() => {
  return 'Dashboard';
});

const isDashboardActive = computed(() => {
  return isGuideDashboard.value || isAdminDashboard.value || isTravelerProfile.value;
});

const mobileMenuOpen = ref(false);

async function handleLogout(): Promise<void> {
  await authStore.logout();
  mobileMenuOpen.value = false;
  await router.push({ name: 'login' });
}

watch(
  () => route.fullPath,
  () => {
    mobileMenuOpen.value = false;
  },
);
</script>

<template>
  <nav
    class="fixed left-0 right-0 top-0 z-50 bg-surface/80 shadow-[0_20px_50px_rgba(30,42,47,0.06)] backdrop-blur-md"
  >
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4">
      <RouterLink
        to="/"
        class="flex shrink-0 items-center gap-3"
      >
        <img
          :src="logoUrl"
          alt="Kasbah Trek"
          class="h-10 w-auto object-contain"
        />
        <span
          class="font-noto-serif text-2xl font-bold uppercase tracking-[0.06em] text-orange-900 dark:text-orange-500"
        >
          KASBAH TREK
        </span>
      </RouterLink>

      <div class="hidden items-center gap-8 md:flex">
        <RouterLink
          v-if="showDashboardLink"
          :to="dashboardRoute"
          :class="
            isDashboardActive
              ? 'border-b-2 border-orange-800 pb-1 font-bold text-orange-800 dark:border-orange-400 dark:text-orange-400'
              : 'text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400'
          "
          >{{ dashboardLabel }}</RouterLink
        >
        <RouterLink
          :to="{ name: 'home' }"
          :class="
            isHome
              ? 'border-b-2 border-orange-800 pb-1 font-bold text-orange-800 dark:border-orange-400 dark:text-orange-400'
              : 'text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400'
          "
          >Home</RouterLink
        >
        <RouterLink
          :to="{ name: 'tours' }"
          :class="
            isTours
              ? 'border-b-2 border-orange-800 pb-1 font-bold text-orange-800 dark:border-orange-400 dark:text-orange-400'
              : 'text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400'
          "
          >Tours</RouterLink
        >
        <RouterLink
          v-if="showFavoritesLink"
          :to="{ name: 'traveler-favorites' }"
          :class="
            isTravelerFavorites
              ? 'border-b-2 border-orange-800 pb-1 font-bold text-orange-800 dark:border-orange-400 dark:text-orange-400'
              : 'text-slate-600 transition-colors hover:text-orange-800 dark:text-slate-400'
          "
          >Favorites</RouterLink
        >
      </div>

      <div class="flex items-center gap-4">
        <button
          type="button"
          class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-outline-variant/30 bg-surface-container-lowest text-slate-700 transition-colors hover:bg-surface-container-low md:hidden"
          :aria-expanded="mobileMenuOpen ? 'true' : 'false'"
          aria-controls="mobile-nav"
          aria-label="Open menu"
          @click="mobileMenuOpen = !mobileMenuOpen"
        >
          <span class="material-symbols-outlined">
            {{ mobileMenuOpen ? 'close' : 'menu' }}
          </span>
        </button>
        <template v-if="!isAuthenticated">
          <RouterLink
            :to="{ name: 'login' }"
            class="hidden px-4 py-2 font-semibold text-slate-600 transition-colors hover:text-orange-800 md:block"
          >
            Login
          </RouterLink>
          <RouterLink
            :to="{ name: 'register' }"
            class="hidden rounded-full bg-primary px-8 py-3 font-bold text-on-primary transition-all hover:brightness-110 hover:shadow-lg active:scale-95 md:inline-flex"
          >
            Register
          </RouterLink>
        </template>
        <button
          v-else-if="isAuthenticated"
          type="button"
          class="hidden rounded-full border border-outline-variant/30 bg-surface px-6 py-3 font-bold text-slate-700 transition-colors hover:bg-surface-container-low active:scale-95 md:inline-flex"
          @click="handleLogout"
        >
          Logout
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div
      id="mobile-nav"
      class="md:hidden"
      v-show="mobileMenuOpen"
    >
      <div class="mx-auto w-full max-w-7xl px-6 pb-4">
        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-4 shadow-sm">
          <div class="flex flex-col gap-3">
            <RouterLink
              v-if="showDashboardLink"
              :to="dashboardRoute"
              class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low"
              >{{ dashboardLabel }}</RouterLink
            >
            <RouterLink
              :to="{ name: 'home' }"
              class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low"
              >Home</RouterLink
            >
            <RouterLink
              :to="{ name: 'tours' }"
              class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low"
              >Tours</RouterLink
            >
            <RouterLink
              v-if="showFavoritesLink"
              :to="{ name: 'traveler-favorites' }"
              class="rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-surface-container-low"
              >Favorites</RouterLink
            >
          </div>

          <div class="mt-4 grid grid-cols-2 gap-3" v-if="!isAuthenticated">
            <RouterLink
              :to="{ name: 'login' }"
              class="rounded-full border border-outline-variant/30 bg-surface px-4 py-3 text-center font-bold text-slate-700 transition-colors hover:bg-surface-container-low active:scale-95"
            >
              Login
            </RouterLink>
            <RouterLink
              :to="{ name: 'register' }"
              class="rounded-full bg-primary px-4 py-3 text-center font-bold text-on-primary transition-all hover:brightness-110 hover:shadow-lg active:scale-95"
            >
              Register
            </RouterLink>
          </div>
          <div class="mt-4" v-else-if="isAuthenticated">
            <button
              type="button"
              class="w-full rounded-full border border-outline-variant/30 bg-surface px-4 py-3 text-center font-bold text-slate-700 transition-colors hover:bg-surface-container-low active:scale-95"
              @click="handleLogout"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
