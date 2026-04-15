<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { getCurrentUser } from '../../api/auth';

const router = useRouter();

const loading = ref(true);
const error = ref<string | null>(null);
const fullname = ref('Traveler');
const email = ref('');
const role = ref('TRAVELER');

onMounted(async () => {
  loading.value = true;
  error.value = null;

  try {
    const me = await getCurrentUser();
    fullname.value = me.fullname?.trim() || 'Traveler';
    email.value = me.email?.trim() || '';
    role.value = (me.role ?? 'TRAVELER').toUpperCase();
  } catch {
    error.value = 'Could not load your profile right now.';
  } finally {
    loading.value = false;
  }
});

function goToTours(): void {
  void router.push({ name: 'tours' });
}
</script>

<template>
  <div class="min-h-screen bg-surface text-on-surface">
    <main class="mx-auto max-w-5xl px-6 pb-16 pt-28">
      <header class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-primary">Traveler Space</p>
        <h1 class="mt-2 text-4xl font-headline font-bold">My Profile</h1>
        <p class="mt-3 text-on-surface-variant">Quick auth/profile baseline page for traveler account access.</p>
      </header>

      <section class="rounded-2xl border border-outline-variant/20 bg-surface-container-low p-6">
        <p v-if="loading" class="text-on-surface-variant">Loading profile...</p>
        <p v-else-if="error" class="rounded-xl bg-error-container px-4 py-3 text-sm font-medium text-error">
          {{ error }}
        </p>

        <div v-else class="space-y-4">
          <div>
            <p class="text-xs uppercase tracking-wider text-on-surface-variant">Full Name</p>
            <p class="mt-1 text-lg font-semibold">{{ fullname }}</p>
          </div>

          <div>
            <p class="text-xs uppercase tracking-wider text-on-surface-variant">Email</p>
            <p class="mt-1 text-lg font-semibold">{{ email || 'No email available' }}</p>
          </div>

          <div>
            <p class="text-xs uppercase tracking-wider text-on-surface-variant">Role</p>
            <p class="mt-1 inline-flex rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">
              {{ role }}
            </p>
          </div>
        </div>
      </section>

      <div class="mt-6">
        <button
          type="button"
          class="rounded-full bg-primary px-6 py-3 font-semibold text-on-primary transition hover:brightness-110"
          @click="goToTours"
        >
          Explore Tours
        </button>
      </div>
    </main>
  </div>
</template>
