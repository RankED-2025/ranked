<template>
  <v-form ref="formAnchor" v-model="isFormValid" @submit.prevent="handleLogin">
    <v-text-field
      label="E-mail"
      v-model="email"
      :rules="emailRules"
      prepend-inner-icon="mdi-email"
      variant="outlined"
      color="primary"
      ref="usernameAnchor"
      id="email-login-input"
    />

    <v-text-field
      label="Mot de passe"
      v-model="password"
      :rules="loginPasswordRules"
      prepend-inner-icon="mdi-lock"
      variant="outlined"
      color="primary"
      :type="computedPasswordFieldType"
      :append-inner-icon="isPasswordShown ? 'mdi-eye-off' : 'mdi-eye'"
      @click:append-inner="clickAppendIconPassword"
      id="password-login-input"
    />

    <v-alert v-if="errorMessage" type="error" class="mb-4" variant="tonal">
      {{ errorMessage }}
    </v-alert>

    <v-btn
      type="submit"
      color="primary"
      block
      size="large"
      id="submit-login-button"
      :disabled="!isFormValid"
      class="mb-4"
    >
      Se connecter
    </v-btn>

    <div class="text-center">
      <v-btn variant="text" color="primary" to="/forgot-password" size="small">
        Mot de passe oublié ?
      </v-btn>
    </div>
  </v-form>
</template>

<script lang="ts" setup>
import { useUserStore } from '@/stores/userStore'
import router from '@/router'
import { emailRules, loginPasswordRules } from '@/utils/validation'
import { computed, ref } from "vue"
import type { LoginData } from '@/types'

const userStore = useUserStore()

const email = ref('')
const password = ref('')
const isFormValid = ref(false)
const isPasswordShown = ref(false)
const errorMessage = ref('')

const computedPasswordFieldType = computed(() => {
  return isPasswordShown.value ? 'text' : 'password'
})

const clickAppendIconPassword = () => {
  isPasswordShown.value = !isPasswordShown.value
}

const handleLogin = async () => {
  if (isFormValid.value) {
    errorMessage.value = ''
    const loginData: LoginData = {
      email: email.value,
      password: password.value,
    }

    const success = await userStore.loginAttempt(loginData)

    if (success) {
      router.push('/')
    } else {
      errorMessage.value = 'Email ou mot de passe incorrect. Veuillez réessayer.'
    }
  }
}
</script>
