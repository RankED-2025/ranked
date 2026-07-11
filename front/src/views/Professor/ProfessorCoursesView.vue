<template>
  <StatusAlert v-model:error="loadError" test-id="load-error-message" />
  <StatusAlert v-model:error="deleteError" test-id="delete-error-message" />

  <v-container class="py-8">
    <div class="d-flex align-center justify-space-between mb-6">
      <h1 class="text-h4 font-weight-bold">Mes cours</h1>
      <v-btn
        color="primary"
        variant="elevated"
        prepend-icon="mdi-plus"
        @click="$router.push('/professor/create-course')"
      >
        Créer un cours
      </v-btn>
    </div>

    <v-row v-if="loading">
      <v-col v-for="n in 3" :key="n" cols="12" sm="6" lg="4">
        <v-skeleton-loader type="card" />
      </v-col>
    </v-row>

    <template v-else>
      <EmptyState
        v-if="professorCourses.length === 0 && !loadError"
        icon="mdi-book-plus-outline"
        title="Vous n'avez pas encore créé de cours."
      >
        <template #action>
          <v-btn color="primary" variant="elevated" @click="$router.push('/professor/create-course')">
            Créer un cours
          </v-btn>
        </template>
      </EmptyState>

      <v-row v-else>
        <v-col v-for="course in professorCourses" :key="course.id" cols="12" sm="6" lg="4">
          <v-card elevation="2" rounded="lg" class="d-flex flex-column h-100" hover>
            <v-card-title class="d-flex justify-space-between align-start pt-4 pb-1">
              <span class="text-body-1 font-weight-bold text-wrap">{{ course.title }}</span>
              <v-btn
                icon="mdi-delete-forever"
                color="error"
                variant="text"
                size="small"
                density="comfortable"
                @click="openDeleteModal(course.id)"
              />
            </v-card-title>

            <v-card-text class="grow">
              <p class="text-body-2 text-grey-darken-1 mb-3">{{ course.description }}</p>
              <div class="d-flex flex-wrap gap-2">
                <v-chip v-if="course.difficulte" color="primary" size="small" variant="tonal">
                  {{ course.difficulte.label }}
                </v-chip>
                <v-chip v-if="course.matiere" color="secondary" size="small" variant="tonal">
                  {{ course.matiere.libelle }}
                </v-chip>
              </div>
            </v-card-text>

            <v-card-actions class="pa-4 pt-0 d-flex flex-column gap-2">
              <v-btn
                color="primary"
                variant="elevated"
                block
                @click="goToCourse(course.id.toString())"
              >
                Voir le cours
              </v-btn>
              <v-btn
                color="secondary"
                variant="elevated"
                block
                @click="$router.push(`/professor/edit-course/${course.id}`)"
              >
                Modifier
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-container>

  <v-snackbar v-model="showDeleteErrorSnackbar" color="error" :timeout="4000" location="bottom">
    Erreur lors de la suppression. Veuillez réessayer.
  </v-snackbar>

  <ConfirmationModal
    v-model="isConfirmOpen"
    title="Supprimer ce cours"
    message="Êtes-vous sûr de vouloir supprimer ce cours ? Cette action est irréversible."
    confirmText="Supprimer"
    cancelText="Annuler"
    :isLoading="isDeleting"
    @confirm="confirmDelete"
  />
</template>

<script setup lang="ts">
import type { ProfessorCourse } from '@/types/course';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import ConfirmationModal from '@/components/layouts/ConfirmationModal.vue';
import EmptyState from '@/components/layouts/EmptyState.vue';
import { courseService } from '@/services/courseService';
import { useModal } from '@/composables';
import StatusAlert from '@/components/layouts/StatusAlert.vue'

const router = useRouter();
const { isOpen: isConfirmOpen, open: openConfirmModal, close: closeConfirmModal } = useModal();

const professorCourses = ref<ProfessorCourse[]>([])
const loading = ref(true)
const isDeleting = ref(false)
const selectedCourseId = ref<number | null>(null)
const loadError = ref<unknown>(null)
const deleteError = ref<unknown>(null)
const showDeleteErrorSnackbar = ref(false)

onMounted(async () => {
  try {
    professorCourses.value = await courseService.getProfessorCourses()
  } catch (error) {
    loadError.value = error
  } finally {
    loading.value = false
  }
})

const goToCourse = (courseId: string) => router.push(`/course/${courseId}`)

const openDeleteModal = (courseId: number) => {
  deleteError.value = null
  showDeleteErrorSnackbar.value = false
  selectedCourseId.value = courseId
  openConfirmModal();
}

const confirmDelete = async () => {
  if (selectedCourseId.value === null) return

  isDeleting.value = true
  try {
    await courseService.deleteCourse(selectedCourseId.value);
    professorCourses.value = professorCourses.value.filter(c => c.id !== selectedCourseId.value);
    closeConfirmModal();
  } catch (error) {
    deleteError.value = error
    showDeleteErrorSnackbar.value = true
    closeConfirmModal()
  } finally {
    isDeleting.value = false
    selectedCourseId.value = null
  }
}
</script>
