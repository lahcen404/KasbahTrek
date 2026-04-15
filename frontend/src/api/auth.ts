import { api, clearAuthToken, setAuthToken } from './client';
import type { CurrentUser, LoginUser, RegisterRole } from '../types/auth';

const USER_ROLE_STORAGE_KEY = 'kasbah_user_role';

type LoginResponse = {
  message: string;
  token: string;
  user: LoginUser;
};


export async function login(email: string, password: string): Promise<LoginUser> {
  const { data } = await api.post<LoginResponse>('/login', { email, password });
  setAuthToken(data.token);
  localStorage.setItem(USER_ROLE_STORAGE_KEY, data.user.role);
  return data.user;
}

export function getStoredUserRole(): string | null {
  return localStorage.getItem(USER_ROLE_STORAGE_KEY);
}

export async function getCurrentUser(): Promise<CurrentUser> {
  const { data } = await api.get<CurrentUser>('/me');
  return data;
}

export async function registerAccount(payload: {
  fullname: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: RegisterRole;
}): Promise<void> {
  await api.post('/register', payload);
}

export async function logout(): Promise<void> {
  try {
    await api.post('/logout');
  } finally {
    clearAuthToken();
    localStorage.removeItem(USER_ROLE_STORAGE_KEY);
  }
}
