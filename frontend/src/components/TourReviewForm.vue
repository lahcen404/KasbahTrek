<script setup lang="ts">
import { computed, ref } from 'vue';
import StarRating from './common/StarRating.vue';

interface Props {
  modelValue: boolean; // Modal open/close
  tourTitle?: string;
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  tourTitle: 'Tour',
  isLoading: false,
});

const emit = defineEmits<{
  'update:modelValue': [open: boolean];
  'submit': [payload: { rating: number; comment: string }];
}>();

const rating = ref<number>(0);
const comment = ref<string>('');
const error = ref<string | null>(null);

const isValid = computed(() => rating.value > 0);

function handleSubmit(): void {
  error.value = null;

  if (!isValid.value) {
    error.value = 'Please select a star rating';
    return;
  }

  if (comment.value.trim().length === 0) {
    error.value = 'Please write a comment';
    return;
  }

  emit('submit', {
    rating: rating.value,
    comment: comment.value.trim(),
  });
}

function handleClose(): void {
  if (!props.isLoading) {
    emit('update:modelValue', false);
  }
}

function handleModalClick(e: MouseEvent): void {
  // Only close if user clicks the backdrop (outside the modal)
  if (e.target === e.currentTarget) {
    handleClose();
  }
}

function resetForm(): void {
  rating.value = 0;
  comment.value = '';
  error.value = null;
}

// Watch modelValue to reset form when modal opens
const openModel = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit('update:modelValue', value);
    if (!value) {
      resetForm();
    }
  },
});
</script>

<template>
  <!-- Backdrop with fade animation -->
  <Transition name="backdrop">
    <div
      v-show="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
      @click="handleModalClick"
    />
  </Transition>

  <!-- Modal with slide & scale animation -->
  <Transition name="modal">
    <div
      v-show="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center px-4 pointer-events-none"
    >
      <div
        class="relative w-full max-w-md rounded-3xl border border-outline-variant/20 bg-surface-container-low p-8 shadow-2xl pointer-events-auto"
      >
        <!-- Close button -->
        <button
          type="button"
          class="absolute right-6 top-6 inline-flex h-9 w-9 items-center justify-center rounded-full bg-surface-container-high text-slate-600 transition-all hover:bg-error/20 hover:text-error disabled:opacity-50"
          :disabled="isLoading"
          @click="handleClose"
        >
          <span class="material-symbols-outlined text-xl">close</span>
        </button>

        <!-- Header -->
        <div class="mb-6">
          <h2 class="text-2xl font-headline font-bold text-on-surface">Share Your Experience</h2>
          <p class="mt-1 text-sm text-on-surface-variant">
            Help other travelers by reviewing <strong>{{ tourTitle }}</strong>
          </p>
        </div>

        <!-- Form content -->
        <form class="space-y-6" @submit.prevent="handleSubmit">
          <!-- Star rating section -->
          <div>
            <label class="mb-3 block text-sm font-semibold text-on-surface">Your Rating</label>
            <div class="flex justify-center py-4">
              <StarRating v-model="rating" size="lg" interactive />
            </div>
            <p class="text-center text-xs text-on-surface-variant">
              {{ rating === 0 ? 'Click to rate (1-5 stars)' : '' }}
            </p>
          </div>

          <!-- Comment section -->
          <div>
            <label for="comment" class="mb-2 block text-sm font-semibold text-on-surface"
              >Your Review</label
            >
            <textarea
              id="comment"
              v-model="comment"
              placeholder="Share what you loved about this tour..."
              class="h-24 w-full resize-none rounded-2xl border border-outline-variant/30 bg-surface px-4 py-3 text-sm text-on-surface outline-none transition placeholder:text-on-surface-variant focus:border-primary focus:ring-2 focus:ring-primary/20 disabled:opacity-50"
              :disabled="isLoading"
              maxlength="1000"
            />
            <div class="mt-1 flex justify-between text-xs text-on-surface-variant">
              <span />
              <span>{{ comment.length }}/1000</span>
            </div>
          </div>

          <!-- Error message -->
          <Transition name="error">
            <div
              v-if="error"
              class="rounded-xl bg-error-container/70 px-4 py-3 text-sm font-medium text-error"
            >
              {{ error }}
            </div>
          </Transition>

          <!-- Action buttons -->
          <div class="flex gap-3 pt-2">
            <button
              type="button"
              class="flex-1 rounded-full px-6 py-3 text-sm font-semibold text-on-surface-variant transition hover:bg-surface-container-high disabled:opacity-50"
              :disabled="isLoading"
              @click="handleClose"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-on-primary transition hover:brightness-110 disabled:opacity-50"
              :disabled="!isValid || isLoading"
            >
              <span v-if="!isLoading">Submit Review</span>
              <span v-else class="inline-flex items-center gap-2">
                <span class="material-symbols-outlined animate-spin text-lg">hourglass_top</span>
                Submitting...
              </span>
            </button>
          </div>
        </form>

        <!-- Decorative elements -->
        <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-primary/10 blur-2xl" />
        <div class="pointer-events-none absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-tertiary/10 blur-2xl" />
      </div>
    </div>
  </Transition>
</template>

<style scoped>
/* Backdrop transitions */
.backdrop-enter-active,
.backdrop-leave-active {
  transition: opacity 300ms ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
  opacity: 0;
}

/* Modal transitions */
.modal-enter-active {
  transition: all 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-leave-active {
  transition: all 300ms ease;
}

.modal-enter-from {
  opacity: 0;
  transform: scale(0.95) translateY(20px);
}

.modal-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}

/* Error message animation */
.error-enter-active,
.error-leave-active {
  transition: all 200ms ease;
}

.error-enter-from,
.error-leave-to {
  opacity: 0;
  max-height: 0;
}

.error-enter-to {
  opacity: 1;
  max-height: 100px;
}
</style>
