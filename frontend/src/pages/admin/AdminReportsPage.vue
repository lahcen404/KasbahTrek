<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getAdminReports, updateAdminReportStatus } from '../../api/admin';
import type {
  AdminTripReport,
  AdminTripReportActionStatus,
  AdminTripReportStatus,
} from '../../types/admin';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const feedback = ref<string | null>(null);
const reports = ref<AdminTripReport[]>([]);
const query = ref('');
const statusFilter = ref<'ALL' | AdminTripReportStatus>('ALL');
const processingId = ref<number | null>(null);

const statusDraft = ref<Record<number, AdminTripReportStatus>>({});

const filteredReports = computed(() => {
  const q = query.value.trim().toLowerCase();

  return reports.value.filter((report) => {
    if (statusFilter.value !== 'ALL' && report.status !== statusFilter.value) {
      return false;
    }

    if (!q) return true;

    const traveler = travelerName(report).toLowerCase();
    const tour = tourTitle(report).toLowerCase();
    const reason = (report.reason ?? '').toLowerCase();
    const admin = handledBy(report).toLowerCase();

    return (
      traveler.includes(q)
      || tour.includes(q)
      || reason.includes(q)
      || admin.includes(q)
      || String(report.id).includes(q)
    );
  });
});

const pendingCount = computed(() => reports.value.filter((r) => r.status === 'PENDING').length);
const resolvedCount = computed(() => reports.value.filter((r) => r.status === 'RESOLVED').length);

function statusBadge(status: AdminTripReportStatus): string {
  if (status === 'APPROVED') return 'bg-emerald-100 text-emerald-700';
  if (status === 'REJECTED') return 'bg-rose-100 text-rose-700';
  if (status === 'RESOLVED') return 'bg-sky-100 text-sky-700';
  return 'bg-amber-100 text-amber-700';
}

function travelerName(report: AdminTripReport): string {
  const name = report.traveler?.fullname?.trim() || report.traveler?.name?.trim();
  if (name) return name;
  return `Traveler #${report.traveler_id}`;
}

function tourTitle(report: AdminTripReport): string {
  const title = report.tour?.title?.trim();
  if (title) return title;
  return `Tour #${report.tour_id}`;
}

function handledBy(report: AdminTripReport): string {
  const name = report.admin?.fullname?.trim() || report.admin?.name?.trim();
  if (name) return name;
  if (report.admin_id) return `Admin #${report.admin_id}`;
  return 'Not handled yet';
}

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

function getDraftStatus(report: AdminTripReport): AdminTripReportStatus {
  return statusDraft.value[report.id] ?? report.status;
}

function setDraftStatus(reportId: number, status: AdminTripReportStatus): void {
  statusDraft.value = {
    ...statusDraft.value,
    [reportId]: status,
  };
}

function canUpdateStatus(report: AdminTripReport): boolean {
  return getDraftStatus(report) !== report.status;
}

async function loadReports(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    const data = await getAdminReports();
    reports.value = data;
    statusDraft.value = Object.fromEntries(data.map((item) => [item.id, item.status]));
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not load reports right now.';
    reports.value = [];
    statusDraft.value = {};
  } finally {
    loading.value = false;
  }
}

async function updateStatus(report: AdminTripReport): Promise<void> {
  if (processingId.value !== null) return;

  const nextStatus = getDraftStatus(report);
  if (nextStatus === report.status) return;

  const confirmed = window.confirm(`Update report #${report.id} to ${nextStatus}?`);
  if (!confirmed) return;

  processingId.value = report.id;
  feedback.value = null;

  try {
    const updated = await updateAdminReportStatus(report.id, nextStatus as AdminTripReportActionStatus);
    reports.value = reports.value.map((item) => (item.id === report.id ? updated : item));
    setDraftStatus(updated.id, updated.status);
    feedback.value = 'Report status updated successfully.';
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    feedback.value = err.response?.data?.message ?? 'Could not update report status right now.';
  } finally {
    processingId.value = null;
  }
}

