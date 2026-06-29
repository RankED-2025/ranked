<template>
  <div v-if="loading" class="state">
    <LoadingModal message="Chargement de vos cours..." size="medium" />
  </div>
  <div v-else-if="errorMessage" class="state state-error">
    {{ errorMessage }}
  </div>
  <div v-else class="courses-container">
    <h1>Mes cours</h1>

    <StatusAlert v-model:error="loadError" test-id="load-error-message" />
    <StatusAlert v-model:error="deleteError" test-id="delete-error-message" />

    <div v-if="professorCourses.length === 0 && !loadError" class="empty-state">
      <p>Vous n'avez pas encore créé de cours.</p>
      <button @click="$router.push('/professor/create-course')">Créer un cours</button>
    </div>
    <div v-else class="courses-list">
      <div v-for="course in professorCourses" :key="course.id" class="course-card">
        <h2 class="course-title">
          <span>{{ course.title }}</span>
          <button @click="openDeleteModal(course.id)" class="delete-button">
            <v-icon name="delete" size="24" color="white">mdi-delete-forever</v-icon>
          </button>
        </h2>
        <div class="course-meta">
          <span class="instructor">{{ course.description }}</span>
        </div>
        <div>
          <TagElement
            v-if="course.difficulte"
            :text="course.difficulte.label"
            size="small"
            color="primary"
          />
          <TagElement
            v-if="course.matiere"
            :text="course.matiere.libelle"
            size="small"
            color="secondary"
          />
        </div>
        <div class="course-footer">
          <button @click="goToCourse(course.id.toString())" class="more-button">
            Voir le cours
          </button>
          <button
            @click="$router.push(`/professor/edit-course/${course.id}`)"
            class="edit-button"
            style="margin-top: 8px; background: var(--secondary-color)"
          >
            Modifier
          </button>
        </div>
      </div>
    </div>
  </div>

  <v-snackbar v-model="deleteError" color="error" :timeout="4000" location="bottom">
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
import type { ProfessorCourse } from '@/types/course'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import LoadingModal from '@/components/loading/LoadingModal.vue'
import TagElement from '@/components/layouts/TagElement.vue'
import ConfirmationModal from '@/components/layouts/ConfirmationModal.vue'
import { courseService } from '@/services/courseService'
import StatusAlert from '@/components/layouts/StatusAlert.vue'
const { isOpen: isConfirmOpen, open: openConfirmModal, close: closeConfirmModal } = useModal();

const router = useRouter()
const professorCourses = ref<ProfessorCourse[]>([])
const loading = ref(true)
const isDeleting = ref(false)
const selectedCourseId = ref<number | null>(null)
const confirmationModal = ref<InstanceType<typeof ConfirmationModal>>()
const loadError = ref<unknown>(null)
const deleteError = ref<unknown>(null)

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
  selectedCourseId.value = courseId
  openConfirmModal();
}

const confirmDelete = async () => {
  if (!selectedCourseId.value) return

  isDeleting.value = true
  try {
    await courseService.deleteCourse(selectedCourseId.value);
    professorCourses.value = professorCourses.value.filter(c => c.id !== selectedCourseId.value);
    closeConfirmModal();
  } catch (error) {
    deleteError.value = error
    closeConfirmModal()
  } finally {
    isDeleting.value = false
    selectedCourseId.value = null
  }
}
</script>

<style scoped>
.course-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-flow: row nowrap;
}

.courses-container {
  padding: 20px;
}

.courses-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.course-card {
  display: flex;
  flex-flow: column;
  justify-content: space-between;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 20px;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s;
}

.course-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.description {
  color: var(--text-muted-color);
  font-size: 14px;
  margin: 5px 0;
}

.course-meta {
  display: flex;
  justify-content: space-between;
  margin: 10px 0;
  font-size: 12px;
}

.instructor {
  color: var(--primary-color);
  font-weight: 500;
}

.progress {
  background: var(--secondary-color);
  padding: 2px 8px;
  border-radius: 4px;
}

.course-footer {
  margin-top: 15px;
}

button {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.more-button {
  background: var(--primary-color);
  color: var(--white-color);
}

.more-button:hover {
  background: var(--primary-hover-color);
}

.edit-button {
  color: var(--white-color);
}

.edit-button:hover {
  background: var(--secondary-hover-color);
}

.delete-button {
  background: var(--danger-color);
  width: fit-content;
}

.delete-button:hover {
  background: var(--danger-hover-color);
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-light-color);
}
</style>
