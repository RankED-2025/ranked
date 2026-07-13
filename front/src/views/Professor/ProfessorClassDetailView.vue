<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import type { ClassDetail, ClassProgression, ProfessorCourse } from '@/types'
import BestStudentsCard from '@/components/professor/BestStudentsCard.vue'
import StatusAlert from '@/components/layouts/StatusAlert.vue'
import { getSubjectAccent } from '@/utils'

type Badge = ClassProgression['badge']

interface StudentCell {
  courseId: number
  percentage: number | null
  badge: Badge
}

interface StudentRow {
  id: number
  name: string
  firstname: string
  average: number | null
  cells: StudentCell[]
}

const route = useRoute()
const router = useRouter()
const classDetail = ref<ClassDetail | null>(null)
const assignedCourses = ref<ProfessorCourse[]>([])
const loading = ref(false)
const error = ref<unknown>(null)
const invalidIdMessage = ref<string | null>(null)

function getClassIdFromRoute() {
  const classId = Number(route.params.id)

  if (!Number.isFinite(classId)) {
    return null
  }

  return classId
}

const classId = computed(() => {
  const id = Number(route.params.id)
  return Number.isFinite(id) ? id : null
})

onMounted(async () => {
  const id = getClassIdFromRoute()

  if (id === null) {
    invalidIdMessage.value = 'Identifiant de classe invalide.'
    return
  }

  loading.value = true
  try {
    const [detail, courses] = await Promise.all([
      courseService.getProfessorClassDetail(id),
      courseService.getProfessorClassCourses(id),
    ])
    classDetail.value = detail
    assignedCourses.value = courses
  } catch (err) {
    error.value = err
  } finally {
    loading.value = false
  }
})

const studentRows = computed<StudentRow[]>(() => {
  if (!classDetail.value) return []

  return classDetail.value.students.map((student) => {
    const progressionByCourseId = new Map<number, { percentage: number | null; badge: Badge }>()

    for (const prog of student.progressions) {
      const courseId = prog.cours?.id
      if (!courseId) continue
      progressionByCourseId.set(courseId, { percentage: prog.percentage ?? null, badge: prog.badge ?? null })
    }

    const cells: StudentCell[] = assignedCourses.value.map((course) => {
      const match = progressionByCourseId.get(course.id)
      return { courseId: course.id, percentage: match?.percentage ?? null, badge: match?.badge ?? null }
    })

    const percentages = cells
      .map((cell) => cell.percentage)
      .filter((p): p is number => p !== null)

    const average = percentages.length > 0
      ? Math.round(percentages.reduce((sum, p) => sum + p, 0) / percentages.length)
      : null

    return { id: student.id, name: student.name, firstname: student.firstname, average, cells }
  })
})

const classAverage = computed<number | null>(() => {
  const averages = studentRows.value
    .map((s) => s.average)
    .filter((a): a is number => a !== null)

  return averages.length > 0
    ? Math.round(averages.reduce((sum, a) => sum + a, 0) / averages.length)
    : null
})

function progressColor(pct: number | null) {
  if (pct === null) return 'grey'
  if (pct >= 100) return 'success'
  if (pct >= 50) return 'warning'
  return 'error'
}

function courseAccent(course: ProfessorCourse): string {
  return course.matiere ? getSubjectAccent(course.matiere.id) : 'var(--border-strong-color)'
}
</script>

