import { computed, ref } from 'vue';
import { getStoredUserRole, normalizeAppRole, syncCurrentUser } from '../api/auth';
import { getAuthToken } from '../api/client';
import {
  addTravelerFavorite,
  getTravelerFavorites,
  removeTravelerFavorite,
} from '../api/favorites';
import type { TravelerFavorite } from '../types/traveler';

const favoriteItems = ref<TravelerFavorite[]>([]);
const favoriteIdsIndex = ref<Record<number, true>>({});
const loadingFavorites = ref(false);
const favoritesLoaded = ref(false);
const pendingTourIds = ref<Record<number, true>>({});

function hasValidToken(): boolean {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
}

function isTravelerSession(): boolean {
  const role = normalizeAppRole(getStoredUserRole());
  return hasValidToken() && role === 'TRAVELER';
}

async function ensureTravelerSession(): Promise<boolean> {
  if (!hasValidToken()) {
    return false;
  }

  let role = normalizeAppRole(getStoredUserRole());

  // Public pages may not have synced the role yet. Sync once before deciding.
  if (!role) {
    const user = await syncCurrentUser();
    role = normalizeAppRole(user?.role ?? getStoredUserRole());
  }

  return role === 'TRAVELER';
}

function rebuildFavoriteIndex(items: TravelerFavorite[]): void {
  const index: Record<number, true> = {};
  for (const item of items) {
    if (typeof item.tour_id === 'number') {
      index[item.tour_id] = true;
    }
  }
  favoriteIdsIndex.value = index;
}

function setFavorites(items: TravelerFavorite[]): void {
  favoriteItems.value = items;
  rebuildFavoriteIndex(items);
}

async function ensureFavoritesLoaded(force = false): Promise<void> {
  const travelerSession = await ensureTravelerSession();

  if (!travelerSession) {
    setFavorites([]);
    favoritesLoaded.value = false;
    return;
  }

  if (favoritesLoaded.value && !force) {
    return;
  }

  loadingFavorites.value = true;
  try {
    const items = await getTravelerFavorites();
    setFavorites(items);
    favoritesLoaded.value = true;
  } finally {
    loadingFavorites.value = false;
  }
}

function isFavorite(tourId: number): boolean {
  return Boolean(favoriteIdsIndex.value[tourId]);
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
      const remaining = favoriteItems.value.filter((item) => item.tour_id !== tourId);
      setFavorites(remaining);
      return;
    }

    const created = await addTravelerFavorite(tourId);
    const next = [created, ...favoriteItems.value.filter((item) => item.tour_id !== tourId)];
    setFavorites(next);
  } finally {
    const current = { ...pendingTourIds.value };
    delete current[tourId];
    pendingTourIds.value = current;
  }
}

export function useTravelerFavorites() {
  return {
    favoriteItems: computed(() => favoriteItems.value),
    loadingFavorites: computed(() => loadingFavorites.value),
    isTravelerSession,
    ensureFavoritesLoaded,
    isFavorite,
    isFavoriteBusy,
    toggleFavorite,
  };
}
