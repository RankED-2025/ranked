<template>
  <div>
    <v-card-title class="text-h5 text-center">Réinitialiser le mot de passe</v-card-title>
    <v-card-subtitle class="text-center mb-4">
      Entrez votre nouveau mot de passe
    </v-card-subtitle>

    <v-alert
      v-if="successMessage"
      type="success"
      class="mb-4"
      variant="tonal"
      data-testid="success-alert"
    >
      {{ successMessage }}
    </v-alert>

    <v-alert v-if="tokenErrorMessage" type="error" class="mb-4" variant="tonal" data-testid="error-alert">
      {{ tokenErrorMessage }}
    </v-alert>

    <StatusAlert v-model:error="resetError" :overrides="RESET_PASSWORD_STATUS_OVERRIDES" test-id="error-alert" />

    <v-form v-model="valid" @submit.prevent="handleSubmit" ref="formRef" data-testid="reset-password-form">
      <v-text-field
        v-model="password"
        :rules="passwordRules"
        label="Nouveau mot de passe"
        type="password"
        variant="outlined"
        required
        prepend-inner-icon="mdi-lock"
        :disabled="loading"
        color="primary"
        data-testid="password-field"
      ></v-text-field>

      <v-text-field
        v-model="confirmPassword"
        :rules="confirmPasswordRulesComputed"
        label="Confirmer le mot de passe"
        type="password"
        variant="outlined"
        required
        prepend-inner-icon="mdi-lock-check"
        :disabled="loading"
        color="primary"
        data-testid="confirm-password-field"
      ></v-text-field>

      <v-btn
        type="submit"
        color="primary"
        block
        size="large"
        :loading="loading"
        :disabled="!valid || loading"
        class="mt-4"
        data-testid="submit-button"
      >
        Réinitialiser le mot de passe
      </v-btn>

      <div class="text-center mt-4">
        <v-btn variant="text" color="primary" @click="goToLogin" :disabled="loading" data-testid="login-button">
          Retour à la connexion
        </v-btn>
      </div>
    </v-form>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { passwordResetService } from '@/services/passwordResetService'
import { passwordRules, confirmPasswordRules } from '@/utils'
import type { StatusMessageOverride } from '@/types'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

const RESET_PASSWORD_STATUS_OVERRIDES: StatusMessageOverride[] = [
  { status: 400, type: 'error', message: 'Le lien de réinitialisation est invalide ou a expiré.' },
]

const router = useRouter()
const route = useRoute()

const password = ref('')
const confirmPassword = ref('')
const valid = ref(false)
const loading = ref(false)
const tokenErrorMessage = ref('')
const resetError = ref<unknown>(null)
const successMessage = ref('')
const formRef = ref()

// Récupérer le token depuis l'URL
const token = route.query.token as string

const confirmPasswordRulesComputed = computed(() =>
  confirmPasswordRules(password)
)

const handleSubmit = async () => {
  if (!valid.value || !token) {
    tokenErrorMessage.value = 'Token invalide ou manquant'
    return
  }

  loading.value = true
  tokenErrorMessage.value = ''
  resetError.value = null
  successMessage.value = ''

  try {
    const response = await passwordResetService.confirmReset({
      token: token,
      password: password.value,
    })
    successMessage.value = response.message

    // Rediriger vers login après 2 secondes
    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (error) {
    resetError.value = error
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}

// Vérifier que le token existe au chargement
if (!token) {
  tokenErrorMessage.value = 'Token de réinitialisation manquant'
}
</script>

<style scoped>
.v-card {
  margin-top: 2rem;
}
</style>
