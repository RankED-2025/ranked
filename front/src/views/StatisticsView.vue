<script setup lang="ts">
import { onMounted, ref } from 'vue'
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
      </div>
    </section>

    <section id="personnal"></section>
  </div>
</template>

<style scoped></style>
