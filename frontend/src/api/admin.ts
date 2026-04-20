import { api } from './client';
import type { Tour } from '../types/tours';
import type {
  AdminCategory,
  AdminDashboardStats,
  AdminTripReport,
  AdminTripReportActionStatus,
  AdminUser,
  AdminVerification,
  AdminVerificationActionStatus,
  UpsertAdminCategoryPayload,
  UpdateAdminUserPayload,
} from '../types/admin';

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

type AdminVerificationsResponse = {
  status?: string;
  data: AdminVerification[];
};

type AdminVerificationResponse = {
  status?: string;
  message?: string;
  data: AdminVerification;
};

type AdminToursResponse = {
  status?: string;
  data: Tour[];
};

type AdminCategoriesResponse = AdminCategory[];

type AdminCategoryResponse = {
  message?: string;
  category: AdminCategory;
};

type AdminReportsResponse = AdminTripReport[];

type AdminReportResponse = {
  message?: string;
  report: AdminTripReport;
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

export async function getAdminVerifications(): Promise<AdminVerification[]> {
  const { data } = await api.get<AdminVerificationsResponse>('/admin/verifications');
  return data.data;
}

export async function updateAdminVerificationStatus(
  verificationId: number,
  status: AdminVerificationActionStatus,
): Promise<AdminVerification> {
  const { data } = await api.patch<AdminVerificationResponse>(
    `/admin/verifications/${verificationId}/status`,
    { status },
  );

  return data.data;
}

export async function getAdminTours(): Promise<Tour[]> {
  const { data } = await api.get<AdminToursResponse>('/admin/tours');
  return data.data;
}

export async function deleteAdminTour(tourId: number): Promise<void> {
  await api.delete(`/admin/tours/${tourId}`);
}

export async function getAdminCategories(): Promise<AdminCategory[]> {
  const { data } = await api.get<AdminCategoriesResponse>('/admin/categories');
  return Array.isArray(data) ? data : [];
}

export async function createAdminCategory(payload: UpsertAdminCategoryPayload): Promise<AdminCategory> {
  const { data } = await api.post<AdminCategoryResponse>('/admin/categories', payload);
  return data.category;
}

export async function updateAdminCategory(
  categoryId: number,
  payload: Partial<UpsertAdminCategoryPayload>,
): Promise<AdminCategory> {
  const { data } = await api.put<AdminCategoryResponse>(`/admin/categories/${categoryId}`, payload);
  return data.category;
}

export async function deleteAdminCategory(categoryId: number): Promise<void> {
  await api.delete(`/admin/categories/${categoryId}`);
}

export async function getAdminReports(): Promise<AdminTripReport[]> {
  const { data } = await api.get<AdminReportsResponse>('/admin/reports');
  return Array.isArray(data) ? data : [];
}

export async function updateAdminReportStatus(
  reportId: number,
  status: AdminTripReportActionStatus,
): Promise<AdminTripReport> {
  const { data } = await api.patch<AdminReportResponse>(`/admin/reports/${reportId}/status`, { status });
  return data.report;
}
