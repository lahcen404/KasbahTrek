<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
  modelValue: boolean;
  tourTitle?: string;
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  tourTitle: 'this tour',
  isLoading: false,
});

const emit = defineEmits<{
  'update:modelValue': [open: boolean];
  submit: [payload: { reason: string }];
}>();

const reason = ref('');
const error = ref<string | null>(null);

const canSubmit = computed(() => reason.value.trim().length >= 10);

function closeModal(): void {
  if (props.isLoading) return;
  emit('update:modelValue', false);
}

function onBackdropClick(event: MouseEvent): void {
  if (event.target === event.currentTarget) {
    closeModal();
  }
}

function onSubmit(): void {
  error.value = null;
  if (!canSubmit.value) {
    error.value = 'Please provide at least 10 characters.';
    return;
  }

  emit('submit', { reason: reason.value.trim() });
}
</script>

<template>
  <Transition name="backdrop">
    <div
      v-show="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 px-4 backdrop-blur-sm"
      @click="onBackdropClick"
    >
      <Transition name="modal">
        <div
          v-show="modelValue"
          class="relative w-full max-w-lg rounded-3xl border border-outline-variant/20 bg-surface-container-low p-7 shadow-2xl"
        >
          <button
            type="button"
            class="absolute right-5 top-5 inline-flex h-9 w-9 items-center justify-center rounded-full bg-surface-container-high text-on-surface-variant transition hover:bg-error/15 hover:text-error disabled:opacity-50"
            :disabled="isLoading"
            @click="closeModal"
          >
            <span class="material-symbols-outlined text-lg">close</span>
          </button>

          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-error">Safety Report</p>
            <h2 class="mt-2 text-2xl font-headline font-bold text-on-surface">Report Tour</h2>
            <p class="mt-2 text-sm text-on-surface-variant">
              Tell us what happened with <strong>{{ tourTitle }}</strong>. Our team will review it.
            </p>
          </div>

          <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
            <textarea
              v-model="reason"
              rows="5"
              maxlength="1000"
              placeholder="Describe the issue (minimum 10 characters)..."
              class="w-full rounded-2xl border border-outline-variant/30 bg-surface px-4 py-3 text-sm text-on-surface outline-none transition focus:border-error"
              :disabled="isLoading"
            />

            <div class="flex items-center justify-between text-xs text-on-surface-variant">
              <span>Minimum 10 characters</span>
              <span>{{ reason.length }}/1000</span>
            </div>

            <div v-if="error" class="rounded-xl bg-error-container/70 px-4 py-3 text-sm font-medium text-error">
              {{ error }}
            </div>

            <div class="flex gap-3 pt-1">
              <button
                type="button"
                class="flex-1 rounded-full px-5 py-3 text-sm font-semibold text-on-surface-variant transition hover:bg-surface-container-high disabled:opacity-50"
                :disabled="isLoading"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="flex-1 rounded-full bg-error px-5 py-3 text-sm font-semibold text-on-error transition hover:brightness-110 disabled:opacity-50"
                :disabled="!canSubmit || isLoading"
              >
                {{ isLoading ? 'Submitting...' : 'Submit Report' }}
              </button>
            </div>
          </form>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<style scoped>
.backdrop-enter-active,
.backdrop-leave-active {
  transition: opacity 220ms ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
  opacity: 0;
}

.modal-enter-active {
  transition: all 320ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-leave-active {
  transition: all 220ms ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: translateY(18px) scale(0.97);
}
</style>
