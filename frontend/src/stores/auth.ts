import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import type { AppRole } from '../api/auth';
import {
  getCurrentUser,
  getStoredUserRole,
  login as loginApi,
  logout as logoutApi,
  normalizeAppRole,
  registerAccount,
  setStoredUserRole,
  syncCurrentUser,
} from '../api/auth';
import { clearAuthToken, getAuthToken } from '../api/client';
import type { CurrentUser, LoginUser, RegisterRole } from '../types/auth';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<CurrentUser | null>(null);
  const role = ref<AppRole | null>(normalizeAppRole(getStoredUserRole()));

  const hasValidToken = computed(() => {
    const token = getAuthToken();
    return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
  });

  const isAuthenticated = computed(() => hasValidToken.value);

  function setRole(nextRole: string | null | undefined): void {
    role.value = normalizeAppRole(nextRole);
    setStoredUserRole(role.value);
  }

  function clearSession(): void {
    user.value = null;
    setRole(null);
    clearAuthToken();
  }

  async function login(email: string, password: string): Promise<LoginUser> {
    const loggedIn = await loginApi(email, password);
    setRole(loggedIn.role);
    return loggedIn;
  }

  async function register(payload: {
    fullname: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: RegisterRole;
  }): Promise<void> {
    await registerAccount(payload);
  }

  async function fetchMe(): Promise<CurrentUser | null> {
    if (!hasValidToken.value) {
      clearSession();
      return null;
    }

    try {
      const current = await getCurrentUser();
      user.value = current;
      setRole(current.role ?? null);
      return current;
    } catch {
      clearSession();
      return null;
    }
  }

  async function syncUser(): Promise<CurrentUser | null> {
    if (!hasValidToken.value) {
      clearSession();
      return null;
    }

    const current = await syncCurrentUser();
    if (!current) {
      clearSession();
      return null;
    }

    user.value = current;
    setRole(current.role ?? null);
    return current;
  }

  async function logout(): Promise<void> {
    try {
      await logoutApi();
    } finally {
      clearSession();
    }
  }

  return {
    user,
    role,
    hasValidToken,
    isAuthenticated,
    setRole,
    clearSession,
    login,
    register,
    fetchMe,
    syncUser,
    logout,
  };
});
