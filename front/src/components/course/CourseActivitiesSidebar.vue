<template>
  <aside class="course-sidebar">
    <div class="course-modules">
      <h3>Activites</h3>
      <ul>
        <li
          v-for="activity in activities"
          :key="activity.id"
          @click="$emit('select-activity', activity.id)"
          :class="{ active: selectedActivityId === activity.id }"
        >
          <div>
            <span>#{{ activity.ordre }}</span>
            <span>{{ formatActivityType(activity.type) }}</span>
          </div>
          <IconElement
            v-if="completedActivityIds.includes(activity.id)"
            name="check-circle"
            size="medium"
            color="success"
            title="Activite terminee"
            aria-label="Activite terminee"
          />
        </li>
      </ul>
    </div>

    <div class="progress-card">
      <p>Progression locale</p>
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

.course-modules h3 {
  margin-bottom: 1rem;
}

.course-modules ul {
  list-style: none;
  padding: 0;
}

.course-modules li {
  display: flex;
  flex-flow: row nowrap;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.2s;
  & > div {
    display: flex;
    flex-flow: row nowrap;
    gap: 0.75rem;
    font-size: 0.95rem;
  }
}

.course-modules li:hover {
  background-color: var(--primary-soft-color);
}

.course-modules li.active {
  background-color: var(--primary-color);
  color: var(--white-color);
}

.progress-card {
  margin-top: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  padding: 0.75rem;
}

.progress-card p {
  margin: 0;
  color: var(--text-muted-color);
}
</style>
