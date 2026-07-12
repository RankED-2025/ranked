<template>
  <div class="course-content-view">
    <div v-if="loading" class="state" data-testid="loading">
      <v-skeleton-loader type="heading" class="mb-2" width="40%" />
      <v-skeleton-loader type="text" width="60%" class="mb-6" />
      <v-skeleton-loader type="text" class="mb-6" />
      <div class="course-body">
        <v-skeleton-loader type="list-item-two-line@4" class="sidebar-skeleton" />
        <v-skeleton-loader type="article" class="content-skeleton" />
      </div>
    </div>
    <v-alert
      v-else-if="invalidIdMessage"
      type="error"
      variant="tonal"
      rounded="lg"
      data-testid="error-message"
    >
      {{ invalidIdMessage }}
    </v-alert>

    <StatusAlert v-else-if="loadError" v-model:error="loadError" test-id="error-message" />

    <template v-else-if="courseContent">
      <CourseContentHeader
        :course="courseContent!"
        :is-professor="isProfessor"
        @edit="router.push(`/professor/edit-course/${courseId}`)"
        @delete="openDeleteModal"
      />

      <div v-if="!isProfessor" class="progression-bar-wrapper">
        <div class="d-flex align-center justify-space-between mb-1 px-1">
          <span class="text-caption text-grey-darken-1">Progression du cours</span>
          <span class="text-caption font-weight-bold">{{ progression }}%</span>
        </div>
        <v-progress-linear
          :model-value="progression"
          :color="isFullyCompleted ? 'success' : 'primary'"
          bg-color="grey-lighten-3"
          rounded
          height="10"
        />
      </div>

      <v-alert
        v-else
        type="info"
        variant="tonal"
        rounded="lg"
        density="compact"
        class="preview-banner"
      >
        Vous consultez ce cours en tant qu'auteur — la progression et le quiz ne sont pas interactifs ici.
      </v-alert>

      <div class="course-body">
        <CourseActivitiesSidebar
          :activities="sortedActivities"
          :selected-activity-id="selectedActivity?.id ?? null"
          :completed-activity-ids="completedActivityIds"
          :loading-activity-id="loadingActivityId"
          :progression="progression"
          :is-professor="isProfessor"
          @select-activity="selectActivity"
        />

        <CourseActivityDetails
          :activity="selectedActivity"
          :is-completed="selectedActivity ? isActivityCompleted(selectedActivity!.id) : false"
          :is-loading="loadingActivityId === selectedActivity?.id"
          :is-professor="isProfessor"
          :professor-qcm="selectedActivity ? professorQcmByActivityId.get(selectedActivity.id) : null"
          @toggle-completed="toggleCompleted"
          @quiz-completed="onQuizCompleted"
        />
      </div>

      <div v-if="!isProfessor" class="d-flex flex-column align-center mt-6 ga-2">
        <span v-if="isFullyCompleted" class="text-success font-weight-bold" data-testid="fully-completed">
          <v-icon color="success" class="mr-1">mdi-check-circle</v-icon>Cours terminé !
        </span>
      </div>
    </template>

    <v-snackbar
      v-model="toggleError"
      color="error"
      :timeout="4000"
      location="bottom"
      data-testid="toggle-error"
    >
      Une erreur s'est produite, veuillez réessayer.
    </v-snackbar>

    <v-snackbar
      v-model="showDeleteErrorSnackbar"
      color="error"
      :timeout="4000"
      location="bottom"
      data-testid="delete-error-message"
    >
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
  </div>
</template>

<script setup lang="ts">
import CourseActivitiesSidebar from '@/components/course/CourseActivitiesSidebar.vue'
import CourseActivityDetails from '@/components/course/CourseActivityDetails.vue'
import CourseContentHeader from '@/components/course/CourseContentHeader.vue'
import ConfirmationModal from '@/components/layouts/ConfirmationModal.vue'
import { useCourseStore } from '@/stores/courseStore'
import { courseService } from '@/services/courseService'
import type { CourseActivity, CourseContent, QCM } from '@/types/course'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import StatusAlert from '@/components/layouts/StatusAlert.vue'
import { useAuth, useModal } from '@/composables'
import { isProfesseur } from '@/utils'

const route = useRoute()
const router = useRouter()
const courseStore = useCourseStore()
const { user } = useAuth()
const { isOpen: isConfirmOpen, open: openConfirmModal, close: closeConfirmModal } = useModal()

