<script setup lang="ts">
import type { ActiveStudentsPerClassPoint } from '@/types'
import { computed } from 'vue'
import type { ChartData, ChartOptions } from 'chart.js'
import { getRotatingColors } from '@/constants/chartColors.ts'
import AppChart from './AppChart.vue'

type Props = {
  points: ActiveStudentsPerClassPoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'bar'>>(() => {
  const { bg, border } = getRotatingColors(props.points.length)
  return {
    labels: props.points.map((p) => p.classe),
    datasets: [
      {
        data: props.points.map((p) => p.count),
        label: 'Élèves actifs',
        backgroundColor: bg,
        borderColor: border,
        borderWidth: 1,
      },
    ],
  }
})

const options: ChartOptions<'bar'> = {
  responsive: true,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
}
</script>

<template>
  <div>
    <AppChart type="bar" :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
