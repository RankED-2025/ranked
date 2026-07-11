<script setup lang="ts">
import { computed } from 'vue'
import { useAuth } from '@/composables'
import { useRouter } from 'vue-router'
import { isProfesseur } from '@/utils'

const { user, isAuthenticated } = useAuth()
const router = useRouter()

const isProfessor = computed(() => isProfesseur(user.value?.roles ?? []))

const redirectTo = (path: string) => router.push(path)

const displayName = computed(() => {
  const u = user.value
  if (u?.firstname && u?.name) return `${u.firstname} ${u.name}`
  if (u?.firstname) return u.firstname
  if (u?.name) return u.name
  return u?.email ?? ''
})

interface DashboardCard {
  icon: string
  title: string
  description: string
  path: string
  color: string
  bg: string
  condition?: boolean
  disabled?: boolean
}

const professorCards = computed<DashboardCard[]>(() => [
  {
    icon: 'mdi-book-open-page-variant',
    title: 'Mes cours',
    description: 'Gérez et consultez vos cours créés',
    path: '/professor/my-courses',
    color: '#2e3c88',
    bg: 'rgba(46,60,136,0.08)',
  },
  {
    icon: 'mdi-plus-circle-outline',
    title: 'Créer un cours',
    description: 'Concevez un nouveau cours pour vos élèves',
    path: '/professor/create-course',
    color: '#0c7c59',
    bg: 'rgba(12,124,89,0.08)',
  },
  {
    icon: 'mdi-link-variant',
    title: 'Assigner un cours',
    description: 'Attribuez un cours à une classe',
    path: '/professor/assign-course',
    color: '#6a3db5',
    bg: 'rgba(106,61,181,0.08)',
  },
  {
    icon: 'mdi-account-group',
    title: 'Mes classes',
    description: 'Cours assignés et progressions',
    path: '/professor/classes',
    color: '#c57c00',
    bg: 'rgba(197,124,0,0.08)',
  },
  {
    icon: 'mdi-chart-bar',
    title: 'Statistiques',
    description: 'Statistiques globales et performances',
    path: '/stats',
    color: '#b02e0c',
    bg: 'rgba(176,46,12,0.08)',
  },
])

const studentCards = computed<DashboardCard[]>(() => [
  {
    icon: 'mdi-book-open-page-variant',
    title: 'Mes cours',
    description: 'Accédez à vos cours assignés',
    path: '/my-courses',
    color: '#2e3c88',
    bg: 'rgba(46,60,136,0.08)',
  },
  {
    icon: 'mdi-chart-line',
    title: 'Ma progression',
    description: 'Suivez votre avancement',
    path: '/my-courses',
    color: '#0c7c59',
    bg: 'rgba(12,124,89,0.08)',
  },
  {
    icon: 'mdi-trophy-outline',
    title: 'Badges & Compétences',
    description: 'Vos récompenses et acquis',
    path: '/my-badges-competences',
    color: '#c57c00',
    bg: 'rgba(197,124,0,0.08)',
  },
  {
    icon: 'mdi-chart-bar',
    title: 'Statistiques',
    description: 'Consultez vos statistiques personnelles',
    path: '/stats',
    color: '#b02e0c',
    bg: 'rgba(176,46,12,0.08)',
  },
])

const activeCards = computed(() => (isProfessor.value ? professorCards.value : studentCards.value))
</script>

<template>
  <div class="home-view">
    <div v-if="isAuthenticated" class="dashboard-wrapper">

      <!-- Hero Banner -->
      <div class="hero-banner">
        <div class="hero-content">
          <div class="hero-left">
            <div class="role-chip">
              <v-icon size="14" class="mr-1">{{ isProfessor ? 'mdi-school' : 'mdi-account-school' }}</v-icon>
              {{ isProfessor ? 'Professeur' : 'Élève' }}
            </div>
            <h1 class="hero-title">Bienvenue, <span class="hero-name">{{ displayName }}</span></h1>
            <p class="hero-subtitle">
              {{ isProfessor
                ? 'Gérez vos cours, suivez la progression de vos élèves et créez du contenu pédagogique.'
                : 'Accédez à vos cours, suivez votre progression et consultez vos badges.'
              }}
            </p>
          </div>
          <div class="hero-illustration">
            <div class="illustration-ring ring-1"></div>
            <div class="illustration-ring ring-2"></div>
            <div class="illustration-ring ring-3"></div>
            <v-icon class="illustration-icon">{{ isProfessor ? 'mdi-school' : 'mdi-account-student' }}</v-icon>
          </div>
        </div>
        <svg class="hero-wave" viewBox="0 0 1440 56" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,28 C240,56 480,0 720,28 C960,56 1200,0 1440,28 L1440,56 L0,56 Z" fill="var(--background-color)"/>
        </svg>
      </div>

      <!-- Dashboard Content -->
      <div class="dashboard-content">

        <!-- Section header -->
        <div class="section-header">
          <div class="section-header-left">
            <div class="section-icon-wrap">
              <v-icon size="15">mdi-view-grid-outline</v-icon>
            </div>
            <div class="section-meta">
              <h2 class="section-title">Accès rapide</h2>
              <p class="section-subtitle">
                {{ isProfessor ? 'Gérez vos outils de gestion en un clic' : 'Naviguez vers vos espaces de travail' }}
              </p>
            </div>
          </div>
          <span class="section-count">{{ activeCards.length }}</span>
        </div>

        <!-- Cards Grid -->
        <div class="cards-grid">
          <div
            v-for="card in activeCards"
            :key="card.path + card.title"
            class="dash-card"
            @click="redirectTo(card.path)"
          >
            <div class="card-bar" :style="{ background: card.color }"></div>
            <div class="card-icon" :style="{ background: card.bg }">
              <v-icon :color="card.color" size="19">{{ card.icon }}</v-icon>
            </div>
            <div class="card-body">
              <span class="card-title">{{ card.title }}</span>
              <span class="card-desc">{{ card.description }}</span>
            </div>
            <v-icon class="card-arrow" size="16">mdi-chevron-right</v-icon>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── Layout ─────────────────────────────────────────── */
