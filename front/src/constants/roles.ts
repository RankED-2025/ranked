/**
 * Constantes des rôles utilisateur de l'application
 */
export const ROLES = {
  ELEVE: 'ROLE_ELEVE',
  PROFESSEUR: 'ROLE_PROFESSEUR',
  ADMIN: 'ROLE_ADMIN',
  USER: 'ROLE_USER',
} as const

export type RoleType = typeof ROLES[keyof typeof ROLES]

/**
 * Labels en français pour les rôles
 */
export const ROLE_LABELS: Record<RoleType, string> = {
  [ROLES.ADMIN]: 'Administrateur',
  [ROLES.PROFESSEUR]: 'Professeur',
  [ROLES.ELEVE]: 'Élève',
  [ROLES.USER]: 'Utilisateur',
}
