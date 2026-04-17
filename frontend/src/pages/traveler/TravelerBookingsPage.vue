<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getTravelerBookings, cancelBooking } from '../../api/bookings';
import { tourImageUrl } from '../../api/tours';
import type { BookingFilter, BookingStatus, TravelerBooking } from '../../types/traveler';

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const error = ref<string | null>(null);
const bookings = ref<TravelerBooking[]>([]);
const selectedFilter = ref<BookingFilter>('all');
const cancellingId = ref<number | null>(null);
const showCancelModal = ref(false);
const bookingToCancel = ref<TravelerBooking | null>(null);

const filteredBookings = computed(() => {
  const now = new Date();

  return bookings.value.filter((booking) => {
    if (selectedFilter.value === 'all') return true;

    const bookingDate = new Date(booking.date);
    const isUpcoming = bookingDate > now;
    const isCompleted = bookingDate <= now && booking.status === 'CONFIRMED';
    const isCancelled = booking.status === 'CANCELLED';

    switch (selectedFilter.value) {
      case 'upcoming':
        return isUpcoming;
      case 'completed':
        return isCompleted;
      case 'cancelled':
        return isCancelled;
      default:
        return true;
    }
  });
});

async function loadBookings(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    const result = await getTravelerBookings();
    bookings.value = result;
  } catch (err) {
    console.error('[BookingsPage] Error loading bookings:', err);
    error.value = 'Failed to load your bookings. Please try again.';
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await loadBookings();
});

function openCancelModal(booking: TravelerBooking): void {
  bookingToCancel.value = booking;
  showCancelModal.value = true;
}

function closeCancelModal(): void {
  showCancelModal.value = false;
  bookingToCancel.value = null;
}

async function handleCancelBooking(): Promise<void> {
  if (!bookingToCancel.value) return;

  cancellingId.value = bookingToCancel.value.id;
  error.value = null;

  try {
    await cancelBooking(bookingToCancel.value.id);
    bookings.value = await getTravelerBookings();
    closeCancelModal();
  } catch {
    error.value = 'Failed to cancel booking. Please try again.';
  } finally {
    cancellingId.value = null;
  }
}

function getStatusColor(status: BookingStatus): string {
  switch (status) {
    case 'CONFIRMED':
      return 'bg-emerald-100 text-emerald-900 border-emerald-300';
    case 'PENDING':
      return 'bg-amber-100 text-amber-900 border-amber-300';
    case 'REJECTED':
      return 'bg-red-100 text-red-900 border-red-300';
    case 'CANCELLED':
      return 'bg-slate-100 text-slate-900 border-slate-300';
    default:
      return 'bg-gray-100 text-gray-900 border-gray-300';
  }
}

function getPaymentStatusColor(status: string): string {
  return status === 'PAID'
    ? 'bg-emerald-100 text-emerald-900 border-emerald-300'
    : 'bg-red-100 text-red-900 border-red-300';
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date);
}

function bookingTourName(booking: TravelerBooking): string {
  return booking.tour.title ?? booking.tour.name ?? 'Tour';
}

function bookingGuideName(booking: TravelerBooking): string {
  return booking.guide?.fullname ?? booking.tour.guide?.fullname ?? 'Guide';
}

function bookingDuration(booking: TravelerBooking): string {
  return booking.tour.duration_hours ? `${booking.tour.duration_hours} hours` : 'Duration not available';
}

function bookingImageUrl(booking: TravelerBooking): string | null {
  return tourImageUrl(booking.tour.images?.[0]?.path ?? booking.tour.image_url ?? null);
}

