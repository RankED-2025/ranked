<template>
  <div class="assign-shell">
    <v-form @submit.prevent="submitForm" class="form-col">
      <div class="form-section">
        <h4>Sélection</h4>
        <v-select
          v-model="form.cours_id"
          :items="courses"
          item-title="label"
          item-value="id"
          label="Cours *"
          variant="outlined"
          class="mb-4"
          :loading="loadingData"
          :disabled="loadingData"
          no-data-text="Aucun cours disponible"
        />

        <v-select
          v-model="form.classe_id"
          :items="classes"
          item-title="nom"
          item-value="id"
          label="Classe *"
          variant="outlined"
          :loading="loadingData"
          :disabled="loadingData"
          no-data-text="Aucune classe disponible"
        />
      </div>

      <div class="d-flex ga-3">
        <v-btn
          type="submit"
          color="primary"
          :loading="loading"
          :disabled="!form.cours_id || !form.classe_id"
        >
          Assigner le cours
        </v-btn>
        <v-btn variant="tonal" @click="router.push('/')">Annuler</v-btn>
      </div>

      <StatusAlert v-model:error="loadError" test-id="load-error-message" />
      <StatusAlert v-model:error="submitError" test-id="submit-error-message" />
      <v-alert v-if="successMessage" type="success" class="mt-2">{{ successMessage }}</v-alert>
    </v-form>

    <div class="preview-col">
      <p class="preview-label">Aperçu</p>

      <CourseCard
        v-if="selectedCourse"
        :title="selectedCourse.title"
        :description="selectedCourse.description"
        :matiere="selectedCourse.matiere"
        :difficulte="selectedCourse.difficulte"
        :accent="previewAccent"
        :show-actions="false"
        :clickable="false"
      />
      <div v-else class="empty-slot">
        <v-icon size="18">mdi-book-outline</v-icon>
        Sélectionnez un cours
      </div>

      <div class="connector">
        <v-icon size="16">mdi-arrow-down</v-icon>
      </div>

      <div class="class-chip" :class="{ 'is-filled': !!selectedClass }">
        <v-icon size="16">mdi-account-group-outline</v-icon>
        {{ selectedClass?.nom ?? 'Sélectionnez une classe' }}
      </div>

      <p class="preview-hint">
        {{
          selectedCourse && selectedClass
            ? `Ce cours sera visible par les élèves de la classe « ${selectedClass.nom} ».`
            : 'Choisissez un cours et une classe pour prévisualiser l\'attribution.'
        }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import type { AssignCourseData, ProfessorCourse, Classe } from '@/types'
import { getSubjectAccent } from '@/utils'
import StatusAlert from '@/components/layouts/StatusAlert.vue'
import CourseCard from '@/components/professor/CourseCard.vue'

const router = useRouter()

const form = ref<Partial<AssignCourseData>>({ cours_id: undefined, classe_id: undefined })
const rawCourses = ref<ProfessorCourse[]>([])
const classes = ref<Classe[]>([])
const loading = ref(false)
const loadingData = ref(false)
const loadError = ref<unknown>(null)
const submitError = ref<unknown>(null)
const successMessage = ref('')

const courses = computed(() =>
  rawCourses.value.map((c) => ({
    id: c.id,
    label: `${c.matiere.libelle}${c.difficulte ? ` — ${c.difficulte.label}` : ''}`,
  })),
)

const selectedCourse = computed<ProfessorCourse | null>(
  () => rawCourses.value.find((c) => c.id === form.value.cours_id) ?? null,
)

const selectedClass = computed<Classe | null>(
  () => classes.value.find((c) => c.id === form.value.classe_id) ?? null,
)

const previewAccent = computed(() =>
  selectedCourse.value ? getSubjectAccent(selectedCourse.value.matiere.id) : 'var(--border-strong-color)',
)

onMounted(async () => {
  loadingData.value = true
  try {
    const [coursesData, classesData] = await Promise.all([
      courseService.getProfessorCourses(),
      courseService.getProfessorClasses(),
    ])
    rawCourses.value = coursesData
    classes.value = classesData
  } catch (error) {
    loadError.value = error
  } finally {
    loadingData.value = false
  }
})

async function submitForm() {
  if (!form.value.cours_id || !form.value.classe_id) return

  loading.value = true
  submitError.value = null
  successMessage.value = ''

  try {
    await courseService.assignCourseToClass(form.value as AssignCourseData)
    successMessage.value = 'Cours assigné avec succès !'
    setTimeout(() => router.push('/'), 1500)
  } catch (error: unknown) {
    submitError.value = error
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.assign-shell {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 22px;
  align-items: start;
}

@media (max-width: 720px) {
  .assign-shell {
    grid-template-columns: 1fr;
  }
}

.form-col {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-section {
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: var(--surface-color);
  padding: 18px 20px 4px;
}

.form-section h4 {
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-light-color);
  margin: 0 0 14px;
}

.preview-col {
  position: sticky;
  top: 20px;
  display: flex;
  flex-direction: column;
}

.preview-label {
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-light-color);
  margin: 0 0 10px;
  display: flex;
  align-items: center;
  gap: 7px;
}

.preview-label::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border-color);
}

.empty-slot {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px dashed var(--border-strong-color);
  border-radius: 10px;
  padding: 16px;
  color: var(--text-light-color);
  font-size: 13px;
}

.connector {
  display: flex;
  justify-content: center;
  color: var(--text-light-color);
  padding: 6px 0;
}

.class-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--border-color);
  background: var(--surface-color);
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-light-color);
}

.class-chip.is-filled {
  border-color: var(--primary-color);
  background: var(--primary-soft-color);
  color: var(--primary-color);
}

.preview-hint {
  font-size: 12px;
  color: var(--text-light-color);
  margin-top: 10px;
  line-height: 1.5;
}
</style>
