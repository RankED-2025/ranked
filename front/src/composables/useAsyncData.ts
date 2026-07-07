import { ref } from 'vue'
import type { Ref } from 'vue'

/**
 * Composable générique pour gérer un appel API asynchrone.
 *
 * @param fn           - Fonction qui retourne une Promise (l'appel API)
 * @param initialValue - Valeur initiale de `data` avant la première exécution
 * @param initialLoading - Démarre en état loading (utile si on va appeler execute() immédiatement)
 *
 * @returns { data, loading, error, execute }
 *   - data    : résultat de fn(), réactif
 *   - loading : true pendant l'appel
 *   - error   : message d'erreur, null si succès
 *   - execute : déclenche (ou re-déclenche) l'appel
 */
export function useAsyncData<T>(fn: () => Promise<T>, initialValue: T, initialLoading = false) {
  const data = ref<T>(initialValue) as Ref<T>
  const loading = ref(initialLoading)
  const error = ref<string | null>(null)

  async function execute() {
    loading.value = true
    error.value = null
    try {
      data.value = await fn()
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Une erreur est survenue'
    } finally {
      loading.value = false
    }
  }

  return { data, loading, error, execute }
}
