<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { statisticService } from '@/services/statisticService'
import type { MyBadgeDetail, MyCompetenceDetail } from '@/types'
import BadgeElement from '@components/layouts/BadgeElement.vue'
import LoadingElement from '@components/loading/LoadingElement.vue'

const router = useRouter()
const activeTab = ref<'badges' | 'competences'>('badges')

const badges = ref<MyBadgeDetail[]>([])
const competences = ref<MyCompetenceDetail[]>([])
const loadingBadges = ref(true)
const loadingCompetences = ref(true)

const openPanels = ref<string[]>(['in-progress'])
const openCompetencePanels = ref<string[]>([])

onMounted(() => {
  statisticService
    .getMyBadgesDetail()
    .then((d) => (badges.value = d))
    .finally(() => {
      loadingBadges.value = false
    })
  
  statisticService
    .getMyCompetencesDetail()
    .then((d) => {
      competences.value = d
      openCompetencePanels.value = [...new Set(d.map((c) => c.matiere))]
    })
    .finally(() => {
      loadingCompetences.value = false
    })
})

const acquiredBadges = computed(() => badges.value.filter((b) => b.percentage === 100))
const inProgressBadges = computed(() => badges.value.filter((b) => b.percentage < 100))

const acquiredCompetences = computed(() => competences.value.filter((c) => c.acquired))
const inProgressCompetences = computed(() => competences.value.filter((c) => !c.acquired))

const competencesByMatiere = computed(() => {
  const grouped: Record<string, MyCompetenceDetail[]> = {}
  for (const c of competences.value) {
    if (!grouped[c.matiere]) grouped[c.matiere] = [] as MyCompetenceDetail[]
    ;(grouped[c.matiere] as MyCompetenceDetail[]).push(c)
  }
  return grouped
})

const goToCourse = (courseId: number) => router.push(`/course/${courseId}`)
</script>

