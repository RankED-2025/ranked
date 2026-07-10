import { createRouter, createWebHistory } from 'vue-router'
import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
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
  access: RouteAccess,
  breadcrumb?: BreadcrumbMeta,
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
  makeRoute('/', 'home', () => import('@/views/HomeView.vue'), { requiresAuth: true }),
  makeRoute('/my-courses', 'my-courses', () => import('@/views/Courses/StudentCoursesView.vue'), { requiresAuth: true }, {
    label: 'Mes cours',
    parentName: 'home',
  }),
  makeRoute('/course/:id', 'course-content', () => import('@/views/Courses/CourseContentView.vue'), { requiresAuth: true }, {
    label: 'Contenu du cours',
    parentName: 'my-courses',
  }),
  makeRoute('/stats', 'statistics', () => import('@/views/StatisticsView.vue'), { requiresAuth: true }, {
    label: 'Statistiques',
    parentName: 'home',
  }),
  makeRoute('/my-badges-competences', 'my-badges-competences', () => import('@/views/BadgesCompetencesView.vue'), { requiresAuth: true }, {
    label: 'Badges et compétences',
    parentName: 'home',
  }),
];

export const professorRoutes = [
  makeRoute('/professor/my-courses', 'professor-my-courses', () => import('@/views/Professor/ProfessorCoursesView.vue'), { requiresAuth: true, requiresProfessor: true }, {
    label: 'Cours professeur',
    parentName: 'home',
  }),
  makeRoute('/professor/create-course', 'create-course', () => import('@/views/Professor/CreateCourseView.vue'), { requiresAuth: true, requiresProfessor: true }, {
    label: 'Créer un cours',
    parentName: 'professor-my-courses',
  }),
  makeRoute('/professor/edit-course/:id', 'edit-course', () => import('@/views/Professor/EditCourseView.vue'), { requiresAuth: true, requiresProfessor: true }, {
    label: 'Modifier le cours',
    parentName: 'professor-my-courses',
  }),
  makeRoute('/professor/assign-course', 'assign-course', () => import('@/views/Professor/AssignCourseView.vue'), { requiresAuth: true, requiresProfessor: true }, {
    label: 'Affecter un cours',
    parentName: 'professor-my-courses',
  }),
  makeRoute('/professor/classes', 'professor-classes', () => import('@/views/Professor/ProfessorClassesView.vue'), { requiresAuth: true, requiresProfessor: true }, {
    label: 'Classes',
    parentName: 'home',
  }),
  makeRoute('/professor/classes/:id', 'professor-class-detail', () => import('@/views/Professor/ProfessorClassDetailView.vue'), { requiresAuth: true, requiresProfessor: true }, {
    label: 'Détail de la classe',
    parentName: 'professor-classes',
  }),
];

export const guestRoutes = [
  makeRoute('/login', 'login', () => import('@/views/Auth/LoginView.vue'), { requiresGuest: true }),
  makeRoute('/register', 'register', () => import('@/views/Auth/RegisterView.vue'), { requiresGuest: true }),
  makeRoute('/forgot-password', 'forgot-password', () => import('@/views/Auth/ForgotPasswordView.vue'), { requiresGuest: true }),
  makeRoute('/reset-password', 'reset-password', () => import('@/views/Auth/ResetPasswordView.vue'), { requiresGuest: true }),
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    ...authRoutes,
    ...professorRoutes,
    ...guestRoutes,
  ],
})

type UserStore = ReturnType<typeof useUserStore>

/**
 * Initialise la session depuis le localStorage si des tokens valides existent
 * mais que l'utilisateur n'est pas encore chargé en mémoire.
 * Retourne true si la navigation a été prise en charge (next() appelé).
 */
async function tryInitializeSession(
  userStore: UserStore,
  next: NavigationGuardNext,
): Promise<boolean> {
  if (userStore.hasValidTokens() && !userStore.isLoggedIn()) {
    try {
      await userStore.initializeFromStorage()
      next({ name: 'home' })
    } catch {
      userStore.forceDisconnect()
      next('/login')
    }
    return true
  }
  return false
}

/**
 * Applique les guards de route selon les meta requiresAuth, requiresGuest,
 * requiresProfessor et l'état de connexion de l'utilisateur.
 */
function applyRouteGuards(
  to: RouteLocationNormalized,
  userStore: UserStore,
  next: NavigationGuardNext,
): void {
  const requiresGuest = to.matched.some((r) => r.meta.requiresGuest)
  const requiresAuth = to.matched.some((r) => r.meta.requiresAuth)
  const requiresProfessor = to.matched.some((r) => r.meta.requiresProfessor)
  const userIsProfessor = isProfesseur(userStore.user?.roles ?? [])

  if (requiresAuth && userStore.isLoggedIn()) {
    if (requiresProfessor && !userIsProfessor) {
      next('/')
      return
    }
    next()
    return
  }

  if ((requiresGuest && userStore.isLoggedIn()) || (requiresAuth && !userStore.isLoggedIn())) {
    next('/login')
    return
  }

  next()
}

router.beforeEach(async (to, _from, next) => {
  const userStore = useUserStore()
  if (await tryInitializeSession(userStore, next)) return
  applyRouteGuards(to, userStore, next)
})

export default router
