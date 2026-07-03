<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { statisticService } from '@/services/statisticService'
import type { BestStudent } from '@/types'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

type Props = {
  classeId: number
  limit?: number
}

const props = withDefaults(defineProps<Props>(), {
  limit: 5,
})

const students = ref<BestStudent[]>([])
const loading = ref(false)
const error = ref<unknown>(null)

onMounted(async () => {
  loading.value = true
  try {
    students.value = await statisticService.getBestStudents(props.classeId, props.limit)
  } catch (err) {
    error.value = err
  } finally {
    loading.value = false
  }
})

function rankColor(rank: number): string {
  switch (rank) {
    case 1:
      return 'amber-darken-2'
    case 2:
      return 'grey'
    case 3:
      return 'deep-orange'
    default:
      return 'primary'
  }
}

function progressColor(pct: number): string {
  if (pct >= 80) return 'success'
  if (pct >= 50) return 'warning'
  return 'error'
}
</script>

<template>
  <v-card elevation="2" rounded="lg" class="mb-6">
    <v-card-title class="pa-4 pb-0 d-flex align-center">
      <v-icon color="amber-darken-2" class="mr-2">mdi-trophy</v-icon>
      <span class="text-h6 font-weight-bold">Meilleurs élèves</span>
      <v-spacer />
      <v-chip size="small" color="primary" variant="tonal"> Top {{ props.limit }} </v-chip>
    </v-card-title>

    <v-card-text class="pa-4">
      <div v-if="loading" class="d-flex align-center justify-center py-8">
        <v-progress-circular indeterminate color="primary" />
      </div>

      <StatusAlert v-else-if="error" v-model:error="error" />

      <div v-else-if="students.length === 0" class="text-center py-8">
        <v-icon size="48" color="grey-lighten-1" class="mb-2">mdi-account-off-outline</v-icon>
        <div class="text-body-2 text-grey-darken-1">
          Aucune donnée disponible pour cette classe.
        </div>
      </div>

      <v-list data-testid="student-list" v-else>
        <v-list-item v-for="student in students" :key="student.rank" class="px-0 py-2">
          <template #prepend>
            <v-avatar :color="rankColor(student.rank)" size="36" class="mr-3">
              <span class="text-caption font-weight-bold text-white">
                {{ student.firstname[0] }}{{ student.name[0] }}
              </span>
            </v-avatar>
          </template>

          <v-list-item-title class="font-weight-medium">
            {{ student.firstname }} {{ student.name }}
          </v-list-item-title>

          <v-list-item-subtitle class="mt-1">
            <v-chip
              v-if="student.topSubject"
              data-testid="top-subject-chip"
              size="x-small"
              color="primary"
              variant="tonal"
              class="mr-2"
            >
              {{ student.topSubject }}
            </v-chip>
            <span class="text-caption text-grey-darken-1">
              {{ student.completedCourses }}/{{ student.totalCourses }} cours terminés
            </span>
          </v-list-item-subtitle>

          <template #append>
            <div class="d-flex align-center ga-2" style="min-width: 180px">
              <v-progress-linear
                :model-value="student.average"
                :color="progressColor(student.average)"
                rounded
                height="8"
                style="flex: 1"
              />
              <span
                class="text-caption font-weight-bold"
                style="min-width: 40px; text-align: right"
              >
                {{ student.average }}%
              </span>
              <v-icon v-if="student.rank <= 3" size="20" :color="rankColor(student.rank)">
                mdi-medal
              </v-icon>
            </div>
          </template>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>
