export type TourImage = {
  id: number;
  path: string;
  tour_id: number;
};

export type TourCategory = {
  id: number;
  name: string;
};

export type TourGuide = {
  id: number;
  fullname?: string;
  name?: string;
  is_verified?: boolean;
};

export type TourReview = {
  id: number;
  rating?: number | null;
  comment?: string | null;
  created_at?: string | null;
  traveler?: { id: number; fullname?: string; name?: string } | null;
  user?: { id: number; fullname?: string; name?: string } | null;
};

export type Tour = {
  id: number;
  title: string;
  description?: string | null;
  location?: string | null;
  price: number;
  max_spots?: number | null;
  current_bookings?: number | null;
  duration_hours?: number | null;
  date?: string | null;
  difficulty?: string | null;
  category_id?: number | null;
  images?: TourImage[];
  category?: TourCategory | string | null;
  category_name?: string | null;
  guide?: TourGuide | null;
  reviews?: TourReview[];
  rating_avg?: number | null;
  reviews_count?: number;
};

export type GetToursParams = {
  per_page?: number;
  page?: number;
  search?: string;
  location?: string;
  category_id?: number;
  difficulty?: string;
  min_price?: number;
  max_price?: number;
  min_duration_hours?: number;
  max_duration_hours?: number;
  verified_only?: boolean | '1' | 'true';
  available_only?: boolean | '1' | 'true';
};
