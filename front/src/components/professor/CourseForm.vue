<template>
  <div class="course-form">
    <div v-if="initialLoading" class="edit-shell">
      <div class="form-col">
        <v-skeleton-loader type="card" />
        <v-skeleton-loader type="card" />
      </div>
      <div v-if="mode === 'edit'" class="activities-col">
        <v-skeleton-loader type="card" class="mb-3" />
        <v-skeleton-loader type="card" class="mb-3" />
        <v-skeleton-loader type="card" />
      </div>
    </div>

    <div v-else class="edit-shell">
      <v-form @submit.prevent="submitForm" v-model="mainFormValid" class="form-col">
        <div class="form-section">
          <h4>Détails du cours</h4>
          <v-text-field
            v-model="form.title"
            label="Titre *"
            variant="outlined"
            class="mb-4"
            :rules="required"
          />

          <v-textarea
            v-model="form.description"
            label="Description du cours *"
            variant="outlined"
            rows="3"
            auto-grow
            :rules="required"
          />
        </div>

        <div class="form-section">
          <h4>Classification</h4>
          <div class="field-row">
            <v-select
              v-model="form.matiere_id"
              :items="matieres"
              item-title="libelle"
              item-value="id"
              label="Matière *"
              :loading="loadingMatieres"
              :disabled="loadingMatieres"
              variant="outlined"
              :rules="required"
            />

            <v-select
              v-model="form.difficulte_id"
              :items="difficulties"
              item-title="label"
              item-value="id"
              label="Difficulté *"
              :loading="loadingDifficulties"
              :disabled="loadingDifficulties"
              variant="outlined"
              :rules="required"
            />
          </div>
        </div>
      </v-form>

      <!-- Colonne ressources (edit uniquement) -->
      <div v-if="mode === 'edit'" class="activities-col">
        <div class="activities-head">
          <h3>Ressources <span class="count">{{ activities.length }}</span></h3>
        </div>

        <div class="activity-list">
          <div
            v-for="(act, index) in activities"
            :key="act.__localId"
            class="activity-card"
            draggable="true"
            @dragstart="onDragStart($event, index)"
            @dragover.prevent
            @drop="onDrop($event, index)"
          >
            <div class="activity-card-head">
              <v-icon class="drag-handle" size="16">mdi-drag-vertical</v-icon>
              <span class="order-badge">#{{ index + 1 }}</span>
              <span class="type-badge" :class="act.type">{{ act.type === 'qcm' ? 'QCM' : 'Contenu' }}</span>
              <span class="head-spacer" />
              <button
                class="icon-btn"
                type="button"
                :disabled="index === 0"
                @click="moveUp(index)"
                aria-label="Monter la ressource"
              >
                <v-icon size="15">mdi-arrow-up</v-icon>
              </button>
              <button
                class="icon-btn"
                type="button"
                :disabled="index === activities.length - 1"
                @click="moveDown(index)"
                aria-label="Descendre la ressource"
              >
                <v-icon size="15">mdi-arrow-down</v-icon>
              </button>
              <button class="icon-btn danger" type="button" @click="removeActivity(index)" aria-label="Supprimer la ressource">
                <v-icon size="15">mdi-delete-outline</v-icon>
              </button>
            </div>

            <div class="activity-body">
              <v-select
                v-model="act.type"
                :items="[
                  { title: 'Contenu', value: 'contenu' },
                  { title: 'QCM', value: 'qcm' },
                ]"
                @update:modelValue="onActivityTypeChange(act)"
                :rules="required"
                label="Type"
                variant="outlined"
                density="compact"
                class="mb-3"
              />

              <section v-if="act.type === 'contenu' && act.contenu">
                <div class="content-row">
                  <span class="content-type-pill">
                    <v-icon size="15">{{ contentTypeMeta(act.contenu.type).icon }}</v-icon>
                    {{ contentTypeMeta(act.contenu.type).label }}
                  </span>
                  <v-text-field
                    v-model="act.contenu.url"
                    label="URL"
                    placeholder="https://..."
                    variant="outlined"
                    :rules="required"
                    density="compact"
                    hide-details="auto"
                  />
                </div>
                <v-select
                  v-model="act.contenu.type"
                  :items="[
                    { title: 'Vidéo', value: 'video' },
                    { title: 'Image', value: 'image' },
                    { title: 'PDF', value: 'pdf' },
                    { title: 'Article', value: 'article' },
                  ]"
                  label="Type de contenu"
                  :rules="required"
                  variant="outlined"
                  density="compact"
                  class="mt-3"
                />
              </section>

              <section v-if="act.type === 'qcm' && act.qcm">
                <div class="qcm-meta">
                  <v-text-field
                    v-model.number="act.qcm.gainPts"
                    label="Points"
                    type="number"
                    :rules="required"
                    variant="outlined"
                    density="compact"
                    hide-details="auto"
                    class="qcm-points-field"
                  />
                  <span class="hint">{{ act.qcm.questions?.length ?? 0 }} question(s)</span>
                </div>

                <div class="question-list">
                  <div
                    v-for="(question, qIndex) in act.qcm.questions"
                    :key="question.__uid"
                    class="question-card"
                  >
                    <div class="question-head">
                      <strong>Question {{ qIndex + 1 }}</strong>
                      <button
                        class="icon-btn danger"
                        type="button"
                        @click="removeQuestion(act, qIndex)"
                        :data-testid="`remove-question-${qIndex}`"
                        aria-label="Supprimer la question"
                      >
                        <v-icon size="14">mdi-close</v-icon>
                      </button>
                    </div>

                    <v-text-field
                      v-model="question.enonce"
                      label="Énoncé"
                      :rules="required"
                      variant="outlined"
                      density="compact"
                      class="mb-2"
                    />

                    <v-radio-group
                      :model-value="correctIndex(question)"
                      @update:model-value="(value: unknown) => setCorrect(question, Number(value))"
                      :rules="atLeastOneCorrect"
                      hide-details="auto"
                    >
                      <div
                        v-for="(reponse, rIndex) in question.reponses"
                        :key="reponse.__uid"
                        class="answer-row"
                      >
                        <v-radio :value="rIndex" :data-testid="`correct-${qIndex}-${rIndex}`" density="compact" />
                        <v-text-field
                          v-model="reponse.texte"
                          label="Réponse"
                          :rules="required"
                          variant="outlined"
                          density="compact"
                          hide-details="auto"
                        />
                        <button
                          class="icon-btn danger"
                          type="button"
                          :disabled="question.reponses.length <= 2"
                          @click="removeReponse(question, rIndex)"
                          :data-testid="`remove-reponse-${qIndex}-${rIndex}`"
                          aria-label="Supprimer la réponse"
                        >
                          <v-icon size="14">mdi-close</v-icon>
                        </button>
                      </div>
                    </v-radio-group>

                    <button
                      class="ghost-btn"
                      type="button"
                      @click="addReponse(question)"
                      :data-testid="`add-reponse-${qIndex}`"
                    >
                      <v-icon size="14">mdi-plus</v-icon>
                      Ajouter une réponse
                    </button>
                  </div>
                </div>

                <button
                  class="ghost-btn add-question-btn"
                  type="button"
                  @click="addQuestion(act)"
                  data-testid="add-question"
                >
                  <v-icon size="14">mdi-plus</v-icon>
                  Ajouter une question
                </button>
              </section>
            </div>
          </div>

          <button class="add-activity-card" type="button" @click="addActivity">
            <v-icon size="16">mdi-plus</v-icon>
            Ajouter une ressource
          </button>
        </div>
      </div>
    </div>

    <template v-if="!initialLoading">
      <StatusAlert v-model:error="loadError" test-id="load-error-message" />
      <StatusAlert v-model:error="submitError" test-id="submit-error-message" />
      <v-alert v-if="successMessage" type="success" class="mt-4">{{ successMessage }}</v-alert>

      <div class="action-bar">
        <v-btn
          color="primary"
          :loading="loading"
          :disabled="!isSubmittable"
          @click="submitForm"
        >
          {{ mode === 'create' ? 'Créer le cours' : 'Enregistrer' }}
        </v-btn>
        <v-btn variant="tonal" @click="cancel">Annuler</v-btn>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { courseService } from '@/services/courseService'
