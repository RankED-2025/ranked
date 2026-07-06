import axios from 'axios'
import type { StatusMessage, StatusMessageOverride } from '@/types'

/**
 * Messages génériques par défaut, indexés par code HTTP.
 * Chaque page peut les surcharger via le paramètre `overrides` de `resolveStatusMessage`.
 */
export const DEFAULT_STATUS_MESSAGES: Record<number, StatusMessage> = {
  400: { type: 'error', message: 'La requête envoyée est invalide. Veuillez vérifier les informations saisies.' },
  401: { type: 'error', message: 'Vous devez vous identifier pour accéder à cette ressource.' },
  403: { type: 'error', message: "Vous n'avez pas les droits nécessaires pour effectuer cette action." },
  404: { type: 'error', message: "La ressource demandée n'a pas été trouvée." },
  409: { type: 'warning', message: 'Un conflit est survenu. Cette ressource existe peut-être déjà.' },
  422: { type: 'error', message: 'Certaines informations saisies sont invalides. Veuillez vérifier votre saisie.' },
  429: { type: 'warning', message: 'Trop de tentatives. Veuillez réessayer dans quelques instants.' },
  500: { type: 'error', message: 'Une erreur interne est survenue. Veuillez réessayer plus tard.' },
  503: { type: 'error', message: 'Le service est temporairement indisponible. Veuillez réessayer plus tard.' },
}

/** Utilisé quand la requête n'a reçu aucune réponse (hors-ligne, CORS, timeout réseau). */
export const NETWORK_ERROR_MESSAGE: StatusMessage = {
  type: 'error',
  message: 'Impossible de contacter le serveur. Vérifiez votre connexion internet.',
}

/** Utilisé quand le statut est absent/non reconnu et qu'il ne s'agit pas d'une erreur réseau. */
export const FALLBACK_STATUS_MESSAGE: StatusMessage = {
  type: 'error',
  message: 'Une erreur est survenue. Veuillez réessayer.',
}

function extractHttpStatus(error: unknown): number | undefined {
  if (!error || typeof error !== 'object') return undefined
  const status = (error as { response?: { status?: unknown } }).response?.status
  return typeof status === 'number' ? status : undefined
}

function isNetworkError(error: unknown): boolean {
  return axios.isAxiosError(error) && !error.response && !!error.request
}

/**
 * Résout le message à afficher pour une erreur donnée à partir du statut HTTP,
 * en donnant priorité aux overrides de la page appelante sur les messages par défaut.
 *
 * @param error     L'erreur interceptée (forme axios attendue, mais toute valeur
 *                  est gérée sans lever d'exception).
 * @param overrides Tableau d'override propre à la page, prioritaire sur les messages
 *                  par défaut. Les entrées sont recherchées par correspondance exacte de `status`.
 */
export function resolveStatusMessage(
  error: unknown,
  overrides: StatusMessageOverride[] = []
): StatusMessage {
  const status = extractHttpStatus(error)

  if (status !== undefined) {
    const override = overrides.find(o => o.status === status)
    if (override) {
      return { type: override.type, message: override.message }
    }

    const defaultMessage = DEFAULT_STATUS_MESSAGES[status]
    if (defaultMessage) {
      return defaultMessage
    }
  }

  if (isNetworkError(error)) {
    return NETWORK_ERROR_MESSAGE
  }

  return FALLBACK_STATUS_MESSAGE
}
