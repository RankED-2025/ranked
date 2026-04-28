<script setup lang="ts">
import { onMounted } from 'vue'
import MostCompletedCoursesChart from '@components/chart/MostCompletedCoursesChart.vue'
import type { MostCompletedCourseSinglePoint } from '@/types/component/chart/most-completed-courses.ts'

const points: MostCompletedCourseSinglePoint[] = [

]

const updateMostCompletedCourses = async (): void => {
  const data = await
}

onMounted(() => {

})
</script>

<template>
  <div class="stats-view">
    <section id="global">
      <div>
        <h1>Top 5 Most Completed Courses</h1>

        <MostCompletedCoursesChart points="" />
      </div>
    </section>

    <section id="personnal"></section>
  </div>
</template>

<style scoped></style>
