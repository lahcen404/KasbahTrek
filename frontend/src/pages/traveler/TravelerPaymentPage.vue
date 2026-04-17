<script setup lang="ts">
import type { AxiosError } from 'axios';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getBookingById, initiatePayPalCheckout, initiateStripeCheckout } from '../../api/bookings';
import type { TravelerBooking } from '../../types/traveler';

const stripeLogoUrl = 'https://1000logos.net/wp-content/uploads/2021/05/Stripe-logo-500x281.png';
const paypalLogoUrl = 'https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-200px.png';

const route = useRoute();
const router = useRouter();

const travelerNavItems = [
  { key: 'traveler-profile', label: 'Dashboard', icon: 'dashboard' },
  { key: 'traveler-bookings', label: 'Bookings', icon: 'event_note' },
  { key: 'traveler-favorites', label: 'Favorites', icon: 'favorite' },
  { key: 'traveler-reviews', label: 'Reviews', icon: 'reviews' },
] as const;

const loadingMethod = ref<'stripe' | 'paypal' | null>(null);
const error = ref<string | null>(null);
const loadingBooking = ref(true);
const booking = ref<TravelerBooking | null>(null);

const bookingId = computed(() => Number(route.params.id));

const isAlreadyPaid = computed(() => booking.value?.payment_status === 'PAID');

const canPay = computed(() => {
  if (!booking.value) return false;
  const pending = booking.value.status === 'PENDING';
  const payable = booking.value.payment_status === 'UNPAID' || booking.value.payment_status === 'FAILED';
  return pending && payable;
});

const bookingTourName = computed(() => booking.value?.tour?.title ?? booking.value?.tour?.name ?? 'Tour');

const bookingDateLabel = computed(() => {
  if (!booking.value?.date) return 'Unknown date';
  const d = new Date(booking.value.date);
  if (Number.isNaN(d.getTime())) return booking.value.date;

  return new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(d);
});

