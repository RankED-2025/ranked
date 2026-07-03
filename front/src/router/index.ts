import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { isProfesseur } from '@/utils'

type BreadcrumbMeta = {
  label: string
  parentName?: string
}

type RouteAccess = {
  requiresAuth?: boolean
  requiresProfessor?: boolean
  requiresGuest?: boolean
}

/**
 * Builds a route entry from its distinguishing bits, since every route in
 * this file shares the same path/name/component/meta.breadcrumb shape.
 */
function makeRoute(
  path: string,
  name: string,
  component: () => Promise<unknown>,
  breadcrumb: BreadcrumbMeta,
  access: RouteAccess,
) {
  return {
    path,
    name,
    component,
    meta: {
      ...access,
      breadcrumb,
    },
  }
}

export const authRoutes = [
  makeRoute('/', 'home', () => import('@/views/HomeView.vue'), { label: 'Accueil' }, {
    requiresAuth: true,
  }),
  makeRoute('/my-courses', 'my-courses', () => import('@/views/Courses/StudentCoursesView.vue'), {
    label: 'Mes cours',
    parentName: 'home',
  }, { requiresAuth: true }),
  makeRoute('/course/:id', 'course-content', () => import('@/views/Courses/CourseContentView.vue'), {
    label: 'Contenu du cours',
    parentName: 'my-courses',
  }, { requiresAuth: true }),
  makeRoute('/stats', 'statistics', () => import('@/views/StatisticsView.vue'), {
    label: 'Statistiques',
    parentName: 'home',
  }, { requiresAuth: true }),
  makeRoute('/my-badges-competences', 'my-badges-competences', () => import('@/views/BadgesCompetencesView.vue'), {
    label: 'Badges et compétences',
    parentName: 'home',
  }, { requiresAuth: true }),
];

export const professorRoutes = [
  makeRoute('/professor/my-courses', 'professor-my-courses', () => import('@/views/Professor/ProfessorCoursesView.vue'), {
    label: 'Cours professeur',
    parentName: 'home',
  }, { requiresAuth: true, requiresProfessor: true }),
  makeRoute('/professor/create-course', 'create-course', () => import('@/views/Professor/CreateCourseView.vue'), {
    label: 'Créer un cours',
    parentName: 'professor-my-courses',
  }, { requiresAuth: true, requiresProfessor: true }),
  makeRoute('/professor/edit-course/:id', 'edit-course', () => import('@/views/Professor/EditCourseView.vue'), {
    label: 'Modifier le cours',
    parentName: 'professor-my-courses',
  }, { requiresAuth: true, requiresProfessor: true }),
  makeRoute('/professor/assign-course', 'assign-course', () => import('@/views/Professor/AssignCourseView.vue'), {
    label: 'Affecter un cours',
    parentName: 'professor-my-courses',
  }, { requiresAuth: true, requiresProfessor: true }),
  makeRoute('/professor/classes', 'professor-classes', () => import('@/views/Professor/ProfessorClassesView.vue'), {
    label: 'Classes',
    parentName: 'home',
  }, { requiresAuth: true, requiresProfessor: true }),
  makeRoute('/professor/classes/:id', 'professor-class-detail', () => import('@/views/Professor/ProfessorClassDetailView.vue'), {
    label: 'Détail de la classe',
    parentName: 'professor-classes',
  }, { requiresAuth: true, requiresProfessor: true }),
];

export const guestRoutes = [
  makeRoute('/login', 'login', () => import('@/views/Auth/LoginView.vue'), {
    label: 'Connexion',
  }, { requiresGuest: true }),
  makeRoute('/register', 'register', () => import('@/views/Auth/RegisterView.vue'), {
    label: 'Inscription',
  }, { requiresGuest: true }),
  makeRoute('/forgot-password', 'forgot-password', () => import('@/views/Auth/ForgotPasswordView.vue'), {
    label: 'Mot de passe oublié',
  }, { requiresGuest: true }),
  makeRoute('/reset-password', 'reset-password', () => import('@/views/Auth/ResetPasswordView.vue'), {
    label: 'Réinitialisation du mot de passe',
  }, { requiresGuest: true }),
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
  }

  if (requiresGuest && userStore.isLoggedIn() || (requiresAuth && !userStore.isLoggedIn())) {
    next('/login');
  } else {
    next();
  }
});

export default router
