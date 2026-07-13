<template>
  <div class="page-view">
    <v-container class="py-8">
      <PageHeader
        show-back
        icon="mdi-account-group"
        title="Mes classes"
        subtitle="Vos classes et la progression de vos élèves"
        @back="router.back()"
      />

      <div v-if="loading" class="class-grid">
        <v-skeleton-loader v-for="n in 3" :key="n" type="card" />
      </div>

      <StatusAlert v-else-if="error" v-model:error="error" />

      <div v-else-if="classes.length > 0" class="class-grid">
        <div
          v-for="classe in classes"
          :key="classe.id"
          class="class-card"
          @click="goToClass(classe.id)"
        >
          <div class="class-card-top">
            <div>
              <p class="class-name">{{ classe.nom }}</p>
              <p class="class-sub">
                <v-icon size="13">mdi-account-group-outline</v-icon>
                {{ classe.studentCount }} élève{{ classe.studentCount > 1 ? 's' : '' }}
              </p>
            </div>

            <v-progress-circular
              v-if="classe.averagePercentage !== null"
              :model-value="classe.averagePercentage"
              :color="progressColor(classe.averagePercentage)"
              :size="46"
              :width="5"
            >
              <span class="ring-pct">{{ classe.averagePercentage }}%</span>
            </v-progress-circular>
            <v-progress-circular v-else :model-value="0" color="grey-lighten-1" :size="46" :width="5">
              <span class="ring-pct">—</span>
            </v-progress-circular>
          </div>

          <template v-if="classe.courseCount > 0">
            <div class="class-stats">
              <div class="class-stat">
                <div class="num">{{ classe.courseCount }}</div>
                <div class="lbl">Cours</div>
              </div>
              <div class="class-stat">
                <div class="num">{{ classe.studentsAtLeast50 }}</div>
                <div class="lbl">≥ 50%</div>
              </div>
              <div class="class-stat">
                <div class="num">{{ classe.studentsAt100 }}</div>
                <div class="lbl">100%</div>
              </div>
            </div>
            <div class="class-card-cta">
              Voir le détail
              <v-icon size="15">mdi-arrow-right</v-icon>
            </div>
          </template>

          <template v-else>
            <span class="empty-course-note">
              <v-icon size="11">mdi-information-outline</v-icon>
              Aucun cours assigné
            </span>
            <div class="class-card-cta" @click.stop="router.push('/professor/assign-course')">
              Assigner un cours
              <v-icon size="15">mdi-arrow-right</v-icon>
            </div>
          </template>
        </div>
      </div>

      <AppCard v-else>
        <EmptyState
          icon="mdi-account-group-outline"
          title="Aucune classe pour le moment"
          description="Vos classes apparaîtront ici une fois créées."
          :icon-size="64"
        />
      </AppCard>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import type { ClassSummary } from '@/types'
import StatusAlert from '@/components/layouts/StatusAlert.vue'
import PageHeader from '@/components/layouts/PageHeader.vue'
import AppCard from '@/components/layouts/AppCard.vue'
import EmptyState from '@/components/layouts/EmptyState.vue'

const router = useRouter()
const classes = ref<ClassSummary[]>([])
const loading = ref(false)
const error = ref<unknown>(null)

onMounted(async () => {
  loading.value = true
  try {
    classes.value = await courseService.getProfessorClasses()
  } catch (err) {
    error.value = err
  } finally {
    loading.value = false
  }
})

const goToClass = (classeId: number) => router.push(`/professor/classes/${classeId}`)

function progressColor(pct: number): string {
  if (pct >= 80) return 'success'
  if (pct >= 50) return 'warning'
  return 'error'
}
</script>

<style scoped>
.page-view {
  min-height: calc(100vh - 64px);
  background: var(--background-color);
}

.class-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}

.class-card {
  background: var(--surface-color);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 18px 18px 16px;
  cursor: pointer;
  transition: box-shadow 0.15s ease, border-color 0.15s ease;
}

.class-card:hover {
  box-shadow: var(--shadow-md);
  border-color: var(--border-strong-color);
}

.class-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 14px;
}

.class-name {
  font-size: 16.5px;
  font-weight: 800;
  margin: 0 0 4px;
}

.class-sub {
  font-size: 12px;
  color: var(--text-light-color);
  display: flex;
  align-items: center;
  gap: 5px;
  margin: 0;
}

.ring-pct {
  font-size: 11px;
  font-weight: 800;
}

.class-stats {
  display: flex;
  gap: 10px;
  margin-bottom: 14px;
}

.class-stat {
  flex: 1;
  background: var(--neutral-50);
  border-radius: 8px;
  padding: 8px 10px;
}

.class-stat .num {
  font-size: 15px;
  font-weight: 800;
  font-variant-numeric: tabular-nums;
}

.class-stat .lbl {
  font-size: 10.5px;
  color: var(--text-light-color);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  font-weight: 700;
}

.class-card-cta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--primary-color);
  padding-top: 12px;
  border-top: 1px solid var(--border-color);
}

.empty-course-note {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-light-color);
  background: var(--neutral-100);
  padding: 3px 9px;
  border-radius: 999px;
  margin-bottom: 14px;
}
</style>
