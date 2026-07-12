<template>
  <div class="qcm-form" data-testid="qcm-form">
    <div v-if="loading" class="state" data-testid="qcm-loading">Chargement du quiz...</div>
    <div v-else-if="errorMessage" class="state state-error" data-testid="qcm-error">
      {{ errorMessage }}
    </div>

    <div v-else-if="result" class="result" data-testid="qcm-result">
      <div class="result-icon"><v-icon size="22">mdi-check-circle-outline</v-icon></div>
      <h4>Quiz terminé</h4>
      <p class="result-score">Score : <strong>{{ result.score }} / {{ result.total }}</strong></p>
      <p class="result-points">
        Points gagnés : <strong>{{ result.earnedPts }}</strong><span v-if="gainPts !== null"> / {{ gainPts }}</span>
      </p>
      <p class="locked-note">Ce quiz a déjà été validé, il ne peut pas être refait.</p>
    </div>

    <template v-else>
      <div class="qcm-meta-row">
        <span class="points-badge">
          <v-icon size="13">mdi-star-four-points-outline</v-icon>
          {{ gainPts }} points
        </span>
        <span class="hint">{{ questions.length }} question(s)</span>
      </div>

      <form @submit.prevent="submit">
        <div
          v-for="(question, qIndex) in questions"
          :key="question.id"
          class="question"
          :data-testid="`question-${qIndex}`"
        >
          <p class="enonce">{{ qIndex + 1 }}. {{ question.enonce }}</p>

          <div class="options">
            <label v-for="reponse in question.reponses" :key="reponse.id" class="option">
              <input
                type="radio"
                :name="`question-${question.id}`"
                :value="reponse.id"
                v-model="answers[question.id]"
              />
              {{ reponse.texte }}
            </label>
          </div>
        </div>

        <button
          type="submit"
          class="submit-btn"
          :disabled="!allAnswered || submitting"
          data-testid="qcm-submit"
        >
          {{ submitting ? 'Envoi...' : 'Valider le quiz' }}
        </button>

        <p v-if="submitError" class="state state-error" data-testid="qcm-submit-error">
          {{ submitError }}
        </p>
      </form>
    </template>
  </div>
</template>

<script setup lang="ts">
import { courseService } from '@/services/courseService'
import type { ApiError, QuizQuestion, QuizResult } from '@/types'
import { computed, onMounted, reactive, ref, watch } from 'vue'

const props = defineProps<{
  activityId: number
}>()

const emit = defineEmits<{
  (e: 'completed', activityId: number): void
}>()

const loading = ref(true)
const errorMessage = ref('')
const submitting = ref(false)
const submitError = ref('')
const questions = ref<QuizQuestion[]>([])
const gainPts = ref<number | null>(null)
const result = ref<QuizResult | null>(null)
const answers = reactive<Record<number, number | null>>({})

const allAnswered = computed(
  () => questions.value.length > 0 && questions.value.every((question) => answers[question.id] != null),
)

onMounted(load)
watch(() => props.activityId, load)

async function load() {
  loading.value = true
  errorMessage.value = ''
  result.value = null
  questions.value = []

  try {
    const quiz = await courseService.getQuiz(props.activityId)
    gainPts.value = quiz.gainPts

    if (quiz.locked && quiz.result) {
      result.value = quiz.result
    } else {
      questions.value = quiz.questions ?? []
      questions.value.forEach((question) => {
        answers[question.id] = null
      })
    }
  } catch (error) {
    const err = error as ApiError
    errorMessage.value = err.response?.data?.error || 'Impossible de charger le quiz.'
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!allAnswered.value) {
    return
  }

  submitting.value = true
  submitError.value = ''

  try {
    const payload: Record<number, number> = {}
    questions.value.forEach((question) => {
      payload[question.id] = answers[question.id] as number
    })

    result.value = await courseService.submitQuiz(props.activityId, payload)
    emit('completed', props.activityId)
  } catch (error) {
    const err = error as ApiError
    submitError.value = err.response?.data?.error || 'Erreur lors de la validation du quiz.'
  } finally {
    submitting.value = false
  }
}
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

.question {
  border: 1px solid var(--border-color);
  background: var(--neutral-50);
  border-left: 3px solid var(--warning-color);
  border-radius: 8px;
  padding: 14px 16px 16px;
}

.question + .question {
  margin-top: 10px;
}

.enonce {
  font-size: 13.5px;
  font-weight: 700;
  margin: 0 0 12px;
}

.option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border: 1px solid var(--border-color);
  background: var(--surface-color);
  border-radius: 7px;
  font-size: 13px;
  cursor: pointer;
}

.option + .option {
  margin-top: 8px;
}

.option:hover {
  border-color: var(--primary-color);
}

.option input {
  accent-color: var(--primary-color);
}

.submit-btn {
  margin-top: 18px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 18px;
  background-color: var(--primary-color);
  color: var(--white-color);
  font-weight: 700;
  font-size: 13px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.submit-btn:not(:disabled):hover {
  background-color: var(--primary-hover-color);
}

.result {
  text-align: center;
  padding: 12px 8px;
}

.result-icon {
  color: var(--success-color);
  margin-bottom: 6px;
}

.result h4 {
  margin: 0 0 10px;
  font-size: 15px;
  font-weight: 800;
}

.result-score,
.result-points {
  margin: 0 0 4px;
  font-size: 13.5px;
  color: var(--text-muted-color);
}

.locked-note {
  margin-top: 10px;
  color: var(--text-light-color);
  font-size: 12px;
}

.state {
  color: var(--text-muted-color);
  font-size: 0.95rem;
}

.state-error {
  color: var(--danger-color);
}
</style>
