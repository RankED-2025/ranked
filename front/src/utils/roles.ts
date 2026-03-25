import { ROLES, ROLE_LABELS, type RoleType } from '@/constants/roles'

/**
 * Utilitaires pour la gestion des rôles utilisateur
 */

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */
export const hasRole = (userRoles: string[], role: RoleType): boolean => {
  return userRoles.includes(role)
}

/**
 * Vérifie si l'utilisateur a au moins un des rôles spécifiés
 */
export const hasAnyRole = (userRoles: string[], roles: RoleType[]): boolean => {
  return roles.some(role => userRoles.includes(role))
}

/**
 * Vérifie si l'utilisateur a tous les rôles spécifiés
 */
export const hasAllRoles = (userRoles: string[], roles: RoleType[]): boolean => {
  return roles.every(role => userRoles.includes(role))
}

/**
 * Vérifie si l'utilisateur est un élève
 */
export const isEleve = (userRoles: string[]): boolean => {
  return hasRole(userRoles, ROLES.ELEVE)
}

/**
 * Vérifie si l'utilisateur est un professeur
 */
export const isProfesseur = (userRoles: string[]): boolean => {
  return hasRole(userRoles, ROLES.PROFESSEUR)
}

/**
 * Vérifie si l'utilisateur est un administrateur
 */
export const isAdmin = (userRoles: string[]): boolean => {
  return hasRole(userRoles, ROLES.ADMIN)
}

/**
 * Obtient le rôle principal de l'utilisateur (le plus important)
 */
export const getPrimaryRole = (userRoles: string[]): RoleType => {
  if (hasRole(userRoles, ROLES.ADMIN)) return ROLES.ADMIN
  if (hasRole(userRoles, ROLES.PROFESSEUR)) return ROLES.PROFESSEUR
  if (hasRole(userRoles, ROLES.ELEVE)) return ROLES.ELEVE
  return ROLES.ELEVE
}

/**
 * Obtient le libellé du rôle en français
 */
export const getRoleLabel = (role: RoleType): string => {
  return ROLE_LABELS[role] || 'Utilisateur'
}

/**
 * Obtient le libellé du rôle principal de l'utilisateur
 */
export const getUserRoleLabel = (userRoles: string[]): string => {
  const primaryRole = getPrimaryRole(userRoles)
  return getRoleLabel(primaryRole)
}
