import { ref } from 'vue'

/**
 * Composable pour gérer l'état commun d'un formulaire.
 *
 * @returns
 *   - isValid       : booléen lié au v-model de <v-form> (Vuetify)
 *   - errorMessage  : message d'erreur à afficher dans le formulaire
 *   - successMessage: message de succès à afficher dans le formulaire
 *   - isLoading     : état de chargement lors de la soumission
 *   - resetMessages : remet errorMessage et successMessage à vide
 */
export function useForm() {
  const isValid = ref(false)
  const errorMessage = ref('')
  const successMessage = ref('')
  const isLoading = ref(false)

  function resetMessages() {
    errorMessage.value = ''
    successMessage.value = ''
  }

  return { isValid, errorMessage, successMessage, isLoading, resetMessages }
}
