<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useUserStore } from '@/stores/userStore'
import { isEleve } from '@/utils/roles'

import MostCompletedCoursesChart from '@components/chart/MostCompletedCoursesChart.vue'
import CompletionBySubjectChart from '@components/chart/CompletionBySubjectChart.vue'
import ActiveStudentsPerClassChart from '@components/chart/ActiveStudentsPerClassChart.vue'
import BadgeDistributionChart from '@components/chart/BadgeDistributionChart.vue'
import RegistrationsOverTimeChart from '@components/chart/RegistrationsOverTimeChart.vue'
import MyProgressionChart from '@components/chart/MyProgressionChart.vue'
import MyCompetencesChart from '@components/chart/MyCompetencesChart.vue'
import MyQuizScoresChart from '@components/chart/MyQuizScoresChart.vue'
import MyBadgesChart from '@components/chart/MyBadgesChart.vue'
import MyClassRankChart from '@components/chart/MyClassRankChart.vue'

import type {
  MostCompletedCourseSinglePoint,
  CompletionBySubjectPoint,
  ActiveStudentsPerClassPoint,
  BadgeDistributionPoint,
  RegistrationsOverTimePoint,
  MyProgressionPoint,
  MyCompetencePoint,
  MyQuizScorePoint,
  MyBadgePoint,
  MyClassRank,
} from '@/types'

import { courseService } from '@/services/courseService.ts'
import { statisticService } from '@/services/statisticService.ts'
import { useAsyncData } from '@/composables'

const userStore = useUserStore()
const studentView = computed(() => isEleve(userStore.user?.roles ?? []))

const activeTab = ref<'global' | 'personal'>('global')

// — global data —
const { data: mostCompletedCourses, loading: loadingMostCompleted, execute: fetchMostCompleted } =
  useAsyncData(
    async () => {
      const d = await courseService.getTopCoursesByAvg(5)
      return d.map((v) => ({ course: { ...v }, percent: v.average }))
    },
    [] as MostCompletedCourseSinglePoint[],
    true,
  )

const { data: completionBySubject, loading: loadingCompletionBySubject, execute: fetchCompletionBySubject } =
  useAsyncData(() => statisticService.getCompletionBySubject(), [] as CompletionBySubjectPoint[], true)

const { data: activeStudentsPerClass, loading: loadingActiveStudents, execute: fetchActiveStudents } =
  useAsyncData(() => statisticService.getActiveStudentsPerClass(), [] as ActiveStudentsPerClassPoint[], true)

const { data: badgeDistribution, loading: loadingBadgeDistribution, execute: fetchBadgeDistribution } =
  useAsyncData(() => statisticService.getBadgeDistribution(), [] as BadgeDistributionPoint[], true)

const { data: registrationsOverTime, loading: loadingRegistrations, execute: fetchRegistrations } =
  useAsyncData(() => statisticService.getRegistrationsOverTime(), [] as RegistrationsOverTimePoint[], true)

// — personal data —
const personalLoaded = ref(false)

const { data: myProgressions, loading: loadingMyProgressions, execute: fetchMyProgressions } =
  useAsyncData(() => statisticService.getMyProgressions(), [] as MyProgressionPoint[])

const { data: myCompetences, loading: loadingMyCompetences, execute: fetchMyCompetences } =
  useAsyncData(() => statisticService.getMyCompetences(), [] as MyCompetencePoint[])

const { data: myQuizScores, loading: loadingMyQuizScores, execute: fetchMyQuizScores } =
  useAsyncData(() => statisticService.getMyQuizScores(), [] as MyQuizScorePoint[])

const { data: myBadges, loading: loadingMyBadges, execute: fetchMyBadges } =
  useAsyncData(() => statisticService.getMyBadges(), [] as MyBadgePoint[])

const { data: myClassRank, loading: loadingMyClassRank, execute: fetchMyClassRank } =
  useAsyncData(() => statisticService.getMyClassRank(), null as MyClassRank | null)

const loadGlobal = () => {
  fetchMostCompleted()
  fetchCompletionBySubject()
  fetchActiveStudents()
  fetchBadgeDistribution()
  fetchRegistrations()
}

