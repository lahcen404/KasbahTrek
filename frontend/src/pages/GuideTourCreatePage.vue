<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getAuthToken } from '../api/client';
import { getCategories } from '../api/categories';
import { createGuideTour } from '../api/guide';
import type { Category } from '../types/categories';

const router = useRouter();

const loading = ref(false);
const categoriesLoading = ref(true);
const error = ref<string | null>(null);
const success = ref<string | null>(null);
const categories = ref<Category[]>([]);
const selectedImages = ref<File[]>([]);

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

const categorySelected = computed(() => form.value.category_id !== '');
const selectedImageNames = computed(() => selectedImages.value.map((file) => file.name));

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

async function submitForm(): Promise<void> {
  loading.value = true;
  error.value = null;
  success.value = null;

  try {
    const payload = {
      title: form.value.title.trim(),
      description: form.value.description.trim(),
      location: form.value.location.trim(),
      price: Number(form.value.price),
      difficulty: form.value.difficulty as 'EASY' | 'MEDIUM' | 'HARD',
      max_spots: Number(form.value.max_spots),
      duration_hours: form.value.duration_hours ? Number(form.value.duration_hours) : null,
      date: form.value.date || null,
      category_id: form.value.category_id ? Number(form.value.category_id) : null,
      images: selectedImages.value,
    };

    const response = await createGuideTour(payload);
    success.value = response.message ?? 'Tour created successfully.';

    form.value = {
      title: '',
      description: '',
      location: '',
      price: '',
      difficulty: 'MEDIUM',
      max_spots: '',
      duration_hours: '',
      date: '',
      category_id: '',
    };
    selectedImages.value = [];

    setTimeout(() => {
      void router.push({ name: 'guide-dashboard' });
    }, 1200);
  } catch (e) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
    error.value = err.response?.data?.message ?? 'Could not create tour.';
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  if (!hasValidToken()) {
    await router.push({ name: 'login', query: { redirect: '/guide/tours/create' } });
    return;
  }

  await loadCategories();
});

function onImagesChange(event: Event): void {
  const input = event.target as HTMLInputElement;
  selectedImages.value = Array.from(input.files ?? []);
}
</script>

