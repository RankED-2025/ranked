import type { Ref } from 'vue'

/**
 * Règles de validation pour les formulaires
 */

export const passwordRules = [
  (value: string) => !!value || 'Veuillez entrer un mot de passe',
  (value: string) => value.length >= 8 || 'Le mot de passe doit contenir au moins 8 caractères',
  (value: string) => /[A-Z]/.test(value) || 'Le mot de passe doit contenir au moins une majuscule',
  (value: string) => /[a-z]/.test(value) || 'Le mot de passe doit contenir au moins une minuscule',
  (value: string) => /\d/.test(value) || 'Le mot de passe doit contenir au moins un chiffre',
  (value: string) => /[@$!%*?&]/.test(value) || 'Le mot de passe doit contenir un caractère spécial (@, $, !, %, *, ?, &)',
]

export const loginPasswordRules = [
  (value: string) => !!value || 'Veuillez entrer un mot de passe',
]

export const emailRules = [
  (value: string) => !!value || 'Veuillez entrer un e-mail',
  (value: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || "L'e-mail doit être valide",
]

export const usernameRules = [
  (value: string) => !!value || "Veuillez entrer un nom d'utilisateur",
  (value: string) => (value.length >= 3) || "Le nom d'utilisateur doit contenir au moins 3 caractères",
]

export const confirmPasswordRules = (password: Ref<string>) => [
  (v: string) => !!v || 'La confirmation du mot de passe est requise',
  (v: string) => v === password.value || 'Les mots de passe ne correspondent pas',
]

export const eventTypeRules = [(v: string) => !!v || "Le type d'événement est requis"]
export const endDateRules = [(v: string) => !!v || 'La date de fin est requise']
export const startDateRules = [(v: string) => !!v || 'La date de début est requise']
export const titleRules = [(v: string) => !!v || 'Le titre est requis']
