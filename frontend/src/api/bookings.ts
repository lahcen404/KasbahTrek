import { api } from './client';
import type {
  TravelerBooking,
  TravelerBookingsResponse,
} from '../types/traveler';

/**
 * Fetch all bookings for the current traveler
 */
export async function getTravelerBookings(): Promise<TravelerBooking[]> {
  const { data } = await api.get<TravelerBookingsResponse>('/my-bookings');

  if (Array.isArray(data)) {
    return data;
  }

  return data.bookings ?? [];
}

/**
 * Cancel a booking by ID
 */
export async function cancelBooking(bookingId: number): Promise<TravelerBooking> {
  const { data } = await api.patch<TravelerBooking>(`/bookings/${bookingId}/cancel`);
  return data;
}

/**
 * Get a single booking by ID
 */
export async function getBookingById(bookingId: number): Promise<TravelerBooking> {
  const { data } = await api.get<TravelerBooking>(`/bookings/${bookingId}`);
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
