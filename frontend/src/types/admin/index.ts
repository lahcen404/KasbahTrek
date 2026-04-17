export type AdminDashboardStats = {
  total_travelers: number;
  total_guides: number;
  total_tours: number;
  total_revenue: number;
  total_confirmed_bookings: number;
  pending_verifications: number;
  pending_trip_reports: number;
};

export type AdminVerificationStatus = 'PENDING' | 'APPROVED' | 'REJECTED';

export type AdminVerificationActionStatus = 'APPROVED' | 'REJECTED';

export type AdminVerificationGuide = {
  id: number;
  fullname?: string;
  email?: string;
};

export type AdminVerification = {
  id: number;
  file_url: string;
  status: AdminVerificationStatus;
  guide_id: number;
  admin_id?: number | null;
  created_at?: string;
  updated_at?: string;
  guide?: AdminVerificationGuide;
};

export type AdminUserRole = 'TRAVELER' | 'GUIDE' | 'ADMIN';

export type AdminUser = {
  id: number;
  fullname: string;
  email: string;
  role: AdminUserRole;
  is_verified?: boolean;
  created_at?: string;
  updated_at?: string;
};

export type UpdateAdminUserPayload = {
  fullname?: string;
  email?: string;
  role?: AdminUserRole;
  is_verified?: boolean;
  password?: string;
};

export type AdminCategory = {
  id: number;
  name: string;
  description?: string | null;
  created_at?: string;
  updated_at?: string;
};

export type UpsertAdminCategoryPayload = {
  name: string;
  description?: string | null;
};
