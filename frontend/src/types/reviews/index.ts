export type Review = {
  id: number;
  rating: number; // 1-5
  comment: string;
  created_at: string;
  updated_at: string;
  traveler_id: number;
  tour_id: number;
  traveler?: {
    id: number;
    fullname?: string;
    name?: string;
    email: string;
  };
  tour?: {
    id: number;
    title: string;
  };
};

export type SubmitReviewPayload = {
  tour_id: number;
  rating: number;
  comment: string;
};

export type SubmitReviewResponse = {
  message: string;
  review: Review;
};

export type ReviewsResponse = Review[] | { reviews: Review[] };
