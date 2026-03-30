<template>
  <div>
    <v-card-title
      data-testid="form-title"
      class="text-h5 text-center"
    >
      Mot de passe oublié
    </v-card-title>
    <v-card-subtitle
      data-testid="form-subtitle"
      class="text-center mb-4"
    >
      Entrez votre adresse email pour recevoir un lien de réinitialisation
    </v-card-subtitle>

    <v-alert
      v-if="successMessage"
      type="success"
      class="mb-4"
      variant="tonal"
      data-testid="success-message"
    >
      {{ successMessage }}
    </v-alert>

    <v-alert
      v-if="errorMessage"
      type="error"
      class="mb-4"
      variant="tonal"
      data-testid="error-message"
    >
      {{ errorMessage }}
    </v-alert>

    <v-form
      v-model="valid"
      @submit.prevent="handleSubmit"
      ref="formRef"
      data-testid="target-form"
    >
      <v-text-field
        v-model="email"
        :rules="emailRules"
        label="Email"
        type="email"
        variant="outlined"
        required
        prepend-inner-icon="mdi-email"
        :disabled="loading"
        color="primary"
        data-testid="email-field"
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
        Envoyer le lien de réinitialisation
      </v-btn>

      <div class="text-center mt-4">
        <v-btn variant="text" color="primary" @click="goToLogin" :disabled="loading">
          Retour à la connexion
        </v-btn>
      </div>
    </v-form>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { passwordResetService } from '@/services/passwordResetService'
import { emailRules } from '@/utils/validation'

const router = useRouter()

const email = ref('')
const valid = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const formRef = ref()

const handleSubmit = async () => {
  if (!valid.value) return

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await passwordResetService.requestReset({ email: email.value })
    successMessage.value = response.message
    formRef.value?.reset()
    email.value = ''
  } catch (error) {
    errorMessage.value = (error as Error).message || 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}
</script>
