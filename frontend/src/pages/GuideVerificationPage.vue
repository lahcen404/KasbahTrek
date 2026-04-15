<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getCurrentUser } from '../api/auth';
import { getAuthToken } from '../api/client';
import { submitGuideVerification } from '../api/guide';

type VerificationState = 'loading' | 'verified' | 'pending' | 'available';

const router = useRouter();

const loading = ref(false);
const profileLoading = ref(true);
const error = ref<string | null>(null);
const success = ref<string | null>(null);
const selectedDocument = ref<File | null>(null);
const verificationState = ref<VerificationState>('loading');

const selectedDocumentName = computed(() => selectedDocument.value?.name ?? 'No file selected');
const canSubmit = computed(() => verificationState.value === 'available');
const statusLabel = computed(() => {
  if (verificationState.value === 'verified') return 'You are already a verified guide';
  if (verificationState.value === 'pending') return 'You already submitted a verification request';
  if (verificationState.value === 'available') return 'You can submit one verification request';
  return 'Checking your verification status...';
});

function hasValidToken(): boolean {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
}

function onDocumentChange(event: Event): void {
  const input = event.target as HTMLInputElement;
  const files = Array.from(input.files ?? []);
  selectedDocument.value = files[0] ?? null;
}

async function submitRequest(): Promise<void> {
  error.value = null;
  success.value = null;

  if (!canSubmit.value) {
    error.value = verificationState.value === 'verified'
      ? 'Your guide account is already verified.'
      : 'You have already submitted a verification request.';
    return;
  }

  if (!selectedDocument.value) {
    error.value = 'Please choose a document before submitting.';
    return;
  }

  loading.value = true;
  try {
    const response = await submitGuideVerification(selectedDocument.value);
    success.value = response.message ?? 'Verification request submitted successfully.';
    selectedDocument.value = null;
    verificationState.value = 'pending';
  } catch (e) {
    const err = e as {
      response?: {
        data?: { message?: string; errors?: Record<string, string[]> };
      };
    };

    const errors = err.response?.data?.errors;
    if (errors && Object.keys(errors).length > 0) {
      const firstField = Object.keys(errors)[0];
      const firstMessage = errors[firstField]?.[0];
      error.value = firstMessage ?? 'The selected file is not valid.';
    } else {
      error.value = err.response?.data?.message ?? 'Could not submit verification request.';
    }
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  if (!hasValidToken()) {
    await router.push({ name: 'login', query: { redirect: '/guide/verification' } });
    return;
  }

  profileLoading.value = true;
  try {
    const me = await getCurrentUser();
    if (me.is_verified) {
      verificationState.value = 'verified';
    } else if (me.verification_request) {
      verificationState.value = 'pending';
    } else {
      verificationState.value = 'available';
    }
  } catch {
    verificationState.value = 'available';
  } finally {
    profileLoading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-surface font-body text-on-surface zellige-pattern">
    <main class="mx-auto max-w-6xl px-4 pb-16 pt-28 sm:px-6 lg:px-8">
      <section class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-3xl">
          <span class="inline-flex items-center gap-2 rounded-full bg-secondary/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-secondary">
            <span class="material-symbols-outlined text-sm">verified_user</span>
            Guide Workspace
          </span>
          <h1 class="mt-4 font-headline text-4xl font-bold tracking-tight text-primary md:text-5xl">
            Request verification badge
          </h1>
          <p class="mt-3 max-w-2xl text-base leading-relaxed text-on-surface-variant md:text-lg">
            Upload one clear proof document so the admin team can review your guide profile and activate your badge.
          </p>
          <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
            <span class="material-symbols-outlined text-sm">verified_user</span>
            {{ profileLoading ? 'Checking status...' : statusLabel }}
          </div>
        </div>

        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-full border border-outline-variant/20 bg-surface-container-low px-5 py-3 font-bold text-slate-700 shadow-sm transition-all hover:bg-surface-container-high active:scale-95"
          @click="router.push({ name: 'guide-dashboard' })"
        >
          <span class="material-symbols-outlined">arrow_back</span>
          Back to dashboard
        </button>
      </section>

      <div v-if="error" class="mb-6 rounded-2xl border border-error/20 bg-error-container/70 px-5 py-4 text-sm font-medium text-error">
        {{ error }}
      </div>

      <div v-if="success" class="mb-6 rounded-2xl border border-secondary/20 bg-secondary-container/70 px-5 py-4 text-sm font-medium text-secondary">
        {{ success }}
      </div>

      <div class="grid gap-8 lg:grid-cols-[1.35fr_0.65fr]">
        <form
          class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-6 shadow-[0_18px_50px_rgba(30,42,47,0.06)] sm:p-8"
          @submit.prevent="submitRequest"
        >
          <div class="mb-8 flex items-center justify-between gap-4 border-b border-outline-variant/10 pb-5">
            <div>
              <h2 class="font-headline text-2xl font-bold text-on-surface">Verification document</h2>
              <p class="mt-1 text-sm text-on-surface-variant">Accepted formats: PDF, JPG, JPEG, PNG. Max size: 5MB.</p>
            </div>
            <span class="rounded-full bg-primary/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-primary">
              One upload
            </span>
          </div>

          <label class="block" :class="!canSubmit ? 'pointer-events-none opacity-60' : ''">
            <span class="mb-2 block text-sm font-semibold text-on-surface">Document</span>
            <input
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              class="w-full rounded-2xl border border-dashed border-outline-variant/30 bg-surface px-4 py-3 outline-none transition-all file:mr-4 file:rounded-full file:border-0 file:bg-primary file:px-4 file:py-2 file:font-semibold file:text-on-primary hover:border-primary focus:border-primary focus:ring-4 focus:ring-primary/10"
              @change="onDocumentChange"
            />
          </label>

          <div class="mt-4 rounded-2xl bg-surface-container-low px-4 py-3 text-sm text-on-surface-variant">
            {{ selectedDocumentName }}
          </div>

          <div class="mt-8 flex flex-col gap-3 border-t border-outline-variant/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
              {{ canSubmit ? 'After submission, your request appears in the admin verification queue.' : 'This verification request can only be submitted once.' }}
            </p>
            <button
              type="submit"
              :disabled="loading || profileLoading || !canSubmit"
              class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-7 py-3 font-bold text-on-primary shadow-lg transition-all hover:brightness-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
            >
              <span class="material-symbols-outlined">{{ loading ? 'hourglass_top' : 'verified' }}</span>
              {{ loading ? 'Submitting...' : canSubmit ? 'Submit request' : 'Already submitted' }}
            </button>
          </div>
        </form>

        <aside class="space-y-6">
          <div class="rounded-[2rem] bg-secondary p-7 text-white shadow-[0_18px_50px_rgba(241,106,46,0.22)]">
            <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-widest">
              Pro tip
            </span>
            <h3 class="mt-4 font-headline text-2xl font-bold">Use a clear document</h3>
            <p class="mt-3 text-sm leading-relaxed text-white/85">
              A readable file gets approved faster. Crop extra margins and make sure all important details are visible.
            </p>
          </div>

          <div class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-7 shadow-sm">
            <h3 class="font-headline text-xl font-bold text-on-surface">What happens next</h3>
            <ul class="mt-4 space-y-3 text-sm text-on-surface-variant">
              <li>• Admin reviews your uploaded document</li>
              <li>• Request status is updated to approved/rejected</li>
              <li>• You receive an email notification</li>
              <li>• Approved guides get verification badge</li>
            </ul>
          </div>
        </aside>
      </div>
    </main>
  </div>
</template>