<script setup lang="ts">
import { useRouter } from 'vue-router'

const router = useRouter()

const goHome = () => {
  router.push('/')
}

const browseTours = () => {
  router.push('/tours')
}
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface flex items-center justify-center px-4 py-12 overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="pointer-events-none absolute -right-40 -top-40 h-96 w-96 rounded-full bg-primary/5 blur-3xl animate-blob" />
      <div class="pointer-events-none absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-tertiary/5 blur-3xl animate-blob animation-delay-2000" />
    </div>

    <!-- Main content -->
    <div class="relative z-10 text-center max-w-2xl mx-auto">
      <!-- Header with logo hint -->
      <div class="mb-12">
        <span class="inline-block text-xs font-semibold uppercase tracking-[0.24em] text-primary mb-6">
          Page Not Found
        </span>
      </div>

      <!-- Animated 404 -->
      <div class="mb-8">
        <div class="text-9xl md:text-10xl font-black font-headline text-orange-900 dark:text-orange-500 animate-pulse">
          404
        </div>
        <div class="text-6xl animate-bounce mt-2">🧭</div>
      </div>

      <!-- Heading -->
      <h1 class="text-4xl md:text-5xl font-headline font-bold text-on-surface mb-4 leading-tight">
        Lost in the <span class="text-primary">Kasbahs</span>?
      </h1>

      <!-- Description -->
      <p class="text-lg md:text-xl text-on-surface-variant mb-6 leading-relaxed">
        We couldn't find the page you're looking for. It seems you've wandered off our mapped paths!
      </p>

      <!-- Subtext -->
      <p class="text-sm md:text-base text-on-surface-variant mb-10 max-w-md mx-auto">
        Let's get you back on track to discover amazing treks and unforgettable adventures across Morocco.
      </p>

      <!-- CTA Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-10">
        <button
          type="button"
          @click="goHome"
          class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary text-surface font-semibold rounded-xl hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 transform hover:scale-105"
        >
          <span class="material-symbols-outlined">home</span>
          Back to Home
        </button>
        <button
          type="button"
          @click="browseTours"
          class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/10 transition-all duration-300 transform hover:scale-105"
        >
          <span class="material-symbols-outlined">travel_explore</span>
          Browse Tours
        </button>
      </div>

      <!-- Footer link -->
      <div class="text-on-surface-variant text-sm">
        Need assistance?
        <router-link
          to="/"
          class="text-primary hover:text-orange-700 dark:hover:text-orange-400 underline underline-offset-2 transition-colors font-semibold"
        >
          Contact Support
        </router-link>
      </div>

      <!-- Decorative element -->
      <div class="mt-12 p-6 rounded-2xl border border-primary/20 bg-surface-container-low">
        <p class="text-on-surface-variant italic text-sm md:text-base">
          "Some of the best discoveries come from losing your way. But let's find the right path for you." 🐪
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes blob {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