const loadPersonal = () => {
  if (personalLoaded.value) return
  personalLoaded.value = true
  fetchMyProgressions()
  fetchMyCompetences()
  fetchMyQuizScores()
  fetchMyBadges()
  fetchMyClassRank()
}

watch(activeTab, (tab) => {
  if (tab === 'personal') loadPersonal()
})

onMounted(() => {
  loadGlobal()
})

// — global tables —
const subjectTableHeaders = [
  { title: 'Matière', key: 'subject' },
  { title: 'Complétion moy. (%)', key: 'average' },
]
const subjectTableItems = computed(() =>
  completionBySubject.value.map((p) => ({ subject: p.subject, average: p.average.toFixed(1) })),
)

const topCoursesTableHeaders = [
  { title: 'Cours', key: 'title' },
  { title: 'Complétion (%)', key: 'percent' },
]
const topCoursesTableItems = computed(() =>
  (mostCompletedCourses.value ?? []).map((p) => ({
    title: p.course.cours.titre,
    percent: p.percent.toFixed(1),
  })),
)

const activeStudentsTableHeaders = [
  { title: 'Classe', key: 'classe' },
  { title: 'Élèves actifs', key: 'count' },
]
const activeStudentsTableItems = computed(() =>
  activeStudentsPerClass.value.map((p) => ({ classe: p.classe, count: p.count })),
)

const badgeTableHeaders = [
  { title: 'Type de badge', key: 'type' },
  { title: 'Nombre', key: 'count' },
]
const badgeTableItems = computed(() =>
  badgeDistribution.value.map((p) => ({ type: p.type, count: p.count })),
)

const registrationsTableHeaders = [
  { title: 'Semaine', key: 'week' },
  { title: 'Nouvelles inscriptions', key: 'count' },
]
const registrationsTableItems = computed(() =>
  registrationsOverTime.value.map((p) => ({ week: p.week, count: p.count })),
)

// — personal tables —
const myProgressionTableHeaders = [
  { title: 'Cours', key: 'title' },
  { title: 'Complétion (%)', key: 'percentage' },
]
const myProgressionTableItems = computed(() =>
  myProgressions.value.map((p) => ({ title: p.title, percentage: p.percentage })),
)

const myCompetencesTableHeaders = [
  { title: 'Matière', key: 'matiere' },
  { title: 'Acquises (%)', key: 'percentage' },
]
const myCompetencesTableItems = computed(() =>
  myCompetences.value.map((p) => ({ matiere: p.matiere, percentage: p.percentage })),
)

const myQuizTableHeaders = [
  { title: 'Quiz', key: 'label' },
  { title: 'Points', key: 'points' },
]
const myQuizTableItems = computed(() =>
  myQuizScores.value.map((p) => ({ label: p.label, points: p.points })),
)

const myBadgesTableHeaders = [
  { title: 'Type de badge', key: 'type' },
  { title: 'Nombre', key: 'count' },
]
const myBadgesTableItems = computed(() =>
  myBadges.value.map((p) => ({ type: p.type, count: p.count })),
)
</script>

