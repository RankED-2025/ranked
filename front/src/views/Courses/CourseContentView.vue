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
      <CourseContentHeader :course="courseContent!" />

      <div class="progression-bar-wrapper">
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

      <div class="course-body">
        <CourseActivitiesSidebar
          :activities="sortedActivities"
          :selected-activity-id="selectedActivity?.id ?? null"
          :completed-activity-ids="completedActivityIds"
          :loading-activity-id="loadingActivityId"
          :progression="progression"
          @select-activity="selectActivity"
        />

        <CourseActivityDetails
          :activity="selectedActivity"
          :is-completed="selectedActivity ? isActivityCompleted(selectedActivity!.id) : false"
          :is-loading="loadingActivityId === selectedActivity?.id"
          @toggle-completed="toggleCompleted"
          @quiz-completed="onQuizCompleted"
        />
      </div>

      <div class="d-flex flex-column align-center mt-6 ga-2">
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
  </div>
</template>

<script setup lang="ts">
import CourseActivitiesSidebar from '@/components/course/CourseActivitiesSidebar.vue'
import CourseActivityDetails from '@/components/course/CourseActivityDetails.vue'
import CourseContentHeader from '@/components/course/CourseContentHeader.vue'
import { useCourseStore } from '@/stores/courseStore'
import type { CourseActivity, CourseContent } from '@/types/course'
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

const route = useRoute()
const courseStore = useCourseStore()
const courseId = ref('')
const courseContent = ref<CourseContent | null>(null)
const selectedActivity = ref<CourseActivity | null>(null)
const completedActivityIds = ref<number[]>([])
const loading = ref(true)
const invalidIdMessage = ref('')
const loadError = ref<unknown>(null)
const toggleError = ref(false)
const loadingActivityId = ref<number | null>(null)

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
    const content = await courseStore.getCourseContent(courseId.value)
    courseContent.value = content
    completedActivityIds.value = content?.activites
      .filter((activity) => activity.completed)
      .map((activity) => activity.id) ?? []
    selectedActivity.value = sortedActivities.value[0] ?? null
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

.course-body {
  display: flex;
  gap: 2rem;
}

.sidebar-skeleton {
  width: 320px;
  flex-shrink: 0;
}

.content-skeleton {
  flex: 1;
}
</style>
