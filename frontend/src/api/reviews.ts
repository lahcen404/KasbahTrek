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

/**
 * Update an existing review.
 * PUT /api/reviews/:id
 */
export async function updateReview(
  reviewId: number,
  payload: { rating: number; comment: string },
): Promise<Review> {
  const { data } = await api.put<SubmitReviewResponse>(`/reviews/${reviewId}`, payload);
  return data.review;
}

/**
 * Delete an existing review.
 * DELETE /api/reviews/:id
 */
export async function deleteReview(reviewId: number): Promise<{ message?: string }> {
  const { data } = await api.delete<{ message?: string }>(`/reviews/${reviewId}`);
  return data;
}
