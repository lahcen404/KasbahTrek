import type { Tour } from '../tours';

export type BookingStatus = 'PENDING' | 'CONFIRMED' | 'REJECTED' | 'CANCELLED';

export type PaymentStatus = 'UNPAID' | 'PAID' | 'FAILED';

export type BookingFilter = 'all' | 'upcoming' | 'completed' | 'cancelled';

export type TravelerBookingGuide = {
  id: number;
  fullname: string;
};

export type TravelerBookingImage = {
  id: number;
  path: string;
};

export type TravelerBookingTour = {
  id: number;
  title?: string;
  name?: string;
  image_url: string | null;
  difficulty_level?: string;
  duration_hours?: number;
  images?: TravelerBookingImage[];
  guide?: TravelerBookingGuide;
};

export type TravelerBooking = {
  id: number;
  traveler_id: number;
  tour_id: number;
  guide_id: number;
  date: string;
  total_price: number | string;
  status: BookingStatus;
  payment_status: PaymentStatus;
  paid_at: string | null;
  reminder_sent_at: string | null;
  created_at: string;
  tour: TravelerBookingTour;
  guide?: TravelerBookingGuide;
};

export type TravelerBookingsResponse = TravelerBooking[] | { bookings: TravelerBooking[] };

export type CreateTravelerBookingPayload = {
  tour_id: number;
  date: string;
};

export type CreateTravelerBookingResponse = {
  message?: string;
  booking: TravelerBooking;
};

export type TravelerFavorite = {
  id: number;
  traveler_id: number;
  tour_id: number;
  created_at?: string;
  updated_at?: string;
  tour: Tour;
};

export type TravelerFavoritesResponse = TravelerFavorite[] | { favorites: TravelerFavorite[] };

export type AddTravelerFavoriteResponse = {
  message?: string;
  favorite: TravelerFavorite;
};
