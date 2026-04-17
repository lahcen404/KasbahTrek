<script setup lang="ts">
import type { AxiosError } from 'axios';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api, clearAuthToken, getAuthToken } from '../../api/client';
import { createTravelerBooking, getTravelerBookings } from '../../api/bookings';
import { getCurrentUser, getStoredUserRole } from '../../api/auth';
import { getTourById, tourImageUrl } from '../../api/tours';
import { deleteReview, submitReview, updateReview } from '../../api/reviews';
import { createTripReport } from '../../api/reports';
import TourReviewForm from '../../components/TourReviewForm.vue';
import TourReportForm from '../../components/TourReportForm.vue';
import StarRating from '../../components/common/StarRating.vue';
import type { TravelerBooking } from '../../types/traveler';
import type { Tour, TourReview } from '../../types/tours';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const tour = ref<Tour | null>(null);
const selectedDate = ref('');
const reserveError = ref<string | null>(null);
const reserving = ref(false);

// Review form state
const showReviewModal = ref(false);
const showReportModal = ref(false);
const submittingReview = ref(false);
const submittingReport = ref(false);
const reviewSuccess = ref<string | null>(null);
const reportFeedback = ref<string | null>(null);
const activeEditReviewId = ref<number | null>(null);
const editReviewRating = ref<number>(0);
const editReviewComment = ref('');
const reviewActionLoadingId = ref<number | null>(null);
const reviewGuardLoading = ref(false);
const travelerBookings = ref<TravelerBooking[]>([]);
const currentTravelerId = ref<number | null>(null);

// Booking sidebar UI
const travelers = ref(2);
const serviceFee = 25;

const pricePerPerson = computed(() => tour.value?.price ?? 0);
const subtotal = computed(() => pricePerPerson.value * travelers.value);
const total = computed(() => subtotal.value + serviceFee);

const tourId = computed(() => Number(route.params.id));

const categoryLabel = computed(() => {
  if (!tour.value) return '';
  const t = tour.value;
  const category =
    typeof t.category === 'string' ? t.category : t.category?.name ?? t.category_name ?? null;
  if (category) return String(category);
  if (typeof t.category_id === 'number') return `Category #${t.category_id}`;
  return 'Uncategorized';
});

const guideName = computed(() => {
  const g = tour.value?.guide;
  return g?.fullname ?? g?.name ?? 'Local Guide';
});

const gallery = computed(() => {
  const images = tour.value?.images ?? [];
  const urls = images
    .map((img) => tourImageUrl(img.path))
    .filter((u): u is string => Boolean(u));

  if (urls.length > 0) return urls;

  // fallback to your design placeholders when no real images exist yet
  return [
    'https://lh3.googleusercontent.com/aida-public/AB6AXuBJCH0_eu2zzCtdelK4Chmg0fPadlJLL91zyJYTeVIkbXVBKvIGAmQB_qc-92zkiOjaC4_kYsYrW892cDCVD6S1BZgt4QvzrhaaIduYG-Bx8DL0TjY2yhPG-tGj3BoKBjqbFOsIrNZwKVvdRuToAekqL4aKFI39Ri4Dm-ttpqypZGfCdSEKcem1QYrzSK5WqmhI4Iebe9s_cHDADsErtmBNUm8YaVqi5zT3g4kKxzpq0m1gZ-SqgqcPuumrHI4UdC2ltsdTA0ygcpg',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuCheVMc39LesoqrTdP1fYPaTy7DRAESAeXEYL8ruMz3aa-OmrPzTiJ7PUUKtGGnf51hbZ-0ms3Xd_UAYpScWIAOaruKabbAkwXcFJUaVgG5pWDdIu9v9-8hfqI4H85elFpA6J2GZbPdgzuN3-Py0rGZc40GM8MNHKOw4nzK-p8f3oDeneZfhSG5dvrIDeFUUGMX0mCshFHCM8hH01MGSJOOgCxkicHF6991OgawkVO83G98tNNl2z1s-xu1X_r0gpt58rBRTLitGe4',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuBRxc-Zm2dpqDTgXjh-htLMJnKNDfCuHqjV4IfcrndY8yvA7qS8UvzjSnPwGE03_GgQkBZ2bkgE7XlmfF19jGwT7B_E6sSZMNH8op6EL0UmlD8BmHlgZbbtYPiKovCp1jk3NEMWDQM59mCH0nR2CcRprbocd1AgLiBFuB-cCZm01tIPOiH9ncZ8MdzJqtXsclRkl5ke4RZrLbvtNow7lUDGy6c3-_wtTke0hNRqhLlKYIWmq7o6LAR7wQlUuclz10Zh0KALyHabh90',
    'https://lh3.googleusercontent.com/aida-public/AB6AXuD5cxl5593YSghcQ8YMzdAXdu7VlcCDCSieuFBuU7qS6XJKraZukY5xD1vvCTztWXDJG9KaKCD0PnMDxK85rA2guNTALHFZ_0bOdMqdrtdkciBHGoKlC7A2Vx6sbf6oNDoLksfOpBYFxr_RpLviBqBXkVwK5FFe4BGLwSPipbMx44lSY3Ww2ZVS7xZc-bMVfcLAM5YIbvyM17n6wX56EPDP-RFmnrVm8ZxVGbnk9H7-hxbCwGPu-DfLL1yX9XMg-Bu1qMrF3Up3_os',
  ];
});

