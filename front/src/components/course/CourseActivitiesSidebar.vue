<template>
  <aside class="course-sidebar">
    <div class="course-modules">
      <h3 class="mb-4">Activites</h3>
      <ul class="pa-0">
        <li
          v-for="activity in activities"
          :key="activity.id"
          @click="$emit('select-activity', activity.id)"
          class="d-flex flex-row flex-nowrap justify-space-between align-center ga-2 pa-3 mb-2 rounded"
          :class="{ 'bg-primary text-white': selectedActivityId === activity.id }"
        >
          <div class="d-flex flex-row flex-nowrap ga-3">
            <span>#{{ activity.ordre }}</span>
            <span>{{ formatActivityType(activity.type) }}</span>
          </div>
          <IconElement
            v-if="loadingActivityId === activity.id"
            name="loading"
            size="medium"
            class="spin"
            title="Chargement..."
            aria-label="Chargement..."
          />
          <IconElement
            v-else-if="completedActivityIds.includes(activity.id)"
            name="check-circle"
            size="medium"
            color="success"
            title="Activite terminee"
            aria-label="Activite terminee"
          />
        </li>
      </ul>
    </div>

    <div class="progress-card border rounded mt-4 pa-3">
      <p class="ma-0 text-medium-emphasis">Progression</p>
      <strong>{{ progression }}%</strong>
    </div>
  </aside>
</template>

<script setup lang="ts">
import IconElement from "@/components/layouts/IconElement.vue";
import type { CourseActivity } from "@/types/course";

defineProps<{
  activities: CourseActivity[];
  selectedActivityId: number | null;
  completedActivityIds: number[];
  loadingActivityId: number | null;
  progression: number;
}>();

defineEmits<{
  (e: "select-activity", activityId: number): void;
}>();

const formatActivityType = (type: string): string => {
  if (type === "qcm") {
    return "QCM";
  }

  if (type === "contenu") {
    return "Contenu";
  }

  return type;
};
</script>

<style scoped>
.course-sidebar {
  width: 320px;
}

.course-modules ul {
  list-style: none;
}

.course-modules li {
  cursor: pointer;
  transition: background-color 0.2s;

  & > div {
    font-size: 0.95rem;
  }
}

.course-modules li:hover {
  background-color: var(--primary-soft-color);
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
