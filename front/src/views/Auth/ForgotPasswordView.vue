<script setup lang="ts">
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'

const features = [
  { icon: 'mdi-book-open-page-variant', text: 'Suivez vos cours et vos activités' },
  { icon: 'mdi-trophy', text: 'Gagnez des badges et progressez dans le classement' },
  { icon: 'mdi-chart-line', text: 'Visualisez vos statistiques en temps réel' },
  { icon: 'mdi-account-group', text: 'Gérez vos classes et suivez vos élèves' },
]
</script>

<template>
  <v-row no-gutters class="fill-height auth-layout">
    <!-- Panneau gauche : branding -->
    <v-col cols="12" md="6" class="auth-brand-panel d-flex align-center justify-center pa-10">
      <div class="blob blob-1" />
      <div class="blob blob-2" />

      <div class="auth-brand-inner">
        <div class="d-flex align-center mb-6">
          <v-icon size="56" color="white">mdi-crown</v-icon>
          <h1 class="text-h2 font-weight-bold text-white ml-3">Ranked</h1>
        </div>

        <p class="text-h6 text-white font-weight-medium mb-10">
          La plateforme ENT gamifiée de vos cours et classes
        </p>

        <div class="d-flex flex-column ga-4">
          <div v-for="feature in features" :key="feature.text" class="brand-feature pa-4">
            <v-icon color="white" class="mr-3">{{ feature.icon }}</v-icon>
            <span class="text-white text-body-1">{{ feature.text }}</span>
          </div>
        </div>
      </div>
    </v-col>

    <!-- Panneau droit : formulaire -->
    <v-col cols="12" md="6" class="d-flex align-center justify-center bg-white pa-8">
      <div class="auth-form-inner w-100">
        <ForgotPasswordForm />
      </div>
    </v-col>
  </v-row>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
}

.auth-brand-panel {
  background: var(--gradient-primary);
  position: relative;
  overflow: hidden;
}

.auth-brand-inner {
  position: relative;
  z-index: 1;
  max-width: 480px;
}

.auth-form-inner {
  max-width: 420px;
}

.brand-feature {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  backdrop-filter: blur(4px);
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(90px);
  opacity: 0.15;
  pointer-events: none;
}

.blob-1 {
  width: 450px;
  height: 450px;
  background: white;
  top: -150px;
  right: -100px;
}

.blob-2 {
  width: 350px;
  height: 350px;
  background: white;
  bottom: -100px;
  left: -80px;
}
</style>
