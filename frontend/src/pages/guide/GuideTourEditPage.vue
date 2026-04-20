<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getAuthToken } from '../../api/client';
import { getCategories } from '../../api/categories';
import {
  getGuideTours,
  updateGuideTour,
  uploadGuideTourImages,
} from '../../api/guide';
import { tourImageUrl } from '../../api/tours';
import type { Category } from '../../types/categories';
import type { GuideTour, UpdateGuideTourPayload } from '../../types/guide';

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const loadingTour = ref(true);
const categoriesLoading = ref(true);
const error = ref<string | null>(null);
const success = ref<string | null>(null);
const categories = ref<Category[]>([]);
const selectedImages = ref<File[]>([]);
const removedImageIds = ref<number[]>([]);
const existingImages = ref<Array<{ id: number; src: string }>>([]);

const form = ref({
  title: '',
  description: '',
  location: '',
  price: '',
  difficulty: 'MEDIUM',
  max_spots: '',
  duration_hours: '',
  date: '',
  category_id: '',
});

const difficultyOptions = [
  { label: 'Easy', value: 'EASY' },
  { label: 'Medium', value: 'MEDIUM' },
  { label: 'Hard', value: 'HARD' },
] as const;

const tourId = computed(() => Number(route.params.id));
const selectedImageNames = computed(() => selectedImages.value.map((file) => file.name));
const existingImagesList = computed(() => existingImages.value);

function hasValidToken(): boolean {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
}

async function loadCategories(): Promise<void> {
  categoriesLoading.value = true;
  try {
    categories.value = await getCategories();
  } catch {
    error.value = 'Could not load categories.';
    categories.value = [];
  } finally {
    categoriesLoading.value = false;
  }
}

function applyTourToForm(tour: GuideTour): void {
  form.value.title = tour.title ?? '';
  form.value.description = tour.description ?? '';
  form.value.location = tour.location ?? '';
  form.value.price = typeof tour.price === 'number' ? String(tour.price) : '';
  form.value.difficulty = String(tour.difficulty ?? 'MEDIUM').toUpperCase();
  form.value.max_spots = typeof tour.max_spots === 'number' ? String(tour.max_spots) : '';
  form.value.duration_hours =
    typeof tour.duration_hours === 'number' ? String(tour.duration_hours) : '';
  form.value.date = typeof tour.date === 'string' ? String(tour.date).slice(0, 10) : '';
  form.value.category_id = typeof tour.category_id === 'number' ? String(tour.category_id) : '';

  existingImages.value = (tour.images ?? [])
    .map((img) => {
      const src = tourImageUrl(img.path);
      if (!src) return null;
      return { id: img.id, src };
    })
    .filter((img): img is { id: number; src: string } => Boolean(img));
  removedImageIds.value = [];
}

async function loadTour(): Promise<void> {
  loadingTour.value = true;
  error.value = null;
  selectedImages.value = [];
  removedImageIds.value = [];
  existingImages.value = [];

  try {
    const tours = await getGuideTours();
    const tour = tours.find((item) => item.id === tourId.value);

    if (!tour) {
      error.value = 'Tour not found or you are not allowed to edit it.';
      return;
    }

    applyTourToForm(tour);
  } catch {
    error.value = 'Could not load tour details.';
  } finally {
    loadingTour.value = false;
  }
}

async function submitForm(): Promise<void> {
  if (!Number.isFinite(tourId.value) || tourId.value <= 0) {
    error.value = 'Invalid tour id.';
    return;
  }

  loading.value = true;
  error.value = null;
  success.value = null;

  try {
    const payload: UpdateGuideTourPayload = {
      title: form.value.title.trim(),
      description: form.value.description.trim(),
      location: form.value.location.trim(),
      price: Number(form.value.price),
      difficulty: form.value.difficulty as 'EASY' | 'MEDIUM' | 'HARD',
      max_spots: Number(form.value.max_spots),
    };

    if (form.value.duration_hours !== '') {
      payload.duration_hours = Number(form.value.duration_hours);
    }

    if (form.value.date !== '') {
      payload.date = form.value.date;
    }

    if (form.value.category_id !== '') {
      payload.category_id = Number(form.value.category_id);
    }

    const removeIds = [...removedImageIds.value];

    if (removeIds.length > 0) {
      payload.remove_image_ids = removeIds;
    }

    const response = await updateGuideTour(tourId.value, payload);

    if (selectedImages.value.length > 0) {
      await uploadGuideTourImages(tourId.value, selectedImages.value);
    }

    success.value = response.message ?? 'Tour updated successfully.';

    setTimeout(() => {
      void router.push({ name: 'guide-dashboard' });
    }, 1200);
  } catch (e) {
    const err = e as {
      response?: { data?: { message?: string; errors?: Record<string, string[] | string> } };
    };

    const apiErrors = err.response?.data?.errors;
    if (apiErrors && typeof apiErrors === 'object') {
      const firstError = Object.values(apiErrors)
        .flatMap((value) => (Array.isArray(value) ? value : [value]))
        .find(Boolean);
      error.value = firstError ?? err.response?.data?.message ?? 'Could not update tour.';
    } else {
      error.value = err.response?.data?.message ?? 'Could not update tour.';
    }
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  if (!hasValidToken()) {
    await router.push({ name: 'login', query: { redirect: route.fullPath } });
    return;
  }

  await Promise.all([loadCategories(), loadTour()]);
});

watch(
  () => route.params.id,
  () => {
    void loadTour();
  },
);

