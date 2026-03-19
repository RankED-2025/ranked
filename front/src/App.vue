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
  router.push('/login')
}
</script>

<template>
  <v-app>
    <v-app-bar v-if="userStore.isAuthenticated" color="background" elevation="2">
      <template v-slot:prepend>
        <v-toolbar-title class="app-title">
          <span class="gradient-text">Ranked</span>
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
      <div v-if="userStore.isLoading">
        <LoadingModal message="Connexion en cours..." size="medium" />
      </div>

      <div v-else>
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
</style>
