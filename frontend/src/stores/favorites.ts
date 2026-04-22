import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { addTravelerFavorite, getTravelerFavorites, removeTravelerFavorite } from '../api/favorites';
import { useAuthStore } from './auth';
import type { TravelerFavorite } from '../types/traveler';

export const useFavoritesStore = defineStore('favorites', () => {
  const items = ref<TravelerFavorite[]>([]);
  const idsIndex = ref<Record<number, true>>({});
  const loading = ref(false);
  const loaded = ref(false);
  const pendingTourIds = ref<Record<number, true>>({});

  const favoriteItems = computed(() => items.value);
  const loadingFavorites = computed(() => loading.value);

  const authStore = useAuthStore();

  function rebuildIndex(nextItems: TravelerFavorite[]): void {
    const index: Record<number, true> = {};
    for (const item of nextItems) {
      if (typeof item.tour_id === 'number') {
        index[item.tour_id] = true;
      }
    }
    idsIndex.value = index;
  }

  function setFavorites(nextItems: TravelerFavorite[]): void {
    items.value = nextItems;
    rebuildIndex(nextItems);
  }

  function isTravelerSession(): boolean {
    return authStore.hasValidToken && authStore.role === 'TRAVELER';
  }

  async function ensureTravelerSession(): Promise<boolean> {
    if (!authStore.hasValidToken) {
      return false;
    }

    if (!authStore.role) {
      await authStore.syncUser();
    }

    return authStore.role === 'TRAVELER';
  }

  async function ensureFavoritesLoaded(force = false): Promise<void> {
    const travelerSession = await ensureTravelerSession();

    if (!travelerSession) {
      setFavorites([]);
      loaded.value = false;
      return;
    }

    if (loaded.value && !force) {
      return;
    }

    loading.value = true;
    try {
      const nextItems = await getTravelerFavorites();
      setFavorites(nextItems);
      loaded.value = true;
    } finally {
      loading.value = false;
    }
  }

  function isFavorite(tourId: number): boolean {
    return Boolean(idsIndex.value[tourId]);
  }

  function isFavoriteBusy(tourId: number): boolean {
    return Boolean(pendingTourIds.value[tourId]);
  }

  async function toggleFavorite(tourId: number): Promise<void> {
    const travelerSession = await ensureTravelerSession();

    if (!travelerSession) {
      throw new Error('AUTH_REQUIRED');
    }

    if (isFavoriteBusy(tourId)) {
      return;
    }

    pendingTourIds.value[tourId] = true;

    try {
      if (isFavorite(tourId)) {
        await removeTravelerFavorite(tourId);
        const remaining = items.value.filter((item) => item.tour_id !== tourId);
        setFavorites(remaining);
        return;
      }

      const created = await addTravelerFavorite(tourId);
      const nextItems = [created, ...items.value.filter((item) => item.tour_id !== tourId)];
      setFavorites(nextItems);
    } finally {
      const current = { ...pendingTourIds.value };
      delete current[tourId];
      pendingTourIds.value = current;
    }
  }

  return {
    favoriteItems,
    loadingFavorites,
    isTravelerSession,
    ensureFavoritesLoaded,
    isFavorite,
    isFavoriteBusy,
    toggleFavorite,
  };
});
