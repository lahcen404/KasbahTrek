export type LoginUser = {
  fullname: string;
  role: string;
};

export type CurrentUser = {
  id: number;
  fullname?: string;
  email?: string;
  role?: string;
  is_verified?: boolean;
  verification_request?: {
    id?: number;
    status?: string;
    file_url?: string;
    guide_id?: number;
  } | null;
};

export type RegisterRole = 'TRAVELER' | 'GUIDE';
