import { api } from './client';

export type GuideTour = {
  id: number;
  title: string;
  description?: string | null;
  location?: string | null;
  price?: number | null;
  guide_id?: number;
  images?: Array<{ id: number; tour_id: number; path: string }>;
  guide?: { id: number; fullname?: string; is_verified?: boolean } | null;
  bookings_count?: number;
};

export type GuideUser = {
  id: number;
  fullname?: string;
  email?: string;
};

export type GuideBookingStatus = 'PENDING' | 'CONFIRMED' | 'REJECTED' | 'CANCELLED';
export type GuidePaymentStatus = 'UNPAID' | 'PAID' | 'FAILED';

export type GuideBooking = {
  id: number;
  date?: string | null;
  total_price?: number | null;
  status?: GuideBookingStatus | string | null;
  payment_status?: GuidePaymentStatus | string | null;
  traveler?: GuideUser | null;
  tour?: GuideTour | null;
};

export type UpdateBookingStatusResponse = {
  message?: string;
  booking?: GuideBooking;
};

export async function getGuideBookings(): Promise<GuideBooking[]> {
  const res = await api.get('/guide/bookings');
  return Array.isArray(res.data) ? (res.data as GuideBooking[]) : [];
}

export async function getGuideTours(): Promise<GuideTour[]> {
  const res = await api.get('/guide/tours');
  return Array.isArray(res.data) ? (res.data as GuideTour[]) : [];
}

export async function updateGuideBookingStatus(
  bookingId: number,
  status: Exclude<GuideBookingStatus, 'PENDING' | 'CANCELLED'>,
): Promise<UpdateBookingStatusResponse> {
  const res = await api.patch(`/bookings/${bookingId}/status`, { status });
  return res.data as UpdateBookingStatusResponse;
}
