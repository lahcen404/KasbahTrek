<script setup lang="ts">
import { computed, onMounted, ref, unref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { tourImageUrl } from '../../api/tours';
import { useTravelerFavorites } from '../../composables/useTravelerFavorites';
import type { Tour } from '../../types/tours';

const router = useRouter();
const route = useRoute();
const actionError = ref<string | null>(null);
const {
  favoriteItems,
  loadingFavorites,
  ensureFavoritesLoaded,
  isFavorite,
  isFavoriteBusy,
  toggleFavorite,
} = useTravelerFavorites();

const travelerNavItems = [
  { key: 'traveler-profile', label: 'Dashboard', icon: 'dashboard' },
  { key: 'traveler-bookings', label: 'Bookings', icon: 'event_note' },
  { key: 'traveler-favorites', label: 'Favorites', icon: 'favorite' },
  { key: 'traveler-reviews', label: 'Reviews', icon: 'reviews' },
  { key: 'traveler-reports', label: 'Reports', icon: 'report_problem' },
] as const;

const favoriteTours = computed(() =>
  (unref(favoriteItems) ?? [])
    .map((item) => {
      const tour = item.tour;
      if (!tour) {
        return null;
      }

      const id = Number((tour as { id?: number | string }).id);
      if (!Number.isFinite(id) || id <= 0) {
        return null;
      }

      return {
        ...tour,
        id,
      } as Tour;
    })
    .filter((tour): tour is Tour => Boolean(tour)),
);

function money(value: number | string | null | undefined): string {
  const amount = typeof value === 'number' ? value : Number(value ?? 0);
  return new Intl.NumberFormat('en-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(Number.isFinite(amount) ? amount : 0);
}

function tourHeroImage(tour: Tour): string {
  const firstImage = tour.images?.[0]?.path;
  return (
    tourImageUrl(firstImage) ??
    'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=1400&q=80'
  );
}

function categoryLabel(tour: Tour): string {
  const category =
    typeof tour.category === 'string'
      ? tour.category
      : tour.category?.name ?? tour.category_name ?? null;

  if (category) return String(category);
  return 'Uncategorized';
}

function durationLabel(tour: Tour): string {
  if (typeof tour.duration_hours !== 'number' || tour.duration_hours <= 0) {
    return 'Flexible';
  }

  const days = Math.max(1, Math.round(tour.duration_hours / 24));
  return `${days} Day${days > 1 ? 's' : ''}`;
}

function isNavActive(key: string): boolean {
  if (key === 'traveler-bookings') {
    return route.name === 'traveler-bookings' || route.name === 'traveler-booking-payment';
  }

  return route.name === key;
}

function goToTravelerRoute(key: string): void {
  void router.push({ name: key });
}

async function removeFavorite(tourId: number): Promise<void> {
  actionError.value = null;

  try {
    await toggleFavorite(tourId);
  } catch (e) {
    const message = e instanceof Error ? e.message : '';

    if (message === 'AUTH_REQUIRED') {
      await router.push({ name: 'login', query: { redirect: route.fullPath } });
      return;
    }

    actionError.value = 'Could not update favorites right now. Please try again.';
  }
}

onMounted(async () => {
  try {
    await ensureFavoritesLoaded();
  } catch {
    actionError.value = 'Could not load favorites right now. Please refresh.';
  }
});
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <section class="mx-auto w-full max-w-7xl px-5 pb-16 pt-24 sm:px-8">
      <div class="grid gap-6 grid-cols-1 lg:grid-cols-[16rem,1fr]">
      <aside class="h-fit rounded-3xl border border-outline-variant/20 bg-surface-container-low p-4 lg:sticky lg:top-24">
        <p class="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-primary">Traveler Dashboard</p>
        <nav class="mt-3 flex gap-1 overflow-x-auto pb-2 lg:flex-col lg:gap-2 lg:overflow-visible lg:pb-0">
          <button
            v-for="item in travelerNavItems"
            :key="item.key"
            type="button"
            class="inline-flex items-center gap-2 rounded-full px-3 py-2 lg:px-4 lg:py-3 text-xs lg:text-sm font-bold transition-all lg:w-full shrink-0 lg:shrink"
            :class="
              isNavActive(item.key)
                ? 'bg-orange-700 text-white'
                : 'text-slate-600 hover:bg-orange-50'
            "
            @click="goToTravelerRoute(item.key)"
          >
            <span class="material-symbols-outlined text-base">{{ item.icon }}</span>
            <span class="hidden lg:inline">{{ item.label }}</span>
          </button>
        </nav>
      </aside>
      <div>
      <header class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary">Traveler</p>
          <h1 class="mt-2 font-headline text-3xl sm:text-4xl font-bold tracking-tight text-on-surface">My Favorites</h1>
          <p class="mt-2 text-on-surface-variant">All tours you saved for later.</p>
        </div>

        <RouterLink
          :to="{ name: 'tours' }"
          class="inline-flex w-fit items-center gap-2 rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-primary hover:text-primary"
        >
          <span class="material-symbols-outlined text-lg">travel_explore</span>
          Browse More Tours
        </RouterLink>
      </header>

      <div
        v-if="actionError"
        class="mb-6 rounded-2xl bg-error-container/70 px-6 py-4 text-sm font-medium text-error"
      >
        {{ actionError }}
      </div>

      <div v-if="loadingFavorites" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="i in 6"
          :key="i"
          class="overflow-hidden rounded-2xl border border-outline-variant/15 bg-surface-container-lowest"
        >
          <div class="h-52 animate-pulse bg-surface-container-high" />
          <div class="space-y-3 p-5">
            <div class="h-5 w-28 animate-pulse rounded-lg bg-surface-container-high" />
            <div class="h-6 w-3/4 animate-pulse rounded-lg bg-surface-container-high" />
            <div class="h-4 w-2/3 animate-pulse rounded-lg bg-surface-container-high" />
            <div class="h-10 animate-pulse rounded-xl bg-surface-container-high" />
          </div>
        </div>
      </div>

      <div v-else-if="favoriteTours.length === 0" class="rounded-3xl border border-outline-variant/20 bg-surface-container-low p-10 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
          <span class="material-symbols-outlined">favorite</span>
        </div>
        <h2 class="text-2xl font-bold text-on-surface">No favorites yet</h2>
        <p class="mt-2 text-on-surface-variant">Tap the heart icon on any tour card to save it here.</p>
        <RouterLink
          :to="{ name: 'tours' }"
          class="mt-6 inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 font-semibold text-on-primary transition hover:brightness-110"
        >
          Discover Tours
        </RouterLink>
      </div>

      <div v-else class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="tour in favoriteTours"
          :key="tour.id"
          class="group overflow-hidden rounded-2xl border border-outline-variant/20 bg-surface-container-lowest shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg"
        >
          <div class="relative h-52 overflow-hidden">
            <img
              :src="tourHeroImage(tour)"
              :alt="tour.title"
              class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
              loading="lazy"
              decoding="async"
            />

            <span class="absolute left-3 top-3 rounded-full bg-secondary px-3 py-1 text-xs font-bold uppercase tracking-widest text-white">
              {{ categoryLabel(tour) }}
            </span>

            <button
              type="button"
              class="absolute right-3 top-3 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/60 bg-white/85 text-rose-600 shadow-sm backdrop-blur transition hover:scale-105 hover:bg-white"
              :disabled="isFavoriteBusy(tour.id)"
              aria-label="Remove from favorites"
              @click="removeFavorite(tour.id)"
            >
              <svg
                viewBox="0 0 24 24"
                class="h-5 w-5 text-rose-600"
                aria-hidden="true"
              >
                <path
                  d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                  fill="currentColor"
                />
              </svg>
            </button>
          </div>

          <div class="p-4 sm:p-5">
            <h3 class="line-clamp-1 text-lg sm:text-xl font-bold text-on-surface">{{ tour.title }}</h3>

            <div class="mt-2 flex flex-wrap items-center gap-2 sm:gap-3 text-sm text-on-surface-variant">
              <span class="inline-flex min-w-0 items-center gap-1">
                <span class="material-symbols-outlined text-base">location_on</span>
                <span class="truncate">{{ tour.location ?? 'Morocco' }}</span>
              </span>
              <span class="inline-flex items-center gap-1 shrink-0">
                <span class="material-symbols-outlined text-base">schedule</span>
                {{ durationLabel(tour) }}
              </span>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3 border-t border-surface-container-high pt-4">
              <p class="text-sm font-semibold text-on-surface-variant">
                From <span class="text-lg sm:text-xl font-bold text-primary">{{ money(tour.price) }}</span>
              </p>
              <RouterLink
                :to="{ name: 'tour-details', params: { id: tour.id } }"
                class="inline-flex items-center gap-1 rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-semibold text-on-surface transition hover:border-primary hover:text-primary"
              >
                View
              </RouterLink>
            </div>
          </div>
        </article>
      </div>
      </div>
      </div>
    </section>
  </div>
</template>
