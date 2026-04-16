import { api } from './client';
import type {
  AddTravelerFavoriteResponse,
  TravelerFavorite,
  TravelerFavoritesResponse,
} from '../types/traveler';

export async function getTravelerFavorites(): Promise<TravelerFavorite[]> {
  const { data } = await api.get<TravelerFavoritesResponse>('/favorites');

  if (Array.isArray(data)) {
    return data;
  }

  return data.favorites ?? [];
}

export async function addTravelerFavorite(tourId: number): Promise<TravelerFavorite> {
  const { data } = await api.post<AddTravelerFavoriteResponse>('/favorites', {
    tour_id: tourId,
  });

  return data.favorite;
}

export async function removeTravelerFavorite(tourId: number): Promise<{ message?: string }> {
  const { data } = await api.delete<{ message?: string }>(`/favorites/${tourId}`);
  return data;
}
