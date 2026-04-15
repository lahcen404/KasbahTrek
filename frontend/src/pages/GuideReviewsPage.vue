<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getAuthToken } from '../api/client';
import { getGuideReviews } from '../api/guide';
import type { GuideReview } from '../types/guide';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const reviews = ref<GuideReview[]>([]);

function hasValidToken(): boolean {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
}

function formatDate(value?: string | null): string {
  if (!value) return 'Unknown date';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(parsed);
}

function initials(name?: string): string {
  const safe = (name ?? 'Traveler').trim();
  const parts = safe.split(/\s+/).filter(Boolean);
  const first = parts[0]?.[0] ?? 'T';
  const second = parts[1]?.[0] ?? parts[0]?.[1] ?? 'R';
  return `${first}${second}`.toUpperCase();
}

function stars(rating?: number | null): string {
  const safe = Math.max(0, Math.min(5, Math.round(Number(rating ?? 0))));
  return '★'.repeat(safe) + '☆'.repeat(5 - safe);
}

const averageRating = computed(() => {
  if (reviews.value.length === 0) return null;
  const total = reviews.value.reduce((sum, review) => sum + Number(review.rating ?? 0), 0);
  return (total / reviews.value.length).toFixed(2);
});

const totalReviews = computed(() => reviews.value.length);

async function loadReviews(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    if (!hasValidToken()) {
      await router.push({ name: 'login', query: { redirect: '/guide/reviews' } });
      return;
    }

    reviews.value = await getGuideReviews();
  } catch (e) {
    const err = e as { response?: { status?: number; data?: { message?: string } } };
    if (err.response?.status === 403) {
      error.value = 'This page is only available for guides.';
    } else {
      error.value = err.response?.data?.message ?? 'Could not load guide reviews.';
    }
    reviews.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  void loadReviews();
});
</script>

<template>
  <div class="min-h-screen bg-surface font-body text-on-surface">
    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-8 sm:py-12">
      <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Guide Center</p>
          <h1 class="mt-2 text-4xl font-headline font-extrabold tracking-tight text-on-surface">Traveler Reviews</h1>
          <p class="mt-2 max-w-2xl text-sm text-on-surface-variant sm:text-base">
            Read what travelers say about your tours and use feedback to improve your experience quality.
          </p>
        </div>
        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 font-bold text-on-primary shadow-lg transition-all hover:brightness-110 active:scale-95"
          @click="router.push({ name: 'guide-dashboard' })"
        >
          <span class="material-symbols-outlined">arrow_back</span>
          Back to Dashboard
        </button>
      </div>

      <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-3xl border border-outline-variant/10 bg-surface-container-lowest p-6 shadow-sm">
          <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Total Reviews</p>
          <p class="mt-2 text-3xl font-headline font-bold text-on-surface">{{ totalReviews }}</p>
        </div>
        <div class="rounded-3xl border border-outline-variant/10 bg-surface-container-lowest p-6 shadow-sm">
          <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Average Rating</p>
          <p class="mt-2 text-3xl font-headline font-bold text-on-surface">
            {{ averageRating ? `${averageRating} / 5` : 'No ratings yet' }}
          </p>
        </div>
      </div>

      <div v-if="error" class="mb-6 rounded-2xl bg-error-container/70 px-5 py-4 text-sm font-medium text-error">
        {{ error }}
      </div>

      <div v-if="loading" class="rounded-3xl border border-outline-variant/10 bg-surface-container-lowest p-8 text-sm text-on-surface-variant">
        Loading reviews...
      </div>

      <div
        v-else-if="reviews.length === 0"
        class="rounded-3xl border border-outline-variant/10 bg-surface-container-lowest p-10 text-center"
      >
        <p class="text-lg font-semibold text-on-surface">No reviews yet</p>
        <p class="mt-2 text-sm text-on-surface-variant">Your traveler reviews will appear here once guests leave feedback.</p>
      </div>

      <div v-else class="space-y-4">
        <article
          v-for="review in reviews"
          :key="review.id"
          class="rounded-3xl border border-outline-variant/10 bg-surface-container-lowest p-6 shadow-sm"
        >
          <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <div class="flex h-11 w-11 items-center justify-center rounded-full bg-secondary-container font-bold text-secondary">
                {{ initials(review.traveler?.fullname) }}
              </div>
              <div>
                <p class="font-bold text-on-surface">{{ review.traveler?.fullname ?? 'Traveler' }}</p>
                <p class="text-xs text-on-surface-variant">{{ formatDate(review.created_at) }}</p>
              </div>
            </div>

            <div class="text-right">
              <p class="text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Rating</p>
              <p class="font-headline text-lg font-bold text-secondary">{{ stars(review.rating) }}</p>
            </div>
          </div>

          <p class="text-sm leading-relaxed text-on-surface">{{ review.comment ?? 'No written comment.' }}</p>

          <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
            <span class="material-symbols-outlined text-sm">route</span>
            {{ review.tour?.title ?? 'Unknown Tour' }}
          </div>
        </article>
      </div>
    </main>
  </div>
</template>