import { referentielService } from '@/services/referentielService'
import type {
  CourseActivity,
  CourseEditData,
  CreateCourseData,
  Difficulte,
  Matiere,
  Question,
  Reponse,
} from '@/types'
import { isProfesseur } from '@/utils'
import { useAuth } from '@/composables'
import { required } from '@/utils/validation'
import StatusAlert from '@/components/layouts/StatusAlert.vue'

type LocalActivity = CourseActivity & { __localId?: string }

const props = defineProps<{
  mode: 'create' | 'edit'
}>()

const atLeastOneCorrect = [
  (value: unknown) =>
    (value !== null && value !== undefined && value !== '') || 'Sélectionnez la bonne réponse',
]

const CONTENT_TYPE_META: Record<string, { icon: string; label: string }> = {
  video: { icon: 'mdi-play-box-outline', label: 'Vidéo' },
  article: { icon: 'mdi-file-document-outline', label: 'Article' },
  pdf: { icon: 'mdi-file-pdf-box', label: 'PDF' },
  image: { icon: 'mdi-image-outline', label: 'Image' },
}

function contentTypeMeta(type: string | undefined) {
  return CONTENT_TYPE_META[type ?? ''] ?? { icon: 'mdi-file-outline', label: 'Contenu' }
}

function makeUid() {
  return `uid_${globalThis.crypto.randomUUID()}`
}

