<template>
  <main class="detail-panel">
    <div v-if="activity" class="module-content">
      <div class="detail-head">
        <h2>Ressource #{{ activity.ordre }}</h2>
      </div>
      <p class="detail-sub">{{ activitySubtitle }}</p>

      <div v-if="activity.type === 'contenu' && activity.contenu" class="resource-card">
        <div class="resource-icon">
          <v-icon size="20">{{ contentTypeMeta(activity.contenu.type).icon }}</v-icon>
        </div>
        <div class="resource-info">
          <p class="kind">{{ contentTypeMeta(activity.contenu.type).label }}</p>
          <p class="url">{{ activity.contenu.url }}</p>
        </div>
      </div>

      <a
        v-if="activity.type === 'contenu' && activity.contenu?.url"
        :href="activity.contenu.url"
        target="_blank"
        rel="noopener noreferrer"
        class="btn-primary"
      >
        <v-icon size="16">mdi-open-in-new</v-icon>
        Ouvrir la ressource
      </a>

      <template v-if="activity.type === 'qcm' && activity.qcm">
        <QcmPreview v-if="isProfessor" :qcm="professorQcm ?? activity.qcm" />
        <QcmForm v-else :activity-id="activity.id" @completed="$emit('quiz-completed', activity.id)" />
      </template>

      <button
        v-if="activity.type !== 'qcm' && !isProfessor"
        class="btn-success"
        :class="{ 'is-done': isCompleted }"
        type="button"
        @click="$emit('toggle-completed', activity.id)"
        :disabled="isLoading"
      >
        <IconElement v-if="isLoading" name="loading" size="small" class="spin" />
        <template v-else>
          <v-icon size="16">{{ isCompleted ? 'mdi-close-circle-outline' : 'mdi-check-circle-outline' }}</v-icon>
          {{ isCompleted ? 'Marquer non terminé' : 'Marquer terminé' }}
        </template>
      </button>
    </div>

    <div v-else class="state">Aucune activite disponible pour ce cours.</div>
  </main>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import IconElement from "@/components/layouts/IconElement.vue";
import QcmForm from "@/components/course/QcmForm.vue";
import QcmPreview from "@/components/course/QcmPreview.vue";
import type { CourseActivity, QCM } from "@/types/course";
import { contentTypeMeta } from "@/utils";

const props = defineProps<{
  activity: CourseActivity | null;
  isCompleted: boolean;
  isLoading: boolean;
  isProfessor?: boolean;
  professorQcm?: QCM | null;
}>();

defineEmits<{
  (e: "toggle-completed", activityId: number): void;
  (e: "quiz-completed", activityId: number): void;
}>();

const activitySubtitle = computed(() => {
  if (!props.activity) return ''
  if (props.activity.type === 'qcm') return 'Quiz à choix multiples'
  return `Contenu pédagogique — ${contentTypeMeta(props.activity.contenu?.type).label.toLowerCase()}`
})
</script>

<style scoped>
.detail-panel {
  flex: 1;
  background: var(--surface-color);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  padding: 22px 24px 24px;
}

.detail-head {
  display: flex;
  align-items: center;
  gap: 10px;
}

.detail-head h2 {
  font-size: 17px;
  font-weight: 800;
  margin: 0;
}

.detail-sub {
  color: var(--text-light-color);
  font-size: 12.5px;
  margin: 4px 0 18px;
}

.resource-card {
  display: flex;
  align-items: center;
  gap: 14px;
  border: 1px solid var(--border-color);
  background: var(--neutral-50);
  border-radius: 10px;
  padding: 16px;
  margin-bottom: 18px;
}

.resource-icon {
  width: 42px;
  height: 42px;
  border-radius: 9px;
  background: var(--primary-soft-color);
  color: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.resource-info {
  flex: 1;
  min-width: 0;
}

.resource-info .kind {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-light-color);
  margin: 0 0 2px;
}

.resource-info .url {
  font-size: 13px;
  color: var(--text-muted-color);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
  border: none;
  font: inherit;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  text-decoration: none;
}

.btn-primary:hover {
  background: var(--primary-hover-color);
}

.btn-success {
  margin-top: 18px;
  background: var(--success-color);
  color: white;
  border: none;
  font: inherit;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 7px;
}

.btn-success:not(:disabled):hover {
  background: var(--success-hover-color);
}

.btn-success.is-done {
  background: var(--neutral-200);
  color: var(--text-muted-color);
}

.btn-success:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.state {
  color: var(--text-muted-color);
  font-size: 0.95rem;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
