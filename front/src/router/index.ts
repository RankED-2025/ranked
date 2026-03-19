import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/userStore'


const authRoutes = [
  {
    path: '/',
    name: 'home',
    component: import('@/views/HomeView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/my-courses',
    name: 'my-courses',
    component: import('@/views/Courses/MyCoursesView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/course/:id',
    name: 'course-content',
    component: import('@/views/Courses/CourseContentView.vue'),
    meta: { requiresAuth: true },
  }
];

const guestRoutes = [
  {
      path: '/login',
      name: 'login',
      component: import('@/views/Auth/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: import('@/views/Auth/RegisterView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: import('@/views/Auth/ForgotPasswordView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: import('@/views/Auth/ResetPasswordView.vue'),
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
      console.log(e);
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
