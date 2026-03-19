export const ROLES = {
  ELEVE: 'ROLE_ELEVE',
  PROFESSEUR: 'ROLE_PROFESSEUR',
  ADMIN: 'ROLE_ADMIN'
} as const

export const ROLE_LABELS: Record<RoleType, string> = {
  [ROLES.ADMIN]: 'Administrateur',
  [ROLES.PROFESSEUR]: 'Professeur',
  [ROLES.ELEVE]: 'Élève'
}

export type RoleType = typeof ROLES[keyof typeof ROLES]
