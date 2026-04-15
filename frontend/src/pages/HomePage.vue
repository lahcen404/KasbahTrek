<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AppFooter from '../components/layout/AppFooter.vue';
import AppNavbar from '../components/layout/AppNavbar.vue';
import { getTours, tourImageUrl } from '../api/tours';
import type { Tour } from '../types/tours';

const loadingTours = ref(true);
const toursError = ref<string | null>(null);
const tours = ref<Tour[]>([]);

const fallbackTourImages = [
  'https://lh3.googleusercontent.com/aida-public/AB6AXuAEjgHrX2YGJv6DhogNrwgXcBGqxZPOKUojJGB_NkvzSrhNpYOBHziN7Fxom3nkEVnx7v6JpCg0EHvWPF8cO-4qRyR_6WfjAG3m6R-_gH9rOCQ5V86lbCKuJL0oThw2ajEPfc1TbXFBAwRYI32Pulf-T3iek5JSRRTD5xNwY1YPixzpcRY9nNvZRQp2DdS6HsQqwkVPZkxYZg-uHmBn0hoO6Pj-z81Snw1-bAs5t9i_DOGESVzO_rhWrJBsg6KUHsV1NwWEk7MeofM',
  'https://lh3.googleusercontent.com/aida-public/AB6AXuAUqjpyv-NeyfVLlkmf6URJ4HBvV5ZKnMyoAVHZfSQyIGi2fBvXxOwLBTkrABi-QWWYgeI0gTMEIdPituGTO9vx2ULCRwODn3wDhxoK8xWlW50ieNCIZRizDXXbJSeL0Y3p2DGC1PsOFHlMktCQVfgCP0G44tRVntcahS7F3HAFbCC_3VjrqVvGOPQSV_OTtC4X770HV-JBvEAoQO2xaXPkxpytT2LvZ8aknFBkwtYG32z8PkwXArh-mlKGuiRvYmppHSJs_ERaD3Q',
  'https://lh3.googleusercontent.com/aida-public/AB6AXuA6yYb0SlUsf3td3GjBVGdE43FlQUjU7ECw-lKYfx8FN9ykEAii3YWVTdFLMR3yTt90UQz8U0LeA7zZTjfoDQCYSbcWcjSKGmnGi89P2IqjCmH6-8U1Zo1h4kqqFv920L3GXOYVjscq3YCrTZkPr8wePM96il-5NtyDZG9sPAgt8DMrHvYFFDkpShVl2vHzZ7A_L2ngkXZ_edH9PWrdbJmnxjMrvQjxebBuybFhgbSkYFcicrP6Hp1jmUwBd4VPPjE8B4Q93rUyshM',
];

const featuredTours = computed(() => tours.value.slice(0, 3));

