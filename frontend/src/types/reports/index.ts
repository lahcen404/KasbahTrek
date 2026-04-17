export type CreateTripReportPayload = {
  tour_id: number;
  reason: string;
};

export type TripReport = {
  id: number;
  reason: string;
  status: 'PENDING' | 'APPROVED' | 'REJECTED';
  traveler_id: number;
  tour_id: number;
  admin_id?: number | null;
  created_at?: string;
  updated_at?: string;
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
  admin?: {
    id: number;
    fullname?: string;
    name?: string;
    email: string;
  };
};

export type CreateTripReportResponse = {
  message?: string;
  report: TripReport;
};
