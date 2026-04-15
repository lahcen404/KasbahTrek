<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getAuthToken } from '../api/client';
import {
  deleteGuideTour,
  getGuideBookings,
  getGuideTours,
  updateGuideBookingStatus,
} from '../api/guide';
import { tourImageUrl } from '../api/tours';
import type { GuideBooking, GuideBookingStatus, GuideTour } from '../types/guide';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const bookings = ref<GuideBooking[]>([]);
const guideTours = ref<GuideTour[]>([]);
const actingBookingId = ref<number | null>(null);
const deletingTourId = ref<number | null>(null);
const currentImageIndexByTour = ref<Record<number, number>>({});

const fallbackTourImage =
  'https://lh3.googleusercontent.com/aida-public/AB6AXuBwbqU0NLLMLiDAKTjIOM0tbT-EWzLADfXeatqXU4FtHG9WvVzwlMD-HFEje3zqcOWaEycAsNE0s6sYql5QcYBoxwnWbI6iNqmnXYqhSdXRWHDbIaYWkpMDNpKLmdvuYSQfeLgD-3ARcaKpzaTzNuD8PSJr7VFhhH72afg3u13gqUfpCRLwqeYWXUIT7_bQ5-XLLGARqxB7PqZ_PDJjhbP_WMwVF4J5HJltTwFQafi4SZdFO1rTxIA4WH4eJstBirrPnRLLU0d4xRE';

function hasValidToken(): boolean {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
}

function formatMoney(value?: number | null): string {
  const amount = typeof value === 'number' ? value : 0;
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(amount);
}

function formatShortDate(value?: string | null): string {
  if (!value) return '—';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(parsed);
}

function initials(name?: string | null): string {
  const safe = (name ?? 'Traveler').trim();
  const parts = safe.split(/\s+/).filter(Boolean);
  const first = parts[0]?.[0] ?? 'T';
  const second = parts[1]?.[0] ?? parts[0]?.[1] ?? 'R';
  return `${first}${second}`.toUpperCase();
}

const pendingBookings = computed(() =>
  bookings.value.filter((booking) => String(booking.status ?? '').toUpperCase() === 'PENDING'),
);

const confirmedBookings = computed(() =>
  bookings.value.filter((booking) => String(booking.status ?? '').toUpperCase() === 'CONFIRMED'),
);

const paidBookings = computed(() =>
  bookings.value.filter((booking) => String(booking.payment_status ?? '').toUpperCase() === 'PAID'),
);

const guideProfile = computed(() => {
  const fromTours = guideTours.value.find((tour) => tour.guide?.fullname)?.guide;
  const fromBookings = bookings.value.find((booking) => booking.tour?.guide?.fullname)?.tour?.guide;
  return fromTours ?? fromBookings ?? null;
});

const guideName = computed(() => guideProfile.value?.fullname ?? 'Guide');
const guideVerified = computed(() => Boolean(guideProfile.value?.is_verified));
const guideStatus = computed(() => (guideVerified.value ? 'Verified Guide' : 'Verification Pending'));

const monthlyEarnings = computed(() =>
  paidBookings.value.reduce((sum, booking) => sum + (typeof booking.total_price === 'number' ? booking.total_price : 0), 0),
);

const averageRating = '4.92 / 5';

const activeTours = computed(() => {
  return guideTours.value.map((tour) => {
    const bookingCount =
      typeof tour.bookings_count === 'number'
        ? tour.bookings_count
        : bookings.value.filter((booking) => booking.tour?.id === tour.id).length;

    const imageUrls = (tour.images ?? [])
      .map((image) => tourImageUrl(image.path))
      .filter((value): value is string => Boolean(value));

    if (imageUrls.length === 0) {
      imageUrls.push(fallbackTourImage);
    }

    return {
      ...tour,
      bookings_count: bookingCount,
      imageUrls,
    };
  });
});

const recentRequests = computed(() => pendingBookings.value.slice(0, 8));

function tourRequestsLabel(bookingsCount?: number): string {
  const count = bookingsCount ?? 0;
  return `${count} Bookings this week`;
}

function currentImageIndex(tourId: number, total: number): number {
  if (!total || total < 1) return 0;
  const current = currentImageIndexByTour.value[tourId] ?? 0;
  if (current >= total) return 0;
  if (current < 0) return 0;
  return current;
}

function currentImageSrc(tour: (GuideTour & { imageUrls: string[] })): string {
  const total = tour.imageUrls.length;
  const idx = currentImageIndex(tour.id, total);
  return tour.imageUrls[idx] ?? fallbackTourImage;
}

function nextImage(tour: (GuideTour & { imageUrls: string[] })): void {
  const total = tour.imageUrls.length;
  if (total <= 1) return;
  const current = currentImageIndex(tour.id, total);
  currentImageIndexByTour.value[tour.id] = (current + 1) % total;
}

