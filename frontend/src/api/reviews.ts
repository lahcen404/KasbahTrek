import { api } from './client';
import type { SubmitReviewPayload, SubmitReviewResponse, Review, ReviewsResponse } from '../types/reviews';

/**
 * Submit a new review for a tour
 * POST /api/reviews
 */
export async function submitReview(payload: SubmitReviewPayload): Promise<Review> {
  const { data } = await api.post<SubmitReviewResponse>('/reviews', payload);
  return data.review;
}

/**
 * Get all reviews for a specific tour (public endpoint)
 * GET /api/tours/:id/reviews
 */
export async function getTourReviews(tourId: number): Promise<Review[]> {
  const { data } = await api.get<ReviewsResponse>(`/tours/${tourId}/reviews`);
  
  if (Array.isArray(data)) {
    return data;
  }
  
  return data.reviews ?? [];
}

/**
 * Get current traveler's reviews
 * GET /api/my-reviews
 */
export async function getMyReviews(): Promise<Review[]> {
  const { data } = await api.get<ReviewsResponse>('/my-reviews');
  
  if (Array.isArray(data)) {
    return data;
  }
  
  return data.reviews ?? [];
}
