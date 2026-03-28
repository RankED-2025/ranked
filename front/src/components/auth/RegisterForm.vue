<template>
  <v-form
    ref="formAnchor"
    v-model="valid"
    @submit.prevent="handleRegister"
  >
    <v-text-field
      label="Nom"
      v-model="name"
      :rules="usernameRules"
      variant="outlined"
      color="primary"
      prepend-inner-icon="mdi-account"
      id="name-register-input"
    />

    <v-text-field
      label="Prénom"
      v-model="firstname"
      :rules="usernameRules"
      variant="outlined"
      color="primary"
      prepend-inner-icon="mdi-account"
      id="firstname-register-input"
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
    />

    <v-alert v-if="errorMessage" type="error" class="mb-4" variant="tonal">
      {{ errorMessage }}
    </v-alert>

    <v-alert v-if="successMessage" type="success" class="mb-4" variant="tonal">
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
    >
      S'inscrire
    </v-btn>
  </v-form>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue'
import router from '@/router'
import { emailRules, passwordRules, usernameRules, confirmPasswordRules } from '@/utils/validation'
import { useUserStore } from '@/stores/userStore'
import type { LoginData } from '@/types'

const userStore = useUserStore()

const name = ref('')
const firstname = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const confirmPasswordFieldRef = ref()
const valid = ref(false)
const isPasswordShown = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const computedPasswordFieldType = computed(() => isPasswordShown.value ? 'text' : 'password')
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
    errorMessage.value = ''
    successMessage.value = ''

    const registerData = {
      name: name.value,
      firstname: firstname.value,
      email: email.value,
      password: password.value,
    }

    const registerSuccess = await userStore.registerAttempt(
      registerData,
      'eleve'
    )

    if (registerSuccess) {
      successMessage.value = 'Inscription réussie! Connexion en cours...'

      setTimeout(async () => {
        const credentials: LoginData = {
          email: email.value,
          password: password.value,
        }

        const loginSuccess = await userStore.loginAttempt(credentials)
        router.push(loginSuccess ? '/' : '/login')
      }, 2000)

    } else {
      errorMessage.value = 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.'
    }
  }
}
</script>