function prevImage(tour: (GuideTour & { imageUrls: string[] })): void {
  const total = tour.imageUrls.length;
  if (total <= 1) return;
  const current = currentImageIndex(tour.id, total);
  currentImageIndexByTour.value[tour.id] = (current - 1 + total) % total;
}

function goToImage(tour: (GuideTour & { imageUrls: string[] }), index: number): void {
  const total = tour.imageUrls.length;
  if (total <= 1) return;
  if (index < 0 || index >= total) return;
  currentImageIndexByTour.value[tour.id] = index;
}

async function loadDashboard(): Promise<void> {
  loading.value = true;
  error.value = null;
  try {
    if (!hasValidToken()) {
      await router.push({ name: 'login', query: { redirect: '/guide/dashboard' } });
      return;
    }

    const [guideBookings, tours] = await Promise.all([getGuideBookings(), getGuideTours()]);
    bookings.value = guideBookings;
    guideTours.value = tours;
  } catch (e) {
    const err = e as { response?: { status?: number; data?: { message?: string } } };
    if (err.response?.status === 403) {
      error.value = 'This dashboard is only available for guides.';
    } else {
      error.value = err.response?.data?.message ?? 'Could not load guide dashboard.';
    }
    bookings.value = [];
    guideTours.value = [];
  } finally {
    loading.value = false;
  }
}

async function setBookingStatus(bookingId: number, status: Exclude<GuideBookingStatus, 'PENDING' | 'CANCELLED'>) {
  actingBookingId.value = bookingId;
  try {
    const response = await updateGuideBookingStatus(bookingId, status);
    if (response.booking) {
      bookings.value = bookings.value.map((booking) =>
        booking.id === bookingId ? response.booking! : booking,
      );
    }
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not update booking status.';
  } finally {
    actingBookingId.value = null;
  }
}

async function deleteTour(tourId: number, tourTitle?: string): Promise<void> {
  const label = tourTitle?.trim() || 'this tour';
  const confirmed = window.confirm(`Delete ${label}? This action cannot be undone.`);
  if (!confirmed) return;

  deletingTourId.value = tourId;
  error.value = null;

  try {
    await deleteGuideTour(tourId);
    guideTours.value = guideTours.value.filter((tour) => tour.id !== tourId);
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not delete tour.';
  } finally {
    deletingTourId.value = null;
  }
}

onMounted(() => {
  void loadDashboard();
});
</script>

