<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  getAdminVerifications,
  updateAdminVerificationStatus,
} from '../../api/admin';
import type {
  AdminVerification,
  AdminVerificationActionStatus,
} from '../../types/admin';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const feedback = ref<string | null>(null);
const verifications = ref<AdminVerification[]>([]);
const processingId = ref<number | null>(null);

const pendingVerifications = computed(() =>
  verifications.value.filter((item) => item.status === 'PENDING'),
);

function formatDate(value?: string): string {
  if (!value) return 'Unknown';

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date);
}

function getGuideDisplayName(item: AdminVerification): string {
  const fullname = item.guide?.fullname?.trim();
  if (fullname) return fullname;
  return `Guide #${item.guide_id}`;
}

function getGuideEmail(item: AdminVerification): string {
  return item.guide?.email?.trim() || 'Email not provided';
}

async function loadVerifications(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    verifications.value = await getAdminVerifications();
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not load verification requests right now.';
    verifications.value = [];
  } finally {
    loading.value = false;
  }
}

async function handleStatusUpdate(item: AdminVerification, status: AdminVerificationActionStatus): Promise<void> {
  if (processingId.value !== null) return;

  const actionLabel = status === 'APPROVED' ? 'approve' : 'reject';
  const confirmed = window.confirm(`Are you sure you want to ${actionLabel} ${getGuideDisplayName(item)}?`);
  if (!confirmed) return;

  processingId.value = item.id;
  feedback.value = null;

  try {
    await updateAdminVerificationStatus(item.id, status);
    verifications.value = verifications.value.filter((row) => row.id !== item.id);
    feedback.value = `Request ${status === 'APPROVED' ? 'approved' : 'rejected'} successfully.`;
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    feedback.value = err.response?.data?.message ?? 'Could not update request status right now.';
  } finally {
    processingId.value = null;
  }
}

onMounted(() => {
  void loadVerifications();
});
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <main class="mx-auto max-w-7xl px-5 pb-16 pt-24 sm:px-8">
      <section class="relative overflow-hidden rounded-3xl bg-surface-container-low p-6 shadow-sm sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-primary/10 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-24 -left-12 h-64 w-64 rounded-full bg-tertiary/10 blur-3xl" />

        <header class="relative mb-7 flex flex-wrap items-end justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Admin / Verification Queue</p>
            <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">Guide Verifications</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">
              Review pending guide badge requests and take action.
            </p>
          </div>

          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full bg-surface-container px-5 py-3 text-sm font-semibold text-on-surface transition hover:bg-surface-container-high hover:text-primary"
            @click="router.push({ name: 'admin-dashboard' })"
          >
            <span class="material-symbols-outlined text-lg">dashboard</span>
            Back to Dashboard
          </button>
        </header>

        <div class="relative mb-5 rounded-2xl bg-surface p-4 shadow-sm ring-1 ring-outline-variant/10">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-on-surface-variant">
              Pending requests:
              <span class="font-semibold text-on-surface">{{ pendingVerifications.length }}</span>
            </p>

            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-xl bg-surface-container px-4 py-2 text-sm font-semibold text-on-surface-variant transition hover:bg-surface-container-high hover:text-primary"
              :disabled="loading"
              @click="loadVerifications"
            >
              <span class="material-symbols-outlined text-base">refresh</span>
              Refresh
            </button>
          </div>
        </div>

        <div v-if="error" class="relative mb-5 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
          {{ error }}
        </div>

        <div v-if="feedback" class="relative mb-5 rounded-xl bg-secondary-container/70 px-4 py-3 text-sm font-medium text-secondary">
          {{ feedback }}
        </div>

        <div v-if="loading" class="relative space-y-3">
          <div v-for="i in 5" :key="i" class="h-16 animate-pulse rounded-xl bg-surface-container" />
        </div>

        <div
          v-else-if="pendingVerifications.length === 0"
          class="relative rounded-2xl bg-surface p-8 text-center shadow-sm ring-1 ring-outline-variant/10"
        >
          <span class="material-symbols-outlined text-3xl text-primary">verified_user</span>
          <p class="mt-2 text-lg font-semibold">No pending requests</p>
          <p class="mt-1 text-on-surface-variant">All verification requests are processed.</p>
        </div>

        <div v-else class="relative overflow-hidden rounded-2xl bg-surface shadow-sm ring-1 ring-outline-variant/10">
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-surface-container-low text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold">Guide</th>
                  <th class="px-4 py-3 font-semibold">Document</th>
                  <th class="px-4 py-3 font-semibold">Submitted</th>
                  <th class="px-4 py-3 font-semibold">Status</th>
                  <th class="px-4 py-3 font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in pendingVerifications"
                  :key="item.id"
                  class="border-t border-outline-variant/15"
                >
                  <td class="px-4 py-3">
                    <p class="font-semibold text-on-surface">{{ getGuideDisplayName(item) }}</p>
                    <p class="text-xs text-on-surface-variant">{{ getGuideEmail(item) }}</p>
                  </td>

                  <td class="px-4 py-3">
                    <a
                      :href="item.file_url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-1 rounded-full bg-surface-container px-3 py-1.5 text-xs font-semibold text-primary transition hover:bg-surface-container-high"
                    >
                      <span class="material-symbols-outlined text-sm">description</span>
                      Open document
                    </a>
                  </td>

                  <td class="px-4 py-3 text-on-surface-variant">{{ formatDate(item.created_at) }}</td>

                  <td class="px-4 py-3">
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                      {{ item.status }}
                    </span>
                  </td>

                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                      <button
                        type="button"
                        class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:brightness-110 disabled:opacity-60"
                        :disabled="processingId === item.id"
                        @click="handleStatusUpdate(item, 'APPROVED')"
                      >
                        {{ processingId === item.id ? 'Processing...' : 'Approve' }}
                      </button>

                      <button
                        type="button"
                        class="rounded-full bg-error px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="processingId === item.id"
                        @click="handleStatusUpdate(item, 'REJECTED')"
                      >
                        {{ processingId === item.id ? 'Processing...' : 'Reject' }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
