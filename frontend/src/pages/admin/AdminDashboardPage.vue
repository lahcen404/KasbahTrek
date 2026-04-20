<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getCurrentUser } from '../../api/auth';
import { getAdminDashboardStats } from '../../api/admin';
import type { AdminDashboardStats } from '../../types/admin';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const adminName = ref('Admin');
const stats = ref<AdminDashboardStats | null>(null);

function money(value: number | string | null | undefined): string {
  const amount = typeof value === 'number'
    ? value
    : typeof value === 'string'
      ? Number.parseFloat(value)
      : 0;

  const safeAmount = Number.isFinite(amount) ? amount : 0;

  return new Intl.NumberFormat('en-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(safeAmount);
}

const summaryCards = computed(() => [
  {
    label: 'Travelers',
    value: stats.value?.total_travelers ?? 0,
    icon: 'group',
    tone: 'bg-sky-50 text-sky-700',
    detail: 'Registered travelers on the platform',
  },
  {
    label: 'Guides',
    value: stats.value?.total_guides ?? 0,
    icon: 'badge',
    tone: 'bg-emerald-50 text-emerald-700',
    detail: 'Active and pending guide accounts',
  },
  {
    label: 'Tours',
    value: stats.value?.total_tours ?? 0,
    icon: 'travel_explore',
    tone: 'bg-orange-50 text-orange-700',
    detail: 'Available tours in the system',
  },
  {
    label: 'Revenue',
    value: money(stats.value?.total_revenue ?? 0),
    icon: 'payments',
    tone: 'bg-violet-50 text-violet-700',
    detail: 'Confirmed booking revenue',
  },
  {
    label: 'Confirmed Bookings',
    value: stats.value?.total_confirmed_bookings ?? 0,
    icon: 'event_available',
    tone: 'bg-amber-50 text-amber-700',
    detail: 'Bookings ready for fulfillment',
  },
  {
    label: 'Pending Verifications',
    value: stats.value?.pending_verifications ?? 0,
    icon: 'verified_user',
    tone: 'bg-cyan-50 text-cyan-700',
    detail: 'Guide badge requests to review',
  },
  {
    label: 'Pending Trip Reports',
    value: stats.value?.pending_trip_reports ?? 0,
    icon: 'report_problem',
    tone: 'bg-rose-50 text-rose-700',
    detail: 'Traveler reports waiting action',
  },
]);

const adminActions = [
  {
    title: 'Manage Users',
    description: 'View, update, and remove travelers or guides.',
    icon: 'manage_accounts',
    route: 'admin-users',
  },
  {
    title: 'Manage Tours',
    description: 'Review tour listings and remove invalid content.',
    icon: 'tour',
    route: 'admin-tours',
  },
  {
    title: 'Manage Categories',
    description: 'Create, update, and remove tour categories.',
    icon: 'category',
    route: 'admin-categories',
  },
  {
    title: 'Verify Guides',
    description: 'Approve or reject guide verification requests.',
    icon: 'verified_user',
    route: 'admin-verifications',
  },
  {
    title: 'Handle Reports',
    description: 'Review trip reports and update their status.',
    icon: 'flag',
    route: 'admin-reports',
  },
  {
    title: 'View Statistics',
    description: 'Monitor bookings, revenue, and moderation tasks.',
    icon: 'insights',
    route: 'admin-dashboard',
  },
] as const;

async function loadDashboard(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    const [me, dashboardStats] = await Promise.all([getCurrentUser(), getAdminDashboardStats()]);
    adminName.value = me.fullname?.trim() || me.email?.trim() || 'Admin';
    stats.value = dashboardStats;
  } catch (e) {
    const err = e as { response?: { status?: number; data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not load admin dashboard right now.';
    stats.value = null;
  } finally {
    loading.value = false;
  }
}

function openAction(action: (typeof adminActions)[number]): void {
  void router.push({ name: action.route });
}

onMounted(() => {
  void loadDashboard();
});
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <main class="mx-auto max-w-7xl px-5 pb-16 pt-24 sm:px-8">
      <section class="relative overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container-low p-6 sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-primary/10 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-24 -left-12 h-64 w-64 rounded-full bg-tertiary/10 blur-3xl" />

        <header class="relative flex flex-wrap items-end justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Admin Dashboard</p>
            <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">Welcome back, {{ adminName }}</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">
              Monitor users, content, quality, and safety from one place.
            </p>
          </div>

          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-primary hover:text-primary"
            @click="router.push({ name: 'home' })"
          >
            <span class="material-symbols-outlined text-lg">home</span>
            View Site
          </button>
        </header>

        <div v-if="error" class="relative mt-6 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
          {{ error }}
        </div>

        <div v-if="loading" class="relative mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          <div v-for="i in 7" :key="i" class="h-36 animate-pulse rounded-2xl bg-surface-container" />
        </div>

        <div v-else class="relative mt-8 space-y-8">
          <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article
              v-for="card in summaryCards"
              :key="card.label"
              class="rounded-2xl border border-outline-variant/20 bg-surface p-5 shadow-sm"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-sm font-semibold uppercase tracking-[0.16em] text-on-surface-variant">
                    {{ card.label }}
                  </p>
                  <p class="mt-3 text-3xl font-bold text-on-surface">{{ card.value }}</p>
                </div>
                <div :class="['flex h-12 w-12 items-center justify-center rounded-full text-xl', card.tone]">
                  <span class="material-symbols-outlined">{{ card.icon }}</span>
                </div>
              </div>
              <p class="mt-4 text-sm text-on-surface-variant">{{ card.detail }}</p>
            </article>
          </div>

          <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="rounded-2xl border border-outline-variant/20 bg-surface p-5 shadow-sm">
              <div class="flex items-center gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Admin Workflows</p>
                  <h2 class="mt-1 text-2xl font-bold">What you can manage next</h2>
                </div>
              </div>

              <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <button
                  v-for="action in adminActions"
                  :key="action.title"
                  type="button"
                  class="rounded-2xl border border-outline-variant/20 bg-surface-container-low p-4 text-left transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md"
                  @click="openAction(action)"
                >
                  <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                      <span class="material-symbols-outlined text-lg">{{ action.icon }}</span>
                    </div>
                    <div>
                      <h3 class="font-semibold text-on-surface">{{ action.title }}</h3>
                      <p class="mt-1 text-sm text-on-surface-variant">{{ action.description }}</p>
                    </div>
                  </div>
                </button>
              </div>
            </section>

            <section class="rounded-2xl border border-outline-variant/20 bg-surface p-5 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Safety Snapshot</p>
              <h2 class="mt-1 text-2xl font-bold">Moderation overview</h2>
              <div class="mt-5 space-y-4">
                <div class="rounded-2xl bg-surface-container-low px-4 py-4">
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-on-surface-variant">Reports waiting review</p>
                      <p class="mt-1 text-2xl font-bold text-on-surface">
                        {{ stats?.pending_trip_reports ?? 0 }}
                      </p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-rose-600">report_problem</span>
                  </div>
                </div>

                <div class="rounded-2xl bg-surface-container-low px-4 py-4">
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-on-surface-variant">Guide verifications pending</p>
                      <p class="mt-1 text-2xl font-bold text-on-surface">
                        {{ stats?.pending_verifications ?? 0 }}
                      </p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-cyan-600">verified_user</span>
                  </div>
                </div>

                <div class="rounded-2xl bg-surface-container-low px-4 py-4">
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-on-surface-variant">Confirmed booking revenue</p>
                      <p class="mt-1 text-2xl font-bold text-on-surface">{{ money(stats?.total_revenue ?? 0) }}</p>
                    </div>
                    <span class="material-symbols-outlined text-3xl text-violet-600">payments</span>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
