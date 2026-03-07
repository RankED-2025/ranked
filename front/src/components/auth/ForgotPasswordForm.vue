<template>
  <div>
    <v-card-title class="text-h5 text-center">Mot de passe oublié</v-card-title>
    <v-card-subtitle class="text-center mb-4">
      Entrez votre adresse email pour recevoir un lien de réinitialisation
    </v-card-subtitle>

    <v-alert v-if="successMessage" type="success" class="mb-4" variant="tonal">
      {{ successMessage }}
    </v-alert>

    <v-alert v-if="errorMessage" type="error" class="mb-4" variant="tonal">
      {{ errorMessage }}
    </v-alert>

    <v-form v-model="valid" @submit.prevent="handleSubmit" ref="formRef">
      <v-text-field
        v-model="email"
        :rules="emailRules"
        label="Email"
        type="email"
        variant="outlined"
        required
        prepend-inner-icon="mdi-email"
        :disabled="loading"
        color="deep-purple"
      ></v-text-field>

      <v-btn
        type="submit"
        color="deep-purple"
        block
        size="large"
        :loading="loading"
        :disabled="!valid || loading"
        class="mt-4"
      >
        Envoyer le lien de réinitialisation
      </v-btn>

      <div class="text-center mt-4">
        <v-btn variant="text" color="deep-purple" @click="goToLogin" :disabled="loading">
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
    email.value = ''
    formRef.value?.reset()
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error || 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}
</script>

<style scoped>
.v-card {
  margin-top: 2rem;
}
</style>
