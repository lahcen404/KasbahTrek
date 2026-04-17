import type { AxiosError } from 'axios';
import { api, clearAuthToken, getAuthToken, setAuthToken } from './client';
import type { CurrentUser, LoginUser, RegisterRole } from '../types/auth';

const USER_ROLE_STORAGE_KEY = 'kasbah_user_role';

export type AppRole = 'GUIDE' | 'TRAVELER' | 'ADMIN';

type LoginResponse = {
  message: string;
  token: string;
  user: LoginUser;
};


export async function login(email: string, password: string): Promise<LoginUser> {
  const { data } = await api.post<LoginResponse>('/login', { email, password });
  setAuthToken(data.token);
  setStoredUserRole(data.user.role);
  return data.user;
}

export function getStoredUserRole(): string | null {
  return localStorage.getItem(USER_ROLE_STORAGE_KEY);
}

export function setStoredUserRole(role: string | null | undefined): void {
  const normalizedRole = normalizeAppRole(role);
  if (normalizedRole) {
    localStorage.setItem(USER_ROLE_STORAGE_KEY, normalizedRole);
  } else {
    localStorage.removeItem(USER_ROLE_STORAGE_KEY);
  }
}

export function normalizeAppRole(role: string | null | undefined): AppRole | null {
  const value = (role ?? '').toUpperCase();
  if (value === 'GUIDE' || value === 'TRAVELER' || value === 'ADMIN') {
    return value;
  }

  return null;
}

function hasValidToken(): boolean {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
}

let currentUserRequest: Promise<CurrentUser | null> | null = null;

export async function syncCurrentUser(): Promise<CurrentUser | null> {
  if (!hasValidToken()) {
    return null;
  }

  if (!currentUserRequest) {
    currentUserRequest = getCurrentUser()
      .then((user) => {
        setStoredUserRole(user.role ?? null);
        return user;
      })
      .catch((error: unknown) => {
        const err = error as AxiosError;
        if (err.response?.status === 401) {
          clearAuthToken();
          setStoredUserRole(null);
        }
        return null;
      })
      .finally(() => {
        currentUserRequest = null;
      });
  }

  return currentUserRequest;
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
    setStoredUserRole(null);
  }
}
