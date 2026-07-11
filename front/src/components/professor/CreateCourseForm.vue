<template>
  <v-card elevation="2" rounded="lg" class="pa-6">
    <v-form @submit.prevent="submitForm">
      <v-text-field
        v-model="form.title"
        label="Titre *"
        variant="outlined"
        class="mb-4"
        :rules="required"
      />

      <v-text-field
        v-model="form.description"
        label="Description du cours *"
        variant="outlined"
        class="mb-4"
        :rules="required"
      />

      <v-select
        v-model="form.matiere_id"
        :items="matieres"
        item-title="libelle"
        item-value="id"
        label="Matière *"
        :loading="loadingData"
        :disabled="loadingData"
        variant="outlined"
        class="mb-4"
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
        class="mb-4"
        :rules="required"
        no-data-text="Aucune difficulté disponible"
      />

      <div class="d-flex gap-3 mb-4">
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
  </v-card>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import { referentielService } from '@/services/referentielService'
import type { CreateCourseData, Matiere, Difficulte } from '@/types'
import { required } from '@/utils/validation'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

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
/* card styling handled by v-card */
</style>
