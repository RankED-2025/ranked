<script setup lang="ts">
import type { RegistrationsOverTimePoint } from '@/types/component/chart/registrations-over-time.ts'
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import type { ChartData, ChartOptions } from 'chart.js'

type Props = {
  points: RegistrationsOverTimePoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'line'>>(() => ({
  labels: props.points.map((p) => p.week),
  datasets: [
    {
      data: props.points.map((p) => p.count),
      label: 'New registrations',
      fill: false,
      tension: 0.3,
    },
  ],
}))

const options: ChartOptions<'line'> = {
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
    <Line :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
