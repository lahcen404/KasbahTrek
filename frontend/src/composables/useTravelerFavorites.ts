import { storeToRefs } from 'pinia';
import { useFavoritesStore } from '../stores/favorites';

export function useTravelerFavorites() {
  const favoritesStore = useFavoritesStore();
  const { favoriteItems, loadingFavorites } = storeToRefs(favoritesStore);

  return {
    favoriteItems,
    loadingFavorites,
    isTravelerSession: favoritesStore.isTravelerSession,
    ensureFavoritesLoaded: favoritesStore.ensureFavoritesLoaded,
    isFavorite: favoritesStore.isFavorite,
    isFavoriteBusy: favoritesStore.isFavoriteBusy,
    toggleFavorite: favoritesStore.toggleFavorite,
  };
}