const isProfessor = computed(() => isProfesseur(user.value?.roles ?? []))

const courseId = ref('')
const courseContent = ref<CourseContent | null>(null)
const selectedActivity = ref<CourseActivity | null>(null)
const completedActivityIds = ref<number[]>([])
const professorQcmByActivityId = ref<Map<number, QCM>>(new Map())
const loading = ref(true)
const invalidIdMessage = ref('')
const loadError = ref<unknown>(null)
const toggleError = ref(false)
const loadingActivityId = ref<number | null>(null)
const isDeleting = ref(false)
const showDeleteErrorSnackbar = ref(false)

const sortedActivities = computed<CourseActivity[]>(() => {
  if (!courseContent.value) {
    return []
  }

  return [...courseContent.value.activites].sort((a, b) => a.ordre - b.ordre)
})

const progression = computed<number>(() => {
  if (sortedActivities.value.length === 0) {
    return 0
  }

  return Math.round((completedActivityIds.value.length / sortedActivities.value.length) * 100)
})

const isFullyCompleted = computed<boolean>(() => progression.value === 100)

onMounted(async () => {
  courseId.value = String(route.params.id ?? '')

  if (!courseId.value) {
    invalidIdMessage.value = 'Identifiant de cours invalide.'
    loading.value = false
    return
  }

  try {
    const [content, professorContent] = await Promise.all([
      courseStore.getCourseContent(courseId.value),
      isProfessor.value ? courseService.getProfessorCourseContent(courseId.value) : Promise.resolve(null),
    ])

    courseContent.value = content
    completedActivityIds.value = content?.activites
      .filter((activity) => activity.completed)
      .map((activity) => activity.id) ?? []
    selectedActivity.value = sortedActivities.value[0] ?? null

    professorQcmByActivityId.value = new Map(
      (professorContent?.activites ?? [])
        .filter((activity) => activity.qcm)
        .map((activity) => [activity.id, activity.qcm!]),
    )
  } catch (error) {
    loadError.value = error
  } finally {
    loading.value = false
  }
})

const selectActivity = (activityId: number) => {
  selectedActivity.value =
    sortedActivities.value.find((activity) => activity.id === activityId) ?? null
}

const isActivityCompleted = (activityId: number): boolean => {
  return completedActivityIds.value.includes(activityId)
}

const onQuizCompleted = (activityId: number) => {
  if (!completedActivityIds.value.includes(activityId)) {
    completedActivityIds.value.push(activityId);
  }
};

const toggleCompleted = async (activityId: number) => {
  const wasCompleted = isActivityCompleted(activityId)

  if (wasCompleted) {
    completedActivityIds.value = completedActivityIds.value.filter((id) => id !== activityId)
  } else {
    completedActivityIds.value.push(activityId)
  }

  loadingActivityId.value = activityId
  const success = await courseStore.updateActiviteProgression(activityId, !wasCompleted)
  loadingActivityId.value = null

  if (!success) {
    if (wasCompleted) {
      completedActivityIds.value.push(activityId)
    } else {
      completedActivityIds.value = completedActivityIds.value.filter((id) => id !== activityId)
    }
    toggleError.value = true
  }
}

const openDeleteModal = () => {
  showDeleteErrorSnackbar.value = false
  openConfirmModal()
}

const confirmDelete = async () => {
  isDeleting.value = true
  try {
    await courseService.deleteCourse(courseId.value)
    closeConfirmModal()
    router.push('/professor/my-courses')
  } catch {
    showDeleteErrorSnackbar.value = true
    closeConfirmModal()
  } finally {
    isDeleting.value = false
  }
}
</script>

<style scoped>
.course-content-view {
  padding: 2rem;
}

.state {
  color: var(--text-muted-color);
  font-size: 0.95rem;
}

.state-error {
  color: var(--danger-color);
}

.progression-bar-wrapper {
  margin: 1.5rem 0 0.5rem;
}

.preview-banner {
  margin: 1.5rem 0 0.5rem;
}

.course-body {
  display: flex;
  gap: 22px;
  margin-top: 22px;
}

.sidebar-skeleton {
  width: 300px;
  flex-shrink: 0;
}

.content-skeleton {
  flex: 1;
}
</style>
