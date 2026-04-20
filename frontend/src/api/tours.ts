import { api } from './client';
import type { GetToursParams, Tour } from '../types/tours';

type Paginated<T> = {
  data: T[];
  next_page_url?: string | null;
  prev_page_url?: string | null;
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

export async function getTourById(id: number | string): Promise<Tour> {
  const res = await api.get(`/tours/${id}`);
  return res.data as Tour;
}

export function tourImageUrl(imagePath?: string | null): string | null {
  if (!imagePath) return null;
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) return imagePath;
  if (imagePath.startsWith('/')) return imagePath;
  return `/storage/${imagePath}`;
}

