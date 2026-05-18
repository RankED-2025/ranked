<template>
  <div class="pa-6" style="max-width: 900px; margin: 0 auto; background: white; border-radius: 8px;">
    <h2 class="text-h5 font-weight-bold mb-6">Modifier le cours</h2>

    <div class="columns">
      <div class="column">
        <v-form @submit.prevent="submitForm" v-model="mainFormRef">
          <v-text-field
            v-model="form.title"
            label="Titre"
            variant="outlined"
            class="mb-4"
            :rules="required"
          />

          <v-text-field
            v-model="form.description"
            label="Description du cours"
            variant="outlined"
            class="mb-4"
            :rules="required"
          />

          <v-select
            v-model="form.matiere_id"
            :items="matieres"
            item-title="libelle"
            item-value="id"
            label="Matière"
            :loading="loadingMatieres"
            :disabled="loadingMatieres"
            variant="outlined"
            class="mb-4"
            :rules="required"
          />

          <v-select
            v-model="form.difficulte_id"
            :items="difficulties"
            item-title="label"
            item-value="id"
            label="Difficulté"
            :loading="loadingDifficulties"
            :disabled="loadingDifficulties"
            variant="outlined"
            class="mb-4"
            :rules="required"
          />

          <div class="d-flex gap-3 mb-4">
            <v-btn
              type="submit"
              color="primary"
              :loading="loading"
              :disabled="loading || !areFormsValid"
            >
              Enregistrer
            </v-btn>
            <v-btn variant="tonal" @click="router.back()">Annuler</v-btn>
          </div>

          <v-alert v-if="errorMessage" type="error" class="mt-2">{{ errorMessage }}</v-alert>
          <v-alert v-if="successMessage" type="success" class="mt-2">{{ successMessage }}</v-alert>
        </v-form>
      </div>

      <div class="column activities-column">
        <h3>Activités</h3>
        <div class="activities-actions">
          <v-btn color="primary" @click="addActivity">Ajouter une activité</v-btn>
        </div>

        <ul class="activities-list" ref="listRef">
          <li v-for="(act, index) in activities" :key="act.__localId" class="activity-item"
              draggable="true"
              @dragstart="onDragStart($event, index)"
              @dragover.prevent
              @drop="onDrop($event, index)">

            <div class="item-header">
              <strong>#{{ index + 1 }}</strong>
              <div class="item-actions">
                <v-btn @click="moveUp(index)" :disabled="index===0">
                    <v-icon>mdi-arrow-up</v-icon>
                </v-btn>
                <v-btn @click="moveDown(index)" :disabled="index===activities.length-1">
                    <v-icon>mdi-arrow-down</v-icon>
                </v-btn>
                <v-btn @click="removeActivity(index)">
                    <v-icon color="red">mdi-delete</v-icon>
                </v-btn>
              </div>
            </div>

            <div class="item-body">
              <v-select
                v-model="act.type"
                :items="[
                  { title: 'Contenu', value: 'contenu' },
                  { title: 'QCM', value: 'qcm' }
                ]"
                :rules="required"
                label="Type"
                variant="outlined"
                density="compact"
                class="mb-3"
              />

              <section v-if="act.type === 'contenu'">
                <v-text-field
                  v-model="act.contenu.url"
                  label="Contenu URL"
                  placeholder="https://..."
                  variant="outlined"
                  :rules="required"
                  density="compact"
                  class="mb-3"
                />

                <v-select
                  v-model="act.contenu.type"
                  :items="[
                    { title: 'Vidéo', value: 'video' },
                    { title: 'Image', value: 'image' },
                    { title: 'PDF', value: 'pdf' },
                    { title: 'Article', value: 'article' }
                  ]"
                  label="Contenu Type"
                  :rules="required"
                  variant="outlined"
                  density="compact"
                  class="mb-3"
                />
              </section>

              <section v-if="act.type === 'qcm'">
                <v-text-field
                  v-model.number="act.qcm.gainPts"
                  label="QCM gainPts"
                  type="number"
                  :rules="required"
                  variant="outlined"
                  density="compact"
                />
              </section>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { courseService } from '@/services/courseService'
import { referentielService } from '@/services/referentielService'
import type { CreateCourseData, Difficulte, Matiere } from '@/types'
import { isProfesseur } from '@/utils'
import { useAuth } from '@/composables'
import { required } from '@/rules/common-rules'

const router = useRouter()
const route = useRoute()
const { user } = useAuth()
const isProfessor = computed(() => isProfesseur(user.value?.roles ?? []))
const id = Number(route.params.id)

