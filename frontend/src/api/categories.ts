import { api } from './client';
import type { Category } from '../types/categories';

export async function getCategories(): Promise<Category[]> {
  const res = await api.get('/categories');
  return Array.isArray(res.data) ? (res.data as Category[]) : [];
}

