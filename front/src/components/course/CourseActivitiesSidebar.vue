<template>
  <aside class="sidebar">
    <div class="sidebar-card">
      <div class="sidebar-head">
        <h3>Ressources</h3>
        <span class="count">{{ activities.length }}</span>
      </div>

      <div
        v-for="activity in activities"
        :key="activity.id"
        @click="$emit('select-activity', activity.id)"
        class="activity-item"
        :class="{ selected: selectedActivityId === activity.id }"
      >
        <span class="activity-order">#{{ activity.ordre }}</span>
        <span class="activity-type-badge" :class="activity.type">
          {{ formatActivityType(activity.type) }}
        </span>
        <span class="activity-item-label">{{ activityLabel(activity) }}</span>

        <span v-if="!isProfessor" class="activity-status">
          <IconElement
            v-if="loadingActivityId === activity.id"
            name="loading"
            size="small"
            class="spin"
            title="Chargement..."
            aria-label="Chargement..."
          />
          <IconElement
            v-else-if="completedActivityIds.includes(activity.id)"
            name="check-circle"
            size="small"
            color="success"
            title="Activite terminee"
            aria-label="Activite terminee"
          />
        </span>
      </div>
    </div>

    <div v-if="!isProfessor" class="sidebar-card progress-mini">
      <span>Progression</span>
      <strong>{{ progression }}%</strong>
    </div>
  </aside>
</template>

<script setup lang="ts">
import IconElement from "@/components/layouts/IconElement.vue";
import type { CourseActivity } from "@/types/course";
import { contentTypeMeta } from "@/utils";

defineProps<{
  activities: CourseActivity[];
  selectedActivityId: number | null;
  completedActivityIds: number[];
  loadingActivityId: number | null;
  progression: number;
  isProfessor?: boolean;
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

const activityLabel = (activity: CourseActivity): string => {
  if (activity.type === "qcm") {
    return `${activity.qcm?.gainPts ?? 0} points`;
  }

  if (activity.type === "contenu") {
    return contentTypeMeta(activity.contenu?.type).label;
  }

  return "";
};
</script>

<style scoped>
.sidebar {
  width: 300px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.sidebar-card {
  background: var(--surface-color);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  overflow: hidden;
}

.sidebar-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px;
  border-bottom: 1px solid var(--border-color);
}

.sidebar-head h3 {
  margin: 0;
  font-size: 13px;
  font-weight: 700;
}

.sidebar-head .count {
  font-size: 11px;
  font-weight: 700;
  color: var(--text-light-color);
  background: var(--neutral-100);
  padding: 2px 8px;
  border-radius: 999px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px;
  cursor: pointer;
  border-bottom: 1px solid var(--border-color);
  position: relative;
  transition: background-color 0.15s;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-item:hover {
  background: var(--neutral-50);
}

.activity-item.selected {
  background: var(--primary-soft-color);
}

.activity-item.selected::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--primary-color);
}

.activity-order {
  font-size: 10.5px;
  font-weight: 700;
  color: var(--text-light-color);
  font-variant-numeric: tabular-nums;
  flex-shrink: 0;
}

.activity-type-badge {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  padding: 3px 7px;
  border-radius: 5px;
  flex-shrink: 0;
}

.activity-type-badge.contenu {
  background: var(--primary-soft-color);
  color: var(--primary-color);
}

.activity-type-badge.qcm {
  background: color-mix(in srgb, var(--warning-color) 16%, var(--surface-color));
  color: color-mix(in srgb, var(--warning-color) 65%, black);
}

.activity-item-label {
  flex: 1;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-color);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.activity-status {
  flex-shrink: 0;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.progress-mini {
  padding: 12px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.progress-mini span {
  font-size: 12px;
  color: var(--text-light-color);
}

.progress-mini strong {
  font-size: 15px;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
