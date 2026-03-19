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

onMounted(async () => {
  const courseId = String(route.params.id ?? "");

  if (!courseId) {
    errorMessage.value = "Identifiant de cours invalide.";
    loading.value = false;
    return;
  }

  const content = await courseStore.getCourseContent(courseId);
  if (!content) {
    errorMessage.value = "Impossible de recuperer le contenu du cours.";
    loading.value = false;
    return;
  }

  courseContent.value = content;
  selectedActivity.value = sortedActivities.value[0] ?? null;
  loading.value = false;
});

const selectActivity = (activityId: number) => {
  selectedActivity.value = sortedActivities.value.find((activity) => activity.id === activityId) ?? null;
};

const isActivityCompleted = (activityId: number): boolean => {
  return completedActivityIds.value.includes(activityId);
};

const toggleCompleted = (activityId: number) => {
  if (isActivityCompleted(activityId)) {
    completedActivityIds.value = completedActivityIds.value.filter((id) => id !== activityId);
    return;
  }

  completedActivityIds.value.push(activityId);
};
</script>

<style scoped>
.course-content-view {
  padding: 2rem;
}

.state {
  color: #666;
  font-size: 0.95rem;
}

.state-error {
  color: #a12626;
}

.course-body {
  display: flex;
  gap: 2rem;
}
</style>
