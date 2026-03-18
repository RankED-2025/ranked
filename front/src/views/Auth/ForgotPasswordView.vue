<script setup lang="ts">
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
</script>

<template>
  <div class="forgot-password-view d-flex align-center">
    <v-container>
      <v-row align-content="center" justify="center">
        <v-col cols="12" sm="10" md="8" lg="6" xl="5">
          <div class="text-center mb-8">
            <h1 class="text-h3 font-weight-bold mb-2 gradient-text">Ranked</h1>
            <p class="text-h6 text-white">Récupération de compte</p>
          </div>
          
          <v-card class="pa-8" elevation="8" rounded="lg">
            <ForgotPasswordForm />
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<style scoped>
.forgot-password-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-text {
  background: white;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
