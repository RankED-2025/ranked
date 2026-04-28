<script setup lang="ts">
import type { ActiveStudentsPerClassPoint } from '@/types/component/chart/active-students-per-class.ts'
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'

type Props = {
  points: ActiveStudentsPerClassPoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'bar'>>(() => ({
  labels: props.points.map((p) => p.classe),
  datasets: [
    {
      data: props.points.map((p) => p.count),
      label: 'Active students',
    },
  ],
}))

const options: ChartOptions<'bar'> = {
  responsive: true,
  plugins: {
    legend: { display: false },
  },
  scales: {
    y: { beginAtZero: true, ticks: { stepSize: 1 } },
  },
}
</script>

<template>
  <div>
    <Bar :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
