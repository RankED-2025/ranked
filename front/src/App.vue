<script setup lang="ts">
import { RouterView, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { getUserRoleLabel } from '@/utils/roles'
import { computed } from 'vue'
import LoadingModal from '@/components/loading/LoadingModal.vue'

const router = useRouter()
const userStore = useUserStore()

const userRoleLabel = computed(() => {
  return userStore.user?.roles ? getUserRoleLabel(userStore.user.roles) : ''
})

const handleLogout = async () => {
  await userStore.logout()
  await router.push('/login')
}

const handleReturnHomepage = (): void => {
  router.push('/')
}
</script>

<template>
  <v-app>
    <v-app-bar
      v-if="userStore.isAuthenticated"
      color="background"
      elevation="2"
      @click="handleReturnHomepage"
    >
      <template v-slot:prepend>
        <v-toolbar-title class="app-title">
          <button @click="$router.push('/')" class="home-button" title="Accueil">
            <span class="gradient-text">Ranked</span>
          </button>
        </v-toolbar-title>
      </template>

      <v-spacer></v-spacer>

      <v-chip
        class="ma-2"
        color="primary"
        variant="flat"
        prepend-icon="mdi-account"
      >
        {{ userStore.user?.email }}
      </v-chip>

      <v-chip
        class="ma-2"
        color="secondary"
        variant="flat"
      >
        {{ userRoleLabel }}
      </v-chip>

      <v-btn
        color="primary"
        variant="outlined"
        @click="handleLogout"
        prepend-icon="mdi-logout"
      >
        Déconnexion
      </v-btn>
    </v-app-bar>

    <v-main>
      <div v-show="userStore.isLoading">
        <LoadingModal message="Connexion en cours..." size="medium" />
      </div>

      <div v-show="!userStore.isLoading">
        <RouterView />
      </div>
    </v-main>
  </v-app>
</template>

<style scoped>
.app-title {
  font-size: 1.5rem;
  font-weight: 700;
}

.gradient-text {
  background: var(--gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.home-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.3s ease;
}

.home-button:hover {
  background-color: var(--primary-soft-color);
}

.gradient-text {
  background: var(--gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  transition: background 0.3s ease;
  display: inline-block;
}

.home-button:hover .gradient-text {
  background: var(--gradient-primary-hover);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
