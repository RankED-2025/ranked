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
        <v-btn
          :color="isFullyCompleted ? 'primary' : 'grey'"
          :variant="isFullyCompleted && !isFinished ? 'flat' : 'outlined'"
          :disabled="!isFullyCompleted || isFinished || isFinishing"
          :loading="isFinishing"
          @click="openFinishModal"
        >
          {{ isFinished ? 'Cours terminé' : 'Terminer le cours' }}
        </v-btn>
        <v-alert v-if="isFinished" type="success" class="mt-2">Cours terminé ! Progression enregistrée.</v-alert>
        <v-alert v-if="finishError" type="error" class="mt-2">{{ finishError }}</v-alert>
      </div>

      <ConfirmationModal
        ref="finishModal"
        title="Terminer le cours"
        message="Êtes-vous sûr de vouloir marquer ce cours comme terminé ?"
        confirmText="Terminer"
        cancelText="Annuler"
        loadingText="Envoi..."
        :isLoading="isFinishing"
        @confirm="finishCourse"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import CourseActivitiesSidebar from "@/components/course/CourseActivitiesSidebar.vue";
import CourseActivityDetails from "@/components/course/CourseActivityDetails.vue";
import CourseContentHeader from "@/components/course/CourseContentHeader.vue";
import ConfirmationModal from "@/components/layouts/ConfirmationModal.vue";
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
const isFinishing = ref(false);
const isFinished = ref(false);
const finishError = ref("");
const finishModal = ref<InstanceType<typeof ConfirmationModal>>();

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

const openFinishModal = () => {
  if (!isFullyCompleted.value || isFinished.value || isFinishing.value) {
    return;
  }

  finishError.value = "";
  finishModal.value?.open();
};

const finishCourse = async () => {
  if (isFinishing.value || isFinished.value) {
    return;
  }

  isFinishing.value = true;
  finishError.value = "";

  const success = await courseStore.updateProgression(courseId.value, 100);

  if (success) {
    isFinished.value = true;
    finishModal.value?.close();
  } else {
    finishError.value = "Impossible d'enregistrer votre progression. Veuillez réessayer.";
    finishModal.value?.close();
  }

  isFinishing.value = false;
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
