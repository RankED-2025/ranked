<script setup lang="ts">
import type { RegistrationsOverTimePoint } from '@/types'
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'
import { getRotatingColors } from '@/constants/chartColors.ts'

type Props = {
  points: RegistrationsOverTimePoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'line'>>(() => {
  const { bg, border } = getRotatingColors(1)
  return {
    labels: props.points.map((p) => p.week),
    datasets: [
      {
        data: props.points.map((p) => p.count),
        label: 'Nouvelles inscriptions',
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
    <Line :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