function newReponse(isCorrect = false): Reponse {
  return { id: null, texte: '', isCorrect, __uid: makeUid() }
}

function newQuestion(): Question {
  return { id: null, enonce: '', reponses: [newReponse(true), newReponse()], __uid: makeUid() }
}

function correctIndex(question: Question): number {
  const index = question.reponses.findIndex((reponse) => reponse.isCorrect)
  return index === -1 ? 0 : index
}

function setCorrect(question: Question, index: number) {
  question.reponses.forEach((reponse, i) => {
    reponse.isCorrect = i === index
  })
}

function addQuestion(activity: LocalActivity) {
  if (!activity.qcm) return
  if (!activity.qcm.questions) activity.qcm.questions = []
  activity.qcm.questions.push(newQuestion())
}

function removeQuestion(activity: LocalActivity, index: number) {
  activity.qcm?.questions?.splice(index, 1)
}

function addReponse(question: Question) {
  question.reponses.push(newReponse())
}

function removeReponse(question: Question, index: number) {
  const wasCorrect = question.reponses[index]?.isCorrect
  question.reponses.splice(index, 1)
  if (wasCorrect && question.reponses.length > 0) {
    setCorrect(question, 0)
  }
}

const router = useRouter()
const route = useRoute()
const { user } = useAuth()
const isProfessor = computed(() => isProfesseur(user.value?.roles ?? []))
const courseId = computed(() => Number(route.params.id))

// — état du formulaire principal —
const mainFormValid = ref(false)
const activityFormValid = computed(() =>
  activities.value.every(
    (act: CourseActivity) =>
      act.type &&
      (act.type === 'contenu'
        ? act.contenu && act.contenu.url && act.contenu.type
        : act.type === 'qcm'
          ? act.qcm && act.qcm.gainPts && act.qcm.gainPts > 0
          : false),
  ),
)

const isSubmittable = computed(() => {
  if (props.mode === 'create') {
    return !!form.value.matiere_id && !!form.value.difficulte_id && !loading.value
  }
  return !loading.value && mainFormValid.value && activityFormValid.value
})

const form = ref<Partial<CreateCourseData>>({
  title: '',
  description: '',
  matiere_id: undefined,
  difficulte_id: undefined,
})
const matieres = ref<Matiere[]>([])
const difficulties = ref<Difficulte[]>([])
const activities = ref<LocalActivity[]>([])

const loadingMatieres = ref(true)
const loadingDifficulties = ref(true)
const loading = ref(false)

const initialLoading = computed(() => loadingMatieres.value || loadingDifficulties.value)

const loadError = ref<unknown>(null)
const submitError = ref<unknown>(null)
const successMessage = ref('')

let dragIndex: number | null = null

function makeLocalId(item: LocalActivity) {
  if (!item.__localId) item.__localId = `local_${globalThis.crypto.randomUUID()}`
  return item.__localId
}

