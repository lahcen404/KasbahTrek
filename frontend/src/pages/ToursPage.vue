<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { getCategories, type Category } from '../api/categories';
import { getTours, tourImageUrl, type Tour } from '../api/tours';

// states
const loading = ref(true);
const error = ref<string | null>(null);
const tours = ref<Tour[]>([]);
const categories = ref<Category[]>([]);

// filter state (basic)
const selectedCategoryIds = ref<number[]>([]);
const selectedDuration = ref<'DAY' | '2_4' | '5_PLUS' | null>(null);
const selectedDifficulty = ref<'EASY' | 'MEDIUM' | 'HARD' | null>(null);
const mobileFiltersOpen = ref(false);

// derived values
const hasTours = computed(() => tours.value.length > 0);

const filteredTours = computed(() => {
  let list = tours.value;

  // category filter (multiple selection)
  if (selectedCategoryIds.value.length > 0) {
    const ids = new Set(selectedCategoryIds.value);
    list = list.filter((t) => typeof t.category_id === 'number' && ids.has(t.category_id));
  }

  // difficulty filter
  if (selectedDifficulty.value) {
    list = list.filter((t) => (t.difficulty ?? '').toUpperCase() === selectedDifficulty.value);
  }

  // duration filter 
  if (selectedDuration.value) {
    list = list.filter((t) => {
      const hours = t.duration_hours ?? null;
      if (typeof hours !== 'number') return false;
      const days = hours / 24;
      if (selectedDuration.value === 'DAY') return days <= 1.25;
      if (selectedDuration.value === '2_4') return days > 1.25 && days < 4.75;
      return days >= 4.75;
    });
  }

  return list;
});

function categoryLabel(tour: Tour): string {
  const category =
    typeof tour.category === 'string'
      ? tour.category
      : tour.category?.name ?? tour.category_name ?? null;
  if (category) return String(category);
  if (typeof tour.category_id === 'number') return `Category #${tour.category_id}`;
  return 'Uncategorized';
}

function difficultyLabel(tour: Tour): string {
  return tour.difficulty ? String(tour.difficulty) : '—';
}

function durationLabel(tour: Tour): string {
  if (typeof tour.duration_hours === 'number' && tour.duration_hours > 0) {
    const days = Math.max(1, Math.round(tour.duration_hours / 24));
    return `${days} Day${days > 1 ? 's' : ''}`;
  }
  return '—';
}

function locationLabel(tour: Tour): string {
  return tour.location ?? 'Morocco';
}

function priceLabel(tour: Tour): string {
  const value = typeof tour.price === 'number' ? tour.price : 0;
  const rounded = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(value);
  return `$${rounded}`;
}

