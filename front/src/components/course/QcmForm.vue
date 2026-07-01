<template>
  <div class="qcm-form" data-testid="qcm-form">
    <div v-if="loading" class="state" data-testid="qcm-loading">Chargement du quiz...</div>
    <div v-else-if="errorMessage" class="state state-error" data-testid="qcm-error">
      {{ errorMessage }}
    </div>

    <div v-else-if="result" class="result" data-testid="qcm-result">
      <h4>Quiz terminé</h4>
      <p>Score : {{ result.score }} / {{ result.total }}</p>
      <p>
        Points gagnés : {{ result.earnedPts }}<span v-if="gainPts !== null"> / {{ gainPts }}</span>
      </p>
      <p class="locked-note">Ce quiz a déjà été validé, il ne peut pas être refait.</p>
    </div>

    <form v-else @submit.prevent="submit">
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
.qcm-form {
  padding: 20px;
  border: 1px solid var(--border-strong-color);
  border-radius: 8px;
}

.question {
  margin: 20px 0;
}

.enonce {
  font-weight: 600;
  margin-bottom: 8px;
}

.options {
  margin-top: 10px;
}

.option {
  display: block;
  margin: 8px 0;
  cursor: pointer;
}

.submit-btn {
  padding: 10px 20px;
  background-color: var(--success-color);
  color: var(--white-color);
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.submit-btn:not(:disabled):hover {
  background-color: var(--success-hover-color);
}

.result h4 {
  margin-bottom: 8px;
}

.locked-note {
  margin-top: 10px;
  color: var(--text-muted-color);
  font-size: 0.9rem;
}

.state {
  color: var(--text-muted-color);
  font-size: 0.95rem;
}

.state-error {
  color: var(--danger-color);
}
</style>
