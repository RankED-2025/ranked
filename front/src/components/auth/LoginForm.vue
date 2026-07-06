<template>
  <v-form
    ref="formAnchor"
    v-model="isFormValid"
    @submit.prevent="handleLogin"
    data-testid="target-form"
  >
    <v-text-field
      label="E-mail"
      v-model="email"
      :rules="emailRules"
      prepend-inner-icon="mdi-email"
      variant="outlined"
      color="primary"
      ref="usernameAnchor"
      id="email-login-input"
      data-testid="email-field"
    />

    <div class="password-label-row">
      <span class="text-body-2 text-grey-darken-2">Mot de passe</span>
      <v-btn
        variant="text"
        color="primary"
        to="/forgot-password"
        size="x-small"
        density="compact"
        data-testid="forgot-password-btn"
      >
        Mot de passe oublié ?
      </v-btn>
    </div>
    <v-text-field
      v-model="password"
      :rules="loginPasswordRules"
      prepend-inner-icon="mdi-lock"
      variant="outlined"
      color="primary"
      :type="computedPasswordFieldType"
      :append-inner-icon="isPasswordShown ? 'mdi-eye-off' : 'mdi-eye'"
      @click:append-inner="clickAppendIconPassword"
      id="password-login-input"
      data-testid="password-field"
    />

    <StatusAlert v-model:error="loginError" :overrides="LOGIN_STATUS_OVERRIDES" />

    <v-btn
      type="submit"
      color="primary"
      block
      size="large"
      id="submit-login-button"
      :disabled="!isFormValid"
      :loading="isLoading"
      data-testid="submit-button"
    >
      Se connecter
    </v-btn>
  </v-form>
</template>

<script lang="ts" setup>
import { useUserStore } from '@/stores/userStore'
import router from '@/router'
import { emailRules, loginPasswordRules } from '@/utils'
import { computed, ref } from 'vue'
import type { LoginData, StatusMessageOverride } from '@/types'
import StatusAlert from '@/components/layouts/StatusAlert.vue'
import { useForm } from '@/composables'

const LOGIN_STATUS_OVERRIDES: StatusMessageOverride[] = [
  { status: 401, type: 'error', message: 'Email ou mot de passe incorrect. Veuillez réessayer.' },
]

const userStore = useUserStore()
const { isValid: isFormValid, isLoading, resetMessages } = useForm()

const email = ref('')
const password = ref('')
const isPasswordShown = ref(false)
const loginError = ref<unknown>(null)

const computedPasswordFieldType = computed(() => {
  return isPasswordShown.value ? 'text' : 'password'
})

const clickAppendIconPassword = () => {
  isPasswordShown.value = !isPasswordShown.value
}

const handleLogin = async () => {
  if (isFormValid.value) {
    resetMessages()
    isLoading.value = true
    const loginData: LoginData = {
      email: email.value,
      password: password.value,
    }

    try {
      await userStore.loginAttempt(loginData)
      router.push('/')
    } catch (error) {
      loginError.value = error
    } finally {
      isLoading.value = false
    }
  }
}
</script>

<style scoped>
.password-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0;
}
</style>
