<script setup lang="ts">
import type { CompletionBySubjectPoint } from '@/types'
import { computed } from 'vue'
import type { ChartData, ChartOptions } from 'chart.js'
import { getRotatingColors } from '@/constants/chartColors.ts'
import AppChart from './AppChart.vue'

type Props = {
  points: CompletionBySubjectPoint[]
}

const props = defineProps<Props>()

const computedData = computed<ChartData<'doughnut'>>(() => {
  const { bg, border } = getRotatingColors(props.points.length)
  return {
    labels: props.points.map((p) => p.subject),
    datasets: [
      {
        data: props.points.map((p) => p.average),
        label: 'Complétion moy. (%)',
        backgroundColor: bg,
        borderColor: border,
        borderWidth: 1,
      },
    ],
  }
})

const options: ChartOptions<'doughnut'> = {
  responsive: true,
  plugins: { legend: { position: 'right' } },
}
</script>

<template>
  <div>
    <AppChart type="doughnut" :data="computedData" :options="options" />
  </div>
</template>

<style scoped></style>
