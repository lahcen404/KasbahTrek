<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
  modelValue: number; // Current rating (1-5)
  readonly?: boolean; // Display only, no interaction
  size?: 'sm' | 'md' | 'lg'; // Size variants
  interactive?: boolean; // Allow hover effect
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 0,
  readonly: false,
  size: 'md',
  interactive: true,
});

const emit = defineEmits<{
  'update:modelValue': [value: number];
}>();

const hoveredRating = ref<number | null>(null);

const displayRating = computed(() => {
  return hoveredRating.value !== null ? hoveredRating.value : props.modelValue;
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-4 h-4';
    case 'lg':
      return 'w-8 h-8';
    default:
      return 'w-6 h-6';
  }
});

function handleStarClick(rating: number): void {
  if (!props.readonly) {
    emit('update:modelValue', rating);
  }
}

function handleStarHover(rating: number): void {
  if (props.interactive && !props.readonly) {
    hoveredRating.value = rating;
  }
}

function handleMouseLeave(): void {
  hoveredRating.value = null;
}
</script>

<template>
  <div
    class="inline-flex items-center gap-1"
    @mouseleave="handleMouseLeave"
    :class="{ 'cursor-pointer': !readonly && interactive }"
  >
    <button
      v-for="rating in 5"
      :key="rating"
      type="button"
      :disabled="readonly"
      class="relative transition-all duration-200"
      :class="[
        sizeClasses,
        {
          'hover:scale-110': !readonly && interactive,
          'cursor-default': readonly,
          'cursor-pointer': !readonly,
        },
      ]"
      @click="handleStarClick(rating)"
      @mouseenter="handleStarHover(rating)"
      :aria-label="`Rate ${rating} stars`"
    >
      <!-- Background star (empty) -->
      <svg
        viewBox="0 0 24 24"
        class="absolute inset-0 h-full w-full text-slate-300 transition-colors duration-200"
        :class="{ 'text-slate-200': rating <= displayRating }"
      >
        <path
          d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
          fill="currentColor"
        />
      </svg>

      <!-- Filled star (active) -->
      <svg
        viewBox="0 0 24 24"
        class="absolute inset-0 h-full w-full text-tertiary transition-all duration-200"
        :class="{
          'opacity-0': rating > displayRating,
          'opacity-100': rating <= displayRating,
        }"
      >
        <path
          d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
          fill="currentColor"
        />
      </svg>

      <!-- Hover glow effect -->
      <div
        v-if="!readonly && interactive && rating <= displayRating"
        class="absolute inset-0 rounded-full bg-tertiary/20 animate-pulse"
      />
    </button>

    <!-- Rating text display -->
    <span
      v-if="modelValue > 0"
      class="ml-2 text-sm font-semibold text-tertiary transition-all duration-200"
    >
      {{ displayRating }}.0
    </span>
  </div>
</template>

<style scoped>
@keyframes star-bounce {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* Optional: add bounce animation on hover */
button:hover svg {
  animation: star-bounce 0.3s ease-out;
}
</style>