function onImagesChange(event: Event): void {
  const input = event.target as HTMLInputElement;
  selectedImages.value = Array.from(input.files ?? []);
}

function removeExistingImage(imageId: number): void {
  if (removedImageIds.value.includes(imageId)) {
    return;
  }

  removedImageIds.value = [...removedImageIds.value, imageId];
  existingImages.value = existingImages.value.filter((image) => image.id !== imageId);
  success.value = 'Image will be removed after you click Update tour.';
}

</script>

<template>
  <div class="min-h-screen bg-surface font-body text-on-surface zellige-pattern">
    <main class="mx-auto max-w-6xl px-4 pb-16 pt-28 sm:px-6 lg:px-8">
      <section class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-3xl">
          <span class="inline-flex items-center gap-2 rounded-full bg-secondary/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-secondary">
            <span class="material-symbols-outlined text-sm">edit</span>
            Guide Workspace
          </span>
          <h1 class="mt-4 font-headline text-4xl font-bold tracking-tight text-primary md:text-5xl">
            Update tour
          </h1>
          <p class="mt-3 max-w-2xl text-base leading-relaxed text-on-surface-variant md:text-lg">
            Edit the details of your tour and optionally upload extra images.
          </p>
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

      <form
        class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-6 shadow-[0_18px_50px_rgba(30,42,47,0.06)] sm:p-8"
        @submit.prevent="submitForm"
      >
        <div class="mb-8 flex items-center justify-between gap-4 border-b border-outline-variant/10 pb-5">
          <div>
            <h2 class="font-headline text-2xl font-bold text-on-surface">Tour details</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Update details and save changes.</p>
          </div>
          <span class="rounded-full bg-primary/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-primary">
            Edit form
          </span>
        </div>

        <div v-if="loadingTour" class="mb-6 rounded-2xl bg-surface-container-high/40 p-4 text-sm text-on-surface-variant">
          Loading tour...
        </div>

        <div class="grid gap-5 md:grid-cols-2">
          <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-on-surface">Title</span>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
          </label>

          <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-on-surface">Description</span>
            <textarea
              v-model="form.description"
              rows="5"
              required
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
          </label>

          <label>
            <span class="mb-2 block text-sm font-semibold text-on-surface">Location</span>
            <input
              v-model="form.location"
              type="text"
              required
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
          </label>

          <label>
            <span class="mb-2 block text-sm font-semibold text-on-surface">Price</span>
            <input
              v-model="form.price"
              type="number"
              min="0"
              step="0.01"
              required
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
          </label>

          <label>
            <span class="mb-2 block text-sm font-semibold text-on-surface">Difficulty</span>
            <select
              v-model="form.difficulty"
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            >
              <option v-for="option in difficultyOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </label>

          <label>
            <span class="mb-2 block text-sm font-semibold text-on-surface">Max spots</span>
            <input
              v-model="form.max_spots"
              type="number"
              min="1"
              step="1"
              required
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
          </label>

          <label>
            <span class="mb-2 block text-sm font-semibold text-on-surface">Duration hours</span>
            <input
              v-model="form.duration_hours"
              type="number"
              min="1"
              step="1"
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
          </label>

          <label>
            <span class="mb-2 block text-sm font-semibold text-on-surface">Date</span>
            <input
              v-model="form.date"
              type="date"
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
          </label>

          <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-on-surface">Category</span>
            <select
              v-model="form.category_id"
              :disabled="categoriesLoading"
              class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
            >
              <option value="">Select a category</option>
              <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                {{ category.name }}
              </option>
            </select>
          </label>

          <label class="md:col-span-2">
            <span class="mb-2 block text-sm font-semibold text-on-surface">Add more images</span>
            <input
              type="file"
              multiple
              accept="image/*"
              class="w-full rounded-2xl border border-dashed border-outline-variant/30 bg-surface px-4 py-3 outline-none transition-all file:mr-4 file:rounded-full file:border-0 file:bg-primary file:px-4 file:py-2 file:font-semibold file:text-on-primary hover:border-primary focus:border-primary focus:ring-4 focus:ring-primary/10"
              @change="onImagesChange"
            />
            <p class="mt-2 text-xs text-on-surface-variant">Selected images are uploaded after details are saved.</p>

            <div v-if="selectedImageNames.length" class="mt-3 flex flex-wrap gap-2">
              <span
                v-for="name in selectedImageNames"
                :key="name"
                class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary"
              >
                {{ name }}
              </span>
            </div>
          </label>

          <div v-if="existingImagesList.length" class="md:col-span-2">
            <p class="mb-2 text-sm font-semibold text-on-surface">Current images</p>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
              <div
                v-for="existingImage in existingImagesList"
                :key="existingImage.id"
                class="relative overflow-hidden rounded-xl"
              >
                <img
                  :src="existingImage.src"
                  alt="Tour image"
                  class="h-24 w-full rounded-xl object-cover"
                />
                <button
                  type="button"
                  class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/80 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="loading"
                  title="Remove image"
                  @click="removeExistingImage(existingImage.id)"
                >
                  <span class="material-symbols-outlined text-base">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 border-t border-outline-variant/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
          <p class="text-sm text-on-surface-variant">Save your changes when you are ready.</p>
          <button
            type="submit"
            :disabled="loading || loadingTour"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-7 py-3 font-bold text-on-primary shadow-lg transition-all hover:brightness-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <span class="material-symbols-outlined">{{ loading ? 'hourglass_top' : 'save' }}</span>
            {{ loading ? 'Saving...' : 'Update tour' }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>
