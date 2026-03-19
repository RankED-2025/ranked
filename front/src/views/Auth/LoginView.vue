<template>
  <div class="login-view d-flex align-center">
    <v-container>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="10" md="8" lg="6" xl="5">
          <div class="text-center mb-8">
            <h1 class="text-h3 font-weight-bold mb-2 gradient-text">Ranked</h1>
            <p class="text-h6 text-white">Plateforme éducative</p>
          </div>

          <v-card class="pa-8" elevation="8" rounded="lg">
            <v-card-title class="text-h4 font-weight-bold text-center mb-4">
              Connexion
            </v-card-title>
            <v-card-text>
              <LoginForm />
            </v-card-text>
            <v-card-actions class="justify-center">
              <v-btn text color="primary" to="/register">
                Pas encore de compte ? S'inscrire
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import LoginForm from "@/components/auth/LoginForm.vue";
</script>

<style scoped>
.login-view {
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