function formatPrice(value: number): string {
  return new Intl.NumberFormat('en-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value);
}

function tourCardImage(tour: Tour, idx: number): string {
  const first = tour.images?.[0]?.path;
  return tourImageUrl(first) ?? fallbackTourImages[idx % fallbackTourImages.length];
}

function tourCategoryLabel(tour: Tour): string | null {
  const category =
    typeof tour.category === 'string'
      ? tour.category
      : tour.category?.name ?? tour.category_name ?? null;
  if (category) return String(category);
  if (typeof tour.category_id === 'number') return `Category #${tour.category_id}`;
  return 'Uncategorized';
}

function tourDifficultyLabel(tour: Tour): string | null {
  const diff = tour.difficulty ? String(tour.difficulty) : null;
  return diff;
}

function tourMetaLine(tour: Tour): string {
  if (typeof tour.duration_hours === 'number' && tour.duration_hours > 0) {
    const days = Math.max(1, Math.round(tour.duration_hours / 24));
    return `${days} Day${days > 1 ? 's' : ''} • ${tour.location ?? 'Morocco'}`;
  }
  return `${tour.location ?? 'Morocco'} • Curated journey`;
}

onMounted(async () => {
  loadingTours.value = true;
  toursError.value = null;
  try {
    tours.value = await getTours({ per_page: 9 });
  } catch {
    toursError.value = 'Could not load tours right now.';
    tours.value = [];
  } finally {
    loadingTours.value = false;
  }
});
</script>

<template>
  <div
    class="bg-surface font-body text-on-surface selection:bg-primary-container selection:text-on-primary-container"
  >
    <AppNavbar />

    <!-- Hero -->
    <header class="relative flex min-h-[921px] items-center overflow-hidden pt-20">
      <div class="absolute inset-0 z-0">
        <img
          alt=""
          class="h-full w-full object-cover"
          src="https://lh3.googleusercontent.com/aida-public/AB6AXuBBhfI_WpWN8YIZF5ISEjRkkbJPmpbau34vwQIOBJU8MACRIeUnrdchsoFusi9CM1mTCamDDqgrBEmFxNHNNR3WWIhRlOooDcdhjSAoD3DILFCYljUxjixCPMXxLNa2dko6SADxxJ-uzRj_IJAHA1YELLQ6gkGJGe0UlVbYxEO1zPQZFJIES-bbUOO-n_R0Fi2F_4Awzbzh8H8EREHGvCYFmn_F93Mlxi35lwkAcdNkJoXwVEjrn0cLdnKG93WDw5WnINYw1ZUOm_4"
        />
        <div
          class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent opacity-60"
        />
        <div class="absolute inset-0 bg-orange-950/20" />
      </div>
      <div class="relative z-10 mx-auto w-full max-w-7xl px-6">
        <div class="max-w-2xl">
          <h1
            class="mb-6 font-headline text-5xl font-bold leading-tight tracking-tight text-white md:text-7xl"
          >
            Explore Hidden <span class="text-tertiary-fixed-dim">Morocco</span>
          </h1>
          <p class="mb-10 max-w-lg font-body text-xl leading-relaxed text-white/90">
            Experience authentic adventure in high-end desert camps and traditional villages.
            Curated journeys for the modern nomad.
          </p>
          <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-br from-[#C46A2D] to-[#E3B23C] px-10 py-4 font-bold text-white transition-all hover:scale-105 hover:shadow-xl active:scale-95"
            >
              <span class="material-symbols-outlined">explore</span>
              Browse Tours
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-full bg-white/10 px-10 py-4 font-bold text-white backdrop-blur transition-all hover:bg-white/15 active:scale-95"
            >
              <span class="material-symbols-outlined">groups</span>
              Meet Guides
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-full bg-white/10 px-10 py-4 font-bold text-white backdrop-blur transition-all hover:bg-white/15 active:scale-95"
            >
              <span class="material-symbols-outlined">play_circle</span>
              How It Works
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Featured Tours -->
    <section class="bg-surface py-24">
      <div class="mx-auto max-w-7xl px-6">
        <div class="mb-12 flex items-end justify-between">
          <div>
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-primary"
              >Curated Experiences</span
            >
            <h2 class="font-headline text-4xl font-bold text-orange-900">Featured Expeditions</h2>
          </div>
          <RouterLink
            :to="{ name: 'tours' }"
            class="hidden items-center gap-2 font-bold text-primary transition-all hover:gap-4 md:flex"
          >
            View All Tours <span class="material-symbols-outlined">arrow_forward</span>
          </RouterLink>
        </div>
        <div v-if="toursError" class="rounded-2xl bg-error-container/70 px-6 py-4 text-sm font-medium text-error">
          {{ toursError }}
        </div>

        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-3">
          <!-- Skeletons -->
          <div
            v-if="loadingTours"
            v-for="i in 3"
            :key="i"
            class="overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm"
          >
            <div class="h-80 animate-pulse bg-surface-container-high" />
            <div class="space-y-4 p-8">
              <div class="flex items-center justify-between">
                <div class="h-6 w-28 animate-pulse rounded-full bg-surface-container-high" />
                <div class="h-6 w-14 animate-pulse rounded-full bg-surface-container-high" />
              </div>
              <div class="h-7 w-2/3 animate-pulse rounded-lg bg-surface-container-high" />
              <div class="h-5 w-1/2 animate-pulse rounded-lg bg-surface-container-high" />
              <div class="h-6 w-full animate-pulse rounded-lg bg-surface-container-high" />
            </div>
          </div>

          <!-- Real tours -->
          <div
            v-else
            v-for="(tour, idx) in featuredTours"
            :key="tour.id"
            class="group relative overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm transition-all duration-500 hover:shadow-xl"
          >
            <div class="h-80 overflow-hidden">
              <img
                :src="tourCardImage(tour, idx)"
                :alt="tour.title"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                loading="lazy"
                decoding="async"
              />
            </div>
            <div class="p-8">
              <div class="mb-4 flex items-start justify-between">
                <div class="flex flex-wrap items-center gap-2">
                  <span
                    v-if="tourCategoryLabel(tour)"
                    class="rounded-full bg-secondary/10 px-3 py-1 text-xs font-bold uppercase text-secondary"
                  >
                    {{ tourCategoryLabel(tour) }}
                  </span>
                  <span
                    v-if="tourDifficultyLabel(tour)"
                    class="rounded-full bg-tertiary/10 px-3 py-1 text-xs font-bold uppercase text-tertiary"
                  >
                    {{ tourDifficultyLabel(tour) }}
                  </span>
                </div>
                <div class="flex items-center gap-1 text-tertiary">
                  <span
                    class="material-symbols-outlined text-sm"
                    style="font-variation-settings: 'FILL' 1"
                    >star</span
                  >
                  <span class="text-sm font-bold">4.9</span>
                </div>
              </div>
              <h3 class="mb-2 text-2xl font-bold text-orange-950">{{ tour.title }}</h3>
              <p class="mb-6 text-sm text-outline">
                {{ tourMetaLine(tour) }}
              </p>
              <div class="flex items-center justify-between">
                <p class="font-semibold text-on-surface">
                  From <span class="text-xl font-bold text-primary">{{ formatPrice(tour.price) }}</span>
                </p>
                <span
                  class="material-symbols-outlined text-outline transition-colors group-hover:text-primary"
                  >favorite</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Us -->
    <section class="relative overflow-hidden bg-surface-container-low py-24">
      <div
        class="zellige-pattern-mask pointer-events-none absolute inset-0 text-primary"
        aria-hidden="true"
      />
      <div class="relative z-10 mx-auto max-w-7xl px-6">
        <div class="mx-auto mb-16 max-w-2xl text-center">
          <h2 class="mb-6 font-headline text-4xl font-bold text-orange-900">
            Crafting Memories, Not Just Trips
          </h2>
          <p class="font-body text-slate-600">
            We believe in travel that respects the land and its people while providing an
            unparalleled level of comfort.
          </p>
        </div>
        <div class="grid grid-cols-1 gap-12 md:grid-cols-3">
          <div
            class="flex flex-col items-center rounded-2xl bg-surface-container-lowest p-10 text-center shadow-sm"
          >
            <div
              class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary"
            >
              <span class="material-symbols-outlined text-3xl">camping</span>
            </div>
            <h4 class="mb-4 text-xl font-bold text-orange-950">Premium Glamping</h4>
            <p class="text-sm leading-relaxed text-slate-500">
              Stay in artisan-crafted canvas suites with en-suite facilities and handcrafted Moroccan
              furniture.
            </p>
          </div>
          <div
            class="flex flex-col items-center rounded-2xl bg-surface-container-lowest p-10 text-center shadow-sm md:-translate-y-8"
          >
            <div
              class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-secondary/10 text-secondary"
            >
              <span class="material-symbols-outlined text-3xl">local_fire_department</span>
            </div>
            <h4 class="mb-4 text-xl font-bold text-orange-950">Authentic Evenings</h4>
            <p class="text-sm leading-relaxed text-slate-500">
              Nightly campfires featuring traditional music, storytelling, and gourmet Berber cuisine
              under the stars.
            </p>
          </div>
          <div
            class="flex flex-col items-center rounded-2xl bg-surface-container-lowest p-10 text-center shadow-sm"
          >
            <div
              class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-tertiary/10 text-tertiary"
            >
              <span class="material-symbols-outlined text-3xl">landscape</span>
            </div>
            <h4 class="mb-4 text-xl font-bold text-orange-950">Local Expert Guides</h4>
            <p class="text-sm leading-relaxed text-slate-500">
              Our guides are members of the local communities who share deep knowledge of hidden trails
              and history.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Storytelling -->
    <section class="bg-surface py-24">
      <div class="mx-auto max-w-7xl px-6">
        <div class="grid grid-cols-1 items-center gap-20 md:grid-cols-2">
          <div class="relative">
            <div class="relative z-10 aspect-[4/5] overflow-hidden rounded-3xl shadow-2xl">
              <img
                alt=""
                class="h-full w-full object-cover"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuC5INfewA_BmCQwm1g7pey_iP1xtgyCq34XnwHSpmYHCxiuqWdYnminfdK6ObDus8HVnR94he-X2UzrULq01xhIPT4HHC0wNSrGeVF_3Fo2TVDX6G93r02PRa1WZzK8cQ4wRzqAE8PMYbiqbECYJYljaLyxKWfqz1co-NkgLuZwQQJ6VmeFq4HJGxrSy067VdJE6X5dwtXgEDc5r1Lgw8X6_0naZs01NjE5sAwAXHLrXEWvw_5p9KTpsrkWLtGY5VI7pS8R1MVII2s"
              />
            </div>
            <div
              class="absolute -bottom-10 -right-10 z-20 hidden aspect-square w-2/3 overflow-hidden rounded-3xl border-8 border-surface shadow-2xl md:block"
            >
              <img
                alt=""
                class="h-full w-full object-cover"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCXNLYjvsf_HcvO7OeapnFCETEKcyD1sfvtRBujcG2iORVwpG0i9mgGKVoeBanpCv25uKVUFNtvQ8PrZNu4kjWbSIDnumRCx8Y4dLMRZ8vCfVQB_lp1NhJeddYgQaqFDld0r1FNNoZmJ02nRPM-GPcCUdL3ns58vcmyR6U7y0eiFUyB10p9HN6KRLGPhKqQskHigqzSkr71o6Md4rQEZsSU5VNmm5eSbN3k9SGWUV_yD8yZQeDo7XAG0q95YT9Y6k5EYv28P8GZFaI"
              />
            </div>
          </div>
          <div class="space-y-8">
            <span class="text-xs font-bold uppercase tracking-widest text-primary">Our Heritage</span>
            <h2 class="font-headline text-5xl font-bold leading-tight text-orange-900">
              Living Poetry in the <span class="font-normal italic">Atlas Mountains</span>
            </h2>
            <p class="text-lg leading-relaxed text-slate-600">
              Every trek is a narrative. From the first pour of mint tea in a remote village to the
              silence of the high peaks, we invite you to be part of Morocco's living history.
            </p>
            <p class="text-lg leading-relaxed text-slate-600">
              Our journeys are designed to slow down time, allowing for genuine connections with the
              land and its caretakers. Discover the rhythm of life in the Kasbahs.
            </p>
            <div class="pt-6">
              <button
                type="button"
                class="rounded-full bg-secondary px-8 py-4 font-bold text-on-secondary transition-all hover:shadow-lg"
              >
                Discover the Culture
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="relative bg-surface-container-high py-24">
      <div class="mx-auto max-w-7xl px-6">
        <div class="flex flex-col items-center gap-12 md:flex-row">
          <div class="md:w-1/3">
            <h2 class="mb-6 font-headline text-4xl font-bold text-orange-900">
              What Fellow Travelers Say
            </h2>
            <div class="mb-4 flex gap-2 text-tertiary">
              <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1"
                >star</span
              >
              <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1"
                >star</span
              >
              <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1"
                >star</span
              >
              <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1"
                >star</span
              >
              <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1"
                >star</span
              >
            </div>
            <p class="font-body italic text-slate-500">
              Average rating 4.9/5 based on 2,400+ reviews.
            </p>
          </div>
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:w-2/3">
            <div class="rounded-2xl bg-surface-container-lowest p-8 shadow-sm">
              <div class="mb-6 flex items-center gap-4">
                <img
                  alt=""
                  class="h-12 w-12 rounded-full object-cover"
                  src="https://lh3.googleusercontent.com/aida-public/AB6AXuBHEZ0N43aznLrU30yPD3VNZzgkzcFG9Glmll9kZeGVWQxy8q2F-A_BE9v53zD07UDHueAWZQ0UrHDwSKYUbSuNQ-9EIiwY34y2JzW7pLSXK9NCi7XZIh1ZTVV3dCty58gmVW-f2nSn9jMyUHAmBeHujQ85NM0cAVMurXDDsbCjLOHfTk2AjydHoTS6mL6enZ7UeOXMgkKbNQe0c6erx5Ux7FDQIogXsmrtF9XsUN-z6Xqy5ITy2n9t4B8YWeyXGrUk2DTcsKugXK4"
                />
                <div>
                  <h5 class="font-bold text-orange-950">Sarah Jenkins</h5>
                  <p class="text-xs font-semibold uppercase tracking-wide text-outline">London, UK</p>
                </div>
              </div>
              <p class="leading-relaxed text-slate-600">
                "The most spiritual experience of my life. The camp in the Agafay desert felt like a
                dream. Every detail was curated with so much love."
              </p>
            </div>
            <div
              class="rounded-2xl bg-surface-container-lowest p-8 shadow-sm sm:translate-y-6"
            >
              <div class="mb-6 flex items-center gap-4">
                <img
                  alt=""
                  class="h-12 w-12 rounded-full object-cover"
                  src="https://lh3.googleusercontent.com/aida-public/AB6AXuDi3UhFRq8OWq4XP2NVS53a3I0EQUcuVg1NqQ_4zlupj937wpmse2lidVqU74TUPrhCkOXGVdoVLffCzqfel62UJT5ZFAwtMTuyd7wrErl0F-bZroEmHX5hT1fPS3jsI7srysLNOpG_DROIbh8YVySA-mHCMpq6OsCCMI2ZWklZCakE74e8f9nk8wlubGN0GdOeUXgJ7dRENnAwbmDk84LNwIVCP0pvznnvemPMnQAijnhAxuhBI0IWJCcftHtUEGBDo39r5AmR6B0"
                />
                <div>
                  <h5 class="font-bold text-orange-950">Marco Rossi</h5>
                  <p class="text-xs font-semibold uppercase tracking-wide text-outline">Milan, Italy</p>
                </div>
              </div>
              <p class="leading-relaxed text-slate-600">
                "Authentic, challenging, and yet incredibly luxurious. The guides are true experts and
                made us feel like family from day one."
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-24">
      <div class="mx-auto max-w-5xl px-6">
        <div class="relative overflow-hidden rounded-3xl bg-orange-900 p-12 text-center text-white md:p-20">
          <div class="absolute inset-0 z-0">
            <img
              alt=""
              class="h-full w-full object-cover opacity-30"
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuC0_y0ZESgbwP1pf0R5VtnWEnb7psM1BcG3v4ao7wrdr3C3ggeAt7H4saOOeFEjXkWWjhtxbb9aBW0h04Nlim53bcTb5LLys8jHJUKI4S5SIc2CU5z25PxJ2YdsgQl8vvGyYgU7eFn2_re_45wnl4t2bn1prHyku7PvVdkVNeaj2KUJT1uNfCDRQO1Ur2yX-pV4fOP01RvLafaQL8Y8KAdkcxT-uzt5AooIKAJ2KK2bZHbjK_Viy197qAg3eohYnmHGwnSu1rK8l-o"
            />
            <div class="absolute inset-0 bg-gradient-to-br from-primary/80 to-transparent" />
          </div>
          <div class="relative z-10 space-y-8">
            <h2 class="font-headline text-4xl font-bold leading-tight md:text-6xl">
              Ready to Find Your Path?
            </h2>
            <p class="mx-auto max-w-xl font-body text-xl text-white/80">
              Join us for a journey beyond the guidebooks. Custom itineraries tailored to your thirst
              for adventure.
            </p>
            <button
              type="button"
              class="rounded-full bg-surface-bright px-12 py-5 text-lg font-bold text-primary shadow-xl transition-transform hover:scale-105"
            >
              Start Your Journey
            </button>
          </div>
        </div>
      </div>
    </section>

    <AppFooter class="mt-12" />
  </div>
</template>
