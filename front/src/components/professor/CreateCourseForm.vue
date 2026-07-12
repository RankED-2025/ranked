<template>
  <div class="create-course-shell">
    <v-form @submit.prevent="submitForm" class="form-col">
      <div class="form-section">
        <h4>Détails du cours</h4>
        <v-text-field
          v-model="form.title"
          label="Titre *"
          variant="outlined"
          class="mb-4"
          :rules="required"
        />

        <v-textarea
          v-model="form.description"
          label="Description du cours *"
          variant="outlined"
          rows="3"
          auto-grow
          :rules="required"
        />
      </div>

      <div class="form-section">
        <h4>Classification</h4>
        <div class="field-row">
          <v-select
            v-model="form.matiere_id"
            :items="matieres"
            item-title="libelle"
            item-value="id"
            label="Matière *"
            :loading="loadingData"
            :disabled="loadingData"
            variant="outlined"
            :rules="required"
            no-data-text="Aucune matière disponible"
          />

          <v-select
            v-model="form.difficulte_id"
            :items="difficulties"
            item-title="label"
            item-value="id"
            label="Difficulté *"
            :loading="loadingData"
            :disabled="loadingData"
            variant="outlined"
            :rules="required"
            no-data-text="Aucune difficulté disponible"
          />
        </div>
      </div>

      <div class="d-flex ga-3">
        <v-btn
          type="submit"
          color="primary"
          :loading="loading"
          :disabled="!isSubmittable"
        >
          Créer le cours
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
        :title="form.title ?? ''"
        :description="form.description ?? ''"
        :matiere="selectedMatiere"
        :difficulte="selectedDifficulte"
        :accent="previewAccent"
        :show-actions="false"
        :clickable="false"
      />
      <p class="preview-hint">L'aperçu se met à jour à mesure que vous remplissez le formulaire.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import { referentielService } from '@/services/referentielService'
import type { CreateCourseData, Matiere, Difficulte } from '@/types'
import { required } from '@/utils/validation'
import { getSubjectAccent } from '@/utils'
import StatusAlert from '@/components/layouts/StatusAlert.vue'
import CourseCard from '@/components/professor/CourseCard.vue'

const router = useRouter()

const form = ref<Partial<CreateCourseData>>({
  title: '',
  description: '',
  matiere_id: undefined,
  difficulte_id: undefined,
})

const matieres = ref<Matiere[]>([])
const difficulties = ref<Difficulte[]>([])
const loading = ref(false)
const loadingData = ref(false)
const loadError = ref<unknown>(null)
const submitError = ref<unknown>(null)
const successMessage = ref('')

const isSubmittable = computed(
  () =>
    !!form.value.title &&
    !!form.value.description &&
    !!form.value.matiere_id &&
    !!form.value.difficulte_id &&
    !loading.value,
)

const selectedMatiere = computed<Matiere | null>(
  () => matieres.value.find((matiere) => matiere.id === form.value.matiere_id) ?? null,
)

const selectedDifficulte = computed<Difficulte | null>(
  () => difficulties.value.find((difficulte) => difficulte.id === form.value.difficulte_id) ?? null,
)

const previewAccent = computed(() =>
  selectedMatiere.value ? getSubjectAccent(selectedMatiere.value.id) : 'var(--border-strong-color)',
)

onMounted(async () => {
  loadingData.value = true
  try {
    const [matieresData, difficultesData] = await Promise.all([
      referentielService.getMatieres(),
      referentielService.getDifficultes(),
    ])
    matieres.value = matieresData
    difficulties.value = difficultesData
  } catch (error) {
    loadError.value = error
  } finally {
    loadingData.value = false
  }
})

async function submitForm() {
  if (!isSubmittable.value) return

  loading.value = true
  submitError.value = null
  successMessage.value = ''

  try {
    await courseService.createCourse(form.value as CreateCourseData)
    successMessage.value = 'Cours créé avec succès !'
    setTimeout(() => router.push('/'), 1500)
  } catch (error: unknown) {
    submitError.value = error
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.create-course-shell {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 22px;
  align-items: start;
}

@media (max-width: 860px) {
  .create-course-shell {
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

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

@media (max-width: 480px) {
  .field-row {
    grid-template-columns: 1fr;
  }
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

.preview-hint {
  font-size: 12px;
  color: var(--text-light-color);
  margin-top: 10px;
  line-height: 1.5;
}
</style>
