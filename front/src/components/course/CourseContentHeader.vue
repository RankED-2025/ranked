<template>
  <div class="course-header">
    <h1>{{ course.title }}</h1>
    <p class="course-description">{{ course.description }}</p>
    <p class="course-meta">
      {{ course.matiere.libelle }} -
      {{ course.professeur.firstName }} {{ course.professeur.name }}
    </p>
  </div>
</template>

<script setup lang="ts">
import type { CourseContent } from "@/types/course";

defineProps<{
  course: CourseContent;
}>();
</script>

<style scoped>
.course-header {
  margin-bottom: 2rem;
}

.course-header h1 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.course-description {
  color: var(--text-muted-color);
}

.course-meta {
  color: var(--text-subtle-color);
  font-size: 0.9rem;
}
</style>