onMounted(async () => {
  try {
    matieres.value = await referentielService.getMatieres()
    difficulties.value = await referentielService.getDifficultes()

    if (props.mode === 'edit') {
      const data = await courseService.getCourseContentById(String(courseId.value))
      form.value.title = data.title
      form.value.description = data.description
      form.value.matiere_id = data.matiere?.id
      form.value.difficulte_id = data.difficulte?.id

      activities.value = (data.activites || []).map(
        (activity: CourseActivity, idx: number): LocalActivity => {
          const ordre = activity.ordre ?? idx
          if (activity.type === 'contenu') {
            return {
              id: activity.id ?? null,
              type: 'contenu',
              ordre,
              contenu: {
                id: activity.contenu!.id,
                type: activity.contenu?.type ?? 'article',
                url: activity.contenu?.url ?? undefined,
              },
              qcm: undefined,
              completed: activity.completed,
            }
          }
          return {
            id: activity.id ?? null,
            type: 'qcm',
            ordre,
            contenu: undefined,
            qcm: {
              id: activity.qcm!.id,
              gainPts: activity.qcm?.gainPts ?? 0,
            },
            completed: activity.completed,
          }
        },
      )
      activities.value.forEach(makeLocalId)
    }
  } catch (error) {
    loadError.value = error
  } finally {
    loadingMatieres.value = false
    loadingDifficulties.value = false
  }
})

function cancel() {
  if (props.mode === 'create') {
    router.push('/')
  } else {
    router.back()
  }
}

// — gestion des activités —

function addActivity() {
  const a: LocalActivity = {
    id: 0,
    type: 'contenu',
    ordre: activities.value.length,
    contenu: { id: 0, type: 'article', url: '' },
    qcm: { id: 0, gainPts: 0 },
    completed: false,
  }
  makeLocalId(a)
  activities.value.push(a)
}

function onActivityTypeChange(activity: LocalActivity) {
  if (activity.type === 'contenu' && !activity.contenu) {
    activity.contenu = { id: 0, type: 'article', url: '' }
  }
  if (activity.type === 'qcm' && !activity.qcm) {
    activity.qcm = { id: 0, gainPts: 0 }
  }

  if (activity.type === 'qcm' && activity.qcm && (activity.qcm.questions?.length ?? 0) === 0) {
    activity.qcm.questions = [newQuestion()]
  }
}

function removeActivity(index: number) {
  activities.value.splice(index, 1)
  reindex()
}

function moveActivity(from: number, to: number) {
  if (to < 0 || to >= activities.value.length || from === to) return
  const [item] = activities.value.splice(from, 1)
  activities.value.splice(to, 0, item!)
  reindex()
}

function moveUp(index: number) {
  moveActivity(index, index - 1)
}

function moveDown(index: number) {
  moveActivity(index, index + 1)
}

function reindex() {
  activities.value.forEach((a, i) => (a.ordre = i))
}

function onDragStart(e: DragEvent, index: number) {
  dragIndex = index
  e.dataTransfer?.setData('text/plain', String(index))
}

function onDrop(e: DragEvent, index: number) {
  const from = dragIndex ?? Number(e.dataTransfer?.getData('text/plain'))
  const to = index
  if (from === to) return
  const item = activities.value.splice(from, 1)[0]
  activities.value.splice(to, 0, item!)
  reindex()
  dragIndex = null
}

// — soumission —