<template>
  <div class="min-h-screen bg-surface font-body text-on-surface zellige-pattern">
    <main class="mx-auto max-w-6xl px-4 pb-16 pt-28 sm:px-6 lg:px-8">
      <section class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-3xl">
          <span class="inline-flex items-center gap-2 rounded-full bg-secondary/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-secondary">
            <span class="material-symbols-outlined text-sm">add_circle</span>
            Guide Workspace
          </span>
          <h1 class="mt-4 font-headline text-4xl font-bold tracking-tight text-primary md:text-5xl">
            Create a new tour
          </h1>
          <p class="mt-3 max-w-2xl text-base leading-relaxed text-on-surface-variant md:text-lg">
            Keep it simple. Add the main tour details now and upload images in the same form.
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

      <div class="grid gap-8 lg:grid-cols-[1.35fr_0.65fr]">
        <form
          class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-6 shadow-[0_18px_50px_rgba(30,42,47,0.06)] sm:p-8"
          @submit.prevent="submitForm"
        >
          <div class="mb-8 flex items-center justify-between gap-4 border-b border-outline-variant/10 pb-5">
            <div>
              <h2 class="font-headline text-2xl font-bold text-on-surface">Tour details</h2>
              <p class="mt-1 text-sm text-on-surface-variant">Fields marked by the backend are included here.</p>
            </div>
            <span class="rounded-full bg-primary/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-primary">
              Simple form
            </span>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            <label class="md:col-span-2">
              <span class="mb-2 block text-sm font-semibold text-on-surface">Title</span>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-4 focus:ring-primary/10"
                placeholder="Atlas Sunrise Trek"
              />
            </label>

            <label class="md:col-span-2">
              <span class="mb-2 block text-sm font-semibold text-on-surface">Description</span>
              <textarea
                v-model="form.description"
                rows="5"
                required
                class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-4 focus:ring-primary/10"
                placeholder="Describe the route, highlights, meeting point, what is included..."
              />
            </label>

            <label>
              <span class="mb-2 block text-sm font-semibold text-on-surface">Location</span>
              <input
                v-model="form.location"
                type="text"
                required
                class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-4 focus:ring-primary/10"
                placeholder="Marrakech, Morocco"
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
                class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-4 focus:ring-primary/10"
                placeholder="1500"
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
                class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-4 focus:ring-primary/10"
                placeholder="12"
              />
            </label>

            <label>
              <span class="mb-2 block text-sm font-semibold text-on-surface">Duration hours</span>
              <input
                v-model="form.duration_hours"
                type="number"
                min="1"
                step="1"
                class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-4 focus:ring-primary/10"
                placeholder="24"
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
              <span class="mb-2 block text-sm font-semibold text-on-surface">Images</span>
              <input
                type="file"
                multiple
                accept="image/*"
                class="w-full rounded-2xl border border-dashed border-outline-variant/30 bg-surface px-4 py-3 outline-none transition-all file:mr-4 file:rounded-full file:border-0 file:bg-primary file:px-4 file:py-2 file:font-semibold file:text-on-primary hover:border-primary focus:border-primary focus:ring-4 focus:ring-primary/10"
                @change="onImagesChange"
              />
              <p class="mt-2 text-xs text-on-surface-variant">
                PNG, JPG, JPEG, or WEBP. Up to 10 images.
              </p>
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

            <label class="md:col-span-2">
              <span class="mb-2 block text-sm font-semibold text-on-surface">Category</span>
              <select
                v-model="form.category_id"
                class="w-full rounded-2xl border border-outline-variant/20 bg-surface px-4 py-3 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10"
                :disabled="categoriesLoading"
              >
                <option value="">Select a category</option>
                <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                  {{ category.name }}
                </option>
              </select>
              <p class="mt-2 text-xs text-on-surface-variant">
                {{ categorySelected ? 'Category selected.' : 'Optional, but recommended.' }}
              </p>
            </label>
          </div>

          <div class="mt-8 flex flex-col gap-3 border-t border-outline-variant/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
              You can add tour photos now or leave them empty and add later.
            </p>
            <button
              type="submit"
              :disabled="loading"
              class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-7 py-3 font-bold text-on-primary shadow-lg transition-all hover:brightness-110 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
            >
              <span class="material-symbols-outlined">{{ loading ? 'hourglass_top' : 'add' }}</span>
              {{ loading ? 'Saving...' : 'Create tour' }}
            </button>
          </div>
        </form>

        <aside class="space-y-6">
          <div class="rounded-[2rem] bg-secondary p-7 text-white shadow-[0_18px_50px_rgba(241,106,46,0.22)]">
            <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-widest">
              Best practice
            </span>
            <h3 class="mt-4 font-headline text-2xl font-bold">Keep the title clear</h3>
            <p class="mt-3 text-sm leading-relaxed text-white/85">
              Use a short, descriptive name. Travelers understand the tour faster and your dashboard stays clean.
            </p>
          </div>

          <div class="rounded-[2rem] border border-outline-variant/10 bg-surface-container-lowest p-7 shadow-sm">
            <h3 class="font-headline text-xl font-bold text-on-surface">What this form sends</h3>
            <ul class="mt-4 space-y-3 text-sm text-on-surface-variant">
              <li>• title</li>
              <li>• description</li>
              <li>• location</li>
              <li>• price</li>
              <li>• difficulty</li>
              <li>• max spots</li>
              <li>• duration hours</li>
              <li>• date</li>
              <li>• images</li>
              <li>• category</li>
            </ul>
          </div>
        </aside>
      </div>
    </main>
  </div>
</template>