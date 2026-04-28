<script setup lang="ts">
import type { MostCompletedCourseSinglePoint } from '@/types/component/chart/most-completed-courses.ts'
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'

type Props = {
  points: MostCompletedCourseSinglePoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'bar'>>(function () {
  const data = props.points

  return {
    labels: data.map((d) => d.course.cours.titre),

    datasets: [
      {
        data: data.map((d) => d.percent),
        label: '%',
      },
    ],
  }
})

const options: ChartOptions<'bar'> = {
  responsive: true,
  plugins: {
    title: {
      text: 'WOW gros bouvier',
    },
  },
}
</script>

<template>
  <div>
    <Bar :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
