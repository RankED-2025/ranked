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

const userStore = useUserStore()
const studentView = computed(() => isEleve(userStore.user?.roles ?? []))

const activeTab = ref<'global' | 'personal'>('global')

// — global state —
const mostCompletedCourses = ref<MostCompletedCourseSinglePoint[] | null>([])
const completionBySubject = ref<CompletionBySubjectPoint[]>([])
const activeStudentsPerClass = ref<ActiveStudentsPerClassPoint[]>([])
const badgeDistribution = ref<BadgeDistributionPoint[]>([])
const registrationsOverTime = ref<RegistrationsOverTimePoint[]>([])

// — personal state —
const myProgressions = ref<MyProgressionPoint[]>([])
const myCompetences = ref<MyCompetencePoint[]>([])
const myQuizScores = ref<MyQuizScorePoint[]>([])
const myBadges = ref<MyBadgePoint[]>([])
const myClassRank = ref<MyClassRank | null>(null)
const personalLoaded = ref(false)

const loadGlobal = () => {
  courseService.getTopCoursesByAvg(5).then((data) => {
    mostCompletedCourses.value = data.map((v) => ({ course: { ...v }, percent: v.average }))
  })
  statisticService.getCompletionBySubject().then((d) => (completionBySubject.value = d))
  statisticService.getActiveStudentsPerClass().then((d) => (activeStudentsPerClass.value = d))
  statisticService.getBadgeDistribution().then((d) => (badgeDistribution.value = d))
  statisticService.getRegistrationsOverTime().then((d) => (registrationsOverTime.value = d))
}

const loadPersonal = () => {
  if (personalLoaded.value) return
  personalLoaded.value = true
  statisticService.getMyProgressions().then((d) => (myProgressions.value = d))
  statisticService.getMyCompetences().then((d) => (myCompetences.value = d))
  statisticService.getMyQuizScores().then((d) => (myQuizScores.value = d))
  statisticService.getMyBadges().then((d) => (myBadges.value = d))
  statisticService.getMyClassRank().then((d) => (myClassRank.value = d)).catch(() => {})
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
  <v-row justify="center" no-gutters>
    <v-col cols="12" sm="9" md="6" class="px-4 px-sm-0 py-6">
    <v-tabs v-model="activeTab" class="mb-6">
      <v-tab value="global">Global</v-tab>
      <v-tab v-if="studentView" value="personal">Mes statistiques</v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <!-- ── GLOBAL ── -->
      <v-window-item value="global">
        <div class="d-flex flex-column ga-8">
          <div>
            <h2>Taux de complétion par matière</h2>
            <CompletionBySubjectChart v-if="completionBySubject.length" :points="completionBySubject" />
            <v-data-table
              v-if="completionBySubject.length"
              :headers="subjectTableHeaders"
              :items="subjectTableItems"
              hide-default-footer
              density="compact"
              class="mt-4"
            />
          </div>

          <div>
            <h2>Top 5 des cours les plus complétés</h2>
            <MostCompletedCoursesChart v-if="mostCompletedCourses" :points="mostCompletedCourses" />
            <v-data-table
              v-if="mostCompletedCourses && mostCompletedCourses.length"
              :headers="topCoursesTableHeaders"
              :items="topCoursesTableItems"
              hide-default-footer
              density="compact"
              class="mt-4"
            />
          </div>

          <div>
            <h2>Élèves actifs par classe</h2>
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
          </div>

          <div>
            <h2>Répartition des badges</h2>
            <BadgeDistributionChart v-if="badgeDistribution.length" :points="badgeDistribution" />
            <v-data-table
              v-if="badgeDistribution.length"
              :headers="badgeTableHeaders"
              :items="badgeTableItems"
              hide-default-footer
              density="compact"
              class="mt-4"
            />
          </div>

          <div>
            <h2>Nouvelles inscriptions dans le temps</h2>
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
          </div>
        </div>
      </v-window-item>

      <!-- ── PERSONAL ── -->
      <v-window-item v-if="studentView" value="personal">
        <div class="d-flex flex-column ga-8">
          <div>
            <h2>Ma progression par cours</h2>
            <MyProgressionChart v-if="myProgressions.length" :points="myProgressions" />
            <v-data-table
              v-if="myProgressions.length"
              :headers="myProgressionTableHeaders"
              :items="myProgressionTableItems"
              hide-default-footer
              density="compact"
              class="mt-4"
            />
          </div>

          <div>
            <h2>Mes compétences acquises</h2>
            <MyCompetencesChart v-if="myCompetences.length" :points="myCompetences" />
            <v-data-table
              v-if="myCompetences.length"
              :headers="myCompetencesTableHeaders"
              :items="myCompetencesTableItems"
              hide-default-footer
              density="compact"
              class="mt-4"
            />
          </div>

          <div>
            <h2>Historique de mes scores aux quiz</h2>
            <MyQuizScoresChart v-if="myQuizScores.length" :points="myQuizScores" />
            <v-data-table
              v-if="myQuizScores.length"
              :headers="myQuizTableHeaders"
              :items="myQuizTableItems"
              hide-default-footer
              density="compact"
              class="mt-4"
            />
          </div>

          <div>
            <h2>Mes badges obtenus</h2>
            <MyBadgesChart v-if="myBadges.length" :points="myBadges" />
            <v-data-table
              v-if="myBadges.length"
              :headers="myBadgesTableHeaders"
              :items="myBadgesTableItems"
              hide-default-footer
              density="compact"
              class="mt-4"
            />
          </div>

          <div v-if="myClassRank">
            <h2>Mon classement dans la classe</h2>
            <MyClassRankChart :rank="myClassRank" />
          </div>
        </div>
      </v-window-item>
    </v-window>
    </v-col>
  </v-row>
</template>

<style scoped></style>
