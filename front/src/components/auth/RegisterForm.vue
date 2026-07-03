<template>
  <v-form
    ref="formAnchor"
    v-model="valid"
    @submit.prevent="handleRegister"
    data-testid="register-form"
  >
    <v-text-field
      label="Nom"
      v-model="name"
      :rules="usernameRules"
      variant="outlined"
      color="primary"
      prepend-inner-icon="mdi-account"
      id="name-register-input"
      data-testid="name-field"
    />

    <v-text-field
      label="Prénom"
      v-model="firstname"
      :rules="usernameRules"
      variant="outlined"
      color="primary"
      prepend-inner-icon="mdi-account"
      id="firstname-register-input"
      data-testid="firstname-field"
    />

    <v-text-field
      label="E-mail"
      v-model="email"
      :rules="emailRules"
      variant="outlined"
      color="primary"
      prepend-inner-icon="mdi-email"
      ref="emailAnchor"
      id="email-register-input"
      data-testid="email-field"
    />

    <v-text-field
      label="Mot de passe"
      v-model="password"
      :rules="passwordRules"
      variant="outlined"
      color="primary"
      prepend-inner-icon="mdi-lock"
      :type="computedPasswordFieldType"
      :append-inner-icon="isPasswordShown ? 'mdi-eye-off' : 'mdi-eye'"
      @click:append-inner="togglePasswordVisibility"
      id="password-register-input"
      data-testid="password-field"
    />

    <v-text-field
      label="Confirmer le mot de passe"
      v-model="confirmPassword"
      :rules="confirmRules"
      variant="outlined"
      color="primary"
      prepend-inner-icon="mdi-lock-check"
      :type="computedPasswordFieldType"
      :append-inner-icon="isPasswordShown ? 'mdi-eye-off' : 'mdi-eye'"
      @click:append-inner="togglePasswordVisibility"
      ref="confirmPasswordFieldRef"
      id="confirm-password-register-input"
      data-testid="confirm-password-field"
    />

    <StatusAlert
      v-model:error="registerError"
      :overrides="REGISTER_STATUS_OVERRIDES"
      test-id="error-alert"
    />

    <v-alert
      v-if="successMessage"
      type="success"
      class="mb-4"
      variant="tonal"
      data-testid="success-alert"
    >
      {{ successMessage }}
    </v-alert>

    <v-btn
      :disabled="!valid"
      type="submit"
      color="primary"
      block
      size="large"
      id="submit-register-button"
      class="mb-4"
      data-testid="submit-button"
    >
      S'inscrire
    </v-btn>
  </v-form>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue'
import router from '@/router'
import { emailRules, passwordRules, usernameRules, confirmPasswordRules } from '@/utils'
import { useUserStore } from '@/stores/userStore'
import type { LoginData, StatusMessageOverride } from '@/types'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

const REGISTER_STATUS_OVERRIDES: StatusMessageOverride[] = [
  { status: 409, type: 'error', message: 'Un compte existe déjà avec cette adresse email.' },
]

const userStore = useUserStore()

const name = ref('')
const firstname = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const confirmPasswordFieldRef = ref()
const valid = ref(false)
const isPasswordShown = ref(false)
const registerError = ref<unknown>(null)
const successMessage = ref('')

const computedPasswordFieldType = computed(() => (isPasswordShown.value ? 'text' : 'password'))
const confirmRules = computed(() => confirmPasswordRules(password))

watch(password, () => {
  if (confirmPasswordFieldRef.value?.validate) {
    confirmPasswordFieldRef.value.validate()
  }
})

function togglePasswordVisibility() {
  isPasswordShown.value = !isPasswordShown.value
}

async function handleRegister() {
  if (valid.value) {
    registerError.value = null
    successMessage.value = ''

    const registerData = {
      name: name.value,
      firstname: firstname.value,
      email: email.value,
      password: password.value,
    }

    try {
      await userStore.registerAttempt(registerData)
      successMessage.value = 'Inscription réussie ! Connexion en cours...'

      setTimeout(async () => {
        const credentials: LoginData = {
          email: email.value,
          password: password.value,
        }

        try {
          await userStore.loginAttempt(credentials)
          router.push('/')
        } catch {
          router.push('/login')
        }
      }, 2000)
    } catch (error) {
      registerError.value = error
    }
  }
}
</script>
