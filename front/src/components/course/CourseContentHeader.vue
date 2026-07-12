<template>
  <div class="course-header">
    <div class="course-header-main">
      <div class="eyebrow-row">
        <span v-if="course.matiere" class="subject-pill" :style="{ '--accent': subjectAccent }">
          {{ course.matiere.libelle }}
        </span>
        <span v-if="course.difficulte" class="difficulty-pill">{{ course.difficulte.label }}</span>
        <span v-if="isProfessor" class="teacher-pill">
          <v-icon size="13">mdi-school-outline</v-icon>
          Vue enseignant
        </span>
      </div>

      <h1>{{ course.title }}</h1>
      <p class="course-description">{{ course.description }}</p>
      <p class="course-meta-line">
        <span class="avatar-dot">{{ professeurInitials }}</span>
        {{ course.professeur.prenom }} {{ course.professeur.nom }}
      </p>
    </div>

    <div v-if="isProfessor" class="header-actions">
      <button class="btn btn-outline" type="button" @click="$emit('edit')">
        <v-icon size="15">mdi-pencil-outline</v-icon>
        Modifier
      </button>
      <button class="btn btn-danger-outline" type="button" @click="$emit('delete')">
        <v-icon size="15">mdi-trash-can-outline</v-icon>
        Supprimer
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { CourseContent } from '@/types/course'
import { getSubjectAccent } from '@/utils'

const props = defineProps<{
  course: CourseContent
  isProfessor?: boolean
}>()

defineEmits<{
  (e: 'edit'): void
  (e: 'delete'): void
}>()

const subjectAccent = computed(() =>
  props.course.matiere ? getSubjectAccent(props.course.matiere.id) : 'var(--border-strong-color)',
)

const professeurInitials = computed(() => {
  const prenom = props.course.professeur.prenom?.[0] ?? ''
  const nom = props.course.professeur.nom?.[0] ?? ''
  return `${prenom}${nom}`.toUpperCase()
})
</script>

<style scoped>
.course-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 22px;
}

.course-header-main {
  min-width: 0;
}

.eyebrow-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.subject-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  color: var(--accent);
  background: color-mix(in srgb, var(--accent) 14%, var(--surface-color));
  padding: 4px 10px;
  border-radius: 999px;
}

.subject-pill::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--accent);
}

.difficulty-pill {
  font-size: 11.5px;
  font-weight: 700;
  color: var(--text-muted-color);
  border: 1px solid var(--border-strong-color);
  padding: 4px 10px;
  border-radius: 999px;
}

.teacher-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  color: var(--primary-color);
  background: var(--primary-soft-color);
  padding: 4px 10px;
  border-radius: 999px;
}

.course-header h1 {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: -0.01em;
  margin: 0 0 8px;
}

.course-description {
  color: var(--text-muted-color);
  font-size: 14.5px;
  line-height: 1.55;
  max-width: 62ch;
  margin: 0 0 10px;
}

.course-meta-line {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: var(--text-light-color);
  margin: 0;
}

.avatar-dot {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--primary-soft-color);
  color: var(--primary-color);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
  font-weight: 800;
}

.header-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font: inherit;
  font-size: 13px;
  font-weight: 700;
  padding: 9px 14px;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid transparent;
  white-space: nowrap;
}

.btn-outline {
  background: var(--surface-color);
  border-color: var(--border-strong-color);
  color: var(--text-color);
}

.btn-outline:hover {
  border-color: var(--primary-color);
  color: var(--primary-color);
}

.btn-danger-outline {
  background: var(--surface-color);
  border-color: var(--border-strong-color);
  color: var(--danger-color);
}

.btn-danger-outline:hover {
  background: color-mix(in srgb, var(--danger-color) 12%, var(--surface-color));
  border-color: var(--danger-color);
}

@media (max-width: 640px) {
  .course-header {
    flex-direction: column;
  }
}
</style>