const amountMAD = computed(() => {
  const raw = booking.value?.total_price;
  const num = typeof raw === 'string' ? parseFloat(raw) : raw ?? 0;
  if (Number.isNaN(num)) return 'MAD 0';

  return new Intl.NumberFormat('en-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(num);
});

async function loadBooking(): Promise<void> {
  loadingBooking.value = true;
  error.value = null;

  if (!Number.isFinite(bookingId.value) || bookingId.value <= 0) {
    error.value = 'Invalid booking reference.';
    loadingBooking.value = false;
    return;
  }

  try {
    booking.value = await getBookingById(bookingId.value);
  } catch (e) {
    const err = e as AxiosError<{ message?: string }>;
    error.value = err.response?.data?.message ?? 'Could not load booking details.';
  } finally {
    loadingBooking.value = false;
  }
}

async function proceedWithStripe(): Promise<void> {
  if (!Number.isFinite(bookingId.value) || bookingId.value <= 0) {
    error.value = 'Invalid booking reference.';
    return;
  }

  if (!canPay.value) {
    error.value = 'This booking is not eligible for payment.';
    return;
  }

  loadingMethod.value = 'stripe';
  error.value = null;

  try {
    const result = await initiateStripeCheckout(bookingId.value);
    if (!result.url) {
      throw new Error('Missing Stripe checkout URL');
    }

    window.location.assign(result.url);
  } catch (e) {
    const err = e as AxiosError<{ message?: string }>;
    error.value = err.response?.data?.message ?? 'Could not start Stripe checkout. Please try again.';
  } finally {
    loadingMethod.value = null;
  }
}

async function proceedWithPayPal(): Promise<void> {
  if (!Number.isFinite(bookingId.value) || bookingId.value <= 0) {
    error.value = 'Invalid booking reference.';
    return;
  }

  if (!canPay.value) {
    error.value = 'This booking is not eligible for payment.';
    return;
  }

  loadingMethod.value = 'paypal';
  error.value = null;

  try {
    const result = await initiatePayPalCheckout(bookingId.value);
    if (!result.url) {
      throw new Error('Missing PayPal approval URL');
    }

    window.location.assign(result.url);
  } catch (e) {
    const err = e as AxiosError<{ message?: string }>;
    error.value = err.response?.data?.message ?? 'Could not start PayPal checkout. Please try again.';
  } finally {
    loadingMethod.value = null;
  }
}

function goToBookings(): void {
  void router.push({ name: 'traveler-bookings' });
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

function openPayPalInfo(event: Event): void {
  event.preventDefault();
  window.open(
    'https://www.paypal.com/ma/webapps/mpp/paypal-popup',
    'WIPaypal',
    'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=1060,height=700',
  );
}

onMounted(async () => {
  await loadBooking();
});
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <main class="mx-auto max-w-7xl px-6 pb-16 pt-24">
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

        <div class="relative grid gap-6 lg:grid-cols-[1.1fr,1fr]">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Secure Checkout</p>
            <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">Pay with Stripe or PayPal</h1>
            <p class="mt-2 text-on-surface-variant">
              Complete payment for booking #{{ bookingId }} using your preferred provider.
            </p>

            <div v-if="loadingBooking" class="mt-6 rounded-2xl bg-surface p-4 text-sm text-on-surface-variant">
              Loading booking details...
            </div>

            <div v-else-if="booking" class="mt-6 rounded-2xl border border-outline-variant/20 bg-surface p-5">
              <p class="text-xs uppercase tracking-wider text-on-surface-variant">Booking Summary</p>
              <h2 class="mt-2 text-lg font-bold">{{ bookingTourName }}</h2>

              <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div>
                  <p class="text-xs uppercase tracking-wider text-on-surface-variant">Date</p>
                  <p class="mt-1 font-semibold">{{ bookingDateLabel }}</p>
                </div>
                <div>
                  <p class="text-xs uppercase tracking-wider text-on-surface-variant">Amount</p>
                  <p class="mt-1 font-semibold text-primary">{{ amountMAD }}</p>
                </div>
                <div>
                  <p class="text-xs uppercase tracking-wider text-on-surface-variant">Status</p>
                  <p class="mt-1 font-semibold">{{ booking.status }}</p>
                </div>
                <div>
                  <p class="text-xs uppercase tracking-wider text-on-surface-variant">Payment</p>
                  <p class="mt-1 font-semibold">{{ booking.payment_status }}</p>
                </div>
              </div>
            </div>

            <div
              v-if="error"
              class="mt-6 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error"
            >
              {{ error }}
            </div>

            <div
              v-if="isAlreadyPaid"
              class="mt-6 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900"
            >
              This booking is already paid. You can view it in your bookings list.
            </div>
          </div>

          <div class="space-y-4">
            <button
              type="button"
              class="w-full rounded-2xl border border-sky-400/40 bg-sky-50 px-5 py-6 text-left transition hover:border-sky-500/60 hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="loadingMethod !== null || loadingBooking || !canPay"
              @click="proceedWithStripe"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <img
                    :src="stripeLogoUrl"
                    alt="Stripe"
                    class="h-7 w-auto object-contain"
                    loading="lazy"
                    decoding="async"
                  />
                  <p class="text-lg font-bold text-sky-900">Stripe</p>
                </div>
                <span class="rounded-full border border-sky-500/40 px-2 py-0.5 text-xs font-semibold text-sky-800">Cards</span>
              </div>
              <p class="mt-2 text-sm text-sky-900/80">Pay securely with your debit or credit card.</p>
              <p class="mt-4 text-sm font-semibold text-sky-900">
                {{ loadingMethod === 'stripe' ? 'Redirecting to Stripe...' : 'Continue with Stripe' }}
              </p>
            </button>

            <button
              type="button"
              class="w-full rounded-2xl border border-blue-500/35 bg-blue-50 px-5 py-6 text-left transition hover:border-blue-600/60 hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="loadingMethod !== null || loadingBooking || !canPay"
              @click="proceedWithPayPal"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <a
                    href="https://www.paypal.com/ma/webapps/mpp/paypal-popup"
                    title="How PayPal Works"
                    @click="openPayPalInfo"
                  >
                    <img
                      :src="paypalLogoUrl"
                      alt="PayPal Logo"
                      class="h-7 w-auto object-contain"
                      loading="lazy"
                      decoding="async"
                    />
                  </a>
                  <p class="text-lg font-bold text-blue-900">PayPal</p>
                </div>
                <span class="rounded-full border border-blue-600/40 px-2 py-0.5 text-xs font-semibold text-blue-800">Wallet</span>
              </div>
              <p class="mt-2 text-sm text-blue-900/80">Checkout quickly with your PayPal account.</p>
              <p class="mt-4 text-sm font-semibold text-blue-900">
                {{ loadingMethod === 'paypal' ? 'Redirecting to PayPal...' : 'Continue with PayPal' }}
              </p>
            </button>

            <button
              type="button"
              class="w-full rounded-full border border-outline-variant/40 px-4 py-2 text-sm font-semibold transition hover:bg-surface-container"
              :disabled="loadingMethod !== null"
              @click="goToBookings"
            >
              Back to Bookings
            </button>
          </div>
        </div>
      </section>
      </div>
    </main>
  </div>
</template>
