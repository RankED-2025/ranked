import { ref } from 'vue'

/**
 * Composable pour gérer l'état ouvert/fermé d'une modale.
 *
 * Conçu pour être utilisé avec un composant qui accepte un v-model booléen.
 *
 * @returns
 *   - isOpen  : booléen réactif, à passer en v-model sur la modale
 *   - open    : ouvre la modale
 *   - close   : ferme la modale
 *   - toggle  : bascule l'état de la modale
 */
export function useModal() {
  const isOpen = ref(false)

  function open() {
    isOpen.value = true
  }

  function close() {
    isOpen.value = false
  }

  function toggle() {
    isOpen.value = !isOpen.value
  }

  return { isOpen, open, close, toggle }
}