const mainFormRef = ref(false)
const activityFormRef = computed(() => {
  return activities.value.every((act) => {
    switch (act.type) {
      case 'contenu':
        return !!act.contenu.url && !!act.contenu.type
      case 'qcm':
        return !!act.qcm.gainPts && act.qcm.gainPts > 0
      default:
        return false
    }
  })
})
const areFormsValid = computed(() => mainFormRef.value && activityFormRef.value)
const form = ref<Partial<CreateCourseData>>({
  title: '',
  description: '',
  matiere_id: undefined,
  difficulte_id: undefined,
})
const matieres = ref<Matiere[]>([])
const difficulties = ref<Difficulte[]>([])

const activities = ref<any[]>([])

const loadingMatieres = ref(true)
const loadingDifficulties = ref(true)
const loading = ref(false)

const errorMessage = ref('')
const successMessage = ref('')

let dragIndex: number | null = null

function makeLocalId(item: any) {
  if (!item.__localId) item.__localId = `local_${Math.random().toString(36).slice(2,9)}`
  return item.__localId
}

onMounted(async () => {
  try {
    matieres.value = await referentielService.getMatieres()
    difficulties.value = await referentielService.getDifficultes()

    const data = await courseService.getCourseContentById(String(id))
    form.value.title = data.title
    form.value.description = data.description
    form.value.matiere_id = data.matiere?.id
    form.value.difficulte_id = data.difficulte?.id

    activities.value = (data.activites || []).map((activity: any, idx: number) => ({
      id: activity.id,
      type: activity.type || '',
      ordre: activity.ordre ?? idx,
      contenu: activity.contenu ? { id: activity.contenu.id, type: activity.contenu.type ?? '', url: activity.contenu.url ?? '' } : { id: null, type: '', url: '' },
      qcm: activity.qcm ? { id: activity.qcm.id, gainPts: activity  .qcm.gainPts ?? 0 } : { id: null, gainPts: 0 },
    }))
    activities.value.forEach(makeLocalId)
  } catch (error: unknown) {
    const err = error as { response?: { data?: { error?: string } } }
    errorMessage.value = err.response?.data?.error || 'Erreur lors du chargement du cours'
  } finally {
    loadingMatieres.value = false
    loadingDifficulties.value = false
  }
})

function addActivity() {
  const a = { id: null, type: 'contenu', ordre: activities.value.length, contenu: { id: null, type: '', url: '' }, qcm: { id: null, gainPts: 0 } }
  makeLocalId(a)
  activities.value.push(a)
}

function removeActivity(index: number) {
  activities.value.splice(index, 1)
  reindex()
}

function moveUp(index: number) {
  if (index <= 0){
    return
  }
  const tmp = activities.value[index - 1]
  activities.value[index - 1] = activities.value[index]
  activities.value[index] = tmp
  reindex()
}

function moveDown(index: number) {
  if (index >= activities.value.length - 1) {
    return
 }
  const tmp = activities.value[index + 1]
  activities.value[index + 1] = activities.value[index]
  activities.value[index] = tmp
  reindex()
}

function reindex() {
  activities.value.forEach((a, i) => a.ordre = i)
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
  activities.value.splice(to, 0, item)
  reindex()
  dragIndex = null
}

async function submitForm() {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const payload: any = {
      title: form.value.title ?? undefined,
      description: form.value.description ?? undefined,
      matiere_id: form.value.matiere_id ?? undefined,
      difficulte_id: form.value.difficulte_id ?? undefined,
      activites: activities.value.map((a, idx) => {
        const activityPayload: any = {
          id: a.id ?? null,
          type: a.type,
          ordre: idx,
        }

        if (a.type === 'contenu') {
          activityPayload.contenu = {
            id: a.contenu?.id ?? null,
            type: a.contenu?.type || null,
            url: a.contenu?.url || null,
          }
        }

        if (a.type === 'qcm') {
          activityPayload.qcm = {
            id: a.qcm?.id ?? null,
            gainPts: a.qcm?.gainPts ?? null,
          }
        }

        return activityPayload
      })
    }

    await courseService.editCourse(id, payload)
    successMessage.value = 'Cours modifié avec succès !'
    setTimeout(() => router.push(isProfessor ? '/professor/my-courses' : '/my-courses'), 1000)
  } catch (error: unknown) {
    const err = error as { response?: { data?: { error?: string } } }
    errorMessage.value = err.response?.data?.error || 'Erreur lors de la modification du cours'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.columns { display:flex; gap:20px }
.column { flex:1 }
.activities-column { width: 420px }
.activities-list { list-style:none; padding:0; margin-top:10px }
.activity-item { border:1px solid #ddd; padding:10px; margin-bottom:8px; border-radius:6px; background:#fafafa }
.item-header { display:flex; justify-content:space-between; align-items:center }
.item-actions button { margin-left:6px }
.item-body { margin-top:8px; display:flex; flex-direction:column; gap:6px }
.item-body input { padding:6px; border:1px solid #ccc; border-radius:4px }
.activities-actions { margin-bottom:8px }
</style>