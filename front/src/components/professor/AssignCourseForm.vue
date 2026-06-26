<template>
  <div class="pa-6" style="max-width: 500px; margin: 0 auto; background: white; border-radius: 8px;">
    <h2 class="text-h5 font-weight-bold mb-6">Assigner un cours à une classe</h2>

    <v-form @submit.prevent="submitForm">
      <v-select
        v-model="form.cours_id"
        :items="courses"
        item-title="label"
        item-value="id"
        label="Cours *"
        required
        variant="outlined"
        class="mb-4"
        :loading="loadingData"
        no-data-text="Aucun cours disponible"
      />

      <v-select
        v-model="form.classe_id"
        :items="classes"
        item-title="nom"
        item-value="id"
        label="Classe *"
        required
        variant="outlined"
        class="mb-4"
        :loading="loadingData"
        no-data-text="Aucune classe disponible"
      />

      <div class="d-flex gap-3 mb-4">
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

      <v-alert v-if="errorMessage" type="error" class="mt-2">{{ errorMessage }}</v-alert>
      <v-alert v-if="successMessage" type="success" class="mt-2">{{ successMessage }}</v-alert>
    </v-form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import type { AssignCourseData, ProfessorCourse, Classe } from '@/types'

const router = useRouter()

const form = ref<AssignCourseData>({ cours_id: 0, classe_id: 0 })
const rawCourses = ref<ProfessorCourse[]>([])
const classes = ref<Classe[]>([])
const loading = ref(false)
const loadingData = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const courses = computed(() =>
  rawCourses.value.map(c => ({
    id: c.id,
    label: `${c.matiere.libelle}${c.difficulte ? ` — ${c.difficulte.label}` : ''}`,
  }))
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
  } catch {
    errorMessage.value = 'Erreur lors du chargement des données'
  } finally {
    loadingData.value = false
  }
})

async function submitForm() {
  if (!form.value.cours_id || !form.value.classe_id) return

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await courseService.assignCourseToClass(form.value)
    successMessage.value = 'Cours assigné avec succès !'
    setTimeout(() => router.push('/'), 1500)
  } catch (error: unknown) {
    const err = error as { response?: { data?: { error?: string } } }
    errorMessage.value = err.response?.data?.error || "Erreur lors de l'assignation du cours"
  } finally {
    loading.value = false
  }
}
</script>
