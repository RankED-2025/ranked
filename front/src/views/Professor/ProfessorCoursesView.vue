<template>
  <StatusAlert v-model:error="loadError" test-id="load-error-message" />
  <StatusAlert v-model:error="deleteError" test-id="delete-error-message" />

  <v-container class="py-8">
    <PageHeader icon="mdi-book-open-page-variant-outline" title="Mes cours" subtitle="Gérez les cours que vous avez créés" />

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

      <template v-else>
        <div class="toolbar">
          <v-text-field
            v-model="searchQuery"
            placeholder="Rechercher un cours…"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            class="search-field"
          />

          <div class="filter-pills">
            <button
              class="filter-pill"
              :class="{ active: selectedMatiereId === null }"
              type="button"
              @click="selectedMatiereId = null"
            >
              Toutes les matières
            </button>
            <button
              v-for="matiere in distinctMatieres"
              :key="matiere.id"
              class="filter-pill"
              :class="{ active: selectedMatiereId === matiere.id }"
              type="button"
              @click="selectedMatiereId = matiere.id"
            >
              {{ matiere.libelle }}
            </button>
          </div>

          <div class="toolbar-spacer" />

          <span class="course-count">{{ filteredCourses.length }} cours</span>

          <v-btn
            color="primary"
            variant="elevated"
            prepend-icon="mdi-plus"
            @click="$router.push('/professor/create-course')"
          >
            Créer un cours
          </v-btn>
        </div>

        <EmptyState
          v-if="filteredCourses.length === 0"
          icon="mdi-magnify"
          title="Aucun cours ne correspond à votre recherche."
        />

        <v-row v-else>
          <v-col v-for="course in filteredCourses" :key="course.id" cols="12" sm="6" lg="4">
            <CourseCard
              :title="course.title"
              :description="course.description"
              :matiere="course.matiere"
              :difficulte="course.difficulte"
              :accent="getSubjectAccent(course.matiere.id)"
              @view="goToCourse(course.id.toString())"
              @edit="$router.push(`/professor/edit-course/${course.id}`)"
              @delete="openDeleteModal(course.id)"
            />
          </v-col>
        </v-row>
      </template>
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
import type { Matiere } from '@/types';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import ConfirmationModal from '@/components/layouts/ConfirmationModal.vue';
import PageHeader from '@/components/layouts/PageHeader.vue';
import EmptyState from '@/components/layouts/EmptyState.vue';
import CourseCard from '@/components/professor/CourseCard.vue';
import { courseService } from '@/services/courseService';
import { useModal } from '@/composables';
import { getSubjectAccent } from '@/utils';
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
const searchQuery = ref('')
const selectedMatiereId = ref<number | null>(null)

const distinctMatieres = computed<Matiere[]>(() => {
  const seen = new Map<number, Matiere>()
  for (const course of professorCourses.value) {
    if (course.matiere && !seen.has(course.matiere.id)) {
      seen.set(course.matiere.id, course.matiere)
    }
  }
  return [...seen.values()]
})

const filteredCourses = computed<ProfessorCourse[]>(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return professorCourses.value.filter((course) => {
    const matchesQuery = !query || course.title.toLowerCase().includes(query)
    const matchesMatiere = selectedMatiereId.value === null || course.matiere?.id === selectedMatiereId.value
    return matchesQuery && matchesMatiere
  })
})

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

<style scoped>
.toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 22px;
}

.search-field {
  max-width: 280px;
  flex: 0 1 260px;
}

.filter-pills {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-pill {
  border: 1px solid var(--border-strong-color);
  background: var(--surface-color);
  color: var(--text-muted-color);
  border-radius: 999px;
  padding: 7px 13px;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.12s ease, border-color 0.12s ease, color 0.12s ease;
}

.filter-pill:hover {
  border-color: var(--text-light-color);
}

.filter-pill.active {
  background: var(--primary-soft-color);
  border-color: color-mix(in srgb, var(--primary-color) 24%, var(--surface-color));
  color: var(--primary-color);
}

.toolbar-spacer {
  flex: 1;
}

.course-count {
  font-size: 12.5px;
  color: var(--text-light-color);
  white-space: nowrap;
}
</style>
