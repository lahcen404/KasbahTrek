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

export type CreateGuideTourPayload = {
  title: string;
  description: string;
  location: string;
  price: number;
  difficulty: 'EASY' | 'MEDIUM' | 'HARD';
  max_spots: number;
  duration_hours?: number | null;
  date?: string | null;
  category_id?: number | null;
  images?: File[];
};

export type CreateGuideTourResponse = {
  message?: string;
  tour?: GuideTour;
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

export async function createGuideTour(
  payload: CreateGuideTourPayload,
): Promise<CreateGuideTourResponse> {
  const formData = new FormData();

  formData.append('title', payload.title);
  formData.append('description', payload.description);
  formData.append('location', payload.location);
  formData.append('price', String(payload.price));
  formData.append('difficulty', payload.difficulty);
  formData.append('max_spots', String(payload.max_spots));

  if (payload.duration_hours !== null && payload.duration_hours !== undefined) {
    formData.append('duration_hours', String(payload.duration_hours));
  }

  if (payload.date) {
    formData.append('date', payload.date);
  }

  if (payload.category_id !== null && payload.category_id !== undefined) {
    formData.append('category_id', String(payload.category_id));
  }

  if (payload.images?.length) {
    payload.images.forEach((image) => {
      formData.append('images[]', image);
    });
  }

  const res = await api.post('/tours', formData);
  return res.data as CreateGuideTourResponse;
}