<template>
  <v-container class="py-8">
    <h1 class="text-h4 font-weight-bold mb-6">Statistiques</h1>

    <v-tabs v-model="activeTab" class="mb-6">
      <v-tab value="global">Global</v-tab>
      <v-tab v-if="studentView" value="personal">Mes statistiques</v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <!-- ── GLOBAL ── -->
      <v-window-item value="global">
        <v-row>
          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Taux de complétion par matière</h2>
              <v-skeleton-loader v-if="loadingCompletionBySubject" type="image" />
              <template v-else>
                <CompletionBySubjectChart v-if="completionBySubject.length" :points="completionBySubject" />
                <v-data-table
                  v-if="completionBySubject.length"
                  :headers="subjectTableHeaders"
                  :items="subjectTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Top 5 des cours les plus complétés</h2>
              <v-skeleton-loader v-if="loadingMostCompleted" type="image" />
              <template v-else>
                <MostCompletedCoursesChart v-if="mostCompletedCourses" :points="mostCompletedCourses" />
                <v-data-table
                  v-if="mostCompletedCourses && mostCompletedCourses.length"
                  :headers="topCoursesTableHeaders"
                  :items="topCoursesTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Élèves actifs par classe</h2>
              <v-skeleton-loader v-if="loadingActiveStudents" type="image" />
              <template v-else>
                <ActiveStudentsPerClassChart
                  v-if="activeStudentsPerClass.length"
                  :points="activeStudentsPerClass"
                />
                <v-data-table
                  v-if="activeStudentsPerClass.length"
                  :headers="activeStudentsTableHeaders"
                  :items="activeStudentsTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Répartition des badges</h2>
              <v-skeleton-loader v-if="loadingBadgeDistribution" type="image" />
              <template v-else>
                <BadgeDistributionChart v-if="badgeDistribution.length" :points="badgeDistribution" />
                <v-data-table
                  v-if="badgeDistribution.length"
                  :headers="badgeTableHeaders"
                  :items="badgeTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12">
            <v-card elevation="2" rounded="lg" class="pa-4">
              <h2 class="text-h6 font-weight-bold mb-4">Nouvelles inscriptions dans le temps</h2>
              <v-skeleton-loader v-if="loadingRegistrations" type="image" />
              <template v-else>
                <RegistrationsOverTimeChart
                  v-if="registrationsOverTime.length"
                  :points="registrationsOverTime"
                />
                <v-data-table
                  v-if="registrationsOverTime.length"
                  :headers="registrationsTableHeaders"
                  :items="registrationsTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>
        </v-row>
      </v-window-item>

      <!-- ── PERSONAL ── -->
      <v-window-item v-if="studentView" value="personal">
        <v-row>
          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Ma progression par cours</h2>
              <v-skeleton-loader v-if="loadingMyProgressions" type="image" />
              <template v-else>
                <MyProgressionChart v-if="myProgressions.length" :points="myProgressions" />
                <v-data-table
                  v-if="myProgressions.length"
                  :headers="myProgressionTableHeaders"
                  :items="myProgressionTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Mes compétences acquises</h2>
              <v-skeleton-loader v-if="loadingMyCompetences" type="image" />
              <template v-else>
                <MyCompetencesChart v-if="myCompetences.length" :points="myCompetences" />
                <v-data-table
                  v-if="myCompetences.length"
                  :headers="myCompetencesTableHeaders"
                  :items="myCompetencesTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Historique de mes scores aux quiz</h2>
              <v-skeleton-loader v-if="loadingMyQuizScores" type="image" />
              <template v-else>
                <MyQuizScoresChart v-if="myQuizScores.length" :points="myQuizScores" />
                <v-data-table
                  v-if="myQuizScores.length"
                  :headers="myQuizTableHeaders"
                  :items="myQuizTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card elevation="2" rounded="lg" class="pa-4 h-100">
              <h2 class="text-h6 font-weight-bold mb-4">Mes badges obtenus</h2>
              <v-skeleton-loader v-if="loadingMyBadges" type="image" />
              <template v-else>
                <MyBadgesChart v-if="myBadges.length" :points="myBadges" />
                <v-data-table
                  v-if="myBadges.length"
                  :headers="myBadgesTableHeaders"
                  :items="myBadgesTableItems"
                  hide-default-footer
                  density="compact"
                  class="mt-4"
                />
                <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
              </template>
            </v-card>
          </v-col>

          <v-col cols="12">
            <v-card elevation="2" rounded="lg" class="pa-4">
              <h2 class="text-h6 font-weight-bold mb-4">Mon classement dans la classe</h2>
              <v-skeleton-loader v-if="loadingMyClassRank" type="image" />
              <MyClassRankChart v-else-if="myClassRank" :rank="myClassRank" />
              <p v-else class="text-grey text-body-2 text-center py-4">Aucune donnée disponible</p>
            </v-card>
          </v-col>
        </v-row>
      </v-window-item>
    </v-window>
  </v-container>
</template>

<style scoped></style>
