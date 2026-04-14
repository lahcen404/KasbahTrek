<script setup lang="ts">
import AppFooter from '../components/layout/AppFooter.vue';
import AppNavbar from '../components/layout/AppNavbar.vue';
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
          <div
            class="flex max-w-3xl flex-col items-stretch gap-2 rounded-full bg-surface-bright p-2 shadow-2xl md:flex-row md:items-center md:p-3"
          >
            <div class="flex-1 px-6 py-2">
              <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-outline"
                >Location</label
              >
              <input
                class="w-full border-none bg-transparent p-0 font-semibold text-on-surface placeholder:text-outline-variant focus:ring-0"
                placeholder="Where to?"
                type="text"
              />
            </div>
            <div class="hidden h-10 w-px bg-outline-variant/30 md:block" />
            <div class="flex-1 px-6 py-2">
              <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-outline"
                >Date</label
              >
              <input
                class="w-full border-none bg-transparent p-0 font-semibold text-on-surface placeholder:text-outline-variant focus:ring-0"
                placeholder="When?"
                type="text"
              />
            </div>
            <div class="hidden h-10 w-px bg-outline-variant/30 md:block" />
            <div class="flex-1 px-6 py-2">
              <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-outline"
                >Guests</label
              >
              <input
                class="w-full border-none bg-transparent p-0 font-semibold text-on-surface placeholder:text-outline-variant focus:ring-0"
                placeholder="How many?"
                type="text"
              />
            </div>
            <button
              type="button"
              class="flex items-center justify-center gap-2 rounded-full bg-gradient-to-br from-[#C46A2D] to-[#E3B23C] px-10 py-4 font-bold text-white transition-all hover:scale-105 hover:shadow-xl"
            >
              <span class="material-symbols-outlined">search</span>
              Explore
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
          <button
            type="button"
            class="hidden items-center gap-2 font-bold text-primary transition-all hover:gap-4 md:flex"
          >
            View All Tours <span class="material-symbols-outlined">arrow_forward</span>
          </button>
        </div>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
          <div
            class="group relative overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm transition-all duration-500 hover:shadow-xl"
          >
            <div class="h-80 overflow-hidden">
              <img
                alt=""
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAEjgHrX2YGJv6DhogNrwgXcBGqxZPOKUojJGB_NkvzSrhNpYOBHziN7Fxom3nkEVnx7v6JpCg0EHvWPF8cO-4qRyR_6WfjAG3m6R-_gH9rOCQ5V86lbCKuJL0oThw2ajEPfc1TbXFBAwRYI32Pulf-T3iek5JSRRTD5xNwY1YPixzpcRY9nNvZRQp2DdS6HsQqwkVPZkxYZg-uHmBn0hoO6Pj-z81Snw1-bAs5t9i_DOGESVzO_rhWrJBsg6KUHsV1NwWEk7MeofM"
              />
            </div>
            <div class="p-8">
              <div class="mb-4 flex items-start justify-between">
                <span
                  class="rounded-full bg-secondary/10 px-3 py-1 text-xs font-bold uppercase text-secondary"
                  >Culture &amp; Stay</span
                >
                <div class="flex items-center gap-1 text-tertiary">
                  <span
                    class="material-symbols-outlined text-sm"
                    style="font-variation-settings: 'FILL' 1"
                    >star</span
                  >
                  <span class="text-sm font-bold">4.9</span>
                </div>
              </div>
              <h3 class="mb-2 text-2xl font-bold text-orange-950">Marrakech Soul</h3>
              <p class="mb-6 text-sm text-outline">4 Days • Riads &amp; Souks</p>
              <div class="flex items-center justify-between">
                <p class="font-semibold text-on-surface">
                  From <span class="text-xl font-bold text-primary">$850</span>
                </p>
                <span
                  class="material-symbols-outlined text-outline transition-colors group-hover:text-primary"
                  >favorite</span
                >
              </div>
            </div>
          </div>

          <div
            class="group relative overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm transition-all duration-500 hover:shadow-xl"
          >
            <div class="h-80 overflow-hidden">
              <img
                alt=""
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuAUqjpyv-NeyfVLlkmf6URJ4HBvV5ZKnMyoAVHZfSQyIGi2fBvXxOwLBTkrABi-QWWYgeI0gTMEIdPituGTO9vx2ULCRwODn3wDhxoK8xWlW50ieNCIZRizDXXbJSeL0Y3p2DGC1PsOFHlMktCQVfgCP0G44tRVntcahS7F3HAFbCC_3VjrqVvGOPQSV_OTtC4X770HV-JBvEAoQO2xaXPkxpytT2LvZ8aknFBkwtYG32z8PkwXArh-mlKGuiRvYmppHSJs_ERaD3Q"
              />
            </div>
            <div class="p-8">
              <div class="mb-4 flex items-start justify-between">
                <span
                  class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold uppercase text-primary"
                  >Adventure</span
                >
                <div class="flex items-center gap-1 text-tertiary">
                  <span
                    class="material-symbols-outlined text-sm"
                    style="font-variation-settings: 'FILL' 1"
                    >star</span
                  >
                  <span class="text-sm font-bold">5.0</span>
                </div>
              </div>
              <h3 class="mb-2 text-2xl font-bold text-orange-950">Sahara Star Gazing</h3>
              <p class="mb-6 text-sm text-outline">3 Days • Luxury Desert Camp</p>
              <div class="flex items-center justify-between">
                <p class="font-semibold text-on-surface">
                  From <span class="text-xl font-bold text-primary">$1,200</span>
                </p>
                <span
                  class="material-symbols-outlined text-outline transition-colors group-hover:text-primary"
                  >favorite</span
                >
              </div>
            </div>
          </div>

          <div
            class="group relative overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm transition-all duration-500 hover:shadow-xl"
          >
            <div class="h-80 overflow-hidden">
              <img
                alt=""
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuA6yYb0SlUsf3td3GjBVGdE43FlQUjU7ECw-lKYfx8FN9ykEAii3YWVTdFLMR3yTt90UQz8U0LeA7zZTjfoDQCYSbcWcjSKGmnGi89P2IqjCmH6-8U1Zo1h4kqqFv920L3GXOYVjscq3YCrTZkPr8wePM96il-5NtyDZG9sPAgt8DMrHvYFFDkpShVl2vHzZ7A_L2ngkXZ_edH9PWrdbJmnxjMrvQjxebBuybFhgbSkYFcicrP6Hp1jmUwBd4VPPjE8B4Q93rUyshM"
              />
            </div>
            <div class="p-8">
              <div class="mb-4 flex items-start justify-between">
                <span
                  class="rounded-full bg-secondary/10 px-3 py-1 text-xs font-bold uppercase text-secondary"
                  >Mountain Trek</span
                >
                <div class="flex items-center gap-1 text-tertiary">
                  <span
                    class="material-symbols-outlined text-sm"
                    style="font-variation-settings: 'FILL' 1"
                    >star</span
                  >
                  <span class="text-sm font-bold">4.8</span>
                </div>
              </div>
              <h3 class="mb-2 text-2xl font-bold text-orange-950">Berber Trails</h3>
              <p class="mb-6 text-sm text-outline">5 Days • Village Trekking</p>
              <div class="flex items-center justify-between">
                <p class="font-semibold text-on-surface">
                  From <span class="text-xl font-bold text-primary">$950</span>
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
