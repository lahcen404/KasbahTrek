import { createRouter, createWebHistory } from 'vue-router';

import HomePage from '../pages/HomePage.vue';
import LoginPage from '../pages/LoginPage.vue';
import RegisterPage from '../pages/RegisterPage.vue';
import NotFoundPage from '../pages/NotFoundPage.vue';
import GuideDashboardPage from '../pages/GuideDashboardPage.vue';
import GuideTourCreatePage from '../pages/GuideTourCreatePage.vue';
import ToursPage from '../pages/ToursPage.vue';
import TourDetailsPage from '../pages/TourDetailsPage.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: HomePage },
    { path: '/guide/dashboard', name: 'guide-dashboard', component: GuideDashboardPage },
    { path: '/guide/tours/create', name: 'guide-tour-create', component: GuideTourCreatePage },
    { path: '/tours', name: 'tours', component: ToursPage },
    { path: '/tours/:id', name: 'tour-details', component: TourDetailsPage },
    { path: '/login', name: 'login', component: LoginPage },
    { path: '/register', name: 'register', component: RegisterPage },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundPage },
  ],
});

export default router;
