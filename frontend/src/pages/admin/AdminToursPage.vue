<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { deleteAdminTour, getAdminTours } from '../../api/admin';
import { tourImageUrl } from '../../api/tours';
import type { Tour } from '../../types/tours';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const feedback = ref<string | null>(null);
const tours = ref<Tour[]>([]);
const query = ref('');
const deletingTourId = ref<number | null>(null);

const filteredTours = computed(() => {
  const q = query.value.trim().toLowerCase();

  return tours.value.filter((tour) => {
    if (!q) return true;

    const title = (tour.title ?? '').toLowerCase();
    const location = (tour.location ?? '').toLowerCase();
    const guideName = (tour.guide?.fullname ?? tour.guide?.name ?? '').toLowerCase();
    const categoryName = (typeof tour.category === 'string' ? tour.category : tour.category?.name ?? '').toLowerCase();

    return (
      title.includes(q)
      || location.includes(q)
      || guideName.includes(q)
      || categoryName.includes(q)
      || String(tour.id).includes(q)
    );
  });
});

function formatPrice(value: number | null | undefined): string {
  const amount = typeof value === 'number' ? value : 0;
  return new Intl.NumberFormat('en-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(amount);
}

function categoryLabel(tour: Tour): string {
  if (typeof tour.category === 'string' && tour.category.trim()) return tour.category;
  if (tour.category && typeof tour.category !== 'string' && tour.category.name?.trim()) {
    return tour.category.name;
  }
  return 'Uncategorized';
}

function guideLabel(tour: Tour): string {
  const name = tour.guide?.fullname?.trim() || tour.guide?.name?.trim();
  if (name) return name;
  if (tour.guide?.id) return `Guide #${tour.guide.id}`;
  return 'Unknown guide';
}

function difficultyLabel(tour: Tour): string {
  return (tour.difficulty ?? 'N/A').toUpperCase();
}

function coverImage(tour: Tour): string {
  const firstImage = tour.images?.[0]?.path;
  return tourImageUrl(firstImage) ?? 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=220&q=80';
}

async function loadTours(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    tours.value = await getAdminTours();
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not load tours right now.';
    tours.value = [];
  } finally {
    loading.value = false;
  }
}

async function removeTour(tour: Tour): Promise<void> {
  if (deletingTourId.value !== null) return;

  const confirmed = window.confirm(`Delete "${tour.title}"? This action cannot be undone.`);
  if (!confirmed) return;

  deletingTourId.value = tour.id;
  feedback.value = null;

  try {
    await deleteAdminTour(tour.id);
    tours.value = tours.value.filter((item) => item.id !== tour.id);
    feedback.value = 'Tour deleted successfully.';
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    feedback.value = err.response?.data?.message ?? 'Could not delete this tour right now.';
  } finally {
    deletingTourId.value = null;
  }
}

function openTourDetails(tourId: number): void {
  void router.push({ name: 'tour-details', params: { id: tourId } });
}

onMounted(() => {
  void loadTours();
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
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Admin / Manage Tours</p>
            <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">Admin Tours</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">
              Review tours and delete listings that break platform rules.
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
          <div class="grid gap-3 lg:grid-cols-[1fr,auto,auto]">
            <div class="flex items-center gap-2 rounded-xl bg-surface-container px-3 py-2">
              <span class="material-symbols-outlined text-base text-on-surface-variant">search</span>
              <input
                v-model="query"
                type="text"
                placeholder="Search by title, guide, category, location, or tour ID"
                aria-label="Search tours"
                class="w-full border-0 bg-transparent text-sm outline-none ring-0 placeholder:text-on-surface-variant/70 focus:border-0 focus:outline-none focus:ring-0"
              />
            </div>

            <p class="inline-flex items-center rounded-xl bg-surface-container px-4 py-2 text-sm text-on-surface-variant">
              Showing <span class="mx-1 font-semibold text-on-surface">{{ filteredTours.length }}</span> of {{ tours.length }} tours
            </p>

            <button
              type="button"
              class="inline-flex items-center justify-center gap-1 rounded-xl bg-surface-container px-4 py-2 text-sm font-semibold text-on-surface-variant transition hover:bg-surface-container-high hover:text-primary"
              :disabled="loading"
              @click="loadTours"
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
          <div v-for="i in 6" :key="i" class="h-20 animate-pulse rounded-xl bg-surface-container" />
        </div>

        <div
          v-else-if="filteredTours.length === 0"
          class="relative rounded-2xl bg-surface p-8 text-center shadow-sm ring-1 ring-outline-variant/10"
        >
          <span class="material-symbols-outlined text-3xl text-primary">tour</span>
          <p class="mt-2 text-lg font-semibold">No tours found</p>
          <p class="mt-1 text-on-surface-variant">Try adjusting your search query.</p>
        </div>

        <div v-else class="relative overflow-hidden rounded-2xl bg-surface shadow-sm ring-1 ring-outline-variant/10">
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-surface-container-low text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold">Tour</th>
                  <th class="px-4 py-3 font-semibold">Guide</th>
                  <th class="px-4 py-3 font-semibold">Category</th>
                  <th class="px-4 py-3 font-semibold">Price</th>
                  <th class="px-4 py-3 font-semibold">Difficulty</th>
                  <th class="px-4 py-3 font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="tour in filteredTours"
                  :key="tour.id"
                  class="border-t border-outline-variant/15"
                >
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <img
                        :src="coverImage(tour)"
                        alt="Tour cover"
                        class="h-12 w-12 rounded-xl object-cover"
                      />
                      <div>
                        <p class="font-semibold text-on-surface">{{ tour.title }}</p>
                        <p class="text-xs text-on-surface-variant">{{ tour.location || 'Morocco' }} · #{{ tour.id }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 py-3 text-on-surface-variant">{{ guideLabel(tour) }}</td>
                  <td class="px-4 py-3 text-on-surface-variant">{{ categoryLabel(tour) }}</td>
                  <td class="px-4 py-3 font-semibold text-on-surface">{{ formatPrice(tour.price) }}</td>
                  <td class="px-4 py-3 text-on-surface-variant">{{ difficultyLabel(tour) }}</td>

                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                      <button
                        type="button"
                        class="rounded-full bg-surface-container px-3 py-1.5 text-xs font-semibold text-on-surface-variant transition hover:bg-surface-container-high hover:text-primary"
                        @click="openTourDetails(tour.id)"
                      >
                        View
                      </button>

                      <button
                        type="button"
                        class="rounded-full bg-error px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="deletingTourId === tour.id"
                        @click="removeTour(tour)"
                      >
                        {{ deletingTourId === tour.id ? 'Deleting...' : 'Delete' }}
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
