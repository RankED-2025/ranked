<template>
  <div>
    <v-card-title data-testid="form-title" class="text-h5 text-center">
      Mot de passe oublié
    </v-card-title>
    <v-card-subtitle data-testid="form-subtitle" class="text-center mb-4 text-wrap">
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

    <StatusAlert v-model:error="resetError" />

    <v-form v-model="valid" @submit.prevent="handleSubmit" ref="formRef" data-testid="target-form">
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
        <v-btn
          variant="text"
          color="primary"
          @click="goToLogin"
          :disabled="loading"
          data-testid="go-back-button"
        >
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
import { emailRules } from '@/utils'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

const router = useRouter()

const email = ref('')
const valid = ref(false)
const loading = ref(false)
const resetError = ref<unknown>(null)
const successMessage = ref('')
const formRef = ref()

const handleSubmit = async () => {
  if (!valid.value) return

  loading.value = true
  resetError.value = null
  successMessage.value = ''

  try {
    const response = await passwordResetService.requestReset({ email: email.value })
    successMessage.value = response.message
    formRef.value?.reset()
    email.value = ''
  } catch (error) {
    resetError.value = error
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}
</script>