<template>
  <div class="min-h-screen bg-surface font-body text-on-surface">
    <div class="flex min-h-screen pt-0">
      <!-- SideNavBar -->
      <aside
        class="hidden lg:flex flex-col w-64 h-screen sticky top-0 left-0 p-6 bg-surface-container-low border-r border-outline-variant/10"
      >
        <div class="flex flex-col gap-2 mb-8">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full overflow-hidden bg-secondary-container flex items-center justify-center">
              <span class="material-symbols-outlined text-secondary">person</span>
            </div>
            <div>
              <h3 class="font-headline font-semibold text-on-surface leading-tight">{{ guideName }}</h3>
              <p class="text-xs text-secondary font-bold uppercase tracking-wider">{{ guideStatus }}</p>
            </div>
          </div>
        </div>

        <nav class="flex flex-col gap-2">
          <button class="flex items-center gap-3 bg-orange-700 text-white rounded-full px-4 py-3 transition-transform active:scale-[0.98]" type="button">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
            <span class="font-bold text-sm">Dashboard</span>
          </button>
          <button class="flex items-center gap-3 text-slate-600 px-4 py-3 hover:bg-orange-50 rounded-full transition-all" type="button">
            <span class="material-symbols-outlined">explore</span>
            <span class="font-bold text-sm">Manage Tours</span>
          </button>
          <button class="flex items-center gap-3 text-slate-600 px-4 py-3 hover:bg-orange-50 rounded-full transition-all" type="button">
            <span class="material-symbols-outlined">event_available</span>
            <span class="font-bold text-sm">Booking Requests</span>
          </button>
          <button class="flex items-center gap-3 text-slate-600 px-4 py-3 hover:bg-orange-50 rounded-full transition-all" type="button">
            <span class="material-symbols-outlined">photo_library</span>
            <span class="font-bold text-sm">Photos</span>
          </button>
          <button class="flex items-center gap-3 text-slate-600 px-4 py-3 hover:bg-orange-50 rounded-full transition-all" type="button">
            <span class="material-symbols-outlined">verified_user</span>
            <span class="font-bold text-sm">Verification</span>
          </button>
          <button class="flex items-center gap-3 text-slate-600 px-4 py-3 hover:bg-orange-50 rounded-full transition-all" type="button">
            <span class="material-symbols-outlined">payments</span>
            <span class="font-bold text-sm">Earnings</span>
          </button>
          <button class="flex items-center gap-3 text-slate-600 px-4 py-3 hover:bg-orange-50 rounded-full transition-all mt-auto" type="button">
            <span class="material-symbols-outlined">settings</span>
            <span class="font-bold text-sm">Settings</span>
          </button>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-8 py-8 md:py-12 overflow-x-hidden">
        <section class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <span class="bg-secondary/10 text-secondary px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                {{ guideVerified ? 'VERIFIED GUIDE' : 'GUIDE ACCOUNT' }}
              </span>
            </div>
            <h1 class="text-4xl md:text-5xl font-headline font-extrabold text-on-surface tracking-tight">Welcome back, {{ guideName }}.</h1>
            <p class="text-on-surface-variant text-lg mt-2 font-body max-w-2xl">Your next adventure starts in 3 days. Here's what's happening with your tours this month.</p>
          </div>
          <button class="bg-primary text-white px-8 py-4 rounded-full font-bold flex items-center gap-2 shadow-lg hover:brightness-110 active:scale-95 transition-all" type="button" @click="router.push({ name: 'guide-tour-create' })">
            <span class="material-symbols-outlined">add</span>
            Create New Tour
          </button>
        </section>

        <div v-if="error" class="mb-8 rounded-2xl bg-error-container/70 px-6 py-4 text-sm font-medium text-error">
          {{ error }}
        </div>

        <!-- Stats -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
          <div class="bg-surface-container-lowest p-8 rounded-3xl shadow-[0_4px_20px_rgba(30,42,47,0.04)] border border-outline-variant/5">
            <div class="w-12 h-12 bg-secondary/10 text-secondary rounded-2xl flex items-center justify-center mb-6">
              <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
            </div>
            <p class="text-on-surface-variant font-bold text-xs uppercase tracking-widest mb-1">Monthly Earnings</p>
            <h2 class="text-3xl font-headline font-bold text-on-surface">{{ formatMoney(monthlyEarnings) }}</h2>
            <p class="text-secondary text-sm font-semibold mt-2 flex items-center gap-1">
              <span class="material-symbols-outlined text-xs">trending_up</span> +12% from last month
            </p>
          </div>
          <div class="bg-surface-container-lowest p-8 rounded-3xl shadow-[0_4px_20px_rgba(30,42,47,0.04)] border border-outline-variant/5">
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center mb-6">
              <span class="material-symbols-outlined text-2xl">pending_actions</span>
            </div>
            <p class="text-on-surface-variant font-bold text-xs uppercase tracking-widest mb-1">New Requests</p>
            <h2 class="text-3xl font-headline font-bold text-on-surface">{{ pendingBookings.length }} Pending</h2>
            <p class="text-primary text-sm font-semibold mt-2">Requires your attention</p>
          </div>
          <div class="bg-surface-container-lowest p-8 rounded-3xl shadow-[0_4px_20px_rgba(30,42,47,0.04)] border border-outline-variant/5">
            <div class="w-12 h-12 bg-tertiary/10 text-tertiary rounded-2xl flex items-center justify-center mb-6">
              <span class="material-symbols-outlined text-2xl">star_rate</span>
            </div>
            <p class="text-on-surface-variant font-bold text-xs uppercase tracking-widest mb-1">Avg. Rating</p>
            <h2 class="text-3xl font-headline font-bold text-on-surface">{{ averageRating }}</h2>
            <div class="flex gap-1 mt-2 text-tertiary">
              <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
              <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
              <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
              <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
              <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star_half</span>
            </div>
          </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Manage Tours -->
          <div class="lg:col-span-2 space-y-8">
            <div class="flex items-center justify-between">
              <h3 class="text-2xl font-headline font-bold text-on-surface">Active Tours</h3>
              <button class="text-primary font-bold text-sm hover:underline" type="button">View All</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div
                v-for="tour in activeTours"
                :key="tour.id"
                class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm group"
              >
                <div class="h-48 relative overflow-hidden">
                  <img
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    :src="currentImageSrc(tour)"
                    :alt="tour.title"
                  />

                  <template v-if="tour.imageUrls.length > 1">
                    <button
                      class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/85 p-1.5 text-slate-700 shadow hover:bg-white"
                      type="button"
                      @click="prevImage(tour)"
                    >
                      <span class="material-symbols-outlined text-lg">chevron_left</span>
                    </button>
                    <button
                      class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/85 p-1.5 text-slate-700 shadow hover:bg-white"
                      type="button"
                      @click="nextImage(tour)"
                    >
                      <span class="material-symbols-outlined text-lg">chevron_right</span>
                    </button>

                    <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-black/35 px-2 py-1">
                      <button
                        v-for="(_, idx) in tour.imageUrls"
                        :key="`${tour.id}-${idx}`"
                        type="button"
                        class="h-1.5 w-1.5 rounded-full transition-all"
                        :class="currentImageIndex(tour.id, tour.imageUrls.length) === idx ? 'bg-white w-4' : 'bg-white/60'"
                        @click="goToImage(tour, idx)"
                      />
                    </div>
                  </template>

                  <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-secondary">
                    {{ tourRequestsLabel(tour.bookings_count) }}
                  </div>
                </div>
                <div class="p-6">
                  <h4 class="font-headline font-bold text-lg mb-2">{{ tour.title }}</h4>
                  <p class="text-on-surface-variant text-sm line-clamp-2 mb-4">{{ tour.description }}</p>
                  <div class="flex items-center justify-between mt-auto">
                    <span class="font-bold text-primary">{{ formatMoney(tour.price) }} <span class="text-xs font-normal text-on-surface-variant">/ person</span></span>
                    <div class="flex gap-2">
                      <button
                        class="p-2 bg-surface-container-high rounded-full hover:bg-surface-container-highest transition-colors"
                        type="button"
                        @click="router.push({ name: 'guide-tour-edit', params: { id: tour.id } })"
                      >
                        <span class="material-symbols-outlined text-xl">edit</span>
                      </button>
                      <button
                        class="p-2 bg-surface-container-high rounded-full hover:bg-error/10 transition-colors disabled:opacity-50"
                        type="button"
                        :disabled="deletingTourId === tour.id"
                        @click="deleteTour(tour.id, tour.title)"
                      >
                        <span class="material-symbols-outlined text-xl">{{ deletingTourId === tour.id ? 'hourglass_top' : 'delete' }}</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Booking Requests Table -->
            <div class="mt-12 bg-surface-container-lowest rounded-3xl p-8 shadow-sm">
              <div class="flex items-center justify-between mb-8">
                <h3 class="text-2xl font-headline font-bold text-on-surface">Recent Booking Requests</h3>
                <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-bold">{{ pendingBookings.length }} PENDING</span>
              </div>

              <div v-if="loading" class="rounded-2xl bg-surface-container-high/40 p-6 text-sm text-on-surface-variant">
                Loading guide bookings...
              </div>

              <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                  <thead>
                    <tr class="text-on-surface-variant text-xs font-bold uppercase tracking-widest border-b border-outline-variant/10">
                      <th class="pb-4 px-2">Traveler</th>
                      <th class="pb-4 px-2">Tour</th>
                      <th class="pb-4 px-2">Date</th>
                      <th class="pb-4 px-2 text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-outline-variant/10">
                    <tr v-for="booking in recentRequests" :key="booking.id">
                      <td class="py-6 px-2">
                        <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center font-bold text-secondary">
                            {{ initials(booking.traveler?.fullname) }}
                          </div>
                          <div>
                            <p class="font-bold text-on-surface">{{ booking.traveler?.fullname ?? 'Traveler' }}</p>
                            <p class="text-xs text-on-surface-variant">1 Traveler</p>
                          </div>
                        </div>
                      </td>
                      <td class="py-6 px-2">
                        <p class="text-sm font-semibold text-on-surface">{{ booking.tour?.title ?? 'Tour' }}</p>
                      </td>
                      <td class="py-6 px-2 text-sm text-on-surface-variant">{{ formatShortDate(booking.date) }}</td>
                      <td class="py-6 px-2 text-right">
                        <div class="flex justify-end gap-2">
                          <button
                            class="bg-secondary text-white px-4 py-2 rounded-full text-xs font-bold hover:brightness-110 active:scale-95 transition-all disabled:opacity-50"
                            type="button"
                            :disabled="actingBookingId === booking.id"
                            @click="setBookingStatus(booking.id, 'CONFIRMED')"
                          >
                            {{ actingBookingId === booking.id ? 'Saving...' : 'Accept' }}
                          </button>
                          <button
                            class="bg-error/10 text-error px-4 py-2 rounded-full text-xs font-bold hover:bg-error/20 active:scale-95 transition-all disabled:opacity-50"
                            type="button"
                            :disabled="actingBookingId === booking.id"
                            @click="setBookingStatus(booking.id, 'REJECTED')"
                          >
                            Reject
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="recentRequests.length === 0">
                      <td colspan="4" class="py-8 px-2 text-center text-sm text-on-surface-variant">
                        No pending booking requests right now.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Right Sidebar Content -->
          <div class="space-y-8">
            <div class="bg-secondary text-white rounded-3xl p-8 relative overflow-hidden">
              <div class="relative z-10">
                <h4 class="text-xl font-headline font-bold mb-4">Guide Tip</h4>
                <p class="text-white/80 text-sm leading-relaxed mb-6">"Adding a personalized welcome video to your profile increases booking rates by up to 35% during peak season."</p>
                <button class="text-sm font-bold underline underline-offset-4" type="button">Learn More</button>
              </div>
              <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