const reviews = computed(() => tour.value?.reviews ?? []);

const ratingAvg = computed(() => {
  const v = tour.value?.rating_avg;
  if (typeof v === 'number') return v;
  return null;
});
const reviewsCount = computed(() => tour.value?.reviews_count ?? reviews.value.length);
const isTraveler = computed(() => getStoredUserRole()?.toUpperCase() === 'TRAVELER');
const hasValidSessionToken = computed(() => {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
});
const hasConfirmedPaidBookingForTour = computed(() => {
  if (!tour.value?.id) return false;
  return travelerBookings.value.some(
    (booking) =>
      booking.tour_id === tour.value?.id &&
      booking.status === 'CONFIRMED' &&
      booking.payment_status === 'PAID',
  );
});
const hasAlreadyReviewedTour = computed(() => {
  if (!currentTravelerId.value) return false;
  return reviews.value.some((review) => review.traveler?.id === currentTravelerId.value);
});
const canShareExperience = computed(
  () =>
    isTraveler.value &&
    hasValidSessionToken.value &&
    hasConfirmedPaidBookingForTour.value &&
    !hasAlreadyReviewedTour.value,
);
const reviewEligibilityHint = computed(() => {
  if (!hasValidSessionToken.value) return 'Log in as traveler to share your review.';
  if (!isTraveler.value) return 'Only traveler accounts can post reviews.';
  if (hasAlreadyReviewedTour.value) return 'You already reviewed this tour.';
  if (!hasConfirmedPaidBookingForTour.value) return 'Review opens after your booking is confirmed and paid.';
  return null;
});
const minDate = computed(() => {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  return d.toISOString().split('T')[0];
});

const formattedTourDate = computed(() => {
  const date = tour.value?.date;
  if (!date) return 'Date not announced yet';

  const parsed = new Date(date);
  if (Number.isNaN(parsed.getTime())) return date;

  return new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(parsed);
});