function formatPrice(price: string | number): string {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price;
  if (isNaN(numPrice)) return 'MAD 0';

  return new Intl.NumberFormat('en-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(numPrice);
}

const travelerNavItems = [
  { key: 'traveler-profile', label: 'Dashboard', icon: 'dashboard' },
  { key: 'traveler-bookings', label: 'Bookings', icon: 'event_note' },
  { key: 'traveler-favorites', label: 'Favorites', icon: 'favorite' },
  { key: 'traveler-reviews', label: 'Reviews', icon: 'reviews' },
  { key: 'traveler-reports', label: 'Reports', icon: 'report_problem' },
] as const;

function isNavActive(key: string): boolean {
  if (key === 'traveler-bookings') {
    return route.name === 'traveler-bookings' || route.name === 'traveler-booking-payment';
  }

  return route.name === key;
}

function goToTravelerRoute(key: string): void {
  void router.push({ name: key });
}

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

        <header class="relative">
          <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Traveler Dashboard</p>
          <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">My Bookings</h1>
          <p class="mt-2 max-w-2xl text-on-surface-variant">
            Manage your tour bookings, view details, and track payments.
          </p>
        </header>

        <div v-if="error" class="relative mt-6 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
          {{ error }}
        </div>

        <!-- Filter Tabs -->
        <div class="relative mt-8 flex flex-wrap gap-2 border-b border-outline-variant/20 pb-4">
          <button
            v-for="tab in ['all', 'upcoming', 'completed', 'cancelled']"
            :key="tab"
            type="button"
            :class="[
              'rounded-full px-4 py-2 text-sm font-semibold uppercase tracking-wider transition',
              selectedFilter === tab
                ? 'bg-primary text-on-primary'
                : 'border border-outline-variant/40 text-on-surface-variant hover:border-primary/40',
            ]"
            @click="selectedFilter = tab as typeof selectedFilter"
          >
            {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="relative mt-8 space-y-4">
          <div v-for="i in 3" :key="i" class="h-64 animate-pulse rounded-2xl bg-surface-container" />
        </div>

        <!-- Empty State -->
        <div
          v-else-if="filteredBookings.length === 0"
          class="relative mt-12 rounded-2xl border border-outline-variant/20 bg-surface p-12 text-center"
        >
          <p class="text-lg font-semibold">No bookings yet</p>
          <p class="mt-2 text-on-surface-variant">Start exploring tours and make your first booking!</p>
        </div>

        <!-- Bookings Grid -->
        <div v-else class="relative mt-8 space-y-4">
          <article
            v-for="(booking, index) in filteredBookings"
            :key="booking.id"
            class="booking-card overflow-hidden rounded-2xl border border-outline-variant/20 bg-surface p-5 sm:p-6"
            :style="{ animationDelay: `${index * 80}ms` }"
          >
            <div class="grid gap-4 sm:grid-cols-[1fr,auto]">
              <!-- Tour Info -->
              <div class="space-y-3">
                <div class="flex gap-4">
                  <div class="h-20 w-24 shrink-0 overflow-hidden rounded border border-outline-variant/20 bg-surface-container">
                    <img
                      v-if="bookingImageUrl(booking)"
                      :src="bookingImageUrl(booking) ?? ''"
                      :alt="bookingTourName(booking)"
                      class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-xs text-on-surface-variant">
                      No image
                    </div>
                  </div>

                  <div>
                    <h3 class="text-lg font-semibold">{{ bookingTourName(booking) }}</h3>
                    <p class="mt-1 text-sm text-on-surface-variant">
                      Guided by <span class="font-medium text-on-surface">{{ bookingGuideName(booking) }}</span>
                    </p>
                  </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-3">
                  <div>
                    <p class="text-xs uppercase tracking-wider text-on-surface-variant">Booking Date</p>
                    <p class="mt-1 font-semibold">{{ formatDate(booking.date) }}</p>
                  </div>
                  <div>
                    <p class="text-xs uppercase tracking-wider text-on-surface-variant">Duration</p>
                    <p class="mt-1 font-semibold">{{ bookingDuration(booking) }}</p>
                  </div>
                  <div>
                    <p class="text-xs uppercase tracking-wider text-on-surface-variant">Price</p>
                    <p class="mt-1 font-semibold text-primary">{{ formatPrice(booking.total_price) }}</p>
                  </div>
                </div>
              </div>

              <!-- Status & Actions -->
              <div class="flex flex-col items-end justify-between gap-4">
                <div class="flex items-center gap-2">
                  <span
                    :class="[
                      'inline-flex rounded-full border px-3 py-1 text-xs font-semibold',
                      getStatusColor(booking.status),
                    ]"
                  >
                    {{ booking.status }}
                  </span>
                  <span
                    :class="[
                      'inline-flex rounded-full border px-3 py-1 text-xs font-semibold',
                      getPaymentStatusColor(booking.payment_status),
                    ]"
                  >
                    {{ booking.payment_status }}
                  </span>
                </div>

                <div class="flex flex-wrap gap-2">
                  <button
                    v-if="['PENDING', 'CONFIRMED'].includes(booking.status)"
                    type="button"
                    class="rounded-full border border-error/40 px-3 py-1.5 text-sm font-semibold text-error transition hover:bg-error/10"
                    :disabled="cancellingId === booking.id"
                    @click="openCancelModal(booking)"
                  >
                    {{ cancellingId === booking.id ? 'Cancelling...' : 'Cancel' }}
                  </button>
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>
      </div>
    </main>

    <!-- Cancel Confirmation Modal -->
    <div
      v-if="showCancelModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      @click.self="closeCancelModal"
    >
      <div class="w-full max-w-md overflow-hidden rounded-2xl border border-outline-variant/20 bg-surface p-6 sm:p-8">
        <h2 class="text-xl font-bold">Cancel Booking?</h2>
        <p v-if="bookingToCancel" class="mt-3 text-on-surface-variant">
          Are you sure you want to cancel your booking for
          <span class="font-semibold text-on-surface">{{ bookingTourName(bookingToCancel) }}</span>
          on {{ formatDate(bookingToCancel.date) }}?
        </p>
        <div class="mt-6 flex gap-3">
          <button
            type="button"
            class="flex-1 rounded-full border border-outline-variant/40 px-4 py-2 font-semibold transition hover:bg-surface-container"
            @click="closeCancelModal"
          >
            Keep Booking
          </button>
          <button
            type="button"
            class="flex-1 rounded-full bg-error px-4 py-2 font-semibold text-on-error transition hover:brightness-110"
            :disabled="cancellingId !== null"
            @click="handleCancelBooking"
          >
            {{ cancellingId !== null ? 'Cancelling...' : 'Cancel Booking' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.booking-card {
  animation: card-enter 500ms ease-out both;
  transition: transform 280ms ease, box-shadow 280ms ease;
}

.booking-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px -18px rgb(15 23 42 / 0.45);
}

@keyframes card-enter {
  from {
    opacity: 0;
    transform: translateY(12px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
