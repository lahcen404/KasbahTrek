export type AdminDashboardStats = {
  total_travelers: number;
  total_guides: number;
  total_tours: number;
  total_revenue: number;
  total_confirmed_bookings: number;
  pending_verifications: number;
  pending_trip_reports: number;
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
