<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import MostCompletedCoursesChart from '@components/chart/MostCompletedCoursesChart.vue'
import type { MostCompletedCourseSinglePoint } from '@/types/component/chart/most-completed-courses.ts'
import { courseService } from '@/services/courseService.ts'

const mostCompletedCourses = ref<MostCompletedCourseSinglePoint[] | null>([])

const updateMostCompletedCourses = async (): Promise<void> => {
  const data = await courseService.getTopCoursesByAvg(5)

  mostCompletedCourses.value = data.map((v) => ({
    course: {
      ...v,
    },
    percent: v.average,
  }))
}

const tableHeaders = [
  { title: 'Course', key: 'title' },
  { title: 'Completion (%)', key: 'percent' },
]

const tableItems = computed(() =>
  (mostCompletedCourses.value ?? []).map((p) => ({
    title: p.course.cours.titre,
    percent: p.percent.toFixed(1),
  })),
)

onMounted(() => {
  updateMostCompletedCourses()
})
</script>

<template>
  <div class="stats-view">
    <section id="global">
      <div>
        <h1>Top 5 Most Completed Courses</h1>

        <MostCompletedCoursesChart
          v-if="mostCompletedCourses"
          :points="mostCompletedCourses"
        />

        <v-data-table
          v-if="mostCompletedCourses && mostCompletedCourses.length"
          :headers="tableHeaders"
          :items="tableItems"
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