onMounted(() => {
  void loadReports();
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
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Admin / Report Moderation</p>
            <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">Trip Reports</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">
              Review traveler reports and update their moderation status.
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
          <div class="grid gap-3 lg:grid-cols-[1fr,220px,auto]">
            <div class="flex items-center gap-2 rounded-xl bg-surface-container px-3 py-2">
              <span class="material-symbols-outlined text-base text-on-surface-variant">search</span>
              <input
                v-model="query"
                type="text"
                placeholder="Search by traveler, tour, reason, admin, or report ID"
                aria-label="Search reports"
                class="w-full border-0 bg-transparent text-sm outline-none ring-0 placeholder:text-on-surface-variant/70 focus:border-0 focus:outline-none focus:ring-0"
              />
            </div>

            <div class="flex items-center gap-2 rounded-xl bg-surface-container px-3 py-2">
              <span class="material-symbols-outlined text-base text-on-surface-variant">filter_alt</span>
              <select
                v-model="statusFilter"
                aria-label="Filter reports by status"
                class="w-full border-0 bg-transparent text-sm outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0"
              >
                <option value="ALL">All statuses</option>
                <option value="PENDING">Pending</option>
                <option value="APPROVED">Approved</option>
                <option value="REJECTED">Rejected</option>
                <option value="RESOLVED">Resolved</option>
              </select>
            </div>

            <button
              type="button"
              class="inline-flex items-center justify-center gap-1 rounded-xl bg-surface-container px-4 py-2 text-sm font-semibold text-on-surface-variant transition hover:bg-surface-container-high hover:text-primary"
              :disabled="loading"
              @click="loadReports"
            >
              <span class="material-symbols-outlined text-base">refresh</span>
              Refresh
            </button>
          </div>

          <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs font-medium text-on-surface-variant">
            <p>
              Showing <span class="font-semibold text-on-surface">{{ filteredReports.length }}</span> of {{ reports.length }} reports
            </p>
            <p>
              Pending: <span class="font-semibold text-amber-700">{{ pendingCount }}</span>
              · Resolved: <span class="font-semibold text-sky-700">{{ resolvedCount }}</span>
            </p>
          </div>
        </div>

        <div v-if="error" class="relative mb-5 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
          {{ error }}
        </div>

        <div v-if="feedback" class="relative mb-5 rounded-xl bg-secondary-container/70 px-4 py-3 text-sm font-medium text-secondary">
          {{ feedback }}
        </div>

        <div v-if="loading" class="relative space-y-3">
          <div v-for="i in 6" :key="i" class="h-20 animate-pulse rounded-xl bg-surface-container" />
        </div>

        <div
          v-else-if="filteredReports.length === 0"
          class="relative rounded-2xl bg-surface p-8 text-center shadow-sm ring-1 ring-outline-variant/10"
        >
          <span class="material-symbols-outlined text-3xl text-primary">flag</span>
          <p class="mt-2 text-lg font-semibold">No reports found</p>
          <p class="mt-1 text-on-surface-variant">Try adjusting your search or status filter.</p>
        </div>

        <div v-else class="relative overflow-hidden rounded-2xl bg-surface shadow-sm ring-1 ring-outline-variant/10">
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-surface-container-low text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold">Report</th>
                  <th class="px-4 py-3 font-semibold">Traveler</th>
                  <th class="px-4 py-3 font-semibold">Tour</th>
                  <th class="px-4 py-3 font-semibold">Status</th>
                  <th class="px-4 py-3 font-semibold">Handled By</th>
                  <th class="px-4 py-3 font-semibold">Update</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="report in filteredReports"
                  :key="report.id"
                  class="border-t border-outline-variant/15"
                >
                  <td class="px-4 py-3">
                    <p class="font-semibold text-on-surface">#{{ report.id }}</p>
                    <p class="text-xs text-on-surface-variant">{{ report.reason }}</p>
                    <p class="mt-1 text-xs text-on-surface-variant">{{ formatDate(report.created_at) }}</p>
                  </td>

                  <td class="px-4 py-3">
                    <p class="font-semibold text-on-surface">{{ travelerName(report) }}</p>
                    <p class="text-xs text-on-surface-variant">{{ report.traveler?.email || 'No email' }}</p>
                  </td>

                  <td class="px-4 py-3 text-on-surface-variant">{{ tourTitle(report) }}</td>

                  <td class="px-4 py-3">
                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', statusBadge(report.status)]">
                      {{ report.status }}
                    </span>
                  </td>

                  <td class="px-4 py-3 text-on-surface-variant">{{ handledBy(report) }}</td>

                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <select
                        :value="getDraftStatus(report)"
                        class="min-w-[140px] rounded-xl border border-outline-variant/30 bg-surface-container px-3 py-2 text-sm font-semibold text-on-surface outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                        @change="setDraftStatus(report.id, (($event.target as HTMLSelectElement).value as AdminTripReportStatus))"
                      >
                        <option value="PENDING">PENDING</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="REJECTED">REJECTED</option>
                        <option value="RESOLVED">RESOLVED</option>
                      </select>

                      <button
                        type="button"
                        class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-on-primary transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="processingId === report.id || !canUpdateStatus(report)"
                        @click="updateStatus(report)"
                      >
                        {{ processingId === report.id ? 'Saving...' : 'Apply' }}
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