function heroImageFor(idx: number): string {
  const fallbacks = [
    'https://lh3.googleusercontent.com/aida-public/AB6AXuCsi3htFTVgyB7S4dPlUthLxJwiWb3Xv8JCAkYOzdBLfYgLifWiv51gt8ckh351gXZVspgNZYdHD_g3E8sFoqnc0i-YEc_k1VYvl4sosATpCl1zAGlfE-Qui21Jw79s1uKmrr1kWARAcrCKjtvX1l-C42NYPjqtyO1COCuiAcPMOpYfJhDHd_I_YuWeYX1oeoU_xtL25QXPcYwAXeaTkw_MQMr1xn0KJ8Z-B3UxLO5e8Z73vKRxHQOWJvuvXcT2NM4yotxWo47koY8',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuCVycNcdGcJof4_PUEdUytSCNj8609V3iQi5qm4KBpq39H7JvTNGMhbwfHJOg8GRIHwF3HdHS4NKWbujqtlHMxN5exq9_KfmbnGgb7YHFhHEy0ADBuIXsdaznVdNq7F6WMflBJzxN3a__5MiWAFoHETKOCED3t8ZfQ9LaVVjtYcu1L5SZKGlZJGknn3FQ_8z5I_gHjMJlE20b2aWcQ6dDh7KW5ui5Q-zjVqEtTjJuGXJt8nn1P-2DJ8Gv7RGOCe2TNkLVXv92sX3zE',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuCAZM-ubZcoE3GPrdLFNpkf7PgJ0Q0O9y_t37JuUjqS2FWJ44wZ9WC9AAz8wbHoVGhPMRsbaOA5hvS1T-XQqCCrVN0Ism79Iyy37C9aOxMpFDye5qESAS0k-vJvUJz1KJ-5-WtwnXW3cUhQ2e5Xl2z6Lk3qzDkZVvLei3dhsTzuHmZ76zAK5T52Je9yrXxYgFlNXSO2gZx7x6g9Z9pFYVw1CMg-M7K7Yl1Q7Xeg9DOgCBJbBmZAvbk3tvnRjctCzdu4VrTuAkMdzLY',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuC2WW8n6lykISFSxUSnivEXBEy2W0E-LuU3MiE08NJS8uGFiyiDEXvMXXYKyUPJfiTlSi7qZ03l8wlm6XeuqpI17rxqXqtiJQvMcbCGlYoo0mwizPQV9u5SCjlheRVZoc5_qOhoj5_GxGNfMSPMK2H0s7HR7GyDK6f_by5jSFy994aQN2uXCugsjtFlE0Nrq-AxFFX6QZ5bPWHtj-pZTemCxeHiSGeaOuA4u7pV24SC4qMK0OGlgC6evRl03g5F_ne847T81Ola_X8',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuAa2nuqgKxIqUG8F0zvdafeukK2kJkGiK9QEOKN5oGH-At_qwnt3Xmlf93EXlvRrdyZ93wX3XerTCiYDZG6YtQSc5EcwGvaJIj87GWC0AwA1nQt1MS6Cpu9QFhz4-2gxIHrQu3HlCqRqlrMlz9-vzSapmoTUByWDL-mSAz5Zha58KPT3VubBk0oVRySJ0W4T7R2-Mef_coYqgki7TSYQ2JIHlCFWvnG3O54lnuFUc7LYRbaJ9EZd1F13mAwD_J7hq5ipl9J-0BQry0',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuC-AKuE6w_PMM6dHG0mnUBNFC8DCq2xpKrN_IaAAWSeQwwWRmwK2eieAzSc-RyAhENIGALqzQoB5Z5zayuU4DLzbF3XGYlZnGYcrz5FqWGlHMQInZJ9fgF7ULRIAggLLA3EaRmc5EmC_5EV5gCDDR-D5mUVnV1q_gR2GbQHJUqGvWriQRwkHK1qFOQIo0fhTxRcStAyu5Kvfgz0MZ8LIlBd0g76G-5knzW-E3G72xFMyFHoWJVBH-fjP4iOsCyKcktUoDQF7sbU1XM',
  ];
  return fallbacks[idx % fallbacks.length];
}

function cardImage(tour: Tour, idx: number): string {
  const first = tour.images?.[0]?.path;
  return tourImageUrl(first) ?? heroImageFor(idx);
}

