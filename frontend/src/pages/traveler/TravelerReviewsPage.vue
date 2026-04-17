<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { getMyReviews } from '../../api/reviews';
import StarRating from '../../components/common/StarRating.vue';
import type { Review } from '../../types/reviews';

const router = useRouter();
const route = useRoute();

const loading = ref(true);
const error = ref<string | null>(null);
const reviews = ref<Review[]>([]);

const travelerNavItems = [
  { key: 'traveler-profile', label: 'Dashboard', icon: 'dashboard' },
  { key: 'traveler-bookings', label: 'Bookings', icon: 'event_note' },
  { key: 'traveler-favorites', label: 'Favorites', icon: 'favorite' },
  { key: 'traveler-reviews', label: 'Reviews', icon: 'reviews' },
] as const;

const sortedReviews = computed(() =>
  [...reviews.value].sort((a, b) => {
    const aTime = new Date(a.created_at ?? 0).getTime();
    const bTime = new Date(b.created_at ?? 0).getTime();
    return bTime - aTime;
  }),
);

function isNavActive(key: string): boolean {
  if (key === 'traveler-bookings') {
    return route.name === 'traveler-bookings' || route.name === 'traveler-booking-payment';
  }

  return route.name === key;
}

function goToTravelerRoute(key: string): void {
  void router.push({ name: key });
}

function reviewAuthorLabel(review: Review): string {
  return review.tour?.title ?? `Tour #${review.tour_id}`;
}

function reviewDateLabel(value: string | undefined): string {
  if (!value) return 'Unknown date';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(d);
}

onMounted(async () => {
  loading.value = true;
  error.value = null;

  try {
    reviews.value = await getMyReviews();
  } catch {
    error.value = 'Could not load your reviews right now. Please refresh.';
    reviews.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <main class="mx-auto max-w-7xl px-5 pb-16 pt-24 sm:px-8">
      <div class="grid gap-6 lg:grid-cols-[16rem,1fr]">
        <aside class="h-fit rounded-3xl border border-outline-variant/20 bg-surface-container-low p-4 lg:sticky lg:top-24">
          <p class="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-primary">Traveler Dashboard</p>
          <nav class="mt-3 flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible lg:pb-0">
            <button
              v-for="item in travelerNavItems"
              :key="item.key"
              type="button"
              class="inline-flex min-w-max items-center gap-2 rounded-full px-4 py-3 text-sm font-bold transition-all lg:w-full"
              :class="
                isNavActive(item.key)
                  ? 'bg-orange-700 text-white'
                  : 'text-slate-600 hover:bg-orange-50'
              "
              @click="goToTravelerRoute(item.key)"
            >
              <span class="material-symbols-outlined text-base">{{ item.icon }}</span>
              <span>{{ item.label }}</span>
            </button>
          </nav>
        </aside>

        <section class="relative overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container-low p-6 sm:p-8">
          <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-primary/10 blur-2xl" />
          <div class="pointer-events-none absolute -bottom-20 -left-10 h-52 w-52 rounded-full bg-tertiary/10 blur-2xl" />

          <header class="relative mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Traveler Dashboard</p>
              <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">My Reviews</h1>
              <p class="mt-2 max-w-2xl text-on-surface-variant">
                Check everything you posted and revisit your feedback anytime.
              </p>
            </div>

            <span class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-semibold text-on-surface-variant">
              {{ sortedReviews.length }} total
            </span>
          </header>

          <div v-if="error" class="relative mb-6 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
            {{ error }}
          </div>

          <div v-if="loading" class="relative space-y-4">
            <div v-for="i in 4" :key="i" class="h-36 animate-pulse rounded-2xl bg-surface-container" />
          </div>

          <div
            v-else-if="sortedReviews.length === 0"
            class="relative rounded-2xl border border-outline-variant/20 bg-surface p-10 text-center"
          >
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
              <span class="material-symbols-outlined">reviews</span>
            </div>
            <p class="text-lg font-semibold">No reviews yet</p>
            <p class="mt-2 text-on-surface-variant">Open a tour and share your experience.</p>
            <RouterLink
              :to="{ name: 'tours' }"
              class="mt-5 inline-flex items-center gap-2 rounded-full bg-primary px-6 py-3 font-semibold text-on-primary transition hover:brightness-110"
            >
              Explore Tours
            </RouterLink>
          </div>

          <div v-else class="relative space-y-4">
            <article
              v-for="review in sortedReviews"
              :key="review.id"
              class="rounded-2xl border border-outline-variant/20 bg-surface p-5 shadow-sm"
            >
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Reviewed Tour</p>
                  <h3 class="mt-1 text-lg font-bold text-on-surface">{{ reviewAuthorLabel(review) }}</h3>
                </div>

                <div class="text-right">
                  <StarRating :model-value="review.rating ?? 0" readonly size="sm" />
                  <p class="mt-1 text-xs text-on-surface-variant">{{ reviewDateLabel(review.created_at) }}</p>
                </div>
              </div>

              <p class="mt-4 rounded-xl bg-surface-container-low px-4 py-3 text-sm leading-relaxed text-on-surface-variant">
                {{ review.comment }}
              </p>

              <div class="mt-4">
                <RouterLink
                  :to="{ name: 'tour-details', params: { id: review.tour_id } }"
                  class="inline-flex items-center gap-1 text-sm font-semibold text-primary transition hover:opacity-80"
                >
                  View Tour
                  <span class="material-symbols-outlined text-base">arrow_forward</span>
                </RouterLink>
              </div>
            </article>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>
