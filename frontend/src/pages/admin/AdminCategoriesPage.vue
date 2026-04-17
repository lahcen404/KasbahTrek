<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  createAdminCategory,
  deleteAdminCategory,
  getAdminCategories,
  updateAdminCategory,
} from '../../api/admin';
import type { AdminCategory } from '../../types/admin';


const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const feedback = ref<string | null>(null);
const categories = ref<AdminCategory[]>([]);
const query = ref('');

const saving = ref(false);
const deletingId = ref<number | null>(null);

const createName = ref('');
const createDescription = ref('');

const editingCategory = ref<AdminCategory | null>(null);
const editName = ref('');
const editDescription = ref('');

const filteredCategories = computed(() => {
  const q = query.value.trim().toLowerCase();
  return categories.value.filter((item) => {
    if (!q) return true;
    const name = (item.name ?? '').toLowerCase();
    const description = (item.description ?? '').toLowerCase();
    return name.includes(q) || description.includes(q) || String(item.id).includes(q);
  });
});

function formatDate(value?: string): string {
  if (!value) return 'Unknown';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date);
}

async function loadCategories(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    categories.value = await getAdminCategories();
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not load categories right now.';
    categories.value = [];
  } finally {
    loading.value = false;
  }
}

async function handleCreateCategory(): Promise<void> {
  const name = createName.value.trim();
  if (!name || saving.value) return;

  saving.value = true;
  feedback.value = null;

  try {
    const created = await createAdminCategory({
      name,
      description: createDescription.value.trim() || null,
    });
    categories.value = [created, ...categories.value];
    createName.value = '';
    createDescription.value = '';
    feedback.value = 'Category created successfully.';
  } catch (e) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
    const firstError = err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null;
    feedback.value = firstError ?? err.response?.data?.message ?? 'Could not create category right now.';
  } finally {
    saving.value = false;
  }
}

function startEdit(item: AdminCategory): void {
  editingCategory.value = item;
  editName.value = item.name ?? '';
  editDescription.value = item.description ?? '';
  feedback.value = null;
}

function cancelEdit(): void {
  editingCategory.value = null;
  editName.value = '';
  editDescription.value = '';
}

async function handleUpdateCategory(): Promise<void> {
  if (!editingCategory.value || saving.value) return;

  const name = editName.value.trim();
  if (!name) {
    feedback.value = 'Category name is required.';
    return;
  }

  saving.value = true;
  feedback.value = null;

  try {
    const updated = await updateAdminCategory(editingCategory.value.id, {
      name,
      description: editDescription.value.trim() || null,
    });

    categories.value = categories.value.map((item) => (item.id === updated.id ? updated : item));
    feedback.value = 'Category updated successfully.';
    cancelEdit();
  } catch (e) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
    const firstError = err.response?.data?.errors ? Object.values(err.response.data.errors)[0]?.[0] : null;
    feedback.value = firstError ?? err.response?.data?.message ?? 'Could not update category right now.';
  } finally {
    saving.value = false;
  }
}

async function handleDeleteCategory(item: AdminCategory): Promise<void> {
  if (deletingId.value !== null) return;

  const confirmed = window.confirm(`Delete category "${item.name}"?`);
  if (!confirmed) return;

  deletingId.value = item.id;
  feedback.value = null;

  try {
    await deleteAdminCategory(item.id);
    categories.value = categories.value.filter((cat) => cat.id !== item.id);
    if (editingCategory.value?.id === item.id) {
      cancelEdit();
    }
    feedback.value = 'Category deleted successfully.';
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    feedback.value = err.response?.data?.message ?? 'Could not delete category right now.';
  } finally {
    deletingId.value = null;
  }
}

