import { createRouter, createWebHistory } from 'vue-router'
import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { isProfesseur } from '@/utils'


export const authRoutes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: {
      requiresAuth: true,
      breadcrumb: {
        label: 'Accueil',
      },
    },
  },
  {
    path: '/my-courses',
    name: 'my-courses',
    component: () => import('@/views/Courses/StudentCoursesView.vue'),
    meta: {
      requiresAuth: true,
      breadcrumb: {
        label: 'Mes cours',
        parentName: 'home',
      },
    },
  },
  {
    path: '/course/:id',
    name: 'course-content',
    component: () => import('@/views/Courses/CourseContentView.vue'),
    meta: {
      requiresAuth: true,
      breadcrumb: {
        label: 'Contenu du cours',
        parentName: 'my-courses',
      },
    },
  },
  {
    path: '/stats',
    name: 'statistics',
    component: () => import('@/views/StatisticsView.vue'),
    meta: {
      requiresAuth: true,
      breadcrumb: {
        label: 'Statistiques',
        parentName: 'home',
      },
    }
  },
  {
    path: '/my-badges-competences',
    name: 'my-badges-competences',
    component: () => import('@/views/BadgesCompetencesView.vue'),
    meta: {
      requiresAuth: true,
      breadcrumb: {
        label: 'Badges et compétences',
        parentName: 'home',
      },
    },
  },
];

export const professorRoutes = [
  {
    path: '/professor/my-courses',
    name: 'professor-my-courses',
    component: () => import('@/views/Professor/ProfessorCoursesView.vue'),
    meta: {
      requiresAuth: true,
      requiresProfessor: true,
      breadcrumb: {
        label: 'Cours professeur',
        parentName: 'home',
      },
    },
  },
  {
    path: '/professor/create-course',
    name: 'create-course',
    component: () => import('@/views/Professor/CreateCourseView.vue'),
    meta: {
      requiresAuth: true,
      requiresProfessor: true,
      breadcrumb: {
        label: 'Créer un cours',
        parentName: 'professor-my-courses',
      },
    },
  },
  {
    path: '/professor/edit-course/:id',
    name: 'edit-course',
    component: () => import('@/views/Professor/EditCourseView.vue'),
    meta: {
      requiresAuth: true,
      requiresProfessor: true,
      breadcrumb: {
        label: 'Modifier le cours',
        parentName: 'professor-my-courses',
      },
    },
  },
  {
    path: '/professor/assign-course',
    name: 'assign-course',
    component: () => import('@/views/Professor/AssignCourseView.vue'),
    meta: {
      requiresAuth: true,
      requiresProfessor: true,
      breadcrumb: {
        label: 'Affecter un cours',
        parentName: 'professor-my-courses',
      },
    },
  },
  {
    path: '/professor/classes',
    name: 'professor-classes',
    component: () => import('@/views/Professor/ProfessorClassesView.vue'),
    meta: {
      requiresAuth: true,
      requiresProfessor: true,
      breadcrumb: {
        label: 'Classes',
        parentName: 'home',
      },
    },
  },
  {
    path: '/professor/classes/:id',
    name: 'professor-class-detail',
    component: () => import('@/views/Professor/ProfessorClassDetailView.vue'),
    meta: {
      requiresAuth: true,
      requiresProfessor: true,
      breadcrumb: {
        label: 'Détail de la classe',
        parentName: 'professor-classes',
      },
    },
  },
];

export const guestRoutes = [
  {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Auth/LoginView.vue'),
      meta: {
        requiresGuest: true,
        breadcrumb: {
          label: 'Connexion',
        },
      },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/Auth/RegisterView.vue'),
      meta: {
        requiresGuest: true,
        breadcrumb: {
          label: 'Inscription',
        },
      },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('@/views/Auth/ForgotPasswordView.vue'),
      meta: {
        requiresGuest: true,
        breadcrumb: {
          label: 'Mot de passe oublié',
        },
      },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('@/views/Auth/ResetPasswordView.vue'),
      meta: {
        requiresGuest: true,
        breadcrumb: {
          label: 'Réinitialisation du mot de passe',
        },
      },
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
