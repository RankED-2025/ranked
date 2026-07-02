<template>
  <div class="pa-6" style="max-width: 500px; margin: 0 auto; background: white; border-radius: 8px">
    <h2 class="text-h5 font-weight-bold mb-6">Créer un nouveau cours</h2>

    <v-form @submit.prevent="submitForm">
      <v-text-field v-model="form.title" label="Titre *" required variant="outlined" class="mb-4" />

      <v-text-field
        v-model="form.description"
        label="Description du cours *"
        required
        variant="outlined"
        class="mb-4"
      />

      <v-select
        v-model="form.matiere_id"
        :items="matieres"
        item-title="libelle"
        item-value="id"
        label="Matière *"
        required
        :loading="loadingMatieres"
        :disabled="loadingMatieres"
        variant="outlined"
        class="mb-4"
      />

      <v-select
        v-model="form.difficulte_id"
        :items="difficulties"
        item-title="label"
        item-value="id"
        label="Difficulté *"
        required
        :loading="loadingDifficulties"
        :disabled="loadingDifficulties"
        variant="outlined"
        class="mb-4"
      />

      <div class="d-flex gap-3 mb-4">
        <v-btn
          type="submit"
          color="primary"
          :loading="loading"
          :disabled="!form.matiere_id || !form.difficulte_id || loading"
        >
          Créer le cours
        </v-btn>
        <v-btn variant="tonal" @click="router.push('/')">Annuler</v-btn>
      </div>

      <StatusAlert v-model:error="loadError" test-id="load-error-message" />
      <StatusAlert v-model:error="submitError" test-id="submit-error-message" />
      <v-alert v-if="successMessage" type="success" class="mt-2">{{ successMessage }}</v-alert>
    </v-form>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import { referentielService } from '@/services/referentielService'
import type { CreateCourseData, Difficulte, Matiere } from '@/types'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

const router = useRouter()

const form = ref<CreateCourseData>({
  title: '',
  description: '',
  matiere_id: 0,
  difficulte_id: 0,
})
const matieres = ref<Matiere[]>([])
const difficulties = ref<Difficulte[]>([])

const loadingMatieres = ref(true)
const loadingDifficulties = ref(true)
const loading = ref(false)

const loadError = ref<unknown>(null)
const submitError = ref<unknown>(null)
const successMessage = ref('')

onMounted(async () => {
  try {
    matieres.value = await referentielService.getMatieres()
    difficulties.value = await referentielService.getDifficultes()
  } catch (error) {
    loadError.value = error
  } finally {
    loadingMatieres.value = false
    loadingDifficulties.value = false
  }
})

async function submitForm() {
  if (!form.value.matiere_id || !form.value.difficulte_id) return

  loading.value = true
  submitError.value = null
  successMessage.value = ''

  try {
    await courseService.createCourse(form.value)
    successMessage.value = 'Cours créé avec succès !'
    setTimeout(() => router.push('/'), 1500)
  } catch (error) {
    submitError.value = error
  } finally {
    loading.value = false
  }
}
</script>
