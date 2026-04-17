import { createRouter, createWebHistory } from 'vue-router';
import { getAuthToken } from '../api/client';
import {
  getStoredUserRole,
  normalizeAppRole,
  setStoredUserRole,
  syncCurrentUser,
  type AppRole,
} from '../api/auth';

import HomePage from '../pages/common/HomePage.vue';
import LoginPage from '../pages/auth/LoginPage.vue';
import RegisterPage from '../pages/auth/RegisterPage.vue';
import NotFoundPage from '../pages/common/NotFoundPage.vue';
import TravelerProfilePage from '../pages/traveler/TravelerProfilePage.vue';
import TravelerBookingsPage from '../pages/traveler/TravelerBookingsPage.vue';
import TravelerPaymentPage from '../pages/traveler/TravelerPaymentPage.vue';
import TravelerFavoritesPage from '../pages/traveler/TravelerFavoritesPage.vue';
import TravelerReviewsPage from '../pages/traveler/TravelerReviewsPage.vue';
import TravelerReportsPage from '../pages/traveler/TravelerReportsPage.vue';
import GuideDashboardPage from '../pages/guide/GuideDashboardPage.vue';
import GuideReviewsPage from '../pages/guide/GuideReviewsPage.vue';
import GuideTourCreatePage from '../pages/guide/GuideTourCreatePage.vue';
import GuideTourEditPage from '../pages/guide/GuideTourEditPage.vue';
import GuideVerificationPage from '../pages/guide/GuideVerificationPage.vue';
import AdminDashboardPage from '../pages/admin/AdminDashboardPage.vue';
import ToursPage from '../pages/traveler/ToursPage.vue';
import TourDetailsPage from '../pages/traveler/TourDetailsPage.vue';

type RouteMetaGuard = {
  requiresAuth?: boolean;
  guestOnly?: boolean;
  roles?: AppRole[];
};

function hasValidToken(): boolean {
  const token = getAuthToken();
  return typeof token === 'string' && token.trim() !== '' && token !== 'null' && token !== 'undefined';
}

function homeByRole(role: AppRole | null): { name: string } {
  if (role === 'ADMIN') {
    return { name: 'admin-dashboard' };
  }
  if (role === 'GUIDE') {
    return { name: 'guide-dashboard' };
  }

  return { name: 'traveler-profile' };
}

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: HomePage },
    {
      path: '/traveler/profile',
      name: 'traveler-profile',
      component: TravelerProfilePage,
      meta: { requiresAuth: true, roles: ['TRAVELER'] } as RouteMetaGuard,
    },
    {
      path: '/traveler/bookings',
      name: 'traveler-bookings',
      component: TravelerBookingsPage,
      meta: { requiresAuth: true, roles: ['TRAVELER'] } as RouteMetaGuard,
    },
    {
      path: '/traveler/bookings/:id/payment',
      name: 'traveler-booking-payment',
      component: TravelerPaymentPage,
      meta: { requiresAuth: true, roles: ['TRAVELER'] } as RouteMetaGuard,
    },
    {
      path: '/traveler/favorites',
      name: 'traveler-favorites',
      component: TravelerFavoritesPage,
      meta: { requiresAuth: true, roles: ['TRAVELER'] } as RouteMetaGuard,
    },
    {
      path: '/traveler/reviews',
      name: 'traveler-reviews',
      component: TravelerReviewsPage,
      meta: { requiresAuth: true, roles: ['TRAVELER'] } as RouteMetaGuard,
    },
    {
      path: '/traveler/reports',
      name: 'traveler-reports',
      component: TravelerReportsPage,
      meta: { requiresAuth: true, roles: ['TRAVELER'] } as RouteMetaGuard,
    },
    {
      path: '/guide/dashboard',
      name: 'guide-dashboard',
      component: GuideDashboardPage,
      meta: { requiresAuth: true, roles: ['GUIDE'] } as RouteMetaGuard,
    },
    {
      path: '/guide/reviews',
      name: 'guide-reviews',
      component: GuideReviewsPage,
      meta: { requiresAuth: true, roles: ['GUIDE'] } as RouteMetaGuard,
    },
    {
      path: '/guide/tours/create',
      name: 'guide-tour-create',
      component: GuideTourCreatePage,
      meta: { requiresAuth: true, roles: ['GUIDE'] } as RouteMetaGuard,
    },
    {
      path: '/guide/tours/:id/edit',
      name: 'guide-tour-edit',
      component: GuideTourEditPage,
      meta: { requiresAuth: true, roles: ['GUIDE'] } as RouteMetaGuard,
    },
    {
      path: '/guide/verification',
      name: 'guide-verification',
      component: GuideVerificationPage,
      meta: { requiresAuth: true, roles: ['GUIDE'] } as RouteMetaGuard,
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: AdminDashboardPage,
      meta: { requiresAuth: true, roles: ['ADMIN'] } as RouteMetaGuard,
    },
    { path: '/tours', name: 'tours', component: ToursPage },
    { path: '/tours/:id', name: 'tour-details', component: TourDetailsPage },
    { path: '/login', name: 'login', component: LoginPage, meta: { guestOnly: true } as RouteMetaGuard },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage,
      meta: { guestOnly: true } as RouteMetaGuard,
    },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundPage },
  ],
});

router.beforeEach(async (to) => {
  const matched = to.matched.map((record) => (record.meta ?? {}) as RouteMetaGuard);
  const requiresAuth = matched.some((meta) => meta.requiresAuth);
  const guestOnly = matched.some((meta) => meta.guestOnly);
  const roles = matched.flatMap((meta) => meta.roles ?? []);

  const tokenExists = hasValidToken();
  let role = normalizeAppRole(getStoredUserRole());

  // When local role is missing/stale, sync once with /me before making guard decisions.
  if (tokenExists && (!role || requiresAuth || guestOnly)) {
    const user = await syncCurrentUser();
    role = normalizeAppRole(user?.role ?? getStoredUserRole());

    if (!user && requiresAuth) {
      return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (user?.role) {
      setStoredUserRole(user.role);
    }
  }

  if (requiresAuth && !tokenExists) {
    return { name: 'login', query: { redirect: to.fullPath } };
  }

  if (guestOnly && tokenExists) {
    return homeByRole(role);
  }

  if (requiresAuth && roles.length > 0) {
    const allowedRoles = new Set(roles);
    if (!role || !allowedRoles.has(role)) {
      return homeByRole(role);
    }
  }

  return true;
});

export default router;
