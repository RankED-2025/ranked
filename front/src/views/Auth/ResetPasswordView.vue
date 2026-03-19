<script setup lang="ts">
import ResetPasswordForm from '@/components/auth/ResetPasswordForm.vue'
</script>

<template>
  <div class="reset-password-view d-flex align-center">
    <v-container>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="10" md="8" lg="6" xl="5">
          <div class="text-center mb-8">
            <h1 class="text-h3 font-weight-bold mb-2 gradient-text">Ranked</h1>
            <p class="text-h6 text-white">Nouveau mot de passe</p>
          </div>

          <v-card class="pa-8" elevation="8" rounded="lg">
            <ResetPasswordForm />
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<style scoped>
.reset-password-view {
  min-height: 100vh;
  background: var(--gradient-primary);
}

.gradient-text {
  background: var(--surface-color);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
