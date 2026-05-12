import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { isProfesseur } from '@/utils'


const authRoutes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/my-courses',
    name: 'my-courses',
    component: () => import('@/views/Courses/StudentCoursesView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/course/:id',
    name: 'course-content',
    component: () => import('@/views/Courses/CourseContentView.vue'),
    meta: { requiresAuth: true },
  }
];

const professorRoutes = [
  {
    path: '/professor/my-courses',
    name: 'professor-my-courses',
    component: () => import('@/views/Professor/ProfessorCoursesView.vue'),
    meta: { requiresAuth: true, requiresProfessor: true },
  },
  {
    path: '/professor/create-course',
    name: 'create-course',
    component: () => import('@/views/Professor/CreateCourseView.vue'),
    meta: { requiresAuth: true, requiresProfessor: true },
  },
  {
    path: '/professor/edit-course/:id',
    name: 'edit-course',
    component: () => import('@/views/Professor/EditCourseView.vue'),
    meta: { requiresAuth: true, requiresProfessor: true },
  },
  {
    path: '/professor/assign-course',
    name: 'assign-course',
    component: () => import('@/views/Professor/AssignCourseView.vue'),
    meta: { requiresAuth: true, requiresProfessor: true },
  },
  {
    path: '/professor/classes',
    name: 'professor-classes',
    component: () => import('@/views/Professor/ProfessorClassesView.vue'),
    meta: { requiresAuth: true, requiresProfessor: true },
  },
  {
    path: '/professor/classes/:id',
    name: 'professor-class-detail',
    component: () => import('@/views/Professor/ProfessorClassDetailView.vue'),
    meta: { requiresAuth: true, requiresProfessor: true },
  },
];

const guestRoutes = [
  {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Auth/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/Auth/RegisterView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('@/views/Auth/ForgotPasswordView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('@/views/Auth/ResetPasswordView.vue'),
      meta: { requiresGuest: true },
    },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    ...authRoutes,
    ...professorRoutes,
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
  const requiresProfessor = to.matched.some(record => record.meta.requiresProfessor)
  const userIsProfessor = isProfesseur(userStore.user?.roles ?? [])

  if (requiresAuth && userStore.isLoggedIn()) {
    if (requiresProfessor && !userIsProfessor) {
      next('/');
      return;
    }
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
