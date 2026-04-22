import { useFavoritesStore } from '../stores/favorites';

export function useTravelerFavorites() {
  const favoritesStore = useFavoritesStore();

  return {
    favoriteItems: favoritesStore.favoriteItems,
    loadingFavorites: favoritesStore.loadingFavorites,
    isTravelerSession: favoritesStore.isTravelerSession,
    ensureFavoritesLoaded: favoritesStore.ensureFavoritesLoaded,
    isFavorite: favoritesStore.isFavorite,
    isFavoriteBusy: favoritesStore.isFavoriteBusy,
    toggleFavorite: favoritesStore.toggleFavorite,
  };
}
