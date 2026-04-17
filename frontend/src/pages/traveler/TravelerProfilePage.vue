<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getCurrentUser } from '../../api/auth';

const router = useRouter();
const route = useRoute();

const loading = ref(true);
const error = ref<string | null>(null);
const fullname = ref('Traveler');
const email = ref('');
const role = ref('TRAVELER');
const profileGalleryImages = [
  {
    src: 'https://visitmorocco30.com/uploads/blogs/entre-source-et-silence-beni-mellal-autrement.jpg',
    alt: 'Mountain and nature landscape',
  },
  {
    src: 'https://t3.ftcdn.net/jpg/05/36/30/00/360_F_536300051_JPoaBsM5KMR2mKjXzYj5QsWUZrlbZw2P.jpg',
    alt: 'Scenic mountain view',
  },
  {
    src: 'https://upload.wikimedia.org/wikipedia/commons/e/eb/La_cath%C3%A9drale%2C_Azilal.jpg',
    alt: 'Mountain rock formation',
  },
  {
    src: 'https://marocvoyagedereve.com/wp-content/uploads/2025/04/Cathedrale-Imsfrane.jpg',
    alt: 'Nature cliffs and mountain area',
  },
] as const;

const initials = computed(() => {
  const parts = fullname.value
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2);

  return parts.map((part) => part.charAt(0).toUpperCase()).join('') || 'TR';
});

onMounted(async () => {
  loading.value = true;
  error.value = null;

  try {
    const me = await getCurrentUser();
    fullname.value = me.fullname?.trim() || 'Traveler';
    email.value = me.email?.trim() || '';
    role.value = (me.role ?? 'TRAVELER').toUpperCase();
  } catch {
    error.value = 'Could not load your profile right now.';
  } finally {
    loading.value = false;
  }
});

function goToTours(): void {
  void router.push({ name: 'tours' });
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
          <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Traveler Space</p>
          <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">My Profile</h1>
          <p class="mt-2 max-w-2xl text-on-surface-variant">
            Your account overview in one clean place.
          </p>
        </header>

        <section class="relative mt-8 space-y-4">
          <div class="gallery-panel rounded-2xl border border-outline-variant/20 bg-surface p-5 sm:p-6 gallery-panel-animate">
            <div class="flex items-center justify-between gap-4">
              <div>
                <h2 class="text-lg font-semibold">Gallery</h2>
                <p class="mt-1 text-sm text-on-surface-variant">Your selected mountain and nature photos.</p>
              </div>
              <span class="gallery-badge rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                Creative View
              </span>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
              <article
                v-for="(image, index) in profileGalleryImages"
                :key="image.src"
                class="gallery-item photo-frame overflow-hidden rounded-l border border-outline-variant/20 bg-surface-container"
                :style="{ animationDelay: `${index * 80}ms` }"
              >
                <img :src="image.src" :alt="image.alt" class="h-40 w-full object-cover sm:h-48" loading="lazy" />
                
              </article>
            </div>
          </div>

          <div class="rounded-2xl border border-outline-variant/20 bg-surface p-5 sm:p-6 profile-panel-animate">
            <p v-if="loading" class="text-on-surface-variant">Loading profile...</p>
            <p v-else-if="error" class="rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
              {{ error }}
            </p>

            <div v-else class="space-y-6">
              <div class="profile-id-card flex items-center gap-4 rounded-2xl border border-outline-variant/20 bg-surface-container p-4">
                <div
                  class="initials-badge inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary/15 text-lg font-bold text-primary"
                >
                  {{ initials }}
                </div>
                <div class="min-w-0">
                  <p class="truncate text-lg font-semibold">{{ fullname }}</p>
                  <p class="truncate text-sm text-on-surface-variant">{{ email || 'No email available' }}</p>
                </div>
                <span
                  class="ml-auto inline-flex shrink-0 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                >
                  {{ role }}
                </span>
              </div>

              <div class="grid gap-3">
                <article class="rounded-xl border border-outline-variant/20 bg-surface-container px-4 py-3">
                  <p class="text-xs uppercase tracking-wider text-on-surface-variant">Full Name</p>
                  <p class="mt-1 font-semibold">{{ fullname }}</p>
                </article>

                <article class="rounded-xl border border-outline-variant/20 bg-surface-container px-4 py-3">
                  <p class="text-xs uppercase tracking-wider text-on-surface-variant">Email</p>
                  <p class="mt-1 truncate font-semibold">{{ email || 'No email available' }}</p>
                </article>

                <article class="rounded-xl border border-outline-variant/20 bg-surface-container px-4 py-3">
                  <p class="text-xs uppercase tracking-wider text-on-surface-variant">Role</p>
                  <p class="mt-1 font-semibold">{{ role }}</p>
                </article>
              </div>
            </div>
          </div>
        </section>

        <div class="relative mt-6">
          <button
            type="button"
            class="rounded-full bg-primary px-6 py-3 font-semibold text-on-primary transition hover:brightness-110"
            @click="goToTours"
          >
            Explore Tours
          </button>
        </div>
      </section>
      </div>
    </main>
  </div>
</template>

<style scoped>
.profile-panel-animate,
.gallery-panel-animate {
  animation: panel-enter 500ms ease-out both;
}

.profile-id-card {
  background: linear-gradient(145deg, rgb(255 255 255 / 0.5), rgb(255 255 255 / 0.08));
}

.initials-badge {
  box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.45);
  animation: pulse-soft 2.8s ease-in-out infinite;
}

.gallery-panel {
  background-image: radial-gradient(circle at 100% 0%, rgb(59 130 246 / 0.1), transparent 42%);
}

.gallery-badge {
  letter-spacing: 0.07em;
}

.gallery-panel-animate {
  animation-delay: 120ms;
}

.gallery-item {
  position: relative;
  animation: tile-enter 500ms ease-out both;
  transition: transform 280ms ease, box-shadow 280ms ease;
}

.photo-frame {
  box-shadow: 0 18px 30px -24px rgb(15 23 42 / 0.6);
}

.gallery-item:nth-child(1) {
  transform: rotate(-1deg);
}

.gallery-item:nth-child(2) {
  transform: rotate(1deg);
}

.gallery-item:nth-child(3) {
  transform: rotate(-0.6deg);
}

.gallery-item:nth-child(4) {
  transform: rotate(0.8deg);
}

.gallery-item:hover {
  transform: translateY(-6px) rotate(0deg);
  box-shadow: 0 24px 42px -24px rgb(15 23 42 / 0.58);
}

.gallery-item img {
  transition: transform 450ms ease;
}

.gallery-item:hover img {
  transform: scale(1.06);
}

.photo-caption {
  background: linear-gradient(to top, rgb(0 0 0 / 0.62), rgb(0 0 0 / 0));
}

@keyframes panel-enter {
  from {
    opacity: 0;
    transform: translateY(14px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse-soft {
  0%,
  100% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.04);
  }
}

@keyframes tile-enter {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@media (max-width: 640px) {
  .gallery-item {
    min-height: 0;
    transform: none;
  }

  .gallery-item:hover {
    transform: translateY(-4px);
  }

  .initials-badge {
    animation: none;
  }
}
</style>
