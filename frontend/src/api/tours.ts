import { api } from './client';

export type TourImage = {
  id: number;
  path: string;
  tour_id: number;
};

export type TourCategory = {
  id: number;
  name: string;
};

export type TourGuide = {
  id: number;
  fullname?: string;
  name?: string;
  is_verified?: boolean;
};

export type Tour = {
  id: number;
  title: string;
  description?: string | null;
  location?: string | null;
  price: number;
  duration_hours?: number | null;
  difficulty?: string | null;
  category_id?: number | null;
  images?: TourImage[];
  category?: TourCategory | string | null;
  category_name?: string | null;
  guide?: TourGuide | null;
};

type Paginated<T> = {
  data: T[];
  next_page_url?: string | null;
  prev_page_url?: string | null;
};

export type GetToursParams = {
  per_page?: number;
  page?: number;
  search?: string;
  location?: string;
  category_id?: number;
  difficulty?: string;
  min_price?: number;
  max_price?: number;
  min_duration_hours?: number;
  max_duration_hours?: number;
  verified_only?: boolean | '1' | 'true';
  available_only?: boolean | '1' | 'true';
};

function unwrapArrayOrPaginator<T>(payload: unknown): T[] {
  if (Array.isArray(payload)) return payload as T[];
  if (payload && typeof payload === 'object' && Array.isArray((payload as Paginated<T>).data)) {
    return (payload as Paginated<T>).data;
  }
  return [];
}

export async function getTours(params: GetToursParams = {}): Promise<Tour[]> {
  const res = await api.get('/tours', { params });
  return unwrapArrayOrPaginator<Tour>(res.data);
}

export function tourImageUrl(imagePath?: string | null): string | null {
  if (!imagePath) return null;
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) return imagePath;
  if (imagePath.startsWith('/')) return imagePath;
  return `/storage/${imagePath}`;
}

