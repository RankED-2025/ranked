<script setup lang="ts">
import type { MyQuizScorePoint } from '@/types'
import { computed } from 'vue'
import type { ChartData, ChartOptions } from 'chart.js'
import { getRotatingColors } from '@/constants/chartColors.ts'
import AppChart from './AppChart.vue'

type Props = {
  points: MyQuizScorePoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'line'>>(() => {
  const { bg, border } = getRotatingColors(1)
  return {
    labels: props.points.map((p) => p.label),
    datasets: [
      {
        data: props.points.map((p) => p.points),
        label: 'Points',
        backgroundColor: bg[0],
        borderColor: border[0],
        fill: false,
        tension: 0.3,
      },
    ],
  }
})

const options: ChartOptions<'line'> = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
}
</script>

<template>
  <div>
    <AppChart type="line" :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
