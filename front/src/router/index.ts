import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/userStore'

import HomeView from '@views/Auth/HomeView.vue'
import MyCoursesView from '@views/Courses/MyCoursesView.vue'
import LoginView from '@views/Auth/LoginView.vue'
import RegisterView from '@views/Auth/RegisterView.vue'
import ForgotPasswordView from '@views/Auth/ForgotPasswordView.vue'
import ResetPasswordView from '@views/Auth/ResetPasswordView.vue'


const authRoutes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: { requiresAuth: true },
  },
  {
    path: '/my-courses',
    name: 'my-courses',
    component: MyCoursesView,
    meta: { requiresAuth: true },
  }
];

const guestRoutes = [
  {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresGuest: true },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: ForgotPasswordView,
      meta: { requiresGuest: true },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: ResetPasswordView,
      meta: { requiresGuest: true },
    },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    ...authRoutes,
    ...guestRoutes,
  ],
})

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();

  if (userStore.hasValidTokens() && !userStore.isLoggedIn()) {
    try {
      await userStore.initializeFromStorage();
      next({ name: 'home' });
    } catch (e) {
      userStore.forceDisconnect();
      next('/login');
    }
    return;
  }

  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

  if (requiresAuth && userStore.isLoggedIn()) {
    next();
    return;
  }

  if (requiresGuest && userStore.isLoggedIn() || (requiresAuth && !userStore.isLoggedIn())) {
    next('/login');
    return;
  } else {
    next();
    return;
  }
});

export default router