async function submitForm() {
  if (!isSubmittable.value) return

  loading.value = true
  submitError.value = null
  successMessage.value = ''

  try {
    if (props.mode === 'create') {
      await courseService.createCourse(form.value as CreateCourseData)
      successMessage.value = 'Cours créé avec succès !'
      setTimeout(() => router.push('/'), 1500)
    } else {
      const payload: CourseEditData = {
        title: form.value.title!,
        description: form.value.description!,
        matiere_id: form.value.matiere_id!,
        difficulte_id: form.value.difficulte_id!,
        activites: activities.value.map((a, idx): CourseActivity => {
          if (a.type === 'contenu') {
            return {
              id: a.id ?? null,
              type: 'contenu',
              ordre: idx,
              contenu: {
                id: a.contenu!.id,
                type: a.contenu?.type ?? 'article',
                url: a.contenu?.url ?? '',
              },
              completed: a.completed,
            }
          }
          return {
            id: a.id ?? null,
            type: 'qcm',
            ordre: idx,
            qcm: {
              id: a.qcm!.id ?? null,
              gainPts: a.qcm?.gainPts ?? 0,
            },
            completed: a.completed,
          }
        }),
      }
      await courseService.editCourse(courseId.value, payload)
      successMessage.value = 'Cours modifié avec succès !'
      setTimeout(
        () => router.push(isProfessor.value ? '/professor/my-courses' : '/my-courses'),
        1000,
      )
    }
  } catch (error) {
    submitError.value = error
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.edit-shell {
  display: grid;
  grid-template-columns: 0.85fr 1.3fr;
  gap: 20px;
  align-items: start;
}

@media (max-width: 940px) {
  .edit-shell {
    grid-template-columns: 1fr;
  }
}

.form-col {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-section {
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: var(--surface-color);
  padding: 16px 18px 4px;
}

.form-section h4 {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-light-color);
  margin: 0 0 12px;
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

@media (max-width: 480px) {
  .field-row {
    grid-template-columns: 1fr;
  }
}

/* ── Activities column ────────────────────────────── */
.activities-head {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}

.activities-head h3 {
  font-size: 15px;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.activities-head .count {
  font-size: 11.5px;
  font-weight: 700;
  color: var(--text-light-color);
  background: var(--neutral-100);
  border-radius: 999px;
  padding: 2px 9px;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.activity-card {
  border: 1px solid var(--border-color);
  border-radius: 10px;
  background: var(--surface-color);
  overflow: hidden;
}

.activity-card-head {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-bottom: 1px solid var(--border-color);
  background: var(--neutral-50);
}

.drag-handle {
  color: var(--text-light-color);
  cursor: grab;
  flex-shrink: 0;
}

.order-badge {
  font-variant-numeric: tabular-nums;
  font-size: 11px;
  font-weight: 700;
  color: var(--text-light-color);
  flex-shrink: 0;
}

.type-badge {
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 5px;
  flex-shrink: 0;
}

.type-badge.contenu {
  background: var(--primary-soft-color);
  color: var(--primary-color);
}

.type-badge.qcm {
  background: color-mix(in srgb, var(--warning-color) 16%, var(--surface-color));
  color: color-mix(in srgb, var(--warning-color) 65%, black);
}

.head-spacer {
  flex: 1;
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
  flex-shrink: 0;
}

.icon-btn:hover {
  background: var(--neutral-100);
}

.icon-btn:disabled {
  opacity: 0.35;
  cursor: default;
}

.icon-btn:disabled:hover {
  background: transparent;
}

.icon-btn.danger:hover {
  color: var(--danger-color);
  background: color-mix(in srgb, var(--danger-color) 10%, transparent);
}

.activity-body {
  padding: 14px 16px 16px;
}

.content-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.content-type-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted-color);
  border: 1px solid var(--border-strong-color);
  border-radius: 6px;
  padding: 8px 10px;
  flex-shrink: 0;
  white-space: nowrap;
}

.content-row .v-text-field {
  flex: 1;
}

.qcm-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 14px;
}

.qcm-points-field {
  max-width: 120px;
}

.qcm-meta .hint {
  font-size: 12px;
  color: var(--text-light-color);
}

.question-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.question-card {
  background: var(--neutral-50);
  border: 1px solid var(--border-color);
  border-left: 3px solid var(--warning-color);
  border-radius: 8px;
  padding: 12px 14px 14px;
}

.question-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.question-head strong {
  font-size: 12.5px;
  font-weight: 700;
}

.answer-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  color: var(--primary-color);
  background: transparent;
  border: none;
  padding: 6px 4px;
  cursor: pointer;
}

.add-question-btn {
  margin-top: 12px;
  width: 100%;
  justify-content: center;
  border: 1px dashed var(--border-strong-color);
  border-radius: 7px;
  padding: 9px;
  color: var(--text-muted-color);
}

.add-activity-card {
  border: 1px dashed var(--border-strong-color);
  border-radius: 10px;
  padding: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: var(--text-muted-color);
  background: transparent;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}

.add-activity-card:hover {
  border-color: var(--primary-color);
  color: var(--primary-color);
}

.action-bar {
  display: flex;
  gap: 10px;
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid var(--border-color);
}
</style>
