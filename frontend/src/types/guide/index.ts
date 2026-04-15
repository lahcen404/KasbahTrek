export type GuideTour = {
  id: number;
  title: string;
  description?: string | null;
  location?: string | null;
  price?: number | null;
  max_spots?: number | null;
  duration_hours?: number | null;
  difficulty?: string | null;
  date?: string | null;
  category_id?: number | null;
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

export type GuideReview = {
  id: number;
  rating?: number | null;
  comment?: string | null;
  created_at?: string | null;
  traveler?: {
    id: number;
    fullname?: string;
  } | null;
  tour?: {
    id: number;
    title?: string;
    guide_id?: number;
  } | null;
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

export type UpdateGuideTourPayload = {
  title?: string;
  description?: string;
  location?: string;
  price?: number;
  difficulty?: 'EASY' | 'MEDIUM' | 'HARD';
  max_spots?: number;
  duration_hours?: number | null;
  date?: string | null;
  category_id?: number | null;
  remove_image_ids?: number[];
};

export type UpdateGuideTourResponse = {
  message?: string;
  tour?: GuideTour;
};

export type SubmitGuideVerificationResponse = {
  status?: string;
  message?: string;
  data?: {
    id?: number;
    guide_id?: number;
    file_url?: string;
    status?: string;
    created_at?: string;
    updated_at?: string;
  };
};
