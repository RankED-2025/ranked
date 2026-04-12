<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import type { ClassDetail, ClassStudent } from '@/types'

const route = useRoute()
const router = useRouter()
const classDetail = ref<ClassDetail | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)

onMounted(async () => {
  loading.value = true
  try {
    classDetail.value = await courseService.getProfessorClassDetail(Number(route.params.id))
  } catch {
    error.value = 'Impossible de charger les données de la classe.'
  } finally {
    loading.value = false
  }
})

// Extract unique courses from all students' progressions
const assignedCourses = computed(() => {
  if (!classDetail.value) return []
  const seen = new Set<number>()
  const courses: { id: number; matiere: { id: number; libelle: string } }[] = []
  for (const student of classDetail.value.students) {
    for (const prog of student.progressions) {
      if (prog.cours && !seen.has(prog.cours.id)) {
        seen.add(prog.cours.id)
        courses.push(prog.cours)
      }
    }
  }
  return courses
})

// For a given course, get progressions across all students
function studentProgressionsForCourse(courseId: number) {
  if (!classDetail.value) return []
  return classDetail.value.students.map((student: ClassStudent) => {
    const prog = student.progressions.find(p => p.cours?.id === courseId)
    return {
      id: student.id,
      name: student.name,
      firstname: student.firstname,
      percentage: prog?.percentage ?? null,
      badge: prog?.badge ?? null,
    }
  })
}

function progressColor(pct: number | null) {
  if (pct === null) return 'grey'
  if (pct >= 100) return 'success'
  if (pct >= 50) return 'warning'
  return 'error'
}
</script>

<template>
  <div class="class-detail-view">
    <v-container class="py-8">
      <div class="d-flex align-center mb-6">
        <v-btn icon variant="text" @click="router.back()" class="mr-2">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <h1 class="text-h4 font-weight-bold gradient-text">
          {{ classDetail?.nom ?? 'Classe' }}
        </h1>
      </div>

      <v-progress-circular v-if="loading" indeterminate color="primary" class="d-block mx-auto" />

      <v-alert v-else-if="error" type="error" rounded="lg">{{ error }}</v-alert>

      <template v-else-if="classDetail">
        <!-- No courses assigned yet -->
        <v-card v-if="assignedCourses.length === 0" elevation="1" rounded="lg" class="text-center pa-8 mb-6">
          <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-book-open-outline</v-icon>
          <div class="text-h6 text-grey-darken-1 mb-2">Aucun cours assigné à cette classe</div>
          <div class="text-body-2 text-grey mb-4">Assignez un cours à cette classe pour suivre la progression des élèves.</div>
          <v-btn color="primary" variant="tonal" @click="router.push('/professor/assign-course')">
            Assigner un cours
          </v-btn>
        </v-card>

        <!-- One card per assigned course -->
        <v-card
          v-for="course in assignedCourses"
          :key="course.id"
          elevation="2"
          rounded="lg"
          class="mb-6"
        >
          <v-card-title class="pa-4 pb-0 d-flex align-center">
            <v-icon color="primary" class="mr-2">mdi-book-open-page-variant</v-icon>
            <span class="text-h6 font-weight-bold">{{ course.matiere.libelle }}</span>
            <v-spacer />
            <v-chip size="small" color="primary" variant="tonal">
              {{ classDetail.students.length }} élève{{ classDetail.students.length > 1 ? 's' : '' }}
            </v-chip>
          </v-card-title>

          <v-card-text class="pa-4">
            <v-list>
              <v-list-item
                v-for="student in studentProgressionsForCourse(course.id)"
                :key="student.id"
                class="px-0 py-2"
              >
                <template #prepend>
                  <v-avatar color="primary" size="36" class="mr-3">
                    <span class="text-caption font-weight-bold text-white">
                      {{ student.firstname[0] }}{{ student.name[0] }}
                    </span>
                  </v-avatar>
                </template>

                <v-list-item-title class="font-weight-medium">
                  {{ student.firstname }} {{ student.name }}
                </v-list-item-title>

                <template #append>
                  <div class="d-flex align-center gap-2" style="min-width: 180px;">
                    <v-progress-linear
                      :model-value="student.percentage ?? 0"
                      :color="progressColor(student.percentage)"
                      rounded
                      height="8"
                      style="flex: 1;"
                    />
                    <span class="text-caption font-weight-bold" style="min-width: 36px; text-align: right;">
                      {{ student.percentage !== null ? `${student.percentage}%` : '—' }}
                    </span>
                    <v-icon
                      v-if="student.badge"
                      size="20"
                      :color="student.badge.type === 'gold' ? 'amber-darken-2' : student.badge.type === 'silver' ? 'grey' : 'deep-orange'"
                      :title="student.badge.label"
                    >
                      mdi-medal
                    </v-icon>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </template>
    </v-container>
  </div>
</template>

<style scoped>
.class-detail-view {
  min-height: calc(100vh - 64px);
  background: var(--gradient-background);
}

.gradient-text {
  background: var(--gradient-primary);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
