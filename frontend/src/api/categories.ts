import { api } from './client';

export type Category = {
  id: number;
  name: string;
};

export async function getCategories(): Promise<Category[]> {
  const res = await api.get('/categories');
  return Array.isArray(res.data) ? (res.data as Category[]) : [];
}

