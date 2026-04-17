import { api } from './client';
import type { AdminDashboardStats, AdminUser, UpdateAdminUserPayload } from '../types/admin';

type AdminDashboardStatsResponse = {
  status?: string;
  data: AdminDashboardStats;
};

type AdminUsersResponse = {
  status?: string;
  data: AdminUser[];
};

type AdminUserResponse = {
  status?: string;
  message?: string;
  data: AdminUser;
};

export async function getAdminDashboardStats(): Promise<AdminDashboardStats> {
  const { data } = await api.get<AdminDashboardStatsResponse>('/admin/stats');
  return data.data;
}

export async function getAdminUsers(): Promise<AdminUser[]> {
  const { data } = await api.get<AdminUsersResponse>('/admin/users');
  return data.data;
}

export async function getAdminUserById(userId: number): Promise<AdminUser> {
  const { data } = await api.get<AdminUserResponse>(`/admin/users/${userId}`);
  return data.data;
}

export async function updateAdminUser(userId: number, payload: UpdateAdminUserPayload): Promise<AdminUser> {
  const { data } = await api.put<AdminUserResponse>(`/admin/users/${userId}`, payload);
  return data.data;
}

export async function deleteAdminUser(userId: number): Promise<void> {
  await api.delete(`/admin/users/${userId}`);
}
