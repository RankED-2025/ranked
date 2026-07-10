<script setup lang="ts">
import { RouterView, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import { getUserRoleLabel, isAdmin } from '@/utils/roles'
import { authService } from '@/services/authService'
import { computed } from 'vue'
import LoadingModal from '@/components/loading/LoadingModal.vue'
import BreadcrumbTrail from '@/components/layouts/BreadcrumbTrail.vue'

const router = useRouter()
const userStore = useUserStore()

const userRoleLabel = computed(() => {
  return userStore.user?.roles ? getUserRoleLabel(userStore.user.roles) : ''
})

const isAdminUser = computed(() => {
  return userStore.user?.roles ? isAdmin(userStore.user.roles) : false
})

const displayName = computed(() => {
  const u = userStore.user
  if (u?.firstname && u?.name) return `${u.firstname} ${u.name}`
  if (u?.firstname) return u.firstname
  if (u?.name) return u.name
  return u?.email ?? ''
})

const userInitials = computed(() => {
  const u = userStore.user
  if (u?.firstname && u?.name) return `${u.firstname[0]}${u.name[0]}`.toUpperCase()
  if (u?.firstname) return u.firstname.slice(0, 2).toUpperCase()
  if (u?.name) return u.name.slice(0, 2).toUpperCase()
  return u?.email?.slice(0, 2).toUpperCase() ?? '?'
})

const handleLogout = async () => {
  await userStore.logout()
  await router.push('/login')
}

const handleAdminPanel = async () => {
  const url = await authService.getAdminSsoUrl()
  window.location.href = url
}
</script>

<template>
  <v-app>
    <v-app-bar
      v-if="userStore.isAuthenticated"
      elevation="0"
      class="custom-navbar"
      :height="64"
    >
      <div class="navbar-inner">
        <!-- Brand -->
        <button class="nav-brand" @click="$router.push('/')" title="Accueil">
          <v-img src="@/assets/img/LogoRankED.png" alt="Logo" :width="26" :height="26" />
          <span class="brand-text">Rank<span class="brand-accent">ED</span></span>
        </button>

        <div class="nav-spacer"></div>

        <!-- Right zone -->
        <div class="nav-right">
          <!-- Admin -->
          <button
            v-if="isAdminUser"
            class="nav-pill nav-pill--admin"
            id="admin-panel-button"
            @click="handleAdminPanel"
          >
            <v-icon size="14">mdi-shield-account</v-icon>
            Panel admin
          </button>

          <!-- User pill -->
          <div class="nav-user-pill">
            <div class="user-avatar-circle">{{ userInitials }}</div>
            <div class="user-details">
              <span class="user-name">{{ displayName }}</span>
              <span class="user-role">{{ userRoleLabel }}</span>
            </div>
          </div>

          <!-- Logout -->
          <button
            class="nav-logout"
            id="logout-button"
            title="Déconnexion"
            @click="handleLogout"
          >
            <v-icon size="17">mdi-logout-variant</v-icon>
          </button>
        </div>
      </div>
    </v-app-bar>

    <v-main class="main-content">
      <BreadcrumbTrail />

      <div v-if="userStore.isLoading">
        <LoadingModal message="Connexion en cours..." size="medium" id="loading-modal" />
      </div>

      <div v-else>
        <RouterView />
      </div>
    </v-main>
  </v-app>
</template>

<style scoped>
/* ── Main layout ─────────────────────────────────────── */
.main-content {
  min-height: calc(100vh - 64px);
  background: var(--gradient-background);
}

/* ── Navbar override ─────────────────────────────────── */
:deep(.v-app-bar.custom-navbar) {
  background: rgba(255, 255, 255, 0.96) !important;
  border-bottom: 1px solid var(--border-color) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
}

:deep(.v-app-bar.custom-navbar .v-toolbar__content) {
  padding: 0 !important;
}

/* ── Navbar inner ────────────────────────────────────── */
.navbar-inner {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  padding: 0 24px;
  gap: 12px;
}

.nav-spacer {
  flex: 1;
}

/* ── Brand ───────────────────────────────────────────── */
.nav-brand {
  display: flex;
  align-items: center;
  gap: 9px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px 10px;
  border-radius: 8px;
  transition: background 0.15s ease;
  text-decoration: none;
  flex-shrink: 0;
}

.nav-brand:hover {
  background: var(--primary-soft-color);
}

.brand-text {
  font-size: 1.1rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text-color);
}

.brand-accent {
  color: var(--primary-color);
}

/* ── Right zone ──────────────────────────────────────── */
.nav-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* ── Admin pill ──────────────────────────────────────── */
.nav-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 13px;
  border-radius: 8px;
  font-size: 0.78rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background 0.15s ease;
}

.nav-pill--admin {
  background: color-mix(in srgb, var(--primary-color) 10%, transparent);
  color: var(--primary-color);
  border: 1px solid color-mix(in srgb, var(--primary-color) 22%, transparent);
}

.nav-pill--admin:hover {
  background: color-mix(in srgb, var(--primary-color) 18%, transparent);
}

/* ── User pill ───────────────────────────────────────── */
.nav-user-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 14px 5px 5px;
  background: var(--background-color);
  border: 1px solid var(--border-color);
  border-radius: 40px;
}

.user-avatar-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--gradient-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.7rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: 0.03em;
}

.user-details {
  display: flex;
  flex-direction: column;
  line-height: 1.15;
}

.user-name {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text-color);
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-role {
  font-size: 0.68rem;
  color: var(--text-muted-color);
}

/* ── Logout button ───────────────────────────────────── */
.nav-logout {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  cursor: pointer;
  color: var(--text-muted-color);
  transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
  flex-shrink: 0;
}

.nav-logout:hover {
  background: color-mix(in srgb, var(--danger-color) 8%, transparent);
  border-color: var(--danger-color);
  color: var(--danger-color);
}

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 600px) {
  .navbar-inner {
    padding: 0 14px;
  }

  .user-details {
    display: none;
  }

  .nav-user-pill {
    padding: 5px;
    border-radius: 50%;
  }
}
</style>