// fetch data
onMounted(async () => {
  loading.value = true;
  error.value = null;
  try {
    categories.value = await getCategories();
    tours.value = await getTours({ per_page: 30 });
  } catch {
    error.value = 'Could not load tours. Please refresh.';
    tours.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-surface font-body text-on-surface zellige-pattern">
    <main class="mx-auto max-w-7xl px-6 pb-20 pt-28">
      <header class="mb-12 max-w-3xl md:mb-16">
        <h1 class="mb-6 font-headline text-5xl font-bold tracking-tight text-primary md:text-7xl">
          Curated Moroccan Adventures
        </h1>
        <p class="text-lg leading-relaxed text-on-surface-variant md:text-xl">
          Discover the soul of North Africa through hand-picked journeys. From the whispering dunes of
          the Sahara to the rugged peaks of the Atlas, our treks are designed for the modern nomad.
        </p>
      </header>

      <div class="flex flex-col gap-12 md:flex-row">
        <!-- Sidebar filters (UI only for now) -->
        <aside class="hidden w-72 shrink-0 md:block">
          <div
            class="sticky top-32 space-y-10 rounded-3xl border border-outline-variant/20 bg-surface/60 p-6 backdrop-blur-md"
          >
            <div>
              <h3 class="mb-6 font-headline text-xl font-semibold text-primary">Category</h3>
              <div class="space-y-4">
                <label
                  v-for="cat in categories"
                  :key="cat.id"
                  class="group flex cursor-pointer items-center gap-3"
                >
                  <input
                    v-model="selectedCategoryIds"
                    :value="cat.id"
                    type="checkbox"
                    class="h-5 w-5 rounded border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary">
                    {{ cat.name }}
                  </span>
                </label>
              </div>
            </div>

            <div>
              <h3 class="mb-6 font-headline text-xl font-semibold text-primary">Duration</h3>
              <div class="space-y-4">
                <label class="group flex cursor-pointer items-center gap-3">
                  <input
                    v-model="selectedDuration"
                    value="DAY"
                    type="radio"
                    name="duration"
                    class="h-5 w-5 border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary"
                    >Day Trips</span
                  >
                </label>
                <label class="group flex cursor-pointer items-center gap-3">
                  <input
                    v-model="selectedDuration"
                    value="2_4"
                    type="radio"
                    name="duration"
                    class="h-5 w-5 border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary"
                    >2-4 Days</span
                  >
                </label>
                <label class="group flex cursor-pointer items-center gap-3">
                  <input
                    v-model="selectedDuration"
                    value="5_PLUS"
                    type="radio"
                    name="duration"
                    class="h-5 w-5 border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary"
                    >5+ Days</span
                  >
                </label>
              </div>
            </div>

            <div>
              <h3 class="mb-6 font-headline text-xl font-semibold text-primary">Difficulty</h3>
              <div class="grid grid-cols-2 gap-3">
                <button
                  type="button"
                  :class="
                    'rounded-full border border-outline-variant/20 px-4 py-2 text-sm font-semibold transition-all ' +
                    (selectedDifficulty === 'EASY'
                      ? 'bg-primary text-white'
                      : 'text-on-surface-variant hover:bg-primary hover:text-white')
                  "
                  @click="selectedDifficulty = selectedDifficulty === 'EASY' ? null : 'EASY'"
                >
                  Easy
                </button>
                <button
                  type="button"
                  :class="
                    'rounded-full border border-outline-variant/20 px-4 py-2 text-sm font-semibold transition-all ' +
                    (selectedDifficulty === 'MEDIUM'
                      ? 'bg-primary text-white'
                      : 'text-on-surface-variant hover:bg-primary hover:text-white')
                  "
                  @click="selectedDifficulty = selectedDifficulty === 'MEDIUM' ? null : 'MEDIUM'"
                >
                  Medium
                </button>
                <button
                  type="button"
                  :class="
                    'rounded-full border border-outline-variant/20 px-4 py-2 text-sm font-semibold transition-all ' +
                    (selectedDifficulty === 'HARD'
                      ? 'bg-primary text-white'
                      : 'text-on-surface-variant hover:bg-primary hover:text-white')
                  "
                  @click="selectedDifficulty = selectedDifficulty === 'HARD' ? null : 'HARD'"
                >
                  Hard
                </button>
              </div>
            </div>
          </div>
        </aside>

        <!-- Mobile filter trigger (UI only) -->
        <div class="md:hidden">
          <button
            type="button"
            class="mb-8 flex w-full items-center justify-between rounded-2xl bg-surface-container-high px-6 py-4 font-bold text-primary"
            :aria-expanded="mobileFiltersOpen ? 'true' : 'false'"
            aria-controls="mobile-filter-panel"
            @click="mobileFiltersOpen = !mobileFiltersOpen"
          >
            <span class="flex items-center gap-2">
              <span class="material-symbols-outlined">tune</span>
              Filter Adventures
            </span>
            <span class="material-symbols-outlined">{{ mobileFiltersOpen ? 'expand_less' : 'expand_more' }}</span>
          </button>

          <div
            id="mobile-filter-panel"
            v-show="mobileFiltersOpen"
            class="mb-8 space-y-8 rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-5"
          >
            <div>
              <h3 class="mb-4 font-headline text-lg font-semibold text-primary">Category</h3>
              <div class="space-y-3">
                <label
                  v-for="cat in categories"
                  :key="`mobile-cat-${cat.id}`"
                  class="group flex cursor-pointer items-center gap-3"
                >
                  <input
                    v-model="selectedCategoryIds"
                    :value="cat.id"
                    type="checkbox"
                    class="h-5 w-5 rounded border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary">
                    {{ cat.name }}
                  </span>
                </label>
              </div>
            </div>

            <div>
              <h3 class="mb-4 font-headline text-lg font-semibold text-primary">Duration</h3>
              <div class="space-y-3">
                <label class="group flex cursor-pointer items-center gap-3">
                  <input
                    v-model="selectedDuration"
                    value="DAY"
                    type="radio"
                    name="mobile-duration"
                    class="h-5 w-5 border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary"
                    >Day Trips</span
                  >
                </label>
                <label class="group flex cursor-pointer items-center gap-3">
                  <input
                    v-model="selectedDuration"
                    value="2_4"
                    type="radio"
                    name="mobile-duration"
                    class="h-5 w-5 border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary"
                    >2-4 Days</span
                  >
                </label>
                <label class="group flex cursor-pointer items-center gap-3">
                  <input
                    v-model="selectedDuration"
                    value="5_PLUS"
                    type="radio"
                    name="mobile-duration"
                    class="h-5 w-5 border-outline-variant/30 bg-surface-container-high text-primary transition-all focus:ring-primary/20"
                  />
                  <span class="text-on-surface-variant transition-colors group-hover:text-primary"
                    >5+ Days</span
                  >
                </label>
              </div>
            </div>

            <div>
              <h3 class="mb-4 font-headline text-lg font-semibold text-primary">Difficulty</h3>
              <div class="grid grid-cols-3 gap-2">
                <button
                  type="button"
                  :class="
                    'rounded-full border border-outline-variant/20 px-3 py-2 text-xs font-semibold transition-all ' +
                    (selectedDifficulty === 'EASY'
                      ? 'bg-primary text-white'
                      : 'text-on-surface-variant hover:bg-primary hover:text-white')
                  "
                  @click="selectedDifficulty = selectedDifficulty === 'EASY' ? null : 'EASY'"
                >
                  Easy
                </button>
                <button
                  type="button"
                  :class="
                    'rounded-full border border-outline-variant/20 px-3 py-2 text-xs font-semibold transition-all ' +
                    (selectedDifficulty === 'MEDIUM'
                      ? 'bg-primary text-white'
                      : 'text-on-surface-variant hover:bg-primary hover:text-white')
                  "
                  @click="selectedDifficulty = selectedDifficulty === 'MEDIUM' ? null : 'MEDIUM'"
                >
                  Medium
                </button>
                <button
                  type="button"
                  :class="
                    'rounded-full border border-outline-variant/20 px-3 py-2 text-xs font-semibold transition-all ' +
                    (selectedDifficulty === 'HARD'
                      ? 'bg-primary text-white'
                      : 'text-on-surface-variant hover:bg-primary hover:text-white')
                  "
                  @click="selectedDifficulty = selectedDifficulty === 'HARD' ? null : 'HARD'"
                >
                  Hard
                </button>
              </div>
            </div>

            <button
              type="button"
              class="w-full rounded-full border border-outline-variant/30 bg-surface px-4 py-3 text-center font-bold text-slate-700"
              @click="mobileFiltersOpen = false"
            >
              Apply Filters
            </button>
          </div>
        </div>

        <!-- Cards grid -->
        <div class="flex-1">
          <div
            v-if="error"
            class="rounded-2xl bg-error-container/70 px-6 py-4 text-sm font-medium text-error"
          >
            {{ error }}
          </div>

          <div v-else class="grid grid-cols-1 gap-8 lg:grid-cols-2 xl:grid-cols-3">
            <!-- Loading skeletons -->
            <div
              v-if="loading"
              v-for="i in 6"
              :key="i"
              class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm"
            >
              <div class="h-72 animate-pulse bg-surface-container-high" />
              <div class="space-y-4 p-6">
                <div class="h-5 w-40 animate-pulse rounded-lg bg-surface-container-high" />
                <div class="h-7 w-3/4 animate-pulse rounded-lg bg-surface-container-high" />
                <div class="h-5 w-2/3 animate-pulse rounded-lg bg-surface-container-high" />
                <div class="h-14 animate-pulse rounded-2xl bg-surface-container-high" />
              </div>
            </div>

            <!-- Real tours -->
            <div
              v-else
              v-for="(tour, idx) in filteredTours"
              :key="tour.id"
              class="group flex flex-col overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm transition-all duration-500 hover:shadow-xl"
            >
              <div class="relative h-72 overflow-hidden">
                <img
                  :src="cardImage(tour, idx)"
                  :alt="tour.title"
                  class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                  loading="lazy"
                  decoding="async"
                />
                <div
                  class="absolute left-4 top-4 rounded-full bg-secondary px-3 py-1 text-xs font-bold uppercase tracking-widest text-white"
                >
                  {{ categoryLabel(tour) }}
                </div>
                <div
                  class="absolute bottom-4 right-4 flex items-center gap-1 rounded-lg bg-surface/90 px-3 py-1 text-primary backdrop-blur-md"
                >
                  <span
                    class="material-symbols-outlined text-sm"
                    style="font-variation-settings: 'FILL' 1"
                    >star</span
                  >
                  <span class="text-sm font-bold">4.9</span>
                </div>
              </div>

              <div class="flex flex-1 flex-col p-6">
                <div
                  class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-on-surface-variant"
                >
                  <span class="material-symbols-outlined text-base">location_on</span>
                  {{ locationLabel(tour) }}
                </div>

                <h3
                  class="mb-3 font-headline text-2xl font-bold text-on-surface transition-colors group-hover:text-primary"
                >
                  <RouterLink :to="{ name: 'tour-details', params: { id: tour.id } }">
                    {{ tour.title }}
                  </RouterLink>
                </h3>

                <div class="mb-6 flex items-center gap-4 text-sm text-on-surface-variant">
                  <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-lg">schedule</span>
                    {{ durationLabel(tour) }}
                  </span>
                  <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-lg">hiking</span>
                    {{ difficultyLabel(tour) }}
                  </span>
                </div>

                <div class="mt-auto flex items-center justify-between border-t border-surface-container-high pt-6">
                  <div>
                    <span class="block text-xs text-on-surface-variant">Starting from</span>
                    <span class="font-headline text-2xl font-bold text-primary">{{ priceLabel(tour) }}</span>
                  </div>
                  <RouterLink
                    :to="{ name: 'tour-details', params: { id: tour.id } }"
                    class="rounded-full bg-primary px-6 py-3 font-semibold text-on-primary transition-all hover:brightness-110"
                  >
                    Book Now
                  </RouterLink>
                </div>
              </div>
            </div>
          </div>

          <div v-if="!loading && !error && !hasTours" class="mt-10 rounded-2xl bg-surface-container-low p-8">
            <p class="font-semibold text-on-surface-variant">No tours yet.</p>
          </div>

          <!-- Pagination button (UI only) -->
          <div class="mt-16 flex justify-center">
            <button type="button" class="group flex flex-col items-center gap-4">
              <span class="text-sm font-bold uppercase tracking-widest text-primary">Discover More</span>
              <div
                class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-primary/20 transition-all duration-300 group-hover:border-primary group-hover:bg-primary"
              >
                <span
                  class="material-symbols-outlined text-primary transition-colors group-hover:text-white"
                  >expand_more</span
                >
              </div>
            </button>
          </div>
        </div>
      </div>
    </main>

  </div>
</template>

