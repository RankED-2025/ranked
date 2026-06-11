<template>
  <div class="course-content-view">
    <div v-if="loading" class="state">
      <LoadingModal message="Chargement du contenu du cours..." size="medium" />
    </div>
    <div v-else-if="errorMessage" class="state state-error">{{ errorMessage }}</div>

    <template v-else-if="courseContent">
      <CourseContentHeader :course="courseContent!" />

      <div class="course-body">
        <CourseActivitiesSidebar
          :activities="sortedActivities"
          :selected-activity-id="selectedActivity?.id ?? null"
          :completed-activity-ids="completedActivityIds"
          :progression="progression"
          @select-activity="selectActivity"
        />

        <CourseActivityDetails
          :activity="selectedActivity"
          :is-completed="selectedActivity ? isActivityCompleted(selectedActivity!.id) : false"
          @toggle-completed="toggleCompleted"
        />
      </div>

      <div class="d-flex flex-column align-center mt-6 ga-2">
        <span v-if="isFullyCompleted" class="text-primary font-weight-bold">Cours terminé !</span>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import CourseActivitiesSidebar from "@/components/course/CourseActivitiesSidebar.vue";
import CourseActivityDetails from "@/components/course/CourseActivityDetails.vue";
import CourseContentHeader from "@/components/course/CourseContentHeader.vue";
import LoadingModal from "@/components/loading/LoadingModal.vue";
import { useCourseStore } from "@/stores/courseStore";
import type { CourseActivity, CourseContent } from "@/types/course";
import { computed, onMounted, ref } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const courseStore = useCourseStore();
const courseId = ref("");
const courseContent = ref<CourseContent | null>(null);
const selectedActivity = ref<CourseActivity | null>(null);
const completedActivityIds = ref<number[]>([]);
const loading = ref(true);
const errorMessage = ref("");

const sortedActivities = computed<CourseActivity[]>(() => {
  if (!courseContent.value) {
    return [];
  }

  return [...courseContent.value.activites].sort((a, b) => a.ordre - b.ordre);
});

const progression = computed<number>(() => {
  if (sortedActivities.value.length === 0) {
    return 0;
  }

  return Math.round((completedActivityIds.value.length / sortedActivities.value.length) * 100);
});

const isFullyCompleted = computed<boolean>(() => progression.value === 100);

onMounted(async () => {
  courseId.value = String(route.params.id ?? "");

  if (!courseId.value) {
    errorMessage.value = "Identifiant de cours invalide.";
    loading.value = false;
    return;
  }

  const content = await courseStore.getCourseContent(courseId.value);
  if (!content) {
    errorMessage.value = "Impossible de recuperer le contenu du cours.";
    loading.value = false;
    return;
  }

  courseContent.value = content;
  completedActivityIds.value = content.activites
    .filter((activity) => activity.completed)
    .map((activity) => activity.id);
  selectedActivity.value = sortedActivities.value[0] ?? null;
  loading.value = false;
});

const selectActivity = (activityId: number) => {
  selectedActivity.value = sortedActivities.value.find((activity) => activity.id === activityId) ?? null;
};

const isActivityCompleted = (activityId: number): boolean => {
  return completedActivityIds.value.includes(activityId);
};

const toggleCompleted = async (activityId: number) => {
  const wasCompleted = isActivityCompleted(activityId);

  if (wasCompleted) {
    completedActivityIds.value = completedActivityIds.value.filter((id) => id !== activityId);
  } else {
    completedActivityIds.value.push(activityId);
  }

  const success = await courseStore.updateActiviteProgression(activityId, !wasCompleted);

  if (!success) {
    if (wasCompleted) {
      completedActivityIds.value.push(activityId);
    } else {
      completedActivityIds.value = completedActivityIds.value.filter((id) => id !== activityId);
    }
  }
};
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

.course-body {
  display: flex;
  gap: 2rem;
}
</style>
