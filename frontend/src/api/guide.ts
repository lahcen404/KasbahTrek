import { api } from './client';
import type {
  CreateGuideTourPayload,
  CreateGuideTourResponse,
  GuideBooking,
  GuideBookingStatus,
  GuideTour,
  SubmitGuideVerificationResponse,
  UpdateGuideTourPayload,
  UpdateGuideTourResponse,
  UpdateBookingStatusResponse,
} from '../types/guide';

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

export async function updateGuideTour(
  tourId: number,
  payload: UpdateGuideTourPayload,
): Promise<UpdateGuideTourResponse> {
  const res = await api.put(`/tours/${tourId}`, payload);
  return res.data as UpdateGuideTourResponse;
}

export async function uploadGuideTourImages(tourId: number, images: File[]): Promise<void> {
  if (images.length === 0) return;

  const formData = new FormData();
  images.forEach((image) => {
    formData.append('images[]', image);
  });

  await api.post(`/tours/${tourId}/images`, formData);
}

export async function deleteGuideTourImage(tourId: number, imageId: number): Promise<void> {
  await api.delete(`/tours/${tourId}/images/${imageId}`);
}

export async function deleteGuideTour(tourId: number): Promise<void> {
  await api.delete(`/tours/${tourId}`);
}

export async function submitGuideVerification(document: File): Promise<SubmitGuideVerificationResponse> {
  const formData = new FormData();
  formData.append('document', document);

  const res = await api.post('/guide/verifications', formData);
  return res.data as SubmitGuideVerificationResponse;
}
