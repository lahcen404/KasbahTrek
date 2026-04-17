<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  deleteAdminUser,
  getAdminUserById,
  getAdminUsers,
} from '../../api/admin';
import type { AdminUser, AdminUserRole } from '../../types/admin';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const feedback = ref<string | null>(null);
const users = ref<AdminUser[]>([]);
const query = ref('');
const roleFilter = ref<'ALL' | AdminUserRole>('ALL');

const selectedUser = ref<AdminUser | null>(null);
const loadingSelectedUser = ref(false);
const deletingUserId = ref<number | null>(null);
const showUserModal = ref(false);

const filteredUsers = computed(() => {
  const q = query.value.trim().toLowerCase();

  return users.value.filter((user) => {
    const roleMatch = roleFilter.value === 'ALL' || user.role === roleFilter.value;
    if (!roleMatch) return false;

    if (!q) return true;
    return (
      user.fullname.toLowerCase().includes(q) ||
      user.email.toLowerCase().includes(q) ||
      String(user.id).includes(q)
    );
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

function roleBadge(role: AdminUserRole): string {
  if (role === 'ADMIN') return 'bg-violet-100 text-violet-700';
  if (role === 'GUIDE') return 'bg-emerald-100 text-emerald-700';
  return 'bg-sky-100 text-sky-700';
}

async function loadUsers(): Promise<void> {
  loading.value = true;
  error.value = null;

  try {
    users.value = await getAdminUsers();
  } catch (e) {
    const err = e as { response?: { data?: { message?: string } } };
    error.value = err.response?.data?.message ?? 'Could not load users right now.';
    users.value = [];
  } finally {
    loading.value = false;
  }
}

async function viewUser(userId: number): Promise<void> {
  loadingSelectedUser.value = true;
  feedback.value = null;

  try {
    selectedUser.value = await getAdminUserById(userId);
    showUserModal.value = true;
  } catch {
    feedback.value = 'Could not load user details right now.';
  } finally {
    loadingSelectedUser.value = false;
  }
}

function closeUserModal(): void {
  showUserModal.value = false;
}

async function removeUser(user: AdminUser): Promise<void> {
  if (deletingUserId.value !== null) return;
  const confirmed = window.confirm(`Delete ${user.fullname}? This action cannot be undone.`);
  if (!confirmed) return;

  deletingUserId.value = user.id;
  feedback.value = null;

  try {
    await deleteAdminUser(user.id);
    users.value = users.value.filter((item) => item.id !== user.id);
    if (selectedUser.value?.id === user.id) {
      selectedUser.value = null;
    }
    feedback.value = 'User deleted successfully.';
  } catch {
    feedback.value = 'Could not delete this user right now.';
  } finally {
    deletingUserId.value = null;
  }
}

onMounted(() => {
  void loadUsers();
});
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <main class="mx-auto max-w-7xl px-5 pb-16 pt-24 sm:px-8">
      <section class="relative overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container-low p-6 sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-primary/10 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-24 -left-12 h-64 w-64 rounded-full bg-tertiary/10 blur-3xl" />

        <header class="relative mb-7 flex flex-wrap items-end justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Admin / Manage Users</p>
            <h1 class="mt-3 text-3xl font-headline font-bold sm:text-4xl">Admin Users</h1>
            <p class="mt-2 max-w-2xl text-on-surface-variant">
              View travelers, guides, and admins. Remove invalid accounts when needed.
            </p>
          </div>

          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-primary hover:text-primary"
            @click="router.push({ name: 'admin-dashboard' })"
          >
            <span class="material-symbols-outlined text-lg">dashboard</span>
            Back to Dashboard
          </button>
        </header>

        <div class="relative mb-5 grid gap-3 sm:grid-cols-[1fr,220px]">
          <label class="rounded-xl border border-outline-variant/30 bg-surface px-3 py-2">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Search</span>
            <input
              v-model="query"
              type="text"
              placeholder="Search by name, email or id"
              class="w-full bg-transparent text-sm outline-none"
            />
          </label>

          <label class="rounded-xl border border-outline-variant/30 bg-surface px-3 py-2">
            <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-on-surface-variant">Role</span>
            <select v-model="roleFilter" class="w-full bg-transparent text-sm outline-none">
              <option value="ALL">All roles</option>
              <option value="TRAVELER">Traveler</option>
              <option value="GUIDE">Guide</option>
              <option value="ADMIN">Admin</option>
            </select>
          </label>
        </div>

        <div v-if="error" class="relative mb-5 rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
          {{ error }}
        </div>

        <div v-if="feedback" class="relative mb-5 rounded-xl bg-secondary-container/70 px-4 py-3 text-sm font-medium text-secondary">
          {{ feedback }}
        </div>

        <div v-if="loading" class="relative space-y-3">
          <div v-for="i in 6" :key="i" class="h-16 animate-pulse rounded-xl bg-surface-container" />
        </div>

        <div v-else-if="filteredUsers.length === 0" class="relative rounded-2xl border border-outline-variant/20 bg-surface p-8 text-center">
          <span class="material-symbols-outlined text-3xl text-primary">manage_accounts</span>
          <p class="mt-2 text-lg font-semibold">No users found</p>
          <p class="mt-1 text-on-surface-variant">Try adjusting your search or role filter.</p>
        </div>

        <div v-else class="relative overflow-hidden rounded-2xl border border-outline-variant/20 bg-surface">
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-surface-container-low text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold">User</th>
                  <th class="px-4 py-3 font-semibold">Role</th>
                  <th class="px-4 py-3 font-semibold">Verified</th>
                  <th class="px-4 py-3 font-semibold">Created</th>
                  <th class="px-4 py-3 font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in filteredUsers"
                  :key="user.id"
                  class="border-t border-outline-variant/15"
                >
                  <td class="px-4 py-3">
                    <p class="font-semibold text-on-surface">{{ user.fullname }}</p>
                    <p class="text-xs text-on-surface-variant">{{ user.email }} · #{{ user.id }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', roleBadge(user.role)]">
                      {{ user.role }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <span :class="user.is_verified ? 'text-emerald-600' : 'text-slate-400'">
                      {{ user.is_verified ? 'Yes' : 'No' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-on-surface-variant">{{ formatDate(user.created_at) }}</td>
                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                      <button
                        type="button"
                        class="rounded-full border border-outline-variant/30 px-3 py-1.5 text-xs font-semibold text-on-surface-variant transition hover:border-primary/40 hover:text-primary"
                        :disabled="loadingSelectedUser"
                        @click="viewUser(user.id)"
                      >
                        View
                      </button>
                      <button
                        type="button"
                        class="rounded-full bg-error px-3 py-1.5 text-xs font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                        :disabled="deletingUserId === user.id"
                        @click="removeUser(user)"
                      >
                        {{ deletingUserId === user.id ? 'Deleting...' : 'Delete' }}
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
        v-if="showUserModal && selectedUser"
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/45 px-4"
        @click.self="closeUserModal"
      >
        <section class="w-full max-w-xl rounded-3xl border border-outline-variant/20 bg-surface p-6 shadow-2xl">
          <header class="mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">User Details</p>
              <h2 class="mt-1 text-2xl font-bold">{{ selectedUser.fullname }}</h2>
              <p class="mt-1 text-sm text-on-surface-variant">{{ selectedUser.email }}</p>
            </div>

            <button
              type="button"
              class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container"
              @click="closeUserModal"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </header>

          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl bg-surface-container-low px-4 py-3">
              <p class="text-xs uppercase tracking-wider text-on-surface-variant">User ID</p>
              <p class="mt-1 font-semibold">#{{ selectedUser.id }}</p>
            </div>

            <div class="rounded-xl bg-surface-container-low px-4 py-3">
              <p class="text-xs uppercase tracking-wider text-on-surface-variant">Role</p>
              <span :class="['mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', roleBadge(selectedUser.role)]">
                {{ selectedUser.role }}
              </span>
            </div>

            <div class="rounded-xl bg-surface-container-low px-4 py-3">
              <p class="text-xs uppercase tracking-wider text-on-surface-variant">Verified</p>
              <p class="mt-1 font-semibold" :class="selectedUser.is_verified ? 'text-emerald-600' : 'text-slate-500'">
                {{ selectedUser.is_verified ? 'Yes' : 'No' }}
              </p>
            </div>

            <div class="rounded-xl bg-surface-container-low px-4 py-3">
              <p class="text-xs uppercase tracking-wider text-on-surface-variant">Created</p>
              <p class="mt-1 font-semibold">{{ formatDate(selectedUser.created_at) }}</p>
            </div>

            <div class="rounded-xl bg-surface-container-low px-4 py-3 sm:col-span-2">
              <p class="text-xs uppercase tracking-wider text-on-surface-variant">Last Updated</p>
              <p class="mt-1 font-semibold">{{ formatDate(selectedUser.updated_at) }}</p>
            </div>
          </div>

          <footer class="mt-6 flex justify-end">
            <button
              type="button"
              class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-on-primary transition hover:brightness-110"
              @click="closeUserModal"
            >
              Close
            </button>
          </footer>
        </section>
      </div>
    </main>
  </div>
</template>