<template>
  <v-row justify="center" no-gutters>
    <v-col cols="12" sm="10" md="8" class="px-4 px-sm-0 py-6">
      <div class="mb-6">
        <h1 class="text-h4 font-weight-bold">Mes badges &amp; compétences</h1>
        <p class="text-body-1 text-grey-darken-1 mt-1">
          Suivi de votre progression et de vos acquis
        </p>
      </div>

      <v-tabs v-model="activeTab" class="mb-6">
        <v-tab value="badges">
          <v-icon class="mr-2">mdi-trophy</v-icon>
          Badges
        </v-tab>
        <v-tab value="competences">
          <v-icon class="mr-2">mdi-star-circle</v-icon>
          Compétences
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <!-- ── BADGES ── -->
        <v-window-item value="badges">
          <LoadingElement v-if="loadingBadges" />
          <div v-else-if="badges.length === 0" class="text-center py-12 text-grey-darken-1">
            <v-icon size="64" class="mb-4">mdi-trophy-outline</v-icon>
            <p>Aucun badge pour le moment. Complétez des cours pour en obtenir !</p>
          </div>

          <v-expansion-panels
            v-else
            v-model="openPanels"
            multiple
            variant="accordion"
            rounded="lg"
            elevation="2"
          >
            <!-- En cours d'acquisition -->
            <v-expansion-panel v-if="inProgressBadges.length" value="in-progress" elevation="0">
              <v-expansion-panel-title>
                <div class="d-flex align-center ga-2">
                  <v-icon color="warning">mdi-clock-outline</v-icon>
                  <span class="font-weight-bold">En cours d'acquisition</span>
                  <v-chip size="x-small" color="warning" variant="tonal">{{
                    inProgressBadges.length
                  }}</v-chip>
                </div>
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <v-row class="mt-1">
                  <v-col
                    v-for="badge in inProgressBadges"
                    :key="badge.courseId"
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-card
                      elevation="2"
                      rounded="lg"
                      class="pa-4 text-center badge-card badge-in-progress"
                    >
                      <BadgeElement :badge-name="badge.badgeType" />
                      <div class="text-subtitle-1 font-weight-bold mt-3">
                        {{ badge.badgeLabel }}
                      </div>
                      <div class="text-body-2 text-grey-darken-1 mt-1">{{ badge.courseTitle }}</div>
                      <v-progress-linear
                        :model-value="badge.percentage"
                        color="warning"
                        height="8"
                        rounded
                        class="mt-3"
                      />
                      <div class="text-caption text-grey mt-1 mb-3">
                        {{ badge.percentage }} % complété
                      </div>
                      <v-btn
                        size="small"
                        variant="tonal"
                        color="primary"
                        prepend-icon="mdi-book-open-page-variant"
                        @click="goToCourse(badge.courseId)"
                      >
                        Voir le cours
                      </v-btn>
                    </v-card>
                  </v-col>
                </v-row>
              </v-expansion-panel-text>
            </v-expansion-panel>

            <!-- Badges obtenus -->
            <v-expansion-panel v-if="acquiredBadges.length" value="acquired" elevation="0">
              <v-expansion-panel-title>
                <div class="d-flex align-center ga-2">
                  <v-icon color="success">mdi-check-circle</v-icon>
                  <span class="font-weight-bold">Badges obtenus</span>
                  <v-chip size="x-small" color="success" variant="tonal">{{
                    acquiredBadges.length
                  }}</v-chip>
                </div>
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <v-row class="mt-1">
                  <v-col
                    v-for="badge in acquiredBadges"
                    :key="badge.courseId"
                    cols="12"
                    sm="6"
                    md="4"
                  >
                    <v-card
                      elevation="2"
                      rounded="lg"
                      class="pa-4 text-center badge-card badge-acquired"
                    >
                      <BadgeElement :badge-name="badge.badgeType" />
                      <div class="text-subtitle-1 font-weight-bold mt-3">
                        {{ badge.badgeLabel }}
                      </div>
                      <div class="text-body-2 text-grey-darken-1 mt-1 mb-3">
                        {{ badge.courseTitle }}
                      </div>
                      <v-chip color="success" size="small" class="mb-3">100 % complété</v-chip>
                      <br />
                      <v-btn
                        size="small"
                        variant="tonal"
                        color="primary"
                        prepend-icon="mdi-book-open-page-variant"
                        @click="goToCourse(badge.courseId)"
                      >
                        Voir le cours
                      </v-btn>
                    </v-card>
                  </v-col>
                </v-row>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </v-window-item>

        <!-- ── COMPÉTENCES ── -->
        <v-window-item value="competences">
          <LoadingElement v-if="loadingCompetences" />
          <div v-else-if="competences.length === 0" class="text-center py-12 text-grey-darken-1">
            <v-icon size="64" class="mb-4">mdi-star-outline</v-icon>
            <p>Aucune compétence associée à vos cours pour le moment.</p>
          </div>
          <div v-else>
            <div class="d-flex ga-4 mb-6">
              <v-chip color="success" variant="tonal">
                <v-icon start>mdi-check</v-icon>
                {{ acquiredCompetences.length }} acquise{{
                  acquiredCompetences.length > 1 ? 's' : ''
                }}
              </v-chip>
              <v-chip color="warning" variant="tonal">
                <v-icon start>mdi-clock-outline</v-icon>
                {{ inProgressCompetences.length }} en cours
              </v-chip>
            </div>

            <v-expansion-panels
              v-model="openCompetencePanels"
              multiple
              variant="accordion"
              rounded="lg"
              elevation="2"
            >
              <v-expansion-panel
                v-for="(items, matiere) in competencesByMatiere"
                :key="matiere"
                :value="matiere"
                elevation="0"
              >
                <v-expansion-panel-title>
                  <div class="d-flex align-center ga-2">
                    <span class="font-weight-bold">{{ matiere }}</span>
                    <v-chip size="x-small" color="success" variant="tonal">
                      {{ items.filter((c) => c.acquired).length }} / {{ items.length }}
                    </v-chip>
                  </div>
                </v-expansion-panel-title>
                <v-expansion-panel-text class="pa-0">
                  <v-list lines="two" class="py-0">
                    <template v-for="(competence, index) in items" :key="competence.id">
                      <v-list-item>
                        <template #prepend>
                          <v-icon
                            :color="competence.acquired ? 'success' : 'grey-lighten-1'"
                            class="mr-2"
                          >
                            {{ competence.acquired ? 'mdi-check-circle' : 'mdi-circle-outline' }}
                          </v-icon>
                        </template>

                        <v-list-item-title class="font-weight-medium">{{
                          competence.nom
                        }}</v-list-item-title>
                        <v-list-item-subtitle>{{ competence.courseTitle }}</v-list-item-subtitle>

                        <template #append>
                          <div class="d-flex flex-column align-end ga-1">
                            <v-chip
                              :color="competence.acquired ? 'success' : 'warning'"
                              size="x-small"
                              variant="tonal"
                            >
                              {{ competence.acquired ? 'Acquise' : 'En cours' }}
                            </v-chip>
                            <v-chip size="x-small" variant="outlined" color="grey">
                              {{ competence.niveau }}
                            </v-chip>
                            <v-btn
                              size="x-small"
                              variant="text"
                              color="primary"
                              append-icon="mdi-arrow-right"
                              @click="goToCourse(competence.courseId)"
                            >
                              Cours
                            </v-btn>
                          </div>
                        </template>
                      </v-list-item>
                      <v-divider v-if="index < items.length - 1" />
                    </template>
                  </v-list>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
          </div>
        </v-window-item>
      </v-window>
    </v-col>
  </v-row>
</template>

<style scoped>
.badge-card {
  transition: transform 0.2s ease;
}
.badge-card:hover {
  transform: translateY(-4px);
}
.badge-acquired {
  border: 2px solid rgb(var(--v-theme-success));
}
.badge-in-progress {
  border: 2px solid rgb(var(--v-theme-warning));
}
</style>