function fmtMoney(value: number): string {
  return new Intl.NumberFormat('en-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value);
}

async function handleReserveClick(): Promise<void> {
  if (reserving.value) return;
  reserveError.value = null;

  const token = getAuthToken();
  const hasLocalToken = typeof token === 'string' && token.trim() !== '';
  const role = getStoredUserRole()?.toUpperCase();

  if (role === 'GUIDE') {
    reserveError.value = 'Guide accounts cannot book tours. Please use a traveler account.';
    return;
  }

  if (!hasLocalToken || token === 'null' || token === 'undefined') {
    void router.push({
      name: 'login',
      query: { redirect: route.fullPath },
    });
    return;
  }

  if (!tour.value?.id) {
    reserveError.value = 'Tour information is missing. Please refresh and try again.';
    return;
  }

  if (!selectedDate.value) {
    reserveError.value = 'Please select a booking date.';
    return;
  }

  if (selectedDate.value < minDate.value) {
    reserveError.value = 'Please choose a date after today.';
    return;
  }

  try {
    // Validate token with backend so stale localStorage values don't block redirect.
    await api.get('/my-bookings');

    reserving.value = true;
    const created = await createTravelerBooking({
      tour_id: tour.value.id,
      date: selectedDate.value,
    });

    await router.push({
      name: 'traveler-booking-payment',
      params: { id: created.booking.id },
    });
  } catch (e) {
    const err = e as AxiosError;
    if (err.response?.status === 401) {
      clearAuthToken();
      void router.push({
        name: 'login',
        query: { redirect: route.fullPath },
      });
      return;
    }

    if (err.response?.status === 403) {
      reserveError.value = 'Only travelers can book tours.';
      return;
    }

    if (err.response?.status === 422 || err.response?.status === 400) {
      const message = (err.response?.data as { message?: string } | undefined)?.message;
      reserveError.value = message ?? 'Booking validation failed. Please pick a valid upcoming date.';
      return;
    }

    reserveError.value = 'Could not continue booking right now. Please try again.';
  } finally {
    reserving.value = false;
  }
}

async function handleSubmitReview(payload: { rating: number; comment: string }): Promise<void> {
  if (!tour.value?.id || submittingReview.value || !canShareExperience.value) return;

  try {
    submittingReview.value = true;
    reviewSuccess.value = null;

    await submitReview({
      tour_id: tour.value.id,
      rating: payload.rating,
      comment: payload.comment,
    });

    reviewSuccess.value = 'Thank you! Your review has been posted.';
    showReviewModal.value = false;

    // Refresh tour data to show new review.
    tour.value = await getTourById(tourId.value);

    setTimeout(() => {
      reviewSuccess.value = null;
    }, 3000);
  } catch (e) {
    const err = e as AxiosError;
    const message = (err.response?.data as { message?: string } | undefined)?.message;

    if (err.response?.status === 401) {
      void router.push({ name: 'login', query: { redirect: route.fullPath } });
      return;
    }

    reviewSuccess.value = message ?? 'Could not submit review. Please try again.';
  } finally {
    submittingReview.value = false;
  }
}

function openReviewModal(): void {
  if (!canShareExperience.value) {
    reviewSuccess.value = reviewEligibilityHint.value ?? 'You are not eligible to review this tour yet.';
    return;
  }

  showReviewModal.value = true;
}

function openReportModal(): void {
  if (!hasValidSessionToken.value) {
    void router.push({ name: 'login', query: { redirect: route.fullPath } });
    return;
  }

  if (!isTraveler.value) {
    reportFeedback.value = 'Only traveler accounts can submit tour reports.';
    return;
  }

  showReportModal.value = true;
}

async function handleSubmitReport(payload: { reason: string }): Promise<void> {
  if (!tour.value?.id || submittingReport.value) return;

  try {
    submittingReport.value = true;
    reportFeedback.value = null;

    await createTripReport({
      tour_id: tour.value.id,
      reason: payload.reason,
    });

    showReportModal.value = false;
    reportFeedback.value = 'Tour report submitted. Our team will review it shortly.';
  } catch (e) {
    const err = e as AxiosError;
    const message = (err.response?.data as { message?: string } | undefined)?.message;
    reportFeedback.value = message ?? 'Could not submit report right now. Please try again.';
  } finally {
    submittingReport.value = false;
  }
}

function isOwnReview(review: TourReview): boolean {
  if (!currentTravelerId.value) return false;
  return review.traveler?.id === currentTravelerId.value || review.user?.id === currentTravelerId.value;
}

function startEditReview(review: TourReview): void {
  if (!isOwnReview(review)) return;
  activeEditReviewId.value = review.id;
  editReviewRating.value = review.rating ?? 0;
  editReviewComment.value = review.comment ?? '';
  reviewSuccess.value = null;
}

function cancelEditReview(): void {
  activeEditReviewId.value = null;
  editReviewRating.value = 0;
  editReviewComment.value = '';
}

async function saveReviewUpdate(reviewId: number): Promise<void> {
  if (reviewActionLoadingId.value !== null || editReviewRating.value < 1 || editReviewComment.value.trim().length === 0) {
    if (editReviewRating.value < 1 || editReviewComment.value.trim().length === 0) {
      reviewSuccess.value = 'Rating and comment are required.';
    }
    return;
  }

  try {
    reviewActionLoadingId.value = reviewId;
    reviewSuccess.value = null;

    await updateReview(reviewId, {
      rating: editReviewRating.value,
      comment: editReviewComment.value.trim(),
    });

    tour.value = await getTourById(tourId.value);
    cancelEditReview();
    reviewSuccess.value = 'Review updated successfully.';
  } catch (e) {
    const err = e as AxiosError;
    const message = (err.response?.data as { message?: string } | undefined)?.message;
    reviewSuccess.value = message ?? 'Could not update review right now.';
  } finally {
    reviewActionLoadingId.value = null;
  }
}

async function removeReviewFromTour(reviewId: number): Promise<void> {
  if (reviewActionLoadingId.value !== null) return;
  const confirmed = window.confirm('Delete this review? This action cannot be undone.');
  if (!confirmed) return;

  try {
    reviewActionLoadingId.value = reviewId;
    reviewSuccess.value = null;

    await deleteReview(reviewId);
    tour.value = await getTourById(tourId.value);
    cancelEditReview();
    reviewSuccess.value = 'Review deleted successfully.';
  } catch (e) {
    const err = e as AxiosError;
    const message = (err.response?.data as { message?: string } | undefined)?.message;
    reviewSuccess.value = message ?? 'Could not delete review right now.';
  } finally {
    reviewActionLoadingId.value = null;
  }
}

async function loadReviewEligibility(): Promise<void> {
  if (!hasValidSessionToken.value || !isTraveler.value || !tour.value?.id) return;

  reviewGuardLoading.value = true;
  try {
    const [me, bookings] = await Promise.all([getCurrentUser(), getTravelerBookings()]);
    currentTravelerId.value = typeof me.id === 'number' ? me.id : null;
    travelerBookings.value = bookings;
  } catch {
    travelerBookings.value = [];
  } finally {
    reviewGuardLoading.value = false;
  }
}

onMounted(async () => {
  loading.value = true;
  error.value = null;
  try {
    if (!Number.isFinite(tourId.value) || tourId.value <= 0) {
      throw new Error('Invalid tour id');
    }
    tour.value = await getTourById(tourId.value);
    if (tour.value?.date) {
      selectedDate.value = String(tour.value.date).slice(0, 10);
    }
    await loadReviewEligibility();
  } catch {
    error.value = 'Could not load this tour. Please go back and try again.';
    tour.value = null;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="bg-surface text-on-surface selection:bg-primary-fixed">
    <main class="mx-auto max-w-7xl px-6 pb-16 pt-24">
      <div
        v-if="error"
        class="mb-8 rounded-2xl bg-error-container/70 px-6 py-4 text-sm font-medium text-error"
      >
        {{ error }}
      </div>

      <div v-else-if="loading" class="space-y-6">
        <div class="h-[300px] animate-pulse rounded-3xl bg-surface-container-high md:h-[600px]" />
        <div class="h-10 w-2/3 animate-pulse rounded-xl bg-surface-container-high" />
        <div class="h-6 w-1/2 animate-pulse rounded-xl bg-surface-container-high" />
        <div class="h-28 animate-pulse rounded-3xl bg-surface-container-high" />
      </div>

      <template v-else-if="tour">
        <!-- Editorial Image Gallery -->
        <section class="mb-12 grid h-[600px] grid-cols-1 gap-4 md:grid-cols-4 md:grid-rows-2">
          <div class="group relative overflow-hidden rounded-xl md:col-span-2 md:row-span-2">
            <img
              :src="gallery[0]"
              :alt="tour.title"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
              loading="lazy"
              decoding="async"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />
          </div>
          <div class="group overflow-hidden rounded-xl md:col-span-1">
            <img
              :src="gallery[1] ?? gallery[0]"
              alt=""
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
              loading="lazy"
              decoding="async"
            />
          </div>
          <div class="group overflow-hidden rounded-xl md:col-span-1">
            <img
              :src="gallery[2] ?? gallery[0]"
              alt=""
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
              loading="lazy"
              decoding="async"
            />
          </div>
          <div class="group overflow-hidden rounded-xl md:col-span-2">
            <img
              :src="gallery[3] ?? gallery[0]"
              alt=""
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
              loading="lazy"
              decoding="async"
            />
          </div>
        </section>

        <div class="flex flex-col gap-12 lg:flex-row">
          <!-- Main Content -->
          <div class="flex-1 space-y-8">
            <div>
              <div class="mb-4 flex items-center gap-2">
                <span
                  class="rounded-full bg-secondary/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-secondary"
                >
                  {{ categoryLabel }}
                </span>
                <span
                  v-if="tour.difficulty"
                  class="rounded-full bg-tertiary/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-tertiary"
                >
                  {{ tour.difficulty }}
                </span>
                <div class="flex items-center text-tertiary">
                  <span
                    class="material-symbols-outlined text-sm"
                    style="font-variation-settings: 'FILL' 1"
                    >star</span
                  >
                  <span class="ml-1 text-sm font-bold">
                    {{ ratingAvg !== null ? ratingAvg.toFixed(1) : '—' }}
                    <span class="text-on-surface-variant">
                      ({{ reviewsCount }} reviews)
                    </span>
                  </span>
                </div>
              </div>

              <h1 class="mb-2 text-4xl font-bold uppercase tracking-tight text-on-surface md:text-5xl">
                {{ tour.title }}
              </h1>

              <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary">location_on</span>
                <span class="font-medium">{{ tour.location ?? 'Morocco' }}</span>
              </div>
            </div>

            <div class="max-w-none">
              <p class="text-lg leading-relaxed text-slate-700">
                {{ tour.description ?? 'No description provided yet.' }}
              </p>
            </div>

            <!-- Guide Profile section -->
            <div class="flex flex-col items-center gap-6 rounded-3xl bg-surface-container-low p-8 md:flex-row">
              <div class="h-24 w-24 shrink-0 overflow-hidden rounded-full shadow-lg">
                <img
                  :src="gallery[2] ?? gallery[0]"
                  alt=""
                  class="h-full w-full object-cover"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div class="flex-1 text-center md:text-left">
                <div class="mb-1 flex items-center justify-center gap-2 md:justify-start">
                  <h3 class="text-xl font-bold">Meet {{ guideName }}</h3>
                  <span
                    v-if="tour.guide?.is_verified"
                    class="flex items-center gap-1 rounded-full bg-secondary px-2 py-0.5 text-[10px] font-bold text-white"
                  >
                    <span
                      class="material-symbols-outlined text-[12px]"
                      style="font-variation-settings: 'FILL' 1"
                      >verified</span
                    >
                    VERIFIED
                  </span>
                </div>
                <p class="mb-4 text-on-surface-variant">
                  Local expertise, cultural stories, and the safest trails — guided with care.
                </p>
              </div>
            </div>

            <!-- Reviews -->
            <div class="pt-8">
              <h2 class="mb-8 text-2xl font-bold">Traveler Experiences</h2>

              <Transition name="success">
                <div
                  v-if="reviewSuccess"
                  class="mb-6 rounded-2xl bg-secondary-container/70 px-6 py-4 text-sm font-medium text-secondary"
                >
                  {{ reviewSuccess }}
                </div>
              </Transition>

              <div
                v-if="reportFeedback"
                class="mb-6 rounded-2xl bg-error-container/70 px-6 py-4 text-sm font-medium text-error"
              >
                {{ reportFeedback }}
              </div>

              <div class="mb-2 flex flex-wrap gap-3">
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-semibold transition"
                  :class="
                    canShareExperience
                      ? 'bg-primary text-on-primary hover:brightness-110'
                      : 'cursor-not-allowed bg-surface-container-high text-on-surface-variant'
                  "
                  :disabled="!canShareExperience"
                  @click="openReviewModal"
                >
                  <span class="material-symbols-outlined">rate_review</span>
                  Share Your Experience
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-full bg-error/90 px-6 py-3 text-sm font-semibold text-on-error transition hover:brightness-110"
                  @click="openReportModal"
                >
                  <span class="material-symbols-outlined">report</span>
                  Report Tour
                </button>
              </div>
              <p
                v-if="reviewGuardLoading || reviewEligibilityHint"
                class="mb-6 text-sm text-on-surface-variant"
              >
                {{ reviewGuardLoading ? 'Checking your review eligibility...' : reviewEligibilityHint }}
              </p>

              <div v-if="reviews.length === 0" class="rounded-2xl bg-surface-container-lowest p-6 text-slate-600">
                No reviews yet.
              </div>

              <div v-else class="space-y-8">
                <div
                  v-for="r in reviews.slice(0, 3)"
                  :key="r.id"
                  class="rounded-2xl bg-surface-container-lowest p-6"
                >
                  <div class="mb-4 flex items-start justify-between">
                    <div class="flex items-center gap-3">
                      <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-high font-bold text-primary"
                      >
                        {{ (r.traveler?.fullname ?? r.traveler?.name ?? r.user?.fullname ?? r.user?.name ?? 'U').slice(0, 2).toUpperCase() }}
                      </div>
                      <div>
                        <p class="text-sm font-bold">{{ r.traveler?.fullname ?? r.traveler?.name ?? r.user?.fullname ?? r.user?.name ?? 'Traveler' }}</p>
                        <p class="text-xs text-slate-400">{{ r.created_at ?? '' }}</p>
                      </div>
                    </div>
                    <StarRating :model-value="r.rating ?? 5" readonly size="sm" />
                  </div>
                  <p class="italic leading-relaxed text-slate-600">
                    “{{ r.comment ?? 'Amazing experience.' }}”
                  </p>

                  <div v-if="activeEditReviewId === r.id" class="mt-4 space-y-3 rounded-xl bg-surface-container-low p-4">
                    <StarRating v-model="editReviewRating" size="sm" />
                    <textarea
                      v-model="editReviewComment"
                      rows="3"
                      class="w-full rounded-xl border border-outline-variant/30 bg-surface px-3 py-2 text-sm text-on-surface outline-none focus:border-primary"
                      maxlength="1000"
                    />
                    <div class="flex gap-2">
                      <button
                        type="button"
                        class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-on-primary transition hover:brightness-110 disabled:opacity-50"
                        :disabled="reviewActionLoadingId === r.id"
                        @click="saveReviewUpdate(r.id)"
                      >
                        {{ reviewActionLoadingId === r.id ? 'Saving...' : 'Save' }}
                      </button>
                      <button
                        type="button"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-xs font-semibold text-on-surface-variant transition hover:border-primary/40"
                        :disabled="reviewActionLoadingId === r.id"
                        @click="cancelEditReview"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>

                  <div v-if="isOwnReview(r)" class="mt-4 flex items-center gap-3">
                    <button
                      type="button"
                      class="inline-flex items-center gap-1 text-sm font-semibold text-on-surface-variant transition hover:text-primary"
                      :disabled="reviewActionLoadingId === r.id"
                      @click="startEditReview(r)"
                    >
                      <span class="material-symbols-outlined text-base">edit</span>
                      Edit
                    </button>
                    <button
                      type="button"
                      class="inline-flex items-center gap-1 text-sm font-semibold text-error transition hover:opacity-80"
                      :disabled="reviewActionLoadingId === r.id"
                      @click="removeReviewFromTour(r.id)"
                    >
                      <span class="material-symbols-outlined text-base">delete</span>
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Booking Sidebar (UI, uses real tour.price) -->
          <aside class="w-full lg:w-[400px]">
            <div
              class="sticky top-28 space-y-6 rounded-3xl bg-surface-container-lowest p-8 shadow-[0_20px_50px_rgba(30,42,47,0.06)]"
            >
              <div class="flex items-end gap-2">
                <span class="text-3xl font-bold text-on-surface">{{ fmtMoney(pricePerPerson) }}</span>
                <span class="mb-1 text-slate-500">/ person</span>
              </div>

              <div class="space-y-4">
                <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500"
                  >Select Date</label
                >
                <input
                  v-model="selectedDate"
                  :min="minDate"
                  type="date"
                  class="w-full rounded-xl border border-outline-variant/20 bg-surface-container-low px-4 py-3 text-sm font-semibold text-on-surface"
                />
                <p class="text-xs text-on-surface-variant">
                  Tour date from guide: {{ formattedTourDate }}
                </p>
              </div>

              <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-outline-variant/10 py-2">
                  <span class="text-sm text-slate-600">Travelers</span>
                  <div class="flex items-center gap-4">
                    <button
                      type="button"
                      class="flex h-8 w-8 items-center justify-center rounded-full border border-outline text-slate-400"
                      @click="travelers = Math.max(1, travelers - 1)"
                    >
                      -
                    </button>
                    <span class="font-bold">{{ travelers }}</span>
                    <button
                      type="button"
                      class="flex h-8 w-8 items-center justify-center rounded-full border border-outline text-primary"
                      @click="travelers = travelers + 1"
                    >
                      +
                    </button>
                  </div>
                </div>

                <div class="space-y-2 py-4">
                  <div class="flex justify-between text-sm">
                    <span>{{ fmtMoney(pricePerPerson) }} x {{ travelers }} people</span>
                    <span>{{ fmtMoney(subtotal) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span>Service fee</span>
                    <span>{{ fmtMoney(serviceFee) }}</span>
                  </div>
                  <div class="flex justify-between border-t border-outline-variant/20 pt-4 text-lg font-bold">
                    <span>Total</span>
                    <span>{{ fmtMoney(total) }}</span>
                  </div>
                </div>
              </div>

              <button
                type="button"
                class="w-full scale-[0.98] rounded-full bg-primary py-4 text-lg font-bold text-on-primary transition-all hover:brightness-110 hover:shadow-xl active:scale-95"
                :disabled="reserving"
                @click="handleReserveClick"
              >
                {{ reserving ? 'Reserving...' : 'Reserve My Spot' }}
              </button>

              <p
                v-if="reserveError"
                class="rounded-xl bg-error-container/70 px-4 py-3 text-sm font-semibold text-error"
              >
                {{ reserveError }}
              </p>

            </div>
          </aside>
        </div>

        <TourReviewForm
          v-model="showReviewModal"
          :tour-title="tour?.title ?? 'Tour'"
          :is-loading="submittingReview"
          @submit="handleSubmitReview"
        />

        <TourReportForm
          v-model="showReportModal"
          :tour-title="tour?.title ?? 'Tour'"
          :is-loading="submittingReport"
          @submit="handleSubmitReport"
        />
      </template>
    </main>
  </div>
</template>

<style scoped>
/* Success message animation */
.success-enter-active {
  animation: slide-in 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

.success-leave-active {
  animation: slide-out 300ms ease;
}

@keyframes slide-in {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-out {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-20px);
  }
}
</style>

