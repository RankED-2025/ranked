<template>
  <main class="course-content">
    <div v-if="activity" class="module-content">
      <h2>Activite #{{ activity.ordre }}</h2>
      <p class="activity-type">Type: {{ formatActivityType(activity.type) }}</p>

      <div v-if="activity.contenu" class="activity-card">
        <h4>Contenu</h4>
        <p>{{ activity.contenu.type }}</p>
        <a
          v-if="activity.contenu.url"
          :href="activity.contenu.url"
          target="_blank"
          rel="noopener noreferrer"
        >
          Ouvrir la ressource
        </a>
      </div>

      <div v-if="activity.qcm" class="activity-card">
        <h4>QCM</h4>
        <p>Points a gagner: {{ activity.qcm.gainPts }}</p>
      </div>

      <button @click="$emit('toggle-completed', activity.id)">
        {{ isCompleted ? 'Marquer non termine' : 'Marquer termine' }}
      </button>
    </div>

    <div v-else class="state">Aucune activite disponible pour ce cours.</div>
  </main>
</template>

<script setup lang="ts">
import type { CourseActivity } from "@/types/course";

defineProps<{
  activity: CourseActivity | null;
  isCompleted: boolean;
}>();

defineEmits<{
  (e: "toggle-completed", activityId: number): void;
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
.course-content {
  flex: 1;
}

.module-content h2 {
  margin-bottom: 1rem;
}

.activity-type {
  color: #555;
  margin-bottom: 1rem;
}

.activity-card {
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.state {
  color: #666;
  font-size: 0.95rem;
}

button {
  padding: 0.5rem 1rem;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 1rem;
}

button:hover {
  background-color: #218838;
}
</style>
