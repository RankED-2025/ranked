<template>
  <div class="pa-6" style="max-width: 500px; margin: 0 auto; background: white; border-radius: 8px;">
    <h2 class="text-h5 font-weight-bold mb-6">Créer un nouveau cours</h2>

    <v-form @submit.prevent="submitForm">
      <v-select
        v-model="form.matiere_id"
        :items="MATIERES"
        item-title="libelle"
        item-value="id"
        label="Matière *"
        required
        variant="outlined"
        class="mb-4"
      />

      <div class="d-flex gap-3 mb-4">
        <v-btn
          type="submit"
          color="primary"
          :loading="loading"
          :disabled="!form.matiere_id"
        >
          Créer le cours
        </v-btn>
        <v-btn variant="tonal" @click="router.push('/')">Annuler</v-btn>
      </div>

      <v-alert v-if="errorMessage" type="error" class="mt-2">{{ errorMessage }}</v-alert>
      <v-alert v-if="successMessage" type="success" class="mt-2">{{ successMessage }}</v-alert>
    </v-form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { courseService } from '@/services/courseService'
import { MATIERES } from '@/constants'
import type { CreateCourseData } from '@/types'

const router = useRouter()

const form = ref<CreateCourseData>({ matiere_id: 0 })
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function submitForm() {
  if (!form.value.matiere_id) return

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await courseService.createCourse(form.value)
    successMessage.value = 'Cours créé avec succès !'
    setTimeout(() => router.push('/'), 1500)
  } catch (error: any) {
    errorMessage.value = error.response?.data?.error || 'Erreur lors de la création du cours'
  } finally {
    loading.value = false
  }
}
</script>