.home-view {
  min-height: calc(100vh - 64px);
  background: var(--background-color);
}

.dashboard-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* ── Hero Banner ─────────────────────────────────────── */
.hero-banner {
  background: var(--gradient-primary);
  padding: 52px 40px 72px;
  position: relative;
  overflow: hidden;
}

.hero-wave {
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 100%;
  height: 56px;
  display: block;
}

.hero-banner::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.hero-content {
  position: relative;
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
}

.hero-left {
  flex: 1;
}

.role-chip {
  display: inline-flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.28);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 4px 12px;
  border-radius: 20px;
  backdrop-filter: blur(4px);
  margin-bottom: 16px;
}

.hero-title {
  font-size: clamp(1.6rem, 3vw, 2.2rem);
  font-weight: 700;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.2;
  margin-bottom: 12px;
}

.hero-name {
  color: #fff;
}

.hero-subtitle {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.72);
  max-width: 480px;
  line-height: 1.6;
  margin: 0;
}

/* Decorative illustration */
.hero-illustration {
  position: relative;
  width: 130px;
  height: 130px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.illustration-ring {
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.ring-1 { width: 130px; height: 130px; }
.ring-2 { width: 95px; height: 95px; border-color: rgba(255,255,255,0.22); }
.ring-3 { width: 62px; height: 62px; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.32); }

.illustration-icon {
  font-size: 34px !important;
  color: rgba(255, 255, 255, 0.9) !important;
  z-index: 1;
}

/* ── Dashboard Content ───────────────────────────────── */
.dashboard-content {
  max-width: 900px;
  margin: 0 auto;
  width: 100%;
  padding: 32px 40px 48px;
}

/* ── Section Header ──────────────────────────────────── */
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 13px 16px;
  background: #fff;
  border: 1px solid var(--border-color);
  border-left: 3px solid var(--primary-color);
  border-radius: 10px;
  box-shadow: var(--shadow-sm);
  margin-bottom: 12px;
}

.section-header-left {
  display: flex;
  align-items: center;
  gap: 11px;
}

.section-icon-wrap {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--primary-soft-color);
  color: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.section-meta {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.section-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text-color);
  margin: 0;
  line-height: 1.3;
  letter-spacing: -0.01em;
}

.section-subtitle {
  font-size: 0.7rem;
  color: var(--text-muted-color);
  margin: 0;
  line-height: 1.3;
}

.section-count {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--text-muted-color);
  background: var(--background-color);
  border: 1px solid var(--border-color);
  padding: 3px 10px;
  border-radius: 20px;
  line-height: 1.4;
  flex-shrink: 0;
}

/* ── Cards Grid ──────────────────────────────────────── */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

/* ── Dash Card ───────────────────────────────────────── */
.dash-card {
  position: relative;
  display: flex;
  align-items: center;
  gap: 13px;
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 13px 14px 13px 18px;
  cursor: pointer;
  overflow: hidden;
  transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
  box-shadow: var(--shadow-sm);
}

.dash-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  border-color: var(--border-strong-color);
}

.dash-card:hover .card-arrow {
  transform: translateX(2px);
  opacity: 1;
}

/* Colored left bar – always visible */
.card-bar {
  position: absolute;
  left: 0;
  top: 10px;
  bottom: 10px;
  width: 3px;
  border-radius: 0 3px 3px 0;
}

/* Icon */
.card-icon {
  width: 38px;
  height: 38px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Body */
.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.card-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text-color);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
}

.card-desc {
  font-size: 0.72rem;
  color: var(--text-muted-color);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
}

/* Arrow */
.card-arrow {
  flex-shrink: 0;
  color: var(--text-light-color) !important;
  opacity: 0.5;
  transition: transform 0.16s ease, opacity 0.16s ease;
}

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 768px) {
  .hero-banner {
    padding: 36px 20px 60px;
  }

  .hero-illustration {
    display: none;
  }

  .dashboard-content {
    padding: 20px 16px 40px;
  }

  .cards-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 1.4rem;
  }
}
</style>