<template>
  <div class="class-detail-view">
    <v-container class="py-8">
      <div class="detail-head-row">
        <button class="back-btn" data-testid="back-button" @click="router.back()">
          <v-icon size="18">mdi-arrow-left</v-icon>
        </button>
        <div>
          <h1>{{ classDetail?.nom ?? 'Classe' }}</h1>
          <p v-if="classDetail" class="head-sub">
            {{ classDetail.students.length }} élève{{ classDetail.students.length > 1 ? 's' : '' }}
            · {{ assignedCourses.length }} cours assigné{{ assignedCourses.length > 1 ? 's' : '' }}
            <template v-if="classAverage !== null"> · moyenne de classe {{ classAverage }}%</template>
          </p>
        </div>
      </div>

      <v-progress-circular
        v-if="loading"
        data-testid="loading-spinner"
        indeterminate
        color="primary"
        class="d-block mx-auto"
      />

      <v-alert v-else-if="invalidIdMessage" data-testid="error-alert" type="error" rounded="lg">
        {{ invalidIdMessage }}
      </v-alert>

      <StatusAlert v-else-if="error" v-model:error="error" test-id="error-alert" />

      <template v-else-if="classDetail">
        <BestStudentsCard :classe-id="classId!" />

        <div v-if="assignedCourses.length === 0" class="section-card">
          <div class="empty-state">
            <v-icon size="40">mdi-book-open-outline</v-icon>
            <p class="title">Aucun cours assigné à cette classe</p>
            <p class="desc">Assignez un cours à cette classe pour suivre la progression des élèves.</p>
            <v-btn
              color="primary"
              variant="tonal"
              data-testid="assign-course-button"
              @click="router.push('/professor/assign-course')"
            >
              Assigner un cours
            </v-btn>
          </div>
        </div>

        <div v-else class="section-card">
          <div class="section-head">
            <v-icon size="16">mdi-view-grid-outline</v-icon>
            <h2>Progression par cours</h2>
          </div>

          <p v-if="assignedCourses.length > 4" class="scroll-hint">
            <v-icon size="13">mdi-arrow-right</v-icon>
            Faites défiler horizontalement pour voir tous les cours — le nom de l'élève reste visible
          </p>

          <div class="table-scroll">
            <table class="roster">
              <thead>
                <tr>
                  <th class="student-col">Élève</th>
                  <th v-for="course in assignedCourses" :key="course.id" class="course-col">
                    <span class="course-badge">
                      <span class="dot" :style="{ background: courseAccent(course) }"></span>
                      {{ course.matiere?.libelle }}<template v-if="course.difficulte"> — {{ course.difficulte.label }}</template>
                    </span>
                  </th>
                  <th class="avg-col">Moyenne</th>
                </tr>
              </thead>
              <tbody data-testid="student-list">
                <tr v-for="student in studentRows" :key="student.id">
                  <td class="student-col">
                    <div class="student-cell">
                      <span class="avatar">{{ student.firstname[0] }}{{ student.name[0] }}</span>
                      {{ student.firstname }} {{ student.name }}
                    </div>
                  </td>
                  <td v-for="cell in student.cells" :key="cell.courseId">
                    <div class="cell-progress">
                      <template v-if="cell.percentage !== null">
                        <div class="mini-bar">
                          <div
                            class="fill"
                            :class="`bg-${progressColor(cell.percentage)}`"
                            :style="{ width: `${cell.percentage}%` }"
                          ></div>
                        </div>
                        <span class="cell-pct">{{ cell.percentage }}%</span>
                        <v-icon
                          v-if="cell.badge"
                          size="15"
                          data-testid="badge-icon"
                          :color="
                            cell.badge.type === 'gold'
                              ? 'amber-darken-2'
                              : cell.badge.type === 'silver'
                                ? 'grey'
                                : 'deep-orange'
                          "
                          :title="cell.badge.label"
                        >
                          mdi-medal
                        </v-icon>
                      </template>
                      <span v-else class="cell-empty">—</span>
                    </div>
                  </td>
                  <td class="avg-col">{{ student.average !== null ? `${student.average}%` : '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </v-container>
  </div>
</template>

<style scoped>
.class-detail-view {
  min-height: calc(100vh - 64px);
  background: var(--background-color);
}

.detail-head-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 22px;
}

.back-btn {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background: var(--surface-color);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-muted-color);
  flex-shrink: 0;
}

.back-btn:hover {
  border-color: var(--primary-color);
  color: var(--primary-color);
}

.detail-head-row h1 {
  font-size: 22px;
  font-weight: 800;
  margin: 0;
}

.head-sub {
  font-size: 12.5px;
  color: var(--text-light-color);
  margin: 2px 0 0;
}

.section-card {
  background: var(--surface-color);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  margin-bottom: 20px;
  overflow: hidden;
}

.section-head {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border-color);
}

.section-head h2 {
  font-size: 14px;
  font-weight: 800;
  margin: 0;
}

.section-head .v-icon {
  color: var(--text-light-color);
}

.scroll-hint {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-light-color);
  padding: 8px 18px 0;
  margin: 0;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 44px 20px;
  text-align: center;
  color: var(--border-strong-color);
}

.empty-state .title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-color);
  margin: 6px 0 0;
}

.empty-state .desc {
  font-size: 12.5px;
  color: var(--text-light-color);
  max-width: 34ch;
  margin: 0 0 6px;
}

/* ── Roster table ─────────────────────────────────── */
.table-scroll {
  overflow-x: auto;
}

table.roster {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

table.roster th,
table.roster td {
  padding: 10px 14px;
  text-align: left;
  white-space: nowrap;
}

table.roster thead th {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--text-light-color);
  border-bottom: 1px solid var(--border-color);
  background: var(--neutral-50);
}

table.roster thead th.course-col {
  text-align: center;
}

table.roster thead th .course-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 10.5px;
}

table.roster thead th .course-badge .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

table.roster tbody tr {
  border-bottom: 1px solid var(--border-color);
}

table.roster tbody tr:last-child {
  border-bottom: none;
}

table.roster tbody tr:hover td {
  background: var(--neutral-50);
}

/* Frozen first column so the student stays visible while scrolling
   through course columns — same idea as a spreadsheet's frozen pane. */
.student-col {
  position: sticky;
  left: 0;
  background: var(--surface-color);
  z-index: 1;
}

thead .student-col {
  z-index: 2;
  background: var(--neutral-50);
}

table.roster tbody tr:hover .student-col {
  background: var(--neutral-50);
}

.student-col::after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 1px;
  background: var(--border-color);
}

.student-cell {
  display: flex;
  align-items: center;
  gap: 9px;
}

.avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--primary-soft-color);
  color: var(--primary-color);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 10.5px;
  font-weight: 800;
  flex-shrink: 0;
}

.cell-progress {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: center;
}

.mini-bar {
  width: 46px;
  height: 6px;
  border-radius: 999px;
  background: var(--neutral-100);
  overflow: hidden;
}

.mini-bar .fill {
  height: 100%;
  border-radius: 999px;
}

.mini-bar .fill.bg-success {
  background: var(--success-color);
}

.mini-bar .fill.bg-warning {
  background: var(--warning-color);
}

.mini-bar .fill.bg-error {
  background: var(--danger-color);
}

.cell-pct {
  font-size: 11.5px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  width: 30px;
}

.cell-empty {
  color: var(--text-light-color);
  font-size: 12px;
}

.avg-col {
  text-align: center;
  font-weight: 800;
  font-variant-numeric: tabular-nums;
}
</style>
