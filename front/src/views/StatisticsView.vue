<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import MostCompletedCoursesChart from '@components/chart/MostCompletedCoursesChart.vue'
import CompletionBySubjectChart from '@components/chart/CompletionBySubjectChart.vue'
import ActiveStudentsPerClassChart from '@components/chart/ActiveStudentsPerClassChart.vue'
import BadgeDistributionChart from '@components/chart/BadgeDistributionChart.vue'
import RegistrationsOverTimeChart from '@components/chart/RegistrationsOverTimeChart.vue'
import type { MostCompletedCourseSinglePoint } from '@/types/component/chart/most-completed-courses.ts'
import type { CompletionBySubjectPoint } from '@/types/component/chart/completion-by-subject.ts'
import type { ActiveStudentsPerClassPoint } from '@/types/component/chart/active-students-per-class.ts'
import type { BadgeDistributionPoint } from '@/types/component/chart/badge-distribution.ts'
import type { RegistrationsOverTimePoint } from '@/types/component/chart/registrations-over-time.ts'
import { courseService } from '@/services/courseService.ts'
import { statisticService } from '@/services/statisticService.ts'

const mostCompletedCourses = ref<MostCompletedCourseSinglePoint[] | null>([])
const completionBySubject = ref<CompletionBySubjectPoint[]>([])
const activeStudentsPerClass = ref<ActiveStudentsPerClassPoint[]>([])
const badgeDistribution = ref<BadgeDistributionPoint[]>([])
const registrationsOverTime = ref<RegistrationsOverTimePoint[]>([])

const updateMostCompletedCourses = async (): Promise<void> => {
  const data = await courseService.getTopCoursesByAvg(5)
  mostCompletedCourses.value = data.map((v) => ({ course: { ...v }, percent: v.average }))
}

onMounted(() => {
  updateMostCompletedCourses()
  statisticService.getCompletionBySubject().then((d) => (completionBySubject.value = d))
  statisticService.getActiveStudentsPerClass().then((d) => (activeStudentsPerClass.value = d))
  statisticService.getBadgeDistribution().then((d) => (badgeDistribution.value = d))
  statisticService.getRegistrationsOverTime().then((d) => (registrationsOverTime.value = d))
})

// — table definitions —

const subjectTableHeaders = [
  { title: 'Subject', key: 'subject' },
  { title: 'Avg Completion (%)', key: 'average' },
]
const subjectTableItems = computed(() =>
  completionBySubject.value.map((p) => ({ subject: p.subject, average: p.average.toFixed(1) })),
)

const topCoursesTableHeaders = [
  { title: 'Course', key: 'title' },
  { title: 'Completion (%)', key: 'percent' },
]
const topCoursesTableItems = computed(() =>
  (mostCompletedCourses.value ?? []).map((p) => ({
    title: p.course.cours.titre,
    percent: p.percent.toFixed(1),
  })),
)

const activeStudentsTableHeaders = [
  { title: 'Class', key: 'classe' },
  { title: 'Active students', key: 'count' },
]
const activeStudentsTableItems = computed(() =>
  activeStudentsPerClass.value.map((p) => ({ classe: p.classe, count: p.count })),
)

const badgeTableHeaders = [
  { title: 'Badge type', key: 'type' },
  { title: 'Count', key: 'count' },
]
const badgeTableItems = computed(() =>
  badgeDistribution.value.map((p) => ({ type: p.type, count: p.count })),
)

const registrationsTableHeaders = [
  { title: 'Week', key: 'week' },
  { title: 'New registrations', key: 'count' },
]
const registrationsTableItems = computed(() =>
  registrationsOverTime.value.map((p) => ({ week: p.week, count: p.count })),
)
</script>

<template>
  <div class="stats-view">
    <section id="global">
      <div>
        <h2>Course Completion Rate by Subject</h2>
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
        <h2>Top 5 Most Completed Courses</h2>
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
        <h2>Active Students per Class</h2>
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
        <h2>Badge Distribution</h2>
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
        <h2>New Registrations Over Time</h2>
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
    </section>

    <section id="personnal"></section>
  </div>
</template>

<style scoped></style>
