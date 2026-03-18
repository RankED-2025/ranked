<template>
  <div class="register-view d-flex align-center">
    <v-container>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="10" md="8" lg="7" xl="6">
          <div class="text-center mb-8">
            <h1 class="text-h3 font-weight-bold mb-2 gradient-text">Ranked</h1>
            <p class="text-h6 text-white">Rejoignez la plateforme</p>
          </div>
          
          <v-card class="pa-8" elevation="8" rounded="lg">
            <v-card-title class="text-h4 font-weight-bold text-center mb-4">
              Inscription
            </v-card-title>
            <v-card-text>
              <RegisterForm />
            </v-card-text>
            <v-card-actions class="justify-center">
              <v-btn text color="deep-purple" to="/login">
                Déjà un compte ? Se connecter
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import RegisterForm from '@/components/auth/RegisterForm.vue';
</script>

<style scoped>
.register-view {
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