onMounted(() => {
  void loadCategories();
});
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <main class="mx-auto max-w-7xl px-5 pb-16 pt-24 sm:px-8">
      <section class="relative overflow-hidden rounded-3xl bg-surface-container-low p-6 shadow-sm sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-primary/10 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-24 -left-12 h-64 w-64 rounded-full bg-tertiary/10 blur-3xl" />

        <header class="relative mb-7 flex flex-wrap items-end justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Admin / Manage Categories</p>
            <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">Tour Categories</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">
              Create, edit, and remove categories used in tours.
            </p>
          </div>

          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full bg-surface-container px-5 py-3 text-sm font-semibold text-on-surface transition hover:bg-surface-container-high hover:text-primary"
            @click="router.push({ name: 'admin-dashboard' })"
          >
            <span class="material-symbols-outlined text-lg">dashboard</span>
            Back to Dashboard
          </button>
        </header>

        <div class="relative mb-5 grid gap-4 lg:grid-cols-[1.2fr,0.8fr]">
          <section class="rounded-2xl bg-surface p-4 shadow-sm ring-1 ring-outline-variant/10">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Create Category</p>

            <div class="mt-3 grid gap-3 sm:grid-cols-2">
              <input
                v-model="createName"
                type="text"
                placeholder="Category name"
                class="rounded-xl border border-outline-variant/20 bg-surface-container px-3 py-2 text-sm outline-none focus:border-primary"
              />

              <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-on-primary transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="saving || !createName.trim()"
                @click="handleCreateCategory"
              >
                <span class="material-symbols-outlined text-base">add</span>
                {{ saving ? 'Saving...' : 'Add Category' }}
              </button>
            </div>

            <textarea
              v-model="createDescription"
              rows="3"
              placeholder="Optional description"
              class="mt-3 w-full rounded-xl border border-outline-variant/20 bg-surface-container px-3 py-2 text-sm outline-none focus:border-primary"
            />
          </section>

          <section class="rounded-2xl bg-surface p-4 shadow-sm ring-1 ring-outline-variant/10">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Find Category</p>
            <div class="mt-3 flex items-center gap-2 rounded-xl bg-surface-container px-3 py-2">
              <span class="material-symbols-outlined text-base text-on-surface-variant">search</span>
              <input
                v-model="query"
                type="text"
                placeholder="Search by name, description, or ID"
                aria-label="Search categories"
                class="w-full border-0 bg-transparent text-sm outline-none ring-0 placeholder:text-on-surface-variant/70"
              />
            </div>
            <p class="mt-3 text-sm text-on-surface-variant">
              Showing <span class="font-semibold text-on-surface">{{ filteredCategories.length }}</span> of {{ categories.length }} categories
            </p>
          </section>
        </div>

        <div v-if="error" class="relative mb-5 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
          {{ error }}
        </div>

        <div v-if="feedback" class="relative mb-5 rounded-xl bg-secondary-container/70 px-4 py-3 text-sm font-medium text-secondary">
          {{ feedback }}
        </div>

        <div v-if="loading" class="relative space-y-3">
          <div v-for="i in 5" :key="i" class="h-16 animate-pulse rounded-xl bg-surface-container" />
        </div>

        <div
          v-else-if="filteredCategories.length === 0"
          class="relative rounded-2xl bg-surface p-8 text-center shadow-sm ring-1 ring-outline-variant/10"
        >
          <span class="material-symbols-outlined text-3xl text-primary">category</span>
          <p class="mt-2 text-lg font-semibold">No categories found</p>
          <p class="mt-1 text-on-surface-variant">Create a category or adjust your search.</p>
        </div>

        <div v-else class="relative overflow-hidden rounded-2xl bg-surface shadow-sm ring-1 ring-outline-variant/10">
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-surface-container-low text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold">Name</th>
                  <th class="px-4 py-3 font-semibold">Description</th>
                  <th class="px-4 py-3 font-semibold">Created</th>
                  <th class="px-4 py-3 font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in filteredCategories"
                  :key="item.id"
                  class="border-t border-outline-variant/15"
                >
                  <td class="px-4 py-3 font-semibold text-on-surface">{{ item.name }}</td>
                  <td class="px-4 py-3 text-on-surface-variant">{{ item.description || 'No description' }}</td>
                  <td class="px-4 py-3 text-on-surface-variant">{{ formatDate(item.created_at) }}</td>
                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                      <button
                        type="button"
                        class="rounded-full bg-surface-container px-3 py-1.5 text-xs font-semibold text-on-surface-variant transition hover:bg-surface-container-high hover:text-primary"
                        @click="startEdit(item)"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        class="rounded-full bg-error px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="deletingId === item.id"
                        @click="handleDeleteCategory(item)"
                      >
                        {{ deletingId === item.id ? 'Deleting...' : 'Delete' }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <div
        v-if="editingCategory"
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/45 px-4"
        @click.self="cancelEdit"
      >
        <section class="w-full max-w-xl rounded-3xl bg-surface p-6 shadow-2xl ring-1 ring-outline-variant/10">
          <header class="mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Edit Category</p>
              <h2 class="mt-1 text-2xl font-bold">{{ editingCategory.name }}</h2>
            </div>

            <button
              type="button"
              class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container"
              @click="cancelEdit"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </header>

          <div class="space-y-3">
            <input
              v-model="editName"
              type="text"
              placeholder="Category name"
              class="w-full rounded-xl border border-outline-variant/20 bg-surface-container px-3 py-2 text-sm outline-none focus:border-primary"
            />

            <textarea
              v-model="editDescription"
              rows="4"
              placeholder="Description"
              class="w-full rounded-xl border border-outline-variant/20 bg-surface-container px-3 py-2 text-sm outline-none focus:border-primary"
            />
          </div>

          <div class="mt-6 flex items-center justify-end gap-3">
            <button
              type="button"
              class="rounded-full bg-surface-container px-4 py-2 text-sm font-semibold text-on-surface-variant transition hover:bg-surface-container-high"
              @click="cancelEdit"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-on-primary transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="saving || !editName.trim()"
              @click="handleUpdateCategory"
            >
              {{ saving ? 'Saving...' : 'Save changes' }}
            </button>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>
