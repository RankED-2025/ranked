<template>
  <div
    class="course-card"
    :class="{ clickable }"
    :style="{ '--accent': accent }"
    @click="clickable && emit('view')"
  >
    <div class="top-row">
      <div class="title-group">
        <span class="subj-label"><span class="dot"></span>{{ matiere?.libelle ?? '—' }}</span>
        <h3>{{ title || 'Titre du cours…' }}</h3>
      </div>
      <div v-if="showActions" class="card-actions">
        <button class="icon-btn" type="button" aria-label="Modifier" @click.stop="emit('edit')">
          <v-icon size="16">mdi-pencil-outline</v-icon>
        </button>
        <button class="icon-btn danger" type="button" aria-label="Supprimer" @click.stop="emit('delete')">
          <v-icon size="16">mdi-delete-outline</v-icon>
        </button>
      </div>
    </div>

    <p class="desc">{{ description || 'La description apparaîtra ici…' }}</p>

    <div class="meta-row">
      <span><v-icon size="14">mdi-speedometer</v-icon>{{ difficulte?.label ?? '—' }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Matiere, Difficulte } from '@/types'

withDefaults(defineProps<{
  title: string;
  description: string;
  matiere: Matiere | null;
  difficulte: Difficulte | null;
  accent: string;
  showActions?: boolean;
  clickable?: boolean;
}>(), {
  showActions: true,
  clickable: true,
});

const emit = defineEmits<{
  view: [];
  edit: [];
  delete: [];
}>();
</script>

<style scoped>
.course-card {
  position: relative;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: var(--surface-color);
  padding: 16px 16px 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  height: 100%;
  transition: box-shadow 0.15s ease, border-color 0.15s ease;
}

.course-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 14px;
  bottom: 14px;
  width: 3px;
  border-radius: 3px;
  background: var(--accent);
}

.course-card.clickable {
  cursor: pointer;
}

.course-card.clickable:hover {
  box-shadow: var(--shadow-md);
  border-color: var(--border-strong-color);
}

.top-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
  padding-left: 8px;
}

.title-group h3 {
  font-size: 14.5px;
  font-weight: 700;
  margin: 4px 0 0;
  letter-spacing: -0.005em;
  color: var(--text-color);
}

.subj-label {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.02em;
  color: var(--accent);
}

.subj-label .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--accent);
}

.card-actions {
  display: flex;
  gap: 2px;
  opacity: 0;
  transition: opacity 0.12s ease;
}

.course-card:hover .card-actions {
  opacity: 1;
}

.icon-btn {
  width: 26px;
  height: 26px;
  display: grid;
  place-items: center;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: var(--text-light-color);
  cursor: pointer;
}

.icon-btn:hover {
  background: var(--neutral-100);
}

.icon-btn.danger:hover {
  color: var(--danger-color);
  background: color-mix(in srgb, var(--danger-color) 10%, transparent);
}

.desc {
  padding-left: 8px;
  margin: 0;
  color: var(--text-muted-color);
  font-size: 12.5px;
  flex-grow: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.meta-row {
  padding-left: 8px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 11.5px;
  color: var(--text-light-color);
}

.meta-row span {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
</style>
