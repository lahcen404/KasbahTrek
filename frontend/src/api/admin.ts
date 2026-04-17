import { api } from './client';
import type { AdminDashboardStats } from '../types/admin';

type AdminDashboardStatsResponse = {
  status?: string;
  data: AdminDashboardStats;
};

export async function getAdminDashboardStats(): Promise<AdminDashboardStats> {
  const { data } = await api.get<AdminDashboardStatsResponse>('/admin/stats');
  return data.data;
}
