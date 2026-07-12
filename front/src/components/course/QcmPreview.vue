<template>
  <div class="qcm-preview" data-testid="qcm-preview">
    <div class="qcm-meta-row">
      <span class="points-badge">
        <v-icon size="13">mdi-star-four-points-outline</v-icon>
        {{ qcm.gainPts }} points
      </span>
      <span class="hint">{{ qcm.questions?.length ?? 0 }} question(s)</span>
    </div>

    <div class="quiz-question" v-for="(question, qIndex) in qcm.questions" :key="question.id ?? qIndex">
      <p class="enonce">{{ qIndex + 1 }}. {{ question.enonce }}</p>

      <div
        v-for="reponse in question.reponses"
        :key="reponse.id ?? reponse.texte"
        class="quiz-option"
        :class="{ 'correct-preview': reponse.isCorrect }"
      >
        {{ reponse.texte }}
        <span v-if="reponse.isCorrect" class="check">
          <v-icon size="15">mdi-check</v-icon>
        </span>
      </div>
    </div>

    <p class="teacher-note">
      <v-icon size="14">mdi-information-outline</v-icon>
      Aperçu en lecture seule — les professeurs ne peuvent pas passer de quiz. La bonne réponse est surlignée.
    </p>
  </div>
</template>

<script setup lang="ts">
import type { QCM } from '@/types/course'

defineProps<{
  qcm: QCM
}>()
</script>

<style scoped>
.qcm-meta-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.points-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  color: color-mix(in srgb, var(--warning-color) 65%, black);
  background: color-mix(in srgb, var(--warning-color) 16%, var(--surface-color));
  padding: 5px 11px;
  border-radius: 999px;
}

.qcm-meta-row .hint {
  font-size: 12.5px;
  color: var(--text-light-color);
}

.quiz-question {
  border: 1px solid var(--border-color);
  background: var(--neutral-50);
  border-left: 3px solid var(--warning-color);
  border-radius: 8px;
  padding: 14px 16px 16px;
}

.quiz-question + .quiz-question {
  margin-top: 10px;
}

.quiz-question .enonce {
  font-size: 13.5px;
  font-weight: 700;
  margin: 0 0 12px;
}

.quiz-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border: 1px solid var(--border-color);
  background: var(--surface-color);
  border-radius: 7px;
  font-size: 13px;
}

.quiz-option + .quiz-option {
  margin-top: 8px;
}

.quiz-option.correct-preview {
  border-color: color-mix(in srgb, var(--success-color) 45%, var(--border-color));
  background: color-mix(in srgb, var(--success-color) 12%, var(--surface-color));
  font-weight: 600;
}

.quiz-option .check {
  margin-left: auto;
  color: var(--success-color);
  display: flex;
}

.teacher-note {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: var(--text-light-color);
  margin: 14px 0 0;
  padding-top: 14px;
  border-top: 1px solid var(--border-color);
}
</style>
