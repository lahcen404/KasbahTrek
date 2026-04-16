import { api } from './client';

export type BookingStatus = 'PENDING' | 'CONFIRMED' | 'REJECTED' | 'CANCELLED';
export type PaymentStatus = 'UNPAID' | 'PAID';

export type Booking = {
  id: number;
  traveler_id: number;
  tour_id: number;
  guide_id: number;
  date: string;
  total_price: number;
  status: BookingStatus;
  payment_status: PaymentStatus;
  paid_at: string | null;
  reminder_sent_at: string | null;
  created_at: string;
  tour: {
    id: number;
    title?: string;
    name?: string;
    image_url: string | null;
    difficulty_level?: string;
    duration_hours?: number;
    images?: Array<{ id: number; path: string }>;
    guide?: {
      id: number;
      fullname: string;
    };
  };
  guide?: {
    id: number;
    fullname: string;
  };
};

type TravelerBookingsResponse = Booking[] | { bookings: Booking[] };

/**
 * Fetch all bookings for the current traveler
 */
export async function getTravelerBookings(): Promise<Booking[]> {
  const { data } = await api.get<TravelerBookingsResponse>('/my-bookings');

  if (Array.isArray(data)) {
    return data;
  }

  return data.bookings ?? [];
}

/**
 * Cancel a booking by ID
 */
export async function cancelBooking(bookingId: number): Promise<Booking> {
  const { data } = await api.patch<Booking>(`/bookings/${bookingId}/cancel`);
  return data;
}

/**
 * Get a single booking by ID
 */
export async function getBookingById(bookingId: number): Promise<Booking> {
  const { data } = await api.get<Booking>(`/bookings/${bookingId}`);
  return data;
}

/**
 * Initiate Stripe checkout for a booking
 */
export async function initiateStripeCheckout(
  bookingId: number
): Promise<{ url: string }> {
  const { data } = await api.post<{ url: string }>(
    `/bookings/${bookingId}/checkout`
  );
  return data;
}

/**
 * Initiate PayPal checkout for a booking
 */
export async function initiatePayPalCheckout(
  bookingId: number
): Promise<{ url: string }> {
  const { data } = await api.post<{ url: string }>(
    `/bookings/${bookingId}/paypal/checkout`
  );
  return data;
}